<?php
/**
 * Tutor MCP engagement tools (announcements, Q&A, reviews).
 *
 * @package Tutor\Mcp
 * @author  Themeum <support@themeum.com>
 * @link    https://themeum.com
 * @since   4.3.0
 */

namespace Tutor\Mcp\Abilities;

use Tutor\Mcp\Support\AbilityFactory;
use Tutor\Mcp\Support\Pagination;
use Tutor\Mcp\Support\Permissions;
use TUTOR\Announcements;
use TUTOR\Q_And_A;
use TUTOR\User;

defined( 'ABSPATH' ) || exit;

/**
 * Engagement tool registration and execute handlers.
 *
 * @since 4.3.0
 */
class EngagementAbilities {

	/**
	 * Register engagement abilities.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public function register(): void {
		AbilityFactory::tool(
			'tutor-mcp/list-announcements',
			'tutor-mcp-engagement',
			__( 'List Announcements', 'tutor' ),
			__( 'Paginated list of course announcements. Optional course_id filter.', 'tutor' ),
			array(
				'type'       => 'object',
				'properties' => array(
					'course_id' => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'page'      => array(
						'type'    => 'integer',
						'minimum' => 1,
						'default' => 1,
					),
					'per_page'  => array(
						'type'    => 'integer',
						'minimum' => 1,
						'maximum' => 50,
						'default' => 20,
					),
				),
			),
			static function ( array $input ): bool {
				if ( ! Permissions::can_manage_courses() ) {
					return false;
				}
				$course_id = absint( $input['course_id'] ?? 0 );
				if ( $course_id ) {
					return Permissions::can_edit_course( $course_id );
				}
				return true;
			},
			array( $this, 'list_announcements' )
		);

		AbilityFactory::tool(
			'tutor-mcp/get-announcement',
			'tutor-mcp-engagement',
			__( 'Get Announcement', 'tutor' ),
			__( 'Fetch a single course announcement by ID.', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'announcement_id' ),
				'properties' => array(
					'announcement_id' => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
				),
			),
			static function ( array $input ): bool {
				return self::can_manage_announcement( absint( $input['announcement_id'] ?? 0 ) );
			},
			array( $this, 'get_announcement' )
		);

		AbilityFactory::tool(
			'tutor-mcp/create-announcement',
			'tutor-mcp-engagement',
			__( 'Create Announcement', 'tutor' ),
			__( 'Create a course announcement (same path as tutor_announcement_create). notify_students defaults false.', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'course_id', 'title', 'content' ),
				'properties' => array(
					'course_id'       => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'title'           => array( 'type' => 'string' ),
					'content'         => array( 'type' => 'string' ),
					'notify_students' => array(
						'type'    => 'boolean',
						'default' => false,
					),
				),
			),
			static function ( array $input ): bool {
				// Match Ajax::create_or_update_announcement — can_user_manage( 'course', … ).
				return (bool) tutor_utils()->can_user_manage( 'course', absint( $input['course_id'] ?? 0 ) );
			},
			array( $this, 'create_announcement' ),
			AbilityFactory::WRITE_HINTS
		);

		AbilityFactory::tool(
			'tutor-mcp/update-announcement',
			'tutor-mcp-engagement',
			__( 'Update Announcement', 'tutor' ),
			__( 'Update a course announcement (same path as tutor_announcement_create). notify_students defaults false.', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'announcement_id' ),
				'properties' => array(
					'announcement_id' => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'course_id'       => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'title'           => array( 'type' => 'string' ),
					'content'         => array( 'type' => 'string' ),
					'notify_students' => array(
						'type'    => 'boolean',
						'default' => false,
					),
				),
			),
			static function ( array $input ): bool {
				$announcement_id = absint( $input['announcement_id'] ?? 0 );
				if ( ! self::can_manage_announcement( $announcement_id ) ) {
					return false;
				}
				$course_id = absint( $input['course_id'] ?? 0 );
				if ( $course_id ) {
					return (bool) tutor_utils()->can_user_manage( 'course', $course_id );
				}
				return true;
			},
			array( $this, 'update_announcement' ),
			AbilityFactory::WRITE_HINTS
		);

		AbilityFactory::tool(
			'tutor-mcp/delete-announcement',
			'tutor-mcp-engagement',
			__( 'Delete Announcement', 'tutor' ),
			__( 'Delete announcement(s). Requires confirm=true.', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'announcement_id', 'confirm' ),
				'properties' => array(
					'announcement_id' => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'confirm'         => array(
						'type'    => 'boolean',
						'default' => false,
					),
				),
			),
			static function ( array $input ): bool {
				return self::can_manage_announcement( absint( $input['announcement_id'] ?? 0 ) );
			},
			array( $this, 'delete_announcement' ),
			AbilityFactory::DESTRUCTIVE_HINTS
		);

		AbilityFactory::tool(
			'tutor-mcp/list-questions',
			'tutor-mcp-engagement',
			__( 'List Q&A Questions', 'tutor' ),
			__( 'Paginated Q&A questions. Optional course_id filter.', 'tutor' ),
			array(
				'type'       => 'object',
				'properties' => array(
					'course_id' => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'page'      => array(
						'type'    => 'integer',
						'minimum' => 1,
						'default' => 1,
					),
					'per_page'  => array(
						'type'    => 'integer',
						'minimum' => 1,
						'maximum' => 50,
						'default' => 20,
					),
				),
			),
			static function ( array $input ): bool {
				if ( ! Permissions::can_manage_courses() ) {
					return false;
				}
				$course_id = absint( $input['course_id'] ?? 0 );
				if ( $course_id ) {
					return Permissions::can_edit_course( $course_id );
				}
				return true;
			},
			array( $this, 'list_questions' )
		);

		AbilityFactory::tool(
			'tutor-mcp/reply-to-question',
			'tutor-mcp-engagement',
			__( 'Reply to Question', 'tutor' ),
			__( 'Reply to a Q&A question thread.', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'question_id', 'content' ),
				'properties' => array(
					'question_id' => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'content'     => array( 'type' => 'string' ),
				),
			),
			static function ( array $input ): bool {
				$question_id = absint( $input['question_id'] ?? 0 );
				return $question_id
					&& tutor_utils()->can_user_manage( 'qa_question', $question_id );
			},
			array( $this, 'reply_to_question' ),
			AbilityFactory::WRITE_HINTS
		);

		AbilityFactory::tool(
			'tutor-mcp/set-question-flags',
			'tutor-mcp-engagement',
			__( 'Set Question Flags', 'tutor' ),
			__( 'Toggle Q&A flags: read, important, archived. NOT resolved.', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'question_id', 'action' ),
				'properties' => array(
					'question_id' => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'action'      => array(
						'type' => 'string',
						'enum' => array( 'read', 'important', 'archived' ),
					),
				),
			),
			static function ( array $input ): bool {
				$question_id = absint( $input['question_id'] ?? 0 );
				return $question_id
					&& tutor_utils()->can_user_manage( 'qa_question', $question_id );
			},
			array( $this, 'set_question_flags' ),
			AbilityFactory::WRITE_HINTS
		);

		AbilityFactory::tool(
			'tutor-mcp/list-reviews',
			'tutor-mcp-engagement',
			__( 'List Reviews', 'tutor' ),
			__( 'Paginated course reviews (read-only).', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'course_id' ),
				'properties' => array(
					'course_id' => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'page'      => array(
						'type'    => 'integer',
						'minimum' => 1,
						'default' => 1,
					),
					'per_page'  => array(
						'type'    => 'integer',
						'minimum' => 1,
						'maximum' => 50,
						'default' => 20,
					),
				),
			),
			static function ( array $input ): bool {
				return Permissions::can_edit_course( absint( $input['course_id'] ?? 0 ) );
			},
			array( $this, 'list_reviews' )
		);
	}

	/**
	 * List announcements.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array
	 */
	public function list_announcements( array $input ): array {
		$bounds = Pagination::bounds( $input );
		$args   = array(
			'posts_per_page' => $bounds['per_page'],
			'paged'          => $bounds['page'],
		);

		if ( ! empty( $input['course_id'] ) ) {
			$course_id = absint( $input['course_id'] );
			if ( ! Permissions::can_edit_course( $course_id ) ) {
				return array(
					'announcements' => array(),
					'total'         => 0,
					'page'          => $bounds['page'],
					'per_page'      => $bounds['per_page'],
				);
			}
			$args['post_parent'] = $course_id;
		}

		// Match dashboard announcements: non-admins only see their own posts.
		if ( ! User::is_admin() ) {
			$args['author'] = get_current_user_id();
		}

		$query  = Announcements::get_announcements( $args );
		$result = array();
		if ( $query->have_posts() ) {
			foreach ( $query->posts as $post ) {
				$result[] = $this->format_announcement( $post );
			}
		}

		return array(
			'announcements' => $result,
			'total'         => (int) $query->found_posts,
			'page'          => $bounds['page'],
			'per_page'      => $bounds['per_page'],
		);
	}

	/**
	 * Get a single announcement.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function get_announcement( array $input ) {
		$post = $this->resolve_announcement( absint( $input['announcement_id'] ?? 0 ) );
		if ( is_wp_error( $post ) ) {
			return $post;
		}

		return $this->format_announcement( $post );
	}

	/**
	 * Create a course announcement.
	 *
	 * Mirrors Ajax::create_or_update_announcement create path.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function create_announcement( array $input ) {
		return $this->save_announcement( $input, 0 );
	}

	/**
	 * Update a course announcement.
	 *
	 * Mirrors Ajax::create_or_update_announcement update path.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function update_announcement( array $input ) {
		return $this->save_announcement( $input, absint( $input['announcement_id'] ?? 0 ) );
	}

	/**
	 * Delete an announcement.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function delete_announcement( array $input ) {
		$deny = Permissions::allow_write();
		if ( is_wp_error( $deny ) ) {
			return $deny;
		}

		if ( empty( $input['confirm'] ) ) {
			return new \WP_Error( 'tutor_mcp_confirm_required', __( 'Deleting requires confirm=true.', 'tutor' ) );
		}

		$id = absint( $input['announcement_id'] ?? 0 );
		Announcements::delete_announcements( 'delete', (string) $id );

		return array(
			'announcement_id' => $id,
			'deleted'         => true,
		);
	}

	/**
	 * Whether the current user can manage an announcement CPT.
	 *
	 * Same gate as Ajax delete/update and Announcements::can_manage_announcement().
	 *
	 * @since 4.3.0
	 *
	 * @param int $announcement_id Announcement post ID.
	 * @return bool
	 */
	private static function can_manage_announcement( int $announcement_id ): bool {
		if ( ! $announcement_id ) {
			return false;
		}

		$post = get_post( $announcement_id );
		return $post
			&& tutor()->announcement_post_type === $post->post_type
			&& tutor_utils()->can_user_manage( 'announcement', $announcement_id );
	}

	/**
	 * Resolve and validate an announcement post.
	 *
	 * @since 4.3.0
	 *
	 * @param int $announcement_id Announcement post ID.
	 * @return \WP_Post|\WP_Error
	 */
	private function resolve_announcement( int $announcement_id ) {
		$post = get_post( $announcement_id );
		if ( ! $post || tutor()->announcement_post_type !== $post->post_type ) {
			return new \WP_Error( 'tutor_mcp_not_found', __( 'Announcement not found.', 'tutor' ) );
		}

		return $post;
	}

	/**
	 * Format announcement for MCP responses.
	 *
	 * @since 4.3.0
	 *
	 * @param \WP_Post $post Announcement post.
	 * @return array
	 */
	private function format_announcement( \WP_Post $post ): array {
		return array(
			'id'        => (int) $post->ID,
			'title'     => $post->post_title,
			'content'   => $post->post_content,
			'course_id' => (int) $post->post_parent,
			'date'      => $post->post_date,
			'author_id' => (int) $post->post_author,
		);
	}

	/**
	 * Create or update an announcement (Ajax::create_or_update_announcement without JSON exit).
	 *
	 * @since 4.3.0
	 *
	 * @param array $input           Input.
	 * @param int   $announcement_id Existing ID for update; 0 for create.
	 * @return array|\WP_Error
	 */
	private function save_announcement( array $input, int $announcement_id ) {
		$deny = Permissions::allow_write();
		if ( is_wp_error( $deny ) ) {
			return $deny;
		}

		$existing = null;
		if ( $announcement_id ) {
			$existing = $this->resolve_announcement( $announcement_id );
			if ( is_wp_error( $existing ) ) {
				return $existing;
			}
		}

		$course_id = absint( $input['course_id'] ?? 0 );
		if ( ! $course_id && $existing ) {
			$course_id = (int) $existing->post_parent;
		}

		// Match Ajax::create_or_update_announcement permission + validation.
		if ( ! tutor_utils()->can_user_manage( 'course', $course_id ) ) {
			return new \WP_Error( 'tutor_mcp_forbidden', __( 'Access Denied', 'tutor' ) );
		}

		if ( tutor()->course_post_type !== get_post_type( $course_id ) ) {
			return new \WP_Error( 'tutor_mcp_not_found', __( 'Course not found.', 'tutor' ) );
		}

		if ( $announcement_id ) {
			if ( ! tutor_utils()->can_user_manage( 'announcement', $announcement_id ) ) {
				return new \WP_Error( 'tutor_mcp_forbidden', tutor_utils()->error_message() );
			}
		}

		$title   = array_key_exists( 'title', $input )
			? sanitize_text_field( (string) $input['title'] )
			: ( $existing ? (string) $existing->post_title : '' );
		$content = array_key_exists( 'content', $input )
			? wp_kses_post( (string) $input['content'] )
			: ( $existing ? (string) $existing->post_content : '' );

		if ( '' === $title ) {
			return new \WP_Error( 'tutor_mcp_invalid', __( 'Announcement title required', 'tutor' ) );
		}
		if ( '' === $content ) {
			return new \WP_Error( 'tutor_mcp_invalid', __( 'Announcement summary required', 'tutor' ) );
		}

		$form_data = array(
			'post_type'    => tutor()->announcement_post_type,
			'post_title'   => $title,
			'post_content' => $content,
			'post_parent'  => $course_id,
			'post_status'  => 'publish',
		);

		if ( $announcement_id ) {
			$form_data['ID'] = $announcement_id;
		} else {
			$form_data['post_author'] = get_current_user_id();
		}

		$post_id = wp_insert_post( $form_data, true );
		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}
		if ( ! $post_id ) {
			return new \WP_Error( 'tutor_mcp_save_failed', __( 'Something Went Wrong!', 'tutor' ) );
		}

		$action_type  = $announcement_id ? 'update' : 'create';
		$announcement = get_post( $post_id );

		// Opt-in notify — same hook signature as Ajax (emails fire only when true).
		if ( ! empty( $input['notify_students'] ) && $announcement ) {
			/**
			 * Fires after an announcement is created or updated.
			 *
			 * @since 1.7.9
			 *
			 * @param int      $post_id      Announcement post ID.
			 * @param \WP_Post $announcement Announcement post object.
			 * @param string   $action_type  create|update.
			 */
			do_action( 'tutor_announcements/after/save', $post_id, $announcement, $action_type ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
		}

		if ( ! $announcement instanceof \WP_Post ) {
			return new \WP_Error( 'tutor_mcp_save_failed', __( 'Something Went Wrong!', 'tutor' ) );
		}

		return $this->format_announcement( $announcement );
	}

	/**
	 * List Q&A questions.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array
	 */
	public function list_questions( array $input ): array {
		$bounds = Pagination::bounds( $input );
		$args   = array();
		if ( ! empty( $input['course_id'] ) ) {
			$args['course_id'] = absint( $input['course_id'] );
		}

		$questions = tutor_utils()->get_qa_questions( $bounds['offset'], $bounds['per_page'], '', null, null, null, null, false, $args );
		$total     = (int) tutor_utils()->get_qa_questions( 0, 0, '', null, null, null, null, true, $args );
		$rows      = array();

		if ( is_array( $questions ) ) {
			foreach ( $questions as $q ) {
				$rows[] = array(
					'question_id' => (int) $q->comment_ID,
					'course_id'   => (int) $q->comment_post_ID,
					'user_id'     => (int) $q->user_id,
					'content'     => $q->comment_content,
					'date'        => $q->comment_date,
				);
			}
		}

		return array(
			'questions' => $rows,
			'total'     => $total,
			'page'      => $bounds['page'],
			'per_page'  => $bounds['per_page'],
		);
	}

	/**
	 * Reply to a Q&A question.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function reply_to_question( array $input ) {
		$deny = Permissions::allow_write();
		if ( is_wp_error( $deny ) ) {
			return $deny;
		}

		$question_id = absint( $input['question_id'] ?? 0 );
		$content     = wp_kses_post( (string) ( $input['content'] ?? '' ) );
		if ( '' === $content ) {
			return new \WP_Error( 'tutor_mcp_invalid', __( 'Reply content is required.', 'tutor' ) );
		}

		$question = get_comment( $question_id );
		if ( ! $question || 'tutor_q_and_a' !== $question->comment_type ) {
			return new \WP_Error( 'tutor_mcp_not_found', __( 'Question not found.', 'tutor' ) );
		}

		$user = wp_get_current_user();
		$qna  = new Q_And_A( false );

		$qna_object              = new \stdClass();
		$qna_object->course_id   = (int) $question->comment_post_ID;
		$qna_object->question_id = $question_id;
		$qna_object->qna_text    = $content;
		$qna_object->user_id     = get_current_user_id();
		$qna_object->user        = $user;
		$qna_object->date        = current_time( 'mysql' );

		$reply_id = $qna->inset_qna( $qna_object );

		return array(
			'question_id' => $question_id,
			'reply_id'    => (int) $reply_id,
		);
	}

	/**
	 * Toggle Q&A flags.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function set_question_flags( array $input ) {
		$deny = Permissions::allow_write();
		if ( is_wp_error( $deny ) ) {
			return $deny;
		}

		$question_id = absint( $input['question_id'] ?? 0 );
		$action      = sanitize_text_field( (string) ( $input['action'] ?? '' ) );
		$allowed     = array( 'read', 'important', 'archived' );

		if ( ! in_array( $action, $allowed, true ) ) {
			return new \WP_Error( 'tutor_mcp_invalid', __( 'Invalid flag action.', 'tutor' ) );
		}

		$question = get_comment( $question_id );
		if ( ! $question || 'tutor_q_and_a' !== $question->comment_type ) {
			return new \WP_Error( 'tutor_mcp_not_found', __( 'Question not found.', 'tutor' ) );
		}

		$qna       = new Q_And_A( false );
		$new_value = $qna->trigger_qna_action( $question_id, $action, 'tutor-mcp', get_current_user_id() );

		return array(
			'question_id' => $question_id,
			'action'      => $action,
			'new_value'   => $new_value,
		);
	}

	/**
	 * List reviews for a course.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array
	 */
	public function list_reviews( array $input ): array {
		$bounds    = Pagination::bounds( $input );
		$course_id = absint( $input['course_id'] ?? 0 );

		$reviews = tutor_utils()->get_course_reviews( $course_id, $bounds['offset'], $bounds['per_page'] );
		$total   = (int) tutor_utils()->get_course_reviews( $course_id, 0, 0, true );
		$rows    = array();

		if ( is_array( $reviews ) ) {
			foreach ( $reviews as $r ) {
				$rows[] = array(
					'review_id' => (int) $r->comment_ID,
					'user_id'   => (int) $r->user_id,
					'rating'    => (float) ( $r->rating ?? 0 ),
					'content'   => $r->comment_content,
					'date'      => $r->comment_date,
				);
			}
		}

		return array(
			'course_id' => $course_id,
			'reviews'   => $rows,
			'total'     => $total,
			'page'      => $bounds['page'],
			'per_page'  => $bounds['per_page'],
		);
	}
}
