<?php
/**
 * Manage Course Related Logic
 *
 * @package Tutor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use TUTOR\Input;
use Tutor\Models\CourseModel;

/**
 * Course Class
 *
 * @since 1.0.0
 */
class Course extends Tutor_Base {

	/**
	 * Additional course meta info
	 *
	 * @var array
	 */
	private $additional_meta = array(
		'_tutor_enable_qa',
		'_tutor_is_public_course',
	);

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'add_meta_boxes', array( $this, 'register_meta_box' ) );
		add_action( 'save_post_' . $this->course_post_type, array( $this, 'save_course_meta' ), 10, 2 );
		add_action( 'wp_ajax_tutor_save_topic', array( $this, 'tutor_save_topic' ) );

		/**
		 * Add Column
		 */
		add_filter( "manage_{$this->course_post_type}_posts_columns", array( $this, 'add_column' ), 10, 1 );
		add_action( "manage_{$this->course_post_type}_posts_custom_column", array( $this, 'custom_lesson_column' ), 10, 2 );

		add_action( 'wp_ajax_tutor_delete_topic', array( $this, 'tutor_delete_topic' ) );

		/**
		 * Frontend Action
		 */
		add_action( 'template_redirect', array( $this, 'enroll_now' ) );
		add_action( 'init', array( $this, 'mark_course_complete' ) );

		/**
		 * Frontend Dashboard
		 */
		add_action( 'wp_ajax_tutor_delete_dashboard_course', array( $this, 'tutor_delete_dashboard_course' ) );

		/**
		 * Gutenberg author support
		 */
		add_filter( 'wp_insert_post_data', array( $this, 'tutor_add_gutenberg_author' ), '99', 2 );

		/**
		 * Frontend metabox supports for course builder
		 *
		 * @since  v.1.3.4
		 */
		add_action( 'tutor/dashboard_course_builder_form_field_after', array( $this, 'register_meta_box_in_frontend' ) );

		/**
		 * Do Stuff for the course save from frontend
		 */
		add_action( 'save_tutor_course', array( $this, 'attach_product_with_course' ), 10, 2 );

		/**
		 * Add course level to course settings
		 *
		 * @since v.1.4.1
		 */
		add_filter( 'tutor_course_settings_tabs', array( $this, 'add_course_level_to_settings' ) );

		/**
		 * Enable Disable Course Details Page Feature
		 *
		 * @since v.1.4.8
		 */
		$this->course_elements_enable_disable();

		/**
		 * Check if course starting, set meta if starting
		 *
		 * @since v.1.4.8
		 */
		add_action( 'tutor_lesson_load_before', array( $this, 'tutor_lesson_load_before' ) );

		/**
		 * Filter product in shop page
		 *
		 * @since v.1.4.9
		 */
		$this->filter_product_in_shop_page();

		/**
		 * Remove the course price if enrolled
		 *
		 * @since 1.5.8
		 */
		add_filter( 'tutor_course_price', array( $this, 'remove_price_if_enrolled' ) );

		/**
		 * Remove course complete button if course completion is strict mode
		 *
		 * @since v.1.6.1
		 */
		add_filter( 'tutor_course/single/complete_form', array( $this, 'tutor_lms_hide_course_complete_btn' ) );
		add_filter( 'get_gradebook_generate_form_html', array( $this, 'get_generate_greadbook' ) );

		/**
		 * Add social share content in header
		 *
		 * @since v.1.6.3
		 */
		add_action( 'wp_head', array( $this, 'social_share_content' ) );

		/**
		 * Delete course data after deleted course
		 *
		 * @since v.1.6.6
		 */
		add_action( 'deleted_post', array( new CourseModel(), 'delete_course_data' ) );

		/**
		 * Delete course data after deleted course
		 *
		 * @since v.1.8.2
		 */
		add_action( 'before_delete_post', array( $this, 'delete_associated_enrollment' ) );

		/**
		 * Show only own uploads in media library if user is instructor
		 *
		 * @since v1.8.9
		 */
		add_filter( 'posts_where', array( $this, 'restrict_media' ) );

		/**
		 * Restrict new enrol/purchase button if course member limit reached
		 *
		 * @since v1.9.0
		 */
		add_filter( 'tutor_course_restrict_new_entry', array( $this, 'restrict_new_student_entry' ) );

		/**
		 * Reset course progress on retake
		 *
		 * @since v1.9.5
		 */
		add_action( 'wp_ajax_tutor_reset_course_progress', array( $this, 'tutor_reset_course_progress' ) );

		/**
		 * Popup for review
		 *
		 * @since v1.9.7
		 */
		add_action( 'wp_footer', array( $this, 'popup_review_form' ) );
		add_action( 'wp_ajax_tutor_clear_review_popup_data', array( $this, 'clear_review_popup_data' ) );

		/**
		 * Do enroll after login if guest take enroll attempt
		 *
		 * @since 1.9.8
		 */
		add_action( 'tutor_do_enroll_after_login_if_attempt', array( $this, 'enroll_after_login_if_attempt' ), 10, 2 );

		add_action( 'wp_ajax_tutor_update_course_content_order', array( $this, 'tutor_update_course_content_order' ) );

		add_action( 'wp_ajax_tutor_get_wc_product', array( $this, 'tutor_get_wc_product' ) );

		add_action( 'wp_ajax_tutor_course_enrollment', array( $this, 'course_enrollment' ) );

		/**
		 * After trash a course redirect to course list page
		 *
		 * @since 2.1.7
		 */
		add_action( 'trashed_post', __CLASS__ . '::redirect_to_course_list_page' );

		add_filter( 'tutor_enroll_required_login_class', array( $this, 'add_enroll_required_login_class' ) );
	}

	/**
	 * Add enroll require login class
	 *
	 * @since 2.6.0
	 *
	 * @param string $class_name css class name.
	 *
	 * @return string
	 */
	public function add_enroll_required_login_class( $class_name ) {
		$enabled_tutor_login = tutor_utils()->get_option( 'enable_tutor_native_login', null, true, true );
		if ( ! $enabled_tutor_login ) {
			return '';
		}

		return $class_name;
	}

	/**
	 * Get course associate WC product info by Ajax request
	 *
	 * @since 2.0.7
	 * @return void
	 */
	public function tutor_get_wc_product() {
		tutor_utils()->checking_nonce();
		$product_id = Input::post( 'product_id' );
		$product    = wc_get_product( $product_id );
		$course_id  = Input::post( 'course_id', 0, Input::TYPE_INT );

		$is_linked_with_course = tutor_utils()->product_belongs_with_course( $product_id );
		/**
		 * If selected product is already linked with
		 * a course & it is not the current course the
		 * return error
		 *
		 * @since v2.1.0
		 */
		if ( is_object( $is_linked_with_course ) && $is_linked_with_course->post_id != $course_id ) {
			wp_send_json_error(
				__( 'One product can not be added to multiple course!', 'tutor' )
			);
		}

		if ( $product ) {
			$data = array(
				'name'          => $product->get_name(),
				'regular_price' => $product->get_regular_price(),
				'sale_price'    => $product->get_sale_price(),
			);
			wp_send_json_success( $data );
		} else {
			wp_send_json_error( __( 'Product not found', 'tutor' ) );
		}
	}

	/**
	 * Update course content order
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function tutor_update_course_content_order() {
		tutor_utils()->checking_nonce();

		if ( Input::has( 'content_parent' ) ) {
			$content_parent = Input::post( 'content_parent', array(), Input::TYPE_ARRAY );
			$topic_id       = tutor_utils()->array_get( 'parent_topic_id', $content_parent );
			$content_id     = tutor_utils()->array_get( 'content_id', $content_parent );

			if ( ! tutor_utils()->can_user_manage( 'topic', $topic_id ) ) {
				wp_send_json_success( array( 'message' => __( 'Access Denied!', 'tutor' ) ) );
				exit;
			}

			// Update the parent topic id of the content.
			global $wpdb;
			$wpdb->update( $wpdb->posts, array( 'post_parent' => $topic_id ), array( 'ID' => $content_id ) );
		}

		// Save course content order.
		$this->save_course_content_order();

		wp_send_json_success();
	}

	/**
	 * Restrict new student entry
	 *
	 * @since 1.0.0
	 * @param mixed $content content.
	 * @return mixed
	 */
	public function restrict_new_student_entry( $content ) {

		if ( ! tutor_utils()->is_course_fully_booked() ) {
			// No restriction if not fully booked.
			return $content;
		}

		return '<div class="list-item-booking booking-full tutor-d-flex tutor-align-center"><div class="booking-progress tutor-d-flex"><span class="tutor-mr-8 tutor-color-warning tutor-icon-circle-info"></span></div><div class="tutor-fs-7 tutor-fw-medium">Fully Booked</div></div>';
	}

	/**
	 * Restrict media
	 *
	 * @since 1.0.0
	 * @param string $where where clause.
	 * @return string
	 */
	public function restrict_media( $where ) {
		$action = Input::post( 'action' );
		if ( 'query-attachments' === $action && tutor_utils()->is_instructor() ) {
			if ( ! tutor_utils()->has_user_role( array( 'administrator', 'editor' ) ) ) {
				$where .= ' AND post_author=' . get_current_user_id();
			}
		}

		return $where;
	}

	/**
	 * Registering metabox
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_meta_box() {
		$course_post_type = tutor()->course_post_type;

		tutor_meta_box_wrapper( 'tutor-course-topics', __( 'Course Builder', 'tutor' ), array( $this, 'course_meta_box' ), $course_post_type, 'advanced', 'default', 'tutor-admin-post-meta' );

		tutor_meta_box_wrapper( 'tutor-course-additional-data', __( 'Additional Data', 'tutor' ), array( $this, 'course_additional_data_meta_box' ), $course_post_type, 'advanced', 'default', 'tutor-admin-post-meta' );

		tutor_meta_box_wrapper( 'tutor-course-videos', __( 'Video', 'tutor' ), array( $this, 'video_metabox' ), $course_post_type, 'advanced', 'default', 'tutor-admin-post-meta' );
	}

	/**
	 * Course meta box (Topics)
	 *
	 * @since 1.0.0
	 * @param boolean $echo display or not.
	 * @return string
	 */
	public function course_meta_box( $echo = true ) {
		$file_path = tutor()->path . 'views/metabox/course-topics.php';

		if ( $echo ) {
			/**
			 * Use echo raise WPCS security issue
			 * Helper wp_kses_post break content.
			 */
			include $file_path;
		} else {
			ob_start();
			include $file_path;
			return ob_get_clean();
		}
	}

	/**
	 * Additional data meta box
	 *
	 * @since 1.0.0
	 * @param boolean $echo print data or return.
	 * @return string
	 */
	public function course_additional_data_meta_box( $echo = true ) {
		$file_path = tutor()->path . 'views/metabox/course-additional-data.php';

		if ( $echo ) {
			/**
			 * Use echo raise WPCS security issue
			 * Helper wp_kses_post break content.
			 */
			include $file_path;
		} else {
			ob_start();
			include $file_path;
			return ob_get_clean();
		}
	}

	/**
	 * Video meta box
	 *
	 * @since 1.0.0
	 * @param boolean $echo print data or return.
	 * @return string
	 */
	public function video_metabox( $echo = true ) {
		$file_path = tutor()->path . 'views/metabox/video-metabox.php';

		if ( $echo ) {
			/**
			 * Use echo raise WPCS security issue
			 * Helper wp_kses_post break content.
			 */
			include $file_path;
		} else {
			ob_start();
			include $file_path;
			return ob_get_clean();
		}
	}

	/**
	 * Register metabox in course builder tutor
	 *
	 * @since 1.3.4
	 * @return void
	 */
	public function register_meta_box_in_frontend() {
		global $post;

		do_action( 'tutor_course_builder_metabox_before', get_the_ID() );

		course_builder_section_wrap( $this->video_metabox( false ), __( 'Video', 'tutor' ) );
		do_action( 'tutor/frontend_course_edit/after/video', $post );

		course_builder_section_wrap( $this->course_meta_box( false ), __( 'Course Builder', 'tutor' ) );
		do_action( 'tutor/frontend_course_edit/after/course_builder', $post );

		course_builder_section_wrap( $this->course_additional_data_meta_box( false ), __( 'Additional Data', 'tutor' ) );
		do_action( 'tutor/frontend_course_edit/after/additional_data', $post );

		do_action( 'tutor_course_builder_metabox_after', get_the_ID() );
	}

	/**
	 * Save course content order
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function save_course_content_order() {
		global $wpdb;

		$new_order = Input::post( 'tutor_topics_lessons_sorting' );
		if ( ! empty( $new_order ) ) {
			$order = json_decode( $new_order, true );

			if ( is_array( $order ) && count( $order ) ) {
				$i = 0;
				foreach ( $order as $topic ) {
					$i++;
					$wpdb->update(
						$wpdb->posts,
						array( 'menu_order' => $i ),
						array( 'ID' => $topic['topic_id'] )
					);

					/**
					 * Removing All lesson with topic
					 */

					$wpdb->update(
						$wpdb->posts,
						array( 'post_parent' => 0 ),
						array( 'post_parent' => $topic['topic_id'] )
					);

					/**
					 * Lesson Attaching with topic ID
					 * Sorting lesson
					 */
					if ( isset( $topic['lesson_ids'] ) ) {
						$lesson_ids = $topic['lesson_ids'];
					} else {
						$lesson_ids = array();
					}
					if ( count( $lesson_ids ) ) {
						foreach ( $lesson_ids as $lesson_key => $lesson_id ) {
							$wpdb->update(
								$wpdb->posts,
								array(
									'post_parent' => $topic['topic_id'],
									'menu_order'  => $lesson_key,
								),
								array( 'ID' => $lesson_id )
							);
						}
					}
				}
			}
		}
	}

	/**
	 * Insert Topic and attached it with Course
	 *
	 * @since 1.0.0
	 *
	 * @param integer $post_ID post ID.
	 * @param object  $post post object.
	 *
	 * @return void
	 */
	public function save_course_meta( $post_ID, $post ) {
		global $wpdb;

		do_action( 'tutor_save_course', $post_ID, $post );

		/**
		 * Save course price type
		 */
		$price_type = Input::post( 'tutor_course_price_type' );
		if ( $price_type ) {
			update_post_meta( $post_ID, '_tutor_course_price_type', $price_type );
		}

		//phpcs:disable WordPress.Security.NonceVerification.Missing
		// Course Duration.
		if ( ! empty( $_POST['course_duration'] ) ) {
			$video = Input::post( 'course_duration', array(), Input::TYPE_ARRAY );
			update_post_meta( $post_ID, '_course_duration', $video );
		}

		if ( ! empty( $_POST['_tutor_course_level'] ) ) {
			$course_level = Input::post( '_tutor_course_level' );
			update_post_meta( $post_ID, '_tutor_course_level', $course_level );
		}

		$additional_data_edit = Input::post( '_tutor_course_additional_data_edit' );
		if ( $additional_data_edit ) {
			if ( ! empty( $_POST['course_benefits'] ) ) {
				$course_benefits = Input::post( 'course_benefits', '', Input::TYPE_KSES_POST );
				update_post_meta( $post_ID, '_tutor_course_benefits', $course_benefits );
			} else {
				if ( ! tutor_is_rest() ) {
					delete_post_meta( $post_ID, '_tutor_course_benefits' );
				}
			}

			if ( ! empty( $_POST['course_requirements'] ) ) {
				$requirements = Input::post( 'course_requirements', '', Input::TYPE_KSES_POST );
				update_post_meta( $post_ID, '_tutor_course_requirements', $requirements );
			} else {
				if ( ! tutor_is_rest() ) {
					delete_post_meta( $post_ID, '_tutor_course_requirements' );
				}
			}

			if ( ! empty( $_POST['course_target_audience'] ) ) {
				$target_audience = Input::post( 'course_target_audience', '', Input::TYPE_KSES_POST );
				update_post_meta( $post_ID, '_tutor_course_target_audience', $target_audience );
			} else {
				if ( ! tutor_is_rest() ) {
					delete_post_meta( $post_ID, '_tutor_course_target_audience' );
				}
			}

			if ( ! empty( $_POST['course_material_includes'] ) ) {
				$material_includes = Input::post( 'course_material_includes', '', Input::TYPE_KSES_POST );
				update_post_meta( $post_ID, '_tutor_course_material_includes', $material_includes );
			} else {
				if ( ! tutor_is_rest() ) {
					delete_post_meta( $post_ID, '_tutor_course_material_includes' );
				}
			}
			//phpcs:enable WordPress.Security.NonceVerification.Missing
		}

		/**
		 * Sorting Topics and lesson
		 */
		$this->save_course_content_order();

		// Additional data like course intro video.
		if ( $additional_data_edit ) {
			// Sanitize data through helper method.
			$video        = Input::sanitize_array(
				$_POST['video'] ?? array(), //phpcs:ignore
				array(
					'source_external_url' => 'esc_url',
					'source_embedded'     => 'wp_kses_post',
				),
				true
			);
			$video_source = tutor_utils()->array_get( 'source', $video );
			if ( -1 !== $video_source ) {
				update_post_meta( $post_ID, '_video', $video );
			} else {
				if ( ! tutor_is_rest() ) {
					delete_post_meta( $post_ID, '_video' );
				}
			}
		}

		/**
		 * Adding author to instructor automatically
		 */

		// Override post author id.
		$author_id = isset( $_POST['post_author_override'] ) ? $_POST['post_author_override'] : $post->post_author; //phpcs:ignore
		$attached  = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(umeta_id) FROM {$wpdb->usermeta}
					WHERE user_id = %d
						AND meta_key = '_tutor_instructor_course_id'
						AND meta_value = %d ",
				$author_id,
				$post_ID
			)
		);

		if ( ! $attached ) {
			add_user_meta( $author_id, '_tutor_instructor_course_id', $post_ID );
		}

		/**
		 * Disable question and answer for this course
		 *
		 * @since 1.7.0
		 */
		if ( $additional_data_edit ) {
			foreach ( $this->additional_meta as $key ) {
				//phpcs:ignore WordPress.Security.NonceVerification.Missing
				update_post_meta( $post_ID, $key, ( isset( $_POST[ $key ] ) ? 'yes' : 'no' ) );
			}
		}

		do_action( 'tutor_save_course_after', $post_ID, $post );
	}

	/**
	 * Save course topic
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function tutor_save_topic() {
		tutor_utils()->checking_nonce();

		// Check required fields.
		if ( empty( Input::post( 'topic_title' ) ) ) {
			wp_send_json_error( array( 'message' => __( 'Topic title is required!', 'tutor' ) ) );
		}

		// Gather parameters.
		$course_id           = Input::post( 'topic_course_id', 0, Input::TYPE_INT );
		$topic_id            = Input::post( 'topic_id', 0, Input::TYPE_INT );
		$topic_title         = Input::post( 'topic_title' );
		$topic_summery       = Input::post( 'topic_summery', '', Input::TYPE_KSES_POST );
		$next_topic_order_id = tutor_utils()->get_next_topic_order_id( $course_id, $topic_id );

		// Validate if user can manage the topic.
		if ( ! tutor_utils()->can_user_manage( 'course', $course_id ) || ( $topic_id && ! tutor_utils()->can_user_manage( 'topic', $topic_id ) ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied', 'tutor' ) ) );
		}

		// Create payload to create/update the topic.
		$post_arr                   = array(
			'post_type'    => 'topics',
			'post_title'   => $topic_title,
			'post_content' => $topic_summery,
			'post_status'  => 'publish',
			'post_author'  => get_current_user_id(),
			'post_parent'  => $course_id,
			'menu_order'   => $next_topic_order_id,
		);
		$topic_id ? $post_arr['ID'] = $topic_id : 0;
		$current_topic_id           = wp_insert_post( $post_arr );

		ob_start();
		include tutor()->path . 'views/metabox/course-contents.php';

		wp_send_json_success(
			array(
				'topic_title'     => $topic_title,
				'course_contents' => ob_get_clean(),
			)
		);
	}

	/**
	 * Add columns to course row in default WP list table
	 *
	 * @since 1.0.0
	 *
	 * @param array $columns column list.
	 * @return mixed
	 */
	public function add_column( $columns ) {
		$date_col = $columns['date'];
		unset( $columns['date'] );
		$columns['lessons']  = __( 'Lessons', 'tutor' );
		$columns['students'] = __( 'Students', 'tutor' );
		$columns['price']    = __( 'Price', 'tutor' );
		$columns['date']     = $date_col;

		return $columns;
	}

	/**
	 * Add data to custom column
	 *
	 * @since 1.0.0
	 *
	 * @param string  $column column name.
	 * @param integer $post_id post ID.
	 *
	 * @return void
	 */
	public function custom_lesson_column( $column, $post_id ) {
		if ( 'lessons' === $column ) {
			echo esc_html( tutor_utils()->get_lesson_count_by_course( $post_id ) );
		}

		if ( 'students' === $column ) {
			echo esc_html( tutor_utils()->count_enrolled_users_by_course( $post_id ) );
		}

		if ( 'price' === $column ) {
			$price = tutor_utils()->get_course_price( $post_id );
			if ( $price ) {
				$monetize_by = tutils()->get_option( 'monetize_by' );
				if ( function_exists( 'wc_price' ) && 'wc' === $monetize_by ) {
					echo wp_kses(
						'<span class="tutor-label-success">' . wc_price( $price ) . '</span>',
						array(
							'span' => array( 'class' => true ),
						)
					);
				} else {
					echo wp_kses(
						'<span class="tutor-label-success">' . $price . '</span>',
						array( 'span' => array( 'class' => true ) )
					);
				}
			} else {
				echo esc_html( apply_filters( 'tutor-loop-default-price', __( 'free', 'tutor' ) ) );
			}
		}
	}


	/**
	 * Delete a course topic
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function tutor_delete_topic() {

		tutor_utils()->checking_nonce();

		global $wpdb;
		$topic_id = Input::post( 'topic_id', '' );

		if ( ! $topic_id || ! is_numeric( $topic_id ) || ! tutor_utils()->can_user_manage( 'topic', $topic_id ) ) {
			wp_send_json_error( array( 'message' => 'Access Forbidden' ) );
		}

		// Assign course ID to orphan content IDs since the topic will be deleted.
		$course_id   = tutor_utils()->get_course_id_by( 'topic', $topic_id );
		$content_ids = tutor_utils()->get_course_content_ids_by( null, 'topic', $topic_id );
		foreach ( $content_ids as $content_id ) {
			update_post_meta( $content_id, '_tutor_course_id_for_lesson', $course_id );
			// Actually all kind of contents.
			// This keyword '_tutor_course_id_for_lesson' used just to support backward compatibillity.
		}

		// Set contents under the topic orphan.
		$wpdb->update( $wpdb->posts, array( 'post_parent' => 0 ), array( 'post_parent' => $topic_id ) );

		// Then delete the topic from database.
		$wpdb->delete( $wpdb->postmeta, array( 'post_id' => $topic_id ) );
		wp_delete_post( $topic_id );

		wp_send_json_success();
	}

	/**
	 * Handle enroll now action
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enroll_now() {

		// Checking if action comes from Enroll form.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( tutor_utils()->array_get( 'tutor_course_action', tutor_sanitize_data( $_POST ) ) !== '_tutor_course_enroll_now' || ! isset( $_POST['tutor_course_id'] ) ) {
			return;
		}

		// Checking Nonce.
		tutor_utils()->checking_nonce();

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			exit( esc_html__( 'Please Sign In first', 'tutor' ) );
		}

		$course_id = Input::post( 'tutor_course_id', 0, Input::TYPE_INT );
		$user_id   = get_current_user_id();

		/**
		 * TODO: need to check purchase information
		 */

		$is_purchasable = tutor_utils()->is_course_purchasable( $course_id );

		/**
		 * If is is not purchasable, it's free, and enroll right now
		 * If purchasable, then process purchase.
		 *
		 * @since: v.1.0.0
		 */
		if ( $is_purchasable ) { //phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf
			// Process purchase.

		} else {
			// Free enroll.
			tutor_utils()->do_enroll( $course_id );
		}

		$referer_url = wp_get_referer();
		wp_safe_redirect( $referer_url . '?nocache=' . time() );
		exit;
	}

	/**
	 * Mark complete completed
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function mark_course_complete() {
		$tutor_action = Input::post( 'tutor_action' );
		$course_id    = Input::post( 'course_id', 0, Input::TYPE_INT );
		if ( 'tutor_complete_course' !== $tutor_action || ! $course_id ) {
			return;
		}

		// Checking nonce.
		tutor_utils()->checking_nonce();

		$user_id = get_current_user_id();

		// TODO: need to show view if not signed_in.
		if ( ! $user_id ) {
			die( esc_html__( 'Please Sign-In', 'tutor' ) );
		}

		CourseModel::mark_course_as_completed( $course_id, $user_id );

		$permalink = get_the_permalink( $course_id );

		// Set temporary identifier to show review pop up.
		self::set_review_popup_data( $user_id, $course_id, $permalink );

		wp_safe_redirect( $permalink );
		exit;
	}

	/**
	 * Set data for review popup.
	 *
	 * @since 2.2.5
	 * @since 2.4.0 removed $permalink param. store user meta instead of option data.
	 *
	 * @param int $user_id user id.
	 * @param int $course_id course id.
	 *
	 * @return void
	 */
	public static function set_review_popup_data( $user_id, $course_id ) {
		if ( get_tutor_option( 'enable_course_review' ) ) {
			$rating = tutor_utils()->get_course_rating_by_user( $course_id, $user_id );
			if ( ! $rating || ( empty( $rating->rating ) && empty( $rating->review ) ) ) {
				$meta_key = User::get_review_popup_meta( $course_id );
				add_user_meta( $user_id, $meta_key, $course_id, true );
			}
		}
	}

	/**
	 * Popup review form on course details
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function popup_review_form() {
		if ( is_user_logged_in() ) {
			$user_id          = get_current_user_id();
			$course_id        = get_the_ID();
			$meta_key         = User::get_review_popup_meta( $course_id );
			$review_course_id = (int) get_user_meta( $user_id, $meta_key, true );

			if ( is_single() && $course_id === $review_course_id ) {
				include tutor()->path . 'views/modal/review.php';
			}
		}
	}

	/**
	 * Review popup data clear
	 *
	 * @since 2.4.0
	 *
	 * @return void
	 */
	public function clear_review_popup_data() {
		tutils()->checking_nonce();

		if ( is_user_logged_in() ) {
			$user_id   = get_current_user_id();
			$course_id = Input::post( 'course_id', 0, Input::TYPE_INT );

			if ( $course_id ) {
				$meta_key = User::get_review_popup_meta( $course_id );
				delete_user_meta( $user_id, $meta_key, $course_id );
			}

			wp_send_json_success();
		}
	}

	/**
	 * Delete course delete from frontend dashboard
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function tutor_delete_dashboard_course() {
		tutor_utils()->checking_nonce();

		$course_id = Input::post( 'course_id', 0, Input::TYPE_INT );
		if ( ! tutor_utils()->can_user_manage( 'course', $course_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied', 'tutor' ) ) );
		}

		/**
		 * Co-instructor can not delete a course
		 *
		 * @since 2.1.6
		 */
		if ( false === CourseModel::is_main_instructor( $course_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Only main instructor can delete this course', 'tutor' ) ) );
		}

		CourseModel::delete_course( $course_id );
		wp_send_json_success();
	}

	/**
	 * Main author change from gutenberg editor
	 *
	 * @since 2.0.0
	 *
	 * @param array $data data.
	 * @param array $postarr post array.
	 *
	 * @return mixed
	 */
	public function tutor_add_gutenberg_author( $data, $postarr ) {
		$gutenberg_enabled = tutor_utils()->get_option( 'enable_gutenberg_course_edit' );
		$post_type         = $postarr['post_type'];
		$courses_post_type = tutor()->course_post_type;

		if ( false === is_admin() || false === $gutenberg_enabled || $post_type !== $courses_post_type ) {
			return $data;
		}

		/**
		 * Only admin can change main author
		 */
		if ( $courses_post_type === $post_type && ! current_user_can( 'administrator' ) ) {
			global $wpdb;
			$post_ID     = (int) tutor_utils()->avalue_dot( 'ID', $postarr );
			$post_author = (int) $wpdb->get_var( $wpdb->prepare( "SELECT post_author FROM {$wpdb->posts} WHERE ID = %d ", $post_ID ) );

			if ( $post_author > 0 ) {
				$data['post_author'] = $post_author;
			} else {
				$data['post_author'] = get_current_user_id();
			}
		}

		return $data;
	}


	/**
	 * Attach product with course when course save from frontend or backend.
	 *
	 * @since 1.3.4
	 *
	 * @param integer $post_ID  course ID.
	 * @param array   $post_data cretaed course post details.
	 *
	 * @return void
	 */
	public function attach_product_with_course( $post_ID, $post_data ) {

		$monetize_by = tutor_utils()->get_option( 'monetize_by' );

		/**
		 * The function is_admin will check only loaded page from WP admin.
		 * It does not check any role
		 *
		 * @since 2.6.0
		 *
		 * tutor_is_rest() check added, if loaded from rest api
		 */
		$is_admin_panel = is_admin() || tutor_is_rest();
		// From backend course select box.
		$product_id = Input::post( '_tutor_course_product_id', 0, Input::TYPE_INT );

		/**
		 * From Admin Panel, Free user can only select product from dropdown
		 */
		if ( $is_admin_panel && 'wc' === $monetize_by && tutor()->has_pro === false ) {
			if ( $product_id > 0 ) {
				update_post_meta( $post_ID, '_tutor_course_product_id', $product_id );
			} elseif ( -1 === $product_id ) {
				if ( ! tutor_is_rest() ) {
					delete_post_meta( $post_ID, '_tutor_course_product_id' );
				}
			}

			return;
		}

		$attached_product_id = tutor_utils()->get_course_product_id( $post_ID );
		$course_price        = Input::post( 'course_price', 0, Input::TYPE_NUMERIC );
		$sale_price          = Input::post( 'course_sale_price', 0, Input::TYPE_NUMERIC );

		if ( ! $course_price || $sale_price >= $course_price ) {
			return;
		}

		$course = get_post( $post_ID );

		if ( 'wc' === $monetize_by ) {

			$is_update = false;
			if ( $attached_product_id ) {
				$wc_product = get_post_meta( $attached_product_id, '_product_version', true );
				if ( $wc_product ) {
					$is_update = true;
				}
			}

			if ( $is_update || ( $product_id > 0 && $is_admin_panel ) ) {
				// Added in @since 2.0.7.
				if ( $product_id > 0 && $is_admin_panel ) {
					$attached_product_id = $product_id;
					update_post_meta( $post_ID, '_tutor_course_product_id', $product_id );
				}

				$product_obj = wc_get_product( $attached_product_id );
				$product_id  = self::create_wc_product( $course->post_title, $course_price, $sale_price, $attached_product_id );
				if ( $product_obj->is_type( 'subscription' ) ) {
					update_post_meta( $attached_product_id, '_subscription_price', $course_price );
				}
			} else {
				$product_id = self::create_wc_product( $course->post_title, $course_price, $sale_price );
				if ( $product_id ) {
					update_post_meta( $post_ID, '_tutor_course_product_id', $product_id );
					// Mark product for woocommerce.
					update_post_meta( $product_id, '_virtual', 'yes' );
					update_post_meta( $product_id, '_tutor_product', 'yes' );

					$course_post_thumbnail = get_post_meta( $post_ID, '_thumbnail_id', true );
					if ( $course_post_thumbnail ) {
						set_post_thumbnail( $product_id, $course_post_thumbnail );
					}
				}
			}
		} elseif ( 'edd' === $monetize_by ) {

			$is_update = false;

			if ( $attached_product_id ) {
				$edd_price = get_post_meta( $attached_product_id, 'edd_price', true );
				if ( $edd_price ) {
					$is_update = true;
				}
			}

			if ( $is_update ) {
				// Update the product.
				update_post_meta( $attached_product_id, 'edd_price', $course_price );
			} else {
				// Create new product.

				$post_arr    = array(
					'post_type'   => 'download',
					'post_title'  => $course->post_title,
					'post_status' => 'publish',
					'post_author' => get_current_user_id(),
				);
				$download_id = wp_insert_post( $post_arr );
				if ( $download_id ) {
					// EDD edd_price.
					update_post_meta( $download_id, 'edd_price', $course_price );

					update_post_meta( $post_ID, '_tutor_course_product_id', $download_id );
					// Mark product for EDD.
					update_post_meta( $download_id, '_tutor_product', 'yes' );

					$course_post_thumbnail = get_post_meta( $post_ID, '_thumbnail_id', true );
					if ( $course_post_thumbnail ) {
						set_post_thumbnail( $download_id, $course_post_thumbnail );
					}
				}
			}
		}
	}

	/**
	 * Add Course level to course settings
	 *
	 * @since 1.4.1
	 *
	 * @param array $args arguments.
	 * @return array
	 */
	public function add_course_level_to_settings( $args ) {
		$course_id    = get_the_ID();
		$levels       = tutor_utils()->course_levels();
		$course_level = get_post_meta( $course_id, '_tutor_course_level', true );

		$args['general']['fields']['_tutor_course_level'] = array(
			'type'        => 'select',
			'label'       => __( 'Difficulty Level', 'tutor' ),
			'label_title' => __( 'Enable', 'tutor' ),
			'options'     => $levels,
			'value'       => $course_level ? $course_level : 'intermediate',
			'desc'        => __( 'Course difficulty level', 'tutor' ),
		);

		return $args;
	}

	/**
	 * Check if course starting
	 *
	 * @since 1.4.8
	 * @return void
	 */
	public function tutor_lesson_load_before() {
		$course_id         = tutor_utils()->get_course_id_by_content( get_the_ID() );
		$completed_lessons = tutor_utils()->get_completed_lesson_count_by_course( $course_id );
		if ( is_user_logged_in() ) {
			$is_course_started = get_post_meta( $course_id, '_tutor_course_started', true );
			if ( ! $completed_lessons && ! $is_course_started ) {
				update_post_meta( $course_id, '_tutor_course_started', tutor_time() );
				do_action( 'tutor/course/started', $course_id );
			}
		}
	}

	/**
	 * Add Course level to course settings
	 *
	 * @since 1.4.8
	 * @return void
	 */
	public function course_elements_enable_disable() {
		add_filter( 'tutor_course/single/completing-progress-bar', array( $this, 'enable_disable_course_progress_bar' ) );
		add_filter( 'tutor_course/single/material_includes', array( $this, 'enable_disable_material_includes' ) );
		add_filter( 'tutor_course/single/content', array( $this, 'enable_disable_course_content' ) );
		add_filter( 'tutor_course/single/benefits_html', array( $this, 'enable_disable_course_benefits' ) );
		add_filter( 'tutor_course/single/requirements_html', array( $this, 'enable_disable_course_requirements' ) );
		add_filter( 'tutor_course/single/audience_html', array( $this, 'enable_disable_course_target_audience' ) );
		add_filter( 'tutor_course/single/nav_items', array( $this, 'enable_disable_course_nav_items' ), 999, 2 );
	}

	/**
	 * Enable disable course progress bar
	 *
	 * @since 1.4.8
	 *
	 * @param string $html HTML string.
	 * @return string
	 */
	public function enable_disable_course_progress_bar( $html ) {
		$disable_option = ! (bool) tutor_utils()->get_option( 'enable_course_progress_bar', true, true );
		if ( $disable_option ) {
			return '';
		}
		return $html;
	}

	/**
	 * Enable disable material includes
	 *
	 * @since 1.4.8
	 *
	 * @param string $html HTML string.
	 * @return string
	 */
	public function enable_disable_material_includes( $html ) {
		$disable_option = ! (bool) get_tutor_option( 'enable_course_material', true, true );
		if ( $disable_option ) {
			return '';
		}
		return $html;
	}

	/**
	 * Enable disable course content
	 *
	 * @since 1.4.8
	 *
	 * @param string $html HTML string.
	 * @return string
	 */
	public function enable_disable_course_content( $html ) {
		$disable_option = ! (bool) tutor_utils()->get_option( 'enable_course_description', true, true );
		if ( $disable_option ) {
			return '';
		}
		return $html;
	}

	/**
	 * Enable disable course benefits
	 *
	 * @since 1.4.8
	 *
	 * @param string $html HTML string.
	 * @return string
	 */
	public function enable_disable_course_benefits( $html ) {
		$disable_option = ! (bool) tutor_utils()->get_option( 'enable_course_benefits', true, true );
		if ( $disable_option ) {
			return '';
		}
		return $html;
	}

	/**
	 * Enable disable course requirements
	 *
	 * @since 1.4.8
	 *
	 * @param string $html HTML string.
	 * @return string
	 */
	public function enable_disable_course_requirements( $html ) {
		$disable_option = ! (bool) tutor_utils()->get_option( 'enable_course_requirements', true, true );
		if ( $disable_option ) {
			return '';
		}
		return $html;
	}

	/**
	 * Enable disable course target audience
	 *
	 * @since 1.4.8
	 *
	 * @param string $html HTML string.
	 * @return string
	 */
	public function enable_disable_course_target_audience( $html ) {
		$disable_option = ! (bool) tutor_utils()->get_option( 'enable_course_target_audience', true, true );
		if ( $disable_option ) {
			return '';
		}
		return $html;
	}

	/**
	 * Enable disable course nav items
	 *
	 * @since 1.4.8
	 *
	 * @param array   $items item list.
	 * @param integer $course_id course ID.
	 *
	 * @return array
	 */
	public function enable_disable_course_nav_items( $items, $course_id ) {
		global $wp_query, $post;
		$enable_q_and_a_on_course     = (bool) get_tutor_option( 'enable_q_and_a_on_course' );
		$disable_course_announcements = ! (bool) tutor_utils()->get_option( 'enable_course_announcements', true, true );
		$disable_qa_for_this_course   = ( $wp_query->is_single && ! empty( $post ) ) ? get_post_meta( $post->ID, '_tutor_enable_qa', true ) != 'yes' : false;

		// Whether Q&A enabled.
		if ( ! $enable_q_and_a_on_course || $disable_qa_for_this_course ) {
			if ( tutor_utils()->array_get( 'questions', $items ) ) {
				unset( $items['questions'] );
			}
		}

		// Whether announcment enabled.
		if ( $disable_course_announcements ) {
			if ( tutor_utils()->array_get( 'announcements', $items ) ) {
				unset( $items['announcements'] );
			}
		}

		// Hide review section if disabled.
		if ( ! get_tutor_option( 'enable_course_review' ) ) {
			unset( $items['reviews'] );
		}

		// Whether enrollment require.
		$is_enrolled = tutor_utils()->is_enrolled();

		return array_filter(
			$items,
			function( $item ) use ( $is_enrolled ) {
				if ( isset( $item['require_enrolment'] ) && $item['require_enrolment'] ) {
					return $is_enrolled;
				}
				return true;
			}
		);
	}

	/**
	 * Filter product in shop page
	 *
	 * @since 1.4.9
	 * @return void|null
	 */
	public function filter_product_in_shop_page() {
		$hide_course_from_shop_page = (bool) get_tutor_option( 'hide_course_from_shop_page' );
		if ( ! $hide_course_from_shop_page ) {
			return;
		}
		add_action( 'woocommerce_product_query', array( $this, 'filter_woocommerce_product_query' ) );
		add_filter( 'edd_downloads_query', array( $this, 'filter_edd_downloads_query' ), 10, 2 );
		add_action( 'pre_get_posts', array( $this, 'filter_archive_meta_query' ), 1 );
	}

	/**
	 * Tutor product meta query
	 *
	 * @since 1.4.9
	 * @return array
	 */
	public function tutor_product_meta_query() {
		$meta_query = array(
			'key'     => '_tutor_product',
			'compare' => 'NOT EXISTS',
		);
		return $meta_query;
	}

	/**
	 * Filter product in woocommerce shop page
	 *
	 * @since 1.4.9
	 *
	 * @param \WP_Query $wp_query WP Query instance.
	 * @return \WP_Query
	 */
	public function filter_woocommerce_product_query( $wp_query ) {
		$wp_query->set( 'meta_query', array( $this->tutor_product_meta_query() ) );
		return $wp_query;
	}

	/**
	 * Filter product in edd downloads shortcode page
	 *
	 * @since 1.4.9
	 *
	 * @param \WP_Query $query WP Query instance.
	 * @return \WP_Query
	 */
	public function filter_edd_downloads_query( $query ) {
		$query['meta_query'][] = $this->tutor_product_meta_query();
		return $query;
	}

	/**
	 * Filter product in edd downloads archive page
	 *
	 * @since 1.4.9
	 *
	 * @param \WP_Query $wp_query WP Query instance.
	 * @return \WP_Query
	 */
	public function filter_archive_meta_query( $wp_query ) {
		if ( ! is_admin() && $wp_query->is_archive && $wp_query->get( 'post_type' ) === 'download' ) {
			$wp_query->set( 'meta_query', array( $this->tutor_product_meta_query() ) );
		}
		return $wp_query;
	}

	/**
	 * Removed course price if already enrolled at single course
	 *
	 * @since 1.5.8
	 *
	 * @param string $html HTML string.
	 * @return string
	 */
	public function remove_price_if_enrolled( $html ) {
		$should_removed = apply_filters( 'should_remove_price_if_enrolled', true );

		if ( $should_removed ) {
			$course_id = get_the_ID();
			$enrolled  = tutor_utils()->is_enrolled( $course_id );
			if ( $enrolled ) {
				$html = '';
			}
		}
		return $html;
	}

	/**
	 * Check if all lessons and quizzes done before mark course complete.
	 *
	 * @since 1.5.8
	 *
	 * @param string $html HTML string.
	 * @return string
	 */
	public function tutor_lms_hide_course_complete_btn( $html ) {

		$completion_mode = tutor_utils()->get_option( 'course_completion_process' );
		if ( 'strict' !== $completion_mode ) {
			return $html;
		}

		$completed_lesson = tutor_utils()->get_completed_lesson_count_by_course();
		$lesson_count     = tutor_utils()->get_lesson_count_by_course();

		if ( $completed_lesson < $lesson_count ) {
			return '<div class="tutor-alert tutor-warning tutor-mt-28">
						<div class="tutor-alert-text">
							<span class="tutor-alert-icon tutor-fs-4 tutor-icon-circle-info tutor-mr-12"></span>
							<span>' . __( 'Complete all lessons to mark this course as complete', 'tutor' ) . '</span>
						</div>
					</div>';
		}

		$quizzes     = array();
		$assignments = array();

		$course_contents = tutor_utils()->get_course_contents_by_id();
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

		$required_assignment_pass = 0;

		foreach ( $assignments as $row ) {

			$submitted_assignment      = tutor_utils()->is_assignment_submitted( $row->ID );
			$is_reviewed_by_instructor = null === $submitted_assignment
											? false
											: get_comment_meta( $submitted_assignment->comment_ID, 'evaluate_time', true );

			if ( $submitted_assignment && $is_reviewed_by_instructor ) {
				$pass_mark  = tutor_utils()->get_assignment_option( $submitted_assignment->comment_post_ID, 'pass_mark' );
				$given_mark = get_comment_meta( $submitted_assignment->comment_ID, 'assignment_mark', true );

				if ( $given_mark < $pass_mark ) {
					$required_assignment_pass++;
				}
			} else {
				$required_assignment_pass++;
			}
		}

		$is_quiz_pass       = true;
		$required_quiz_pass = 0;

		if ( tutor_utils()->count( $quizzes ) ) {
			foreach ( $quizzes as $quiz ) {

				$attempt = tutor_utils()->get_quiz_attempt( $quiz->ID );
				if ( $attempt ) {
					$passing_grade     = tutor_utils()->get_quiz_option( $quiz->ID, 'passing_grade', 0 );
					$earned_percentage = $attempt->earned_marks > 0 ? ( number_format( ( $attempt->earned_marks * 100 ) / $attempt->total_marks ) ) : 0;

					if ( $earned_percentage < $passing_grade ) {
						$required_quiz_pass++;
						$is_quiz_pass = false;
					}
				} else {
					$required_quiz_pass++;
					$is_quiz_pass = false;
				}
			}
		}

		if ( ! $is_quiz_pass || $required_assignment_pass > 0 ) {
			$_msg           = '';
			$quiz_str       = _n( 'quiz', 'quizzes', $required_quiz_pass, 'tutor' );
			$assignment_str = _n( 'assignment', 'assignments', $required_assignment_pass, 'tutor' );

			if ( ! $is_quiz_pass && 0 == $required_assignment_pass ) {
				/* translators: %s: number of quiz pass required */
				$_msg = sprintf( __( 'You have to pass %1$s %2$s to complete this course.', 'tutor' ), $required_quiz_pass, $quiz_str );
			}
			if ( $is_quiz_pass && $required_assignment_pass > 0 ) {
				/* translators: %s: number of assignment pass required */
				$_msg = sprintf( __( 'You have to pass %1$s %2$s to complete this course.', 'tutor' ), $required_assignment_pass, $assignment_str );
			}
			if ( ! $is_quiz_pass && $required_assignment_pass > 0 ) {
				/* translators: %s: number of quiz pass required */
				$_msg = sprintf( __( 'You have to pass %1$s %2$s and %3$s %4$s to complete this course.', 'tutor' ), $required_quiz_pass, $quiz_str, $required_assignment_pass, $assignment_str );
			}

			return '<div class="tutor-alert tutor-warning tutor-mt-28">
						<div class="tutor-alert-text">
							<span class="tutor-alert-icon tutor-fs-4 tutor-icon-circle-info tutor-mr-12"></span>
							<span>' . $_msg . '</span>
						</div>
					</div>';
		}

		return $html;
	}

	/**
	 * Generate Gradebook
	 *
	 * @since 1.5.8
	 *
	 * @param string $html HTML string.
	 * @return string
	 */
	public function get_generate_greadbook( $html ) {
		if ( ! tutor_utils()->is_completed_course() ) {
			return '';
		}
		return $html;
	}

	/**
	 * Add social share content in header
	 *
	 * @since 1.6.3
	 * @return void
	 */
	public function social_share_content() {
		global $wp_query, $post;
		if ( $wp_query->is_single && ! empty( $wp_query->query_vars['post_type'] ) && $wp_query->query_vars['post_type'] === $this->course_post_type ) { ?>
			<!--Facebook-->
			<meta property="og:type" content="website"/>
			<meta property="og:image" content="<?php echo esc_url( get_tutor_course_thumbnail_src() ); ?>" />
			<meta property="og:description" content="<?php echo esc_html( $post->post_content ); ?>" />
			<!--Twitter-->
			<meta name="twitter:image" content="<?php echo esc_url( get_tutor_course_thumbnail_src() ); ?>">
			<meta name="twitter:description" content="<?php echo esc_html( $post->post_content ); ?>">
			<!--Google+-->
			<meta itemprop="image" content="<?php echo esc_url( get_tutor_course_thumbnail_src() ); ?>">
			<meta itemprop="description" content="<?php echo esc_html( $post->post_content ); ?>">
			<?php
		}
	}

	/**
	 * Delete associated enrollment
	 *
	 * @since 1.8.2
	 *
	 * @param integer $post_id post ID.
	 * @return void
	 */
	public function delete_associated_enrollment( $post_id ) {
		global $wpdb;

		$enroll_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT
				post_id
			FROM
				{$wpdb->postmeta}
			WHERE
				meta_key='_tutor_enrolled_by_order_id'
				AND meta_value = %d
			",
				$post_id
			)
		);

		if ( is_numeric( $enroll_id ) && $enroll_id > 0 ) {

			$course_id = get_post_field( 'post_parent', $enroll_id );
			$user_id   = get_post_field( 'post_author', $enroll_id );

			tutor_utils()->cancel_course_enrol( $course_id, $user_id );
		}
	}

	/**
	 * Reset course progress.
	 *
	 * @since 1.5.8
	 * @return void
	 */
	public function tutor_reset_course_progress() {
		tutor_utils()->checking_nonce();
		$course_id = Input::post( 'course_id' );

		if ( ! $course_id || ! is_numeric( $course_id ) || ! tutor_utils()->is_enrolled( $course_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid Course ID or Access Denied.', 'tutor' ) ) );
			return;
		}

		tutor_utils()->delete_course_progress( $course_id );
		wp_send_json_success( array( 'redirect_to' => tutor_utils()->get_course_first_lesson( $course_id ) ) );
	}

	/**
	 * Do enroll if guest attempt to enroll and course is free
	 *
	 * @since 1.9.8
	 *
	 * @param integer $course_id course ID.
	 * @param integer $user_id user ID.

	 * @return void
	 */
	public function enroll_after_login_if_attempt( int $course_id, int $user_id ) {
		$course_id = sanitize_text_field( $course_id );
		if ( $course_id ) {
			$is_purchasable = tutor_utils()->is_course_purchasable( $course_id );
			if ( ! $is_purchasable ) {
				tutor_utils()->do_enroll( $course_id, $order_id = 0, $user_id );
				do_action( 'guest_attempt_after_enrollment', $course_id );
			}
		}
	}

	/**
	 * Handle course enrollment
	 *
	 * @since 2.1.0
	 * @return void
	 */
	public function course_enrollment() {
		tutor_utils()->checking_nonce();

		$course_id = Input::post( 'course_id', 0, Input::TYPE_INT );
		$user_id   = get_current_user_id();

		if ( $course_id ) {
			$enroll = tutor_utils()->do_enroll( $course_id, 0, $user_id );
			if ( $enroll ) {
				wp_send_json_success( __( 'Enrollment successfully done!', 'tutor' ) );
			} else {
				wp_send_json_error( __( 'Enrollment failed, please try again!', 'tutor' ) );
			}
		} else {
			wp_send_json_error( __( 'Invalid course ID', 'tutor' ) );
		}
	}

	/**
	 * After trash a course direct to the course list page
	 *
	 * @since 2.1.7
	 *
	 * @param integer $post_id int course id.
	 *
	 * @return void
	 */
	public static function redirect_to_course_list_page( int $post_id ): void {
		$post = get_post( $post_id );
		if ( tutor()->course_post_type === $post->post_type ) {
			$is_gutenberg_enabled = tutor_utils()->get_option( 'enable_gutenberg_course_edit' );
			if ( ! $is_gutenberg_enabled ) {
				wp_safe_redirect( admin_url( 'admin.php?page=tutor' ) );
				exit;
			}
		}
	}

	/**
	 * Create or update WooCommerce product
	 *
	 * If product id not set it will create new one.
	 *
	 * @since 2.2.0
	 *
	 * @param string $title product title.
	 * @param string $reg_price product price.
	 * @param string $sale_price product sale price.
	 * @param int    $product_id product ID.
	 * @param string $status product status.
	 *
	 * @return integer
	 */
	public static function create_wc_product( $title, $reg_price, $sale_price, $product_id = 0, $status = 'publish' ) {
		$product_obj = new \WC_Product();
		if ( $product_id ) {
			$product_obj = wc_get_product( $product_id );
		}

		$product_obj->set_name( $title );
		$product_obj->set_status( $status );
		$product_obj->set_price( $reg_price );
		$product_obj->set_regular_price( $reg_price );

		if ( $sale_price > 0 ) {
			$product_obj->set_sale_price( $sale_price );
		} else {
			$product_obj->set_sale_price( null );
		}

		$product_obj->set_sold_individually( true );

		return $product_obj->save();
	}

}
