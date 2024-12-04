<?php
/**
 * Course Model
 *
 * @package Tutor\Models
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.6
 */

namespace Tutor\Models;

use TUTOR\Course;
use Tutor\Helpers\QueryHelper;

/**
 * CourseModel Class
 *
 * @since 2.0.6
 */
class CourseModel {
	/**
	 * WordPress course type name
	 *
	 * @var string
	 */
	const POST_TYPE = 'courses';

	const STATUS_PUBLISH    = 'publish';
	const STATUS_DRAFT      = 'draft';
	const STATUS_AUTO_DRAFT = 'auto-draft';
	const STATUS_PENDING    = 'pending';
	const STATUS_PRIVATE    = 'private';
	const STATUS_FUTURE     = 'future';
	const STATUS_TRASH      = 'trash';

	/**
	 * Course completion modes
	 */
	const MODE_FLEXIBLE = 'flexible';
	const MODE_STRICT   = 'strict';

	/**
	 * Course mapped with the product using this meta key
	 *
	 * @var string
	 */
	const WC_PRODUCT_META_KEY = '_tutor_course_product_id';

	/**
	 * Course attachment/downloadable resources meta key
	 *
	 * @var string
	 */
	const ATTACHMENT_META_KEY = '_tutor_attachments';

	/**
	 * Course benefits meta key
	 *
	 * @var string
	 */
	const BENEFITS_META_KEY = '_tutor_course_benefits';

	/**
	 * Get available status list.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public static function get_status_list() {
		return array(
			self::STATUS_DRAFT,
			self::STATUS_AUTO_DRAFT,
			self::STATUS_PUBLISH,
			self::STATUS_PRIVATE,
			self::STATUS_FUTURE,
			self::STATUS_PENDING,
			self::STATUS_TRASH,
		);
	}

	/**
	 * Course record count
	 *
	 * @since 2.0.7
	 *
	 * @param string $status course status.
	 * @return int
	 */
	public static function count( $status = self::STATUS_PUBLISH ) {
		$count_obj = wp_count_posts( self::POST_TYPE );
		if ( 'all' === $status ) {
			return array_sum( (array) $count_obj );
		}

		return (int) $count_obj->{$status};
	}

	/**
	 * Get courses
	 *
	 * @since 1.0.0
	 *
	 * @param array $excludes   exclude course ids.
	 * @param array $post_status post status array.
	 *
	 * @return array|null|object
	 */
	public static function get_courses( $excludes = array(), $post_status = array( 'publish' ) ) {
		global $wpdb;

		$excludes      = (array) $excludes;
		$exclude_query = '';

		if ( count( $excludes ) ) {
			$exclude_query = implode( "','", $excludes );
		}

		$post_status = array_map(
			function ( $element ) {
				return "'" . $element . "'";
			},
			$post_status
		);

		$post_status      = implode( ',', $post_status );
		$course_post_type = tutor()->course_post_type;

		//phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$query = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID,
					post_author,
					post_title,
					post_name,
					post_status,
					menu_order
			FROM 	{$wpdb->posts}
			WHERE 	post_status IN ({$post_status})
					AND ID NOT IN('$exclude_query')
					AND post_type = %s;
			",
				$course_post_type
			)
		);
		//phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return $query;
	}

	/**
	 * Get courses using provided args
	 *
	 * If user is not admin then it will return only current user's post
	 *
	 * @since 3.0.0
	 *
	 * @param array $args Args.
	 *
	 * @return \WP_Query
	 */
	public static function get_courses_by_args( array $args = array() ) {

		$default_args = array(
			'post_type'      => tutor()->course_post_type,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		);

		if ( ! current_user_can( 'manage_options' ) ) {
			$default_args['author'] = get_current_user_id();
		}

		$args = wp_parse_args( $args, $default_args );

		return new \WP_Query( $args );
	}

	/**
	 * Get course count by instructor
	 *
	 * @since 1.0.0
	 *
	 * @param int $instructor_id instructor ID.
	 *
	 * @return null|string
	 */
	public static function get_course_count_by_instructor( $instructor_id ) {
		global $wpdb;

		$course_post_type = tutor()->course_post_type;

		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(ID)
			FROM 	{$wpdb->posts}
					INNER JOIN {$wpdb->usermeta}
							ON user_id = %d
							AND meta_key = %s
							AND meta_value = ID
			WHERE 	post_status = %s
					AND post_type = %s;
			",
				$instructor_id,
				'_tutor_instructor_course_id',
				'publish',
				$course_post_type
			)
		);

		return $count;
	}

	/**
	 * Get course by quiz
	 *
	 * @since 1.0.0
	 *
	 * @param int $quiz_id quiz id.
	 *
	 * @return array|bool|null|object|void
	 */
	public static function get_course_by_quiz( $quiz_id ) {
		$quiz_id = tutils()->get_post_id( $quiz_id );
		$post    = get_post( $quiz_id );

		if ( $post ) {
			$course = get_post( $post->post_parent );
			if ( $course ) {
				if ( tutor()->course_post_type !== $course->post_type ) {
					$course = get_post( $course->post_parent );
				}
				return $course;
			}
		}

		return false;
	}

	/**
	 * Get courses by a instructor
	 *
	 * @since 1.0.0
	 *
	 * @param integer      $instructor_id instructor id.
	 * @param array|string $post_status post status.
	 * @param integer      $offset offset.
	 * @param integer      $limit limit.
	 * @param boolean      $count_only count or not.
	 *
	 * @return array|null|object
	 */
	public static function get_courses_by_instructor( $instructor_id = 0, $post_status = array( 'publish' ), int $offset = 0, int $limit = PHP_INT_MAX, $count_only = false ) {
		global $wpdb;
		$offset           = sanitize_text_field( $offset );
		$limit            = sanitize_text_field( $limit );
		$instructor_id    = tutils()->get_user_id( $instructor_id );
		$course_post_type = tutor()->course_post_type;

		if ( empty( $post_status ) || 'any' == $post_status ) {
			$where_post_status = '';
		} else {
			! is_array( $post_status ) ? $post_status = array( $post_status ) : 0;
			$statuses                                 = "'" . implode( "','", $post_status ) . "'";
			$where_post_status                        = "AND $wpdb->posts.post_status IN({$statuses}) ";
		}

		$select_col   = $count_only ? " COUNT(DISTINCT $wpdb->posts.ID) " : " $wpdb->posts.* ";
		$limit_offset = $count_only ? '' : " LIMIT $offset, $limit ";

		//phpcs:disable
		$query = $wpdb->prepare(
			"SELECT $select_col
			FROM 	$wpdb->posts
			LEFT JOIN {$wpdb->usermeta}
					ON $wpdb->usermeta.user_id = %d
					AND $wpdb->usermeta.meta_key = %s
					AND $wpdb->usermeta.meta_value = $wpdb->posts.ID
			WHERE	1 = 1 {$where_post_status}
				AND $wpdb->posts.post_type = %s
				AND ($wpdb->posts.post_author = %d OR $wpdb->usermeta.user_id = %d)
			ORDER BY $wpdb->posts.post_date DESC $limit_offset",
			$instructor_id,
			'_tutor_instructor_course_id',
			$course_post_type,
			$instructor_id,
			$instructor_id
		);
		//phpcs:enable

		//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $count_only ? $wpdb->get_var( $query ) : $wpdb->get_results( $query, OBJECT );
	}

	/**
	 * Get courses for instructors
	 *
	 * @since 1.0.0
	 *
	 * @param int $instructor_id    Instructor ID.
	 * @return array|null|object
	 */
	public function get_courses_for_instructors( $instructor_id = 0 ) {
		$instructor_id    = tutor_utils()->get_user_id( $instructor_id );
		$course_post_type = tutor()->course_post_type;

		$courses = get_posts(
			array(
				'post_type'      => $course_post_type,
				'author'         => $instructor_id,
				'post_status'    => array( 'publish', 'pending' ),
				'posts_per_page' => 5,
			)
		);

		return $courses;
	}

	/**
	 * Check a user is main instructor of a course
	 *
	 * @since 2.1.6
	 *
	 * @param integer $course_id course id.
	 * @param integer $user_id instructor id ( optional ) default: current user id.
	 *
	 * @return boolean
	 */
	public static function is_main_instructor( $course_id, $user_id = 0 ) {
		$course  = get_post( $course_id );
		$user_id = tutor_utils()->get_user_id( $user_id );

		if ( ! $course || self::POST_TYPE !== $course->post_type || $user_id !== (int) $course->post_author ) {
			return false;
		}

		return true;
	}

	/**
	 * Mark the course as completed
	 *
	 * @since 2.0.7
	 *
	 * @param int $course_id    course id which is completed.
	 * @param int $user_id      student id who completed the course.
	 *
	 * @return bool
	 */
	public static function mark_course_as_completed( $course_id, $user_id ) {
		if ( ! $course_id || ! $user_id ) {
			return false;
		}

		do_action( 'tutor_course_complete_before', $course_id );

		/**
		 * Marking course completed at Comment.
		 */
		global $wpdb;

		$date = date( 'Y-m-d H:i:s', tutor_time() ); //phpcs:ignore

		// Making sure that, hash is unique.
		do {
			$hash     = substr( md5( wp_generate_password( 32 ) . $date . $course_id . $user_id ), 0, 16 );
			$has_hash = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(comment_ID) from {$wpdb->comments}
				    WHERE comment_agent = 'TutorLMSPlugin' AND comment_type = 'course_completed' AND comment_content = %s ",
					$hash
				)
			);

		} while ( $has_hash > 0 );

		$data = array(
			'comment_post_ID'  => $course_id,
			'comment_author'   => $user_id,
			'comment_date'     => $date,
			'comment_date_gmt' => get_gmt_from_date( $date ),
			'comment_content'  => $hash, // Identification Hash.
			'comment_approved' => 'approved',
			'comment_agent'    => 'TutorLMSPlugin',
			'comment_type'     => 'course_completed',
			'user_id'          => $user_id,
		);

		$wpdb->insert( $wpdb->comments, $data );

		do_action( 'tutor_course_complete_after', $course_id, $user_id );

		return true;
	}

	/**
	 * Delete a course by ID
	 *
	 * @since 2.0.9
	 *
	 * @param int $post_id  course id that need to delete.
	 * @return bool
	 */
	public static function delete_course( $post_id ) {
		if ( get_post_type( $post_id ) !== tutor()->course_post_type ) {
			return false;
		}

		wp_delete_post( $post_id, true );
		return true;
	}

	/**
	 * Get post ids by post type and parent_id
	 *
	 * @since 1.6.6
	 *
	 * @param string  $post_type post type.
	 * @param integer $post_parent post parent ID.
	 *
	 * @return array
	 */
	private function get_post_ids( $post_type, $post_parent ) {
		$args = array(
			'fields'         => 'ids',
			'post_type'      => $post_type,
			'post_parent'    => $post_parent,
			'post_status'    => 'any',
			'posts_per_page' => -1,
		);
		return get_posts( $args );
	}

	/**
	 * Delete course data when permanently deleting a course.
	 *
	 * @since 1.6.6
	 * @since 2.0.9 updated
	 *
	 * @param integer $post_id post ID.
	 * @return bool
	 */
	public function delete_course_data( $post_id ) {
		$course_post_type = tutor()->course_post_type;
		if ( get_post_type( $post_id ) !== $course_post_type ) {
			return false;
		}

		do_action( 'tutor_before_delete_course_content', $post_id, 0 );

		global $wpdb;

		$lesson_post_type     = tutor()->lesson_post_type;
		$assignment_post_type = tutor()->assignment_post_type;
		$quiz_post_type       = tutor()->quiz_post_type;

		$topic_ids = $this->get_post_ids( 'topics', $post_id );

		// Course > Topic > ( Lesson | Quiz | Assignment ).
		if ( ! empty( $topic_ids ) ) {
			foreach ( $topic_ids as $topic_id ) {
				$content_post_type = array( $lesson_post_type, $assignment_post_type, $quiz_post_type );
				$topic_content_ids = $this->get_post_ids( $content_post_type, $topic_id );

				foreach ( $topic_content_ids as $content_id ) {
					/**
					 * Delete Quiz data
					 */
					if ( get_post_type( $content_id ) === 'tutor_quiz' ) {
						$wpdb->delete( $wpdb->prefix . 'tutor_quiz_attempts', array( 'quiz_id' => $content_id ) );
						$wpdb->delete( $wpdb->prefix . 'tutor_quiz_attempt_answers', array( 'quiz_id' => $content_id ) );

						do_action( 'tutor_before_delete_quiz_content', $content_id, null );

						$questions_ids = $wpdb->get_col( $wpdb->prepare( "SELECT question_id FROM {$wpdb->prefix}tutor_quiz_questions WHERE quiz_id = %d ", $content_id ) );
						if ( is_array( $questions_ids ) && count( $questions_ids ) ) {
							$in_question_ids = "'" . implode( "','", $questions_ids ) . "'";
							//phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
							$wpdb->query( "DELETE FROM {$wpdb->prefix}tutor_quiz_question_answers WHERE belongs_question_id IN({$in_question_ids}) " );
						}
						$wpdb->delete( $wpdb->prefix . 'tutor_quiz_questions', array( 'quiz_id' => $content_id ) );
					}

					/**
					 * Delete assignment data ( Assignments, Assignment Submit, Assignment Evalutation )
					 *
					 * @since 2.0.9
					 */
					if ( get_post_type( $content_id ) === $assignment_post_type ) {
						QueryHelper::delete_comment_with_meta(
							array(
								'comment_type'    => 'tutor_assignment',
								'comment_post_ID' => $content_id,
							)
						);
					}

					wp_delete_post( $content_id, true );

				}

				// Delete zoom meeting.
				$wpdb->delete(
					$wpdb->posts,
					array(
						'post_parent' => $topic_id,
						'post_type'   => 'tutor_zoom_meeting',
					)
				);

				/**
				 * Delete Google Meet Record Related to Course Topic
				 *
				 * @since 2.1.0
				 */
				$wpdb->delete(
					$wpdb->posts,
					array(
						'post_parent' => $topic_id,
						'post_type'   => 'tutor-google-meet',
					)
				);

				wp_delete_post( $topic_id, true );
			}
		}

		$child_post_ids = $this->get_post_ids( array( 'tutor_announcements', 'tutor_enrolled', 'tutor_zoom_meeting', 'tutor-google-meet' ), $post_id );
		if ( ! empty( $child_post_ids ) ) {
			foreach ( $child_post_ids as $child_post_id ) {
				wp_delete_post( $child_post_id, true );
			}
		}

		/**
		 * Delete earning, gradebook result, course complete data
		 *
		 * @since 2.0.9
		 */
		$wpdb->delete( $wpdb->prefix . 'tutor_earnings', array( 'course_id' => $post_id ) );
		$wpdb->delete( $wpdb->prefix . 'tutor_gradebooks_results', array( 'course_id' => $post_id ) );
		$wpdb->delete(
			$wpdb->comments,
			array(
				'comment_type'    => 'course_completed',
				'comment_post_ID' => $post_id,
			)
		);

		/**
		 * Delete onsite notification record & _tutor_instructor_course_id user meta
		 *
		 * @since 2.1.0
		 */
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}tutor_notifications WHERE post_id=%d AND type IN ('Announcements','Q&A','Enrollments')", $post_id ) );
		$wpdb->delete(
			$wpdb->usermeta,
			array(
				'meta_key'   => '_tutor_instructor_course_id',
				'meta_value' => $post_id,
			)
		);

		/**
		 * Delete Course rating and review
		 *
		 * @since 2.0.9
		 */
		QueryHelper::delete_comment_with_meta(
			array(
				'comment_type'    => 'tutor_course_rating',
				'comment_post_ID' => $post_id,
			)
		);

		/**
		 * Delete Q&A and its status ( read, replied etc )
		 *
		 * @since 2.0.9
		 */
		QueryHelper::delete_comment_with_meta(
			array(
				'comment_type'    => 'tutor_q_and_a',
				'comment_post_ID' => $post_id,
			)
		);

		/**
		 * Delete caches
		 */
		$attempt_cache = new \Tutor\Cache\QuizAttempts();
		if ( $attempt_cache->has_cache() ) {
			$attempt_cache->delete_cache();
		}

		return true;
	}


	/**
	 * Get paid courses
	 *
	 * To identify course is connected with any product
	 * like WC Product or EDD product meta key will be used
	 *
	 * @since 2.2.0
	 *
	 * @since 3.0.0
	 *
	 * Meta key removed and default meta query updated
	 *
	 * @since 3.0.1
	 * Course::COURSE_PRICE_META meta key exists clause added
	 *
	 * @param array $args wp_query args.
	 *
	 * @return \WP_Query
	 */
	public static function get_paid_courses( array $args = array() ) {
		$current_user = wp_get_current_user();

		$default_args = array(
			'post_type'      => tutor()->course_post_type,
			'posts_per_page' => -1,
			'offset'         => 0,
			'post_status'    => 'publish',
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key'     => Course::COURSE_PRICE_TYPE_META,
					'value'   => Course::PRICE_TYPE_SUBSCRIPTION,
					'compare' => '!=',
				),
				array(
					'key'     => Course::COURSE_PRICE_META,
					'compare' => 'EXISTS',
				),
			),
		);

		// Check if the current user is an admin.
		if ( ! current_user_can( 'administrator' ) ) {
			$args['author'] = $current_user->ID;
		}

		$args = wp_parse_args( $args, $default_args );
		return new \WP_Query( $args );

	}

	/**
	 * Check the course is completeable or not
	 *
	 * @since 2.4.0
	 *
	 * @param int $course_id course id.
	 * @param int $user_id user id.
	 *
	 * @return boolean
	 */
	public static function can_complete_course( $course_id, $user_id ) {
		$mode = tutor_utils()->get_option( 'course_completion_process' );
		if ( self::MODE_FLEXIBLE === $mode ) {
			return true;
		}

		if ( self::MODE_STRICT === $mode ) {
			$completed_lesson = tutor_utils()->get_completed_lesson_count_by_course( $course_id, $user_id );
			$lesson_count     = tutor_utils()->get_lesson_count_by_course( $course_id, $user_id );

			if ( $completed_lesson < $lesson_count ) {
				return false;
			}

			$quizzes     = array();
			$assignments = array();

			$course_contents = tutor_utils()->get_course_contents_by_id( $course_id );
			if ( tutor_utils()->count( $course_contents ) ) {
				foreach ( $course_contents as $content ) {
					if ( 'tutor_quiz' === $content->post_type ) {
						$quizzes[] = $content;
					}
					if ( 'tutor_assignments' === $content->post_type ) {
						$assignments[] = $content;
					}
				}
			}

			foreach ( $quizzes as $row ) {
				$result = QuizModel::get_quiz_result( $row->ID );
				if ( 'pass' !== $result ) {
					return false;
				}
			}

			if ( tutor()->has_pro ) {
				foreach ( $assignments as $row ) {
					$result = \TUTOR_ASSIGNMENTS\Assignments::get_assignment_result( $row->ID, $user_id );
					if ( 'pass' !== $result ) {
						return false;
					}
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Check a course can be auto complete by an enrolled student.
	 *
	 * @since 2.4.0
	 *
	 * @param int $course_id course id.
	 * @param int $user_id user id.
	 *
	 * @return boolean
	 */
	public static function can_autocomplete_course( $course_id, $user_id ) {
		$auto_course_complete_option = (bool) tutor_utils()->get_option( 'auto_course_complete_on_all_lesson_completion' );
		if ( ! $auto_course_complete_option ) {
			return false;
		}

		$is_course_completed = tutor_utils()->is_completed_course( $course_id, $user_id );
		if ( $is_course_completed ) {
			return false;
		}

		$course_stats = tutor_utils()->get_course_completed_percent( $course_id, $user_id, true );
		if ( $course_stats['total_count'] && $course_stats['completed_count'] === $course_stats['total_count'] ) {
			return self::can_complete_course( $course_id, $user_id );
		} else {
			return false;
		}
	}

	/**
	 * Get review progress link when course progress 100% and
	 * User has pending or fail quiz or assignment
	 *
	 * @since 2.4.0
	 *
	 * @param int $course_id course id.
	 * @param int $user_id user id.
	 *
	 * @return string course content permalink.
	 */
	public static function get_review_progress_link( $course_id, $user_id ) {
		$course_progress   = tutor_utils()->get_course_completed_percent( $course_id, $user_id, true );
		$completed_percent = (int) $course_progress['completed_percent'];
		$course_contents   = tutor_utils()->get_course_contents_by_id( $course_id );
		$permalink         = '';

		if ( tutor_utils()->count( $course_contents ) && 100 === $completed_percent ) {
			foreach ( $course_contents as $content ) {
				if ( 'tutor_quiz' === $content->post_type ) {
					$result = QuizModel::get_quiz_result( $content->ID, $user_id );
					if ( 'pass' !== $result ) {
						$permalink = get_the_permalink( $content->ID );
						break;
					}
				}

				if ( tutor()->has_pro && 'tutor_assignments' === $content->post_type ) {
					$result = \TUTOR_ASSIGNMENTS\Assignments::get_assignment_result( $content->ID, $user_id );
					if ( 'pass' !== $result ) {
						$permalink = get_the_permalink( $content->ID );
						break;
					}
				}
			}
		}

		// Fallback link.
		if ( empty( $permalink ) ) {
			$permalink = tutils()->get_course_first_lesson( $course_id );
		}

		return $permalink;
	}

	/**
	 * Get course preview image placeholder
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public static function get_course_preview_image_placeholder() {
		return tutor()->url . 'assets/images/placeholder.svg';
	}

	/**
	 * Retrieve the courses or course bundles that a given coupon code applies to.
	 *
	 * This function fetches published courses or course bundles from the database
	 * based on the specified type. For each course, it retrieves the course prices
	 * and the course thumbnail URL. If the user has Tutor Pro, it additionally
	 * retrieves the total number of courses in a course bundle.
	 *
	 * @since 3.0.0
	 *
	 * @param string $applies_to The type of items the coupon applies to. Accepts 'specific_courses'
	 *                           for individual courses or any other value for course bundles.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return array An array of course objects. Each course object contains:
	 *               - int $id: The ID of the course.
	 *               - string $title: The title of the course.
	 *               - string $type: The post type of the course (e.g., 'courses', 'course-bundle').
	 *               - float $price: The regular price of the course.
	 *               - float $sale_price: The sale price of the course.
	 *               - string $image: The URL of the course's thumbnail image.
	 *               - int|null $total_courses: The total number of courses in the bundle
	 *                                          (only if the user has Tutor Pro and the course type is 'course-bundle').
	 */
	public function get_coupon_applies_to_courses( string $applies_to ) {
		global $wpdb;

		$post_type = 'specific_courses' === $applies_to ? 'courses' : 'course-bundle';

		$where = array(
			'post_status' => 'publish',
			'post_type'   => $post_type,
		);

		$courses = QueryHelper::get_all( $wpdb->posts, $where, 'ID' );

		if ( tutor()->has_pro ) {
			$bundle_model = new \TutorPro\CourseBundle\Models\BundleModel();
		}

		$final_data = array();

		if ( ! empty( $courses ) ) {
			foreach ( $courses as $course ) {
				$data = new \stdClass();

				if ( tutor()->has_pro && 'course-bundle' === $course->type ) {
					$data->total_courses = count( $bundle_model->get_bundle_course_ids( $course->ID ) );
				}

				$author_name      = get_the_author_meta( 'display_name', $course->post_author );
				$course_prices    = tutor_utils()->get_raw_course_price( $course->ID );
				$data->id         = (int) $course->ID;
				$data->title      = $course->post_title;
				$data->price      = $course_prices->regular_price;
				$data->sale_price = $course_prices->sale_price;
				$data->image      = get_the_post_thumbnail_url( $course->ID );
				$data->author     = $author_name;

				$final_data[] = $data;
			}
		}

		return ! empty( $final_data ) ? $final_data : array();
	}

	/**
	 * Get course instructor IDs.
	 *
	 * @since 3.0.0
	 *
	 * @param int $course_id course id.
	 *
	 * @return array
	 */
	public static function get_course_instructor_ids( $course_id ) {
		global $wpdb;
		$instructor_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key=%s AND meta_value=%s",
				'_tutor_instructor_course_id',
				$course_id
			)
		);

		return $instructor_ids;
	}
}
