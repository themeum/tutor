<?php
/**
 * Manage Lesson
 *
 * @package Tutor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace TUTOR;

defined( 'ABSPATH' ) || exit;

use Tutor\Helpers\HttpHelper;
use Tutor\Helpers\QueryHelper;
use Tutor\Helpers\ValidationHelper;
use Tutor\Models\EnrollmentModel;
use Tutor\Models\LessonModel;
use Tutor\Traits\JsonResponse;
use WP_Post;

/**
 * Lesson class
 *
 * @since 1.0.0
 */
class Lesson extends Tutor_Base {
	use JsonResponse;

	/**
	 * Preview meta key
	 *
	 * @since 4.0.0
	 * @var string
	 */
	const PREVIEW_META_KEY = '_is_preview';

	/**
	 * Lesson post type
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	private $post_type;

	/**
	 * Determine if legacy mode is enabled or not
	 *
	 * @since 4.0.0
	 *
	 * @var bool
	 */
	private $is_legacy_learning_mode = false;

	/**
	 * Register hooks
	 *
	 * @since 1.0.0
	 * @since 3.7.0 param register hook added.
	 *
	 * @param bool $register_hooks register hooks or not.
	 *
	 * @return void
	 */
	public function __construct( $register_hooks = true ) {
		parent::__construct();

		$this->post_type = tutor()->lesson_post_type;

		$this->is_legacy_learning_mode = Options_V2::LEARNING_MODE_LEGACY === tutor_utils()->get_option( 'learning_mode' );

		if ( ! $register_hooks ) {
			return;
		}

		add_action( 'save_post_' . $this->lesson_post_type, array( $this, 'save_lesson_meta' ) );

		add_action( 'wp_ajax_tutor_lesson_details', array( $this, 'ajax_lesson_details' ) );
		add_action( 'wp_ajax_tutor_save_lesson', array( $this, 'ajax_save_lesson' ) );
		add_action( 'wp_ajax_tutor_delete_lesson', array( $this, 'ajax_delete_lesson' ) );

		add_filter( 'get_sample_permalink', array( $this, 'change_lesson_permalink' ), 10, 2 );

		/**
		 * Frontend Action
		 */
		add_action( 'template_redirect', array( $this, 'mark_lesson_complete' ) );

		add_action( 'wp_ajax_tutor_render_lesson_content', array( $this, 'tutor_render_lesson_content' ) );

		/**
		 * For public course access
		 */
		add_action( 'wp_ajax_nopriv_tutor_render_lesson_content', array( $this, 'tutor_render_lesson_content' ) );

		/**
		 * Autoplay next video
		 *
		 * @since 1.4.9
		 */
		add_action( 'wp_ajax_autoload_next_course_content', array( $this, 'autoload_next_course_content' ) );

		/**
		 * Load next course item after click complete button
		 *
		 * @since 1.5.3
		 */
		add_action( 'tutor_lesson_completed_after', array( $this, 'tutor_lesson_completed_after' ), 999 );

		/**
		 * Lesson comment & reply ajax handler
		 *
		 * @since 2.0.0
		 */
		add_action( 'wp_ajax_tutor_single_course_lesson_load_more', array( $this, 'ajax_single_course_lesson_load_more' ) );
		add_action( 'wp_ajax_tutor_create_lesson_comment', array( $this, 'ajax_single_course_lesson_load_more' ) );
		add_action( 'wp_ajax_tutor_delete_lesson_comment', array( $this, 'ajax_delete_lesson_comment' ) );
		add_action( 'wp_ajax_tutor_update_lesson_comment', array( $this, 'ajax_update_lesson_comment' ) );
		add_action( 'wp_ajax_tutor_reply_lesson_comment', array( $this, 'ajax_reply_lesson_comment' ) );
		add_action( 'wp_ajax_tutor_load_lesson_comments', array( $this, 'ajax_load_lesson_comments' ) );
		add_action( 'wp_ajax_tutor_load_comment_replies', array( $this, 'ajax_load_comment_replies' ) );

		// Add lesson title as nav item & render single content on the learning area.
		add_action( "tutor_learning_area_nav_item_{$this->post_type}", array( $this, 'render_nav_item' ), 10, 2 );
		add_action( "tutor_single_content_{$this->post_type}", array( $this, 'render_single_content' ) );
	}

	/**
	 * Manage load more & comment create
	 *
	 * @since 2.0.6
	 *
	 * @since 4.0.0 different template returned
	 *
	 * @return void  send wp json data
	 */
	public function ajax_single_course_lesson_load_more() {
		tutor_utils()->checking_nonce();

		$comment_id = 0;
		$comment   = Input::post( 'comment', '', Input::TYPE_TEXTAREA );
		$lesson_id = Input::post( 'comment_post_ID', 0, Input::TYPE_INT );

		if ( ! self::is_comment_enabled_for_lesson( $lesson_id ) ) {
			$this->response_bad_request( tutor_utils()->error_message( 'invalid_req' ) );
		}

		if ( 'tutor_create_lesson_comment' === Input::post( 'action' ) && strlen( $comment ) > 0 ) {
			if ( ! self::can_post_lesson_comment( $lesson_id ) ) {
				wp_send_json_error( tutor_utils()->error_message() );
				return;
			}

			$comment_data = array(
				'comment_content' => $comment,
				'comment_post_ID' => $lesson_id,
				'comment_parent'  => Input::post( 'comment_parent', 0, Input::TYPE_INT ),
			);
			$comment_id   = self::create_comment( $comment_data );
			do_action( 'tutor_new_comment_added', $comment_data );
		}

		if ( ! $this->is_legacy_learning_mode ) {
			$html = '';
			if ( $comment_id ) {
				ob_start();
				tutor_load_template(
					'learning-area.lesson.comment-card',
					array(
						'comment_item' => get_comment( $comment_id ),
						'lesson_id'    => $lesson_id,
						'user_id'      => get_current_user_id(),
					)
				);
				$html = ob_get_clean();
			}

			$count = self::get_comments(
				array(
					'post_id' => $lesson_id,
					'parent'  => 0,
					'count'   => true,
				)
			);

			wp_send_json_success(
				array(
					'html'  => $html,
					'count' => $count,
				)
			);
		}

		ob_start();
		tutor_load_template( 'single.lesson.comment' );
		$html = ob_get_clean();
		wp_send_json_success( array( 'html' => $html ) );
	}

	/**
	 * Delete lesson comment by AJAX
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function ajax_delete_lesson_comment() {
		tutor_utils()->check_nonce();
		$comment_id = Input::post( 'comment_id', 0, Input::TYPE_INT );
		if ( ! $comment_id ) {
			$this->response_bad_request( __( 'Invalid comment ID', 'tutor' ) );
		}

		$comment = get_comment( $comment_id );
		if ( ! $comment ) {
			$this->response_bad_request( __( 'Invalid comment ID', 'tutor' ) );
		}

		$lesson_id = $comment->comment_post_ID;
		$parent_id = $comment->comment_parent;
		$is_reply  = $parent_id > 0;

		if ( ! self::is_comment_enabled_for_lesson( $lesson_id ) ) {
			$this->response_bad_request( tutor_utils()->error_message( 'invalid_req' ) );
		}

		if ( get_current_user_id() === (int) $comment->user_id || tutor_utils()->can_user_manage( 'lesson', $lesson_id ) ) {
			wp_delete_comment( $comment_id, true );

			$total_comments = self::get_comments(
				array(
					'post_id' => $lesson_id,
					'parent'  => 0,
					'count'   => true,
				)
			);

			wp_send_json_success(
				array(
					'message'    => __( 'Comment deleted successfully', 'tutor' ),
					'count'      => $total_comments,
					'is_reply'   => $is_reply,
					'parent_id'  => $parent_id,
					'comment_id' => $comment_id,
				)
			);
		} else {
			$this->response_bad_request( __( 'You are not allowed to delete this comment', 'tutor' ) );
		}
	}

	/**
	 * Update lesson comment by AJAX
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function ajax_update_lesson_comment() {
		tutor_utils()->check_nonce();

		$comment_id = Input::post( 'comment_id', 0, Input::TYPE_INT );
		if ( ! $comment_id ) {
			$this->response_bad_request( __( 'Invalid comment ID', 'tutor' ) );
		}

		$comment = get_comment( $comment_id );
		if ( ! $comment ) {
			$this->response_bad_request( __( 'Invalid comment ID', 'tutor' ) );
		}

		$comment_content = Input::post( 'comment', '', Input::TYPE_KSES_POST );
		$user_id         = get_current_user_id();
		$lesson_id       = $comment->comment_post_ID;

		if ( ! self::is_comment_enabled_for_lesson( $lesson_id ) ) {
			$this->response_bad_request( tutor_utils()->error_message( 'invalid_req' ) );
		}

		if ( $user_id === (int) $comment->user_id ) {
			wp_update_comment(
				array(
					'comment_ID'      => $comment_id,
					'comment_content' => $comment_content,
				)
			);

			$comment->comment_content = $comment_content;
			$is_reply                 = $comment->comment_parent > 0;

			ob_start();
			tutor_load_template(
				'learning-area.lesson.comment-card',
				array(
					'comment_item' => $comment,
					'lesson_id'    => $lesson_id,
					'user_id'      => $user_id,
					'is_reply'     => $is_reply,
				)
			);
			$html = ob_get_clean();

			wp_send_json_success(
				array(
					'html'       => $html,
					'is_reply'   => $is_reply,
					'parent_id'  => $is_reply ? $comment->comment_parent : 0,
					'comment_id' => $comment_id,
				)
			);
		} else {
			$this->response_bad_request( tutor_utils()->error_message() );
		}
	}

	/**
	 * Saving lesson meta and assets
	 *
	 * @since 1.0.0
	 *
	 * @param integer $post_ID post ID.
	 *
	 * @return void
	 */
	public function save_lesson_meta( $post_ID ) {
		$thumbnail_id = Input::post( 'thumbnail_id', 0, Input::TYPE_INT );
		if ( $thumbnail_id ) {
			update_post_meta( $post_ID, '_thumbnail_id', $thumbnail_id );
		} else {
			delete_post_meta( $post_ID, '_thumbnail_id' );
		}

		$video_source = sanitize_text_field( tutor_utils()->array_get( 'video.source', $_POST ) ); //phpcs:ignore
		if ( '-1' === $video_source ) {
			delete_post_meta( $post_ID, '_video' );
		} elseif ( $video_source ) {

			// Sanitize data through helper method.
			$video = Input::sanitize_array(
				$_POST['video'] ?? array(), //phpcs:ignore
				array(
					'source_external_url' => 'esc_url',
					'source_embedded'     => 'wp_kses_post',
				),
				true
			);
			update_post_meta( $post_ID, '_video', $video );
		}

		// Attachments.
		$attachments = array();
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( ! empty( $_POST['tutor_attachments'] ) ) {
			//phpcs:ignore -- data sanitized through helper method.
			$attachments = tutor_utils()->sanitize_array( wp_unslash( $_POST['tutor_attachments'] ) );
			$attachments = array_unique( $attachments );
		}

		/**
		 * If !empty attachment then update meta else
		 * delete meta key to prevent empty data in db
		 *
		 * @since 1.8.9
		*/
		if ( ! empty( $attachments ) ) {
			update_post_meta( $post_ID, '_tutor_attachments', $attachments );
		} else {
			delete_post_meta( $post_ID, '_tutor_attachments' );
		}
	}

	/**
	 * Get lesson details data.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function ajax_lesson_details() {
		if ( ! tutor_utils()->is_nonce_verified() ) {
			$this->response_bad_request( tutor_utils()->error_message( 'nonce' ) );
		}

		$topic_id  = Input::post( 'topic_id', 0, Input::TYPE_INT );
		$lesson_id = Input::post( 'lesson_id', 0, Input::TYPE_INT );

		if ( ! $topic_id || ! $lesson_id ) {
			$this->response_bad_request( tutor_utils()->error_message( 'invalid_req' ) );
		}

		if ( ! tutor_utils()->can_user_manage( 'topic', $topic_id ) || ! tutor_utils()->can_user_manage( 'lesson', $lesson_id ) ) {
			$this->response_bad_request( tutor_utils()->error_message() );
		}

		$data = LessonModel::get_lesson_details( $lesson_id );

		$this->json_response(
			__( 'Lesson data fetched successfully', 'tutor' ),
			$data
		);
	}

	/**
	 * Create or update lesson.
	 *
	 * @since 1.0.0
	 * @since 1.5.1 updated
	 * @since 3.0.0 refactor and response updated.
	 *
	 * @return void
	 */
	public function ajax_save_lesson() {
		if ( ! tutor_utils()->is_nonce_verified() ) {
			$this->response_bad_request( tutor_utils()->error_message( 'nonce' ) );
		}

		/**
		 * Allow iframe inside lesson content to support
		 * embed video & other stuff
		 *
		 * @since 2.1.6
		 */
		add_filter( 'wp_kses_allowed_html', Input::class . '::allow_iframe', 10, 2 );

		$lesson_id = Input::post( 'lesson_id', 0, Input::TYPE_INT );
		$topic_id  = Input::post( 'topic_id', 0, Input::TYPE_INT );

		if ( ! $topic_id || ! tutor_utils()->can_user_manage( 'topic', $topic_id ) ) {
			$this->response_bad_request( tutor_utils()->error_message() );
		}

		$is_update = $lesson_id > 0;

		$title            = Input::post( 'title' );
		$description      = Input::post( 'description', '', Input::TYPE_KSES_POST );
		$is_html_active   = Input::post( 'is_html_active' ) === 'true' ? true : false;
		$raw_html_content = Input::post( 'tutor_lesson_modal_editor', '', Input::TYPE_KSES_POST );
		$post_content     = $is_html_active ? $raw_html_content : $description;

		$rules = array(
			'topic_id'  => 'required|numeric',
			'lesson_id' => 'if_input|numeric',
		);

		//phpcs:ignore WordPress.Security.NonceVerification.Missing
		$params = Input::sanitize_array( $_POST, array( 'description' => 'wp_kses_post' ) );

		$validation = ValidationHelper::validate( $rules, $params );
		if ( ! $validation->success ) {
			$this->json_response(
				__( 'Invalid inputs', 'tutor' ),
				$validation->errors,
				HttpHelper::STATUS_UNPROCESSABLE_ENTITY
			);
		}

		$lesson_data = array(
			'post_type'      => $this->lesson_post_type,
			'post_title'     => wp_slash( $title ), // Note: Added wp_slash to support latex syntaxes.
			'post_name'      => sanitize_title( $title ),
			'post_content'   => wp_slash( $post_content ),
			'post_status'    => 'publish',
			'comment_status' => 'open',
			'post_author'    => get_current_user_id(),
			'post_parent'    => $topic_id,
		);

		if ( ! $is_update ) {
			$lesson_data['menu_order'] = tutor_utils()->get_next_course_content_order_id( $topic_id );
			$lesson_id                 = wp_insert_post( $lesson_data );

			if ( $lesson_id ) {
				do_action( 'tutor/lesson/created', $lesson_id );
			} else {
				$this->json_response(
					tutor_utils()->error_message(),
					null,
					HttpHelper::STATUS_INTERNAL_SERVER_ERROR
				);
			}
		} else {
			$lesson_data['ID'] = $lesson_id;

			if ( ! tutor_utils()->can_user_manage( 'lesson', $lesson_id ) ) {
				$this->json_response(
					tutor_utils()->error_message(),
					null,
					HttpHelper::STATUS_FORBIDDEN
				);
			}

			do_action( 'tutor/lesson_update/before', $lesson_id );
			wp_update_post( $lesson_data );
			do_action( 'tutor/lesson_update/after', $lesson_id );
		}

		if ( $is_update ) {
			$this->json_response(
				__( 'Lesson updated successfully', 'tutor' ),
				$lesson_id
			);
		} else {
			$this->json_response(
				__( 'Lesson created successfully', 'tutor' ),
				$lesson_id,
				HttpHelper::STATUS_CREATED
			);
		}
	}

	/**
	 * Delete Lesson from course builder by ID
	 *
	 * @since 1.0.0
	 * @since 3.0.0 refactor and update response.
	 *
	 * @return void
	 */
	public function ajax_delete_lesson() {
		tutor_utils()->check_nonce();

		$lesson_id = Input::post( 'lesson_id', 0, Input::TYPE_INT );
		if ( ! $lesson_id ) {
			$this->response_bad_request( tutor_utils()->error_message( 'invalid_req' ) );
		}

		if ( ! tutor_utils()->can_user_manage( 'lesson', $lesson_id ) ) {
			$this->response_bad_request( tutor_utils()->error_message() );
		}

		$content   = __( 'Lesson', 'tutor' );
		$post_type = get_post_type( $lesson_id );
		if ( tutor()->assignment_post_type === $post_type ) {
			$content = __( 'Assignment', 'tutor' );
		}

		do_action( 'tutor_before_delete_course_content', 0, $lesson_id );

		wp_delete_post( $lesson_id, true );
		/* translators: %s refers to the name of the content being deleted */
		$this->json_response( sprintf( __( '%s deleted successfully', 'tutor' ), $content ) );
	}


	/**
	 * Changed the URI based
	 *
	 * @since 1.0.0
	 *
	 * @param string  $uri URI.
	 * @param integer $lesson_id lesson ID.
	 *
	 * @return string
	 */
	public function change_lesson_permalink( $uri, $lesson_id ) {
		$post = get_post( $lesson_id );

		if ( $post && $post->post_type === $this->lesson_post_type ) {
			$uri_base = trailingslashit( site_url() );

			$sample_course = 'sample-course';
			$is_course     = tutor_utils()->get_course_id_by( 'lesson', get_the_ID() );
			if ( $is_course ) {
				$course = get_post( $is_course );
				if ( $course ) {
					$sample_course = $course->post_name;
				}
			}

			$new_course_base = $uri_base . "course/{$sample_course}/lesson/%pagename%/";
			$uri[0]          = $new_course_base;
		}

		return $uri;
	}

	/**
	 * Mark lesson completed
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function mark_lesson_complete() {
		if ( 'tutor_complete_lesson' !== Input::post( 'tutor_action' ) ) {
			return;
		}

		tutor_utils()->checking_nonce();

		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			die( esc_html__( 'Please Sign-In', 'tutor' ) );
		}

		$lesson_id = Input::post( 'lesson_id', 0, Input::TYPE_INT );
		if ( ! $lesson_id ) {
			return;
		}

		$course_id = tutor_utils()->get_course_id_by( 'lesson', $lesson_id );
		if ( ! $course_id ) {
			return;
		}

		if ( ! tutor_utils()->is_enrolled( $course_id ) ) {
			die( esc_html( tutor_utils()->error_message() ) );
		}

		$validated = apply_filters( 'tutor_validate_lesson_complete', true, $user_id, $lesson_id );
		if ( ! $validated ) {
			return;
		}

		do_action( 'tutor_lesson_completed_before', $lesson_id );
		/**
		 * Marking lesson at user meta, meta format, _tutor_completed_lesson_id_{id} and value = tutor_time();
		 */
		LessonModel::mark_lesson_complete( $lesson_id );

		do_action( 'tutor_lesson_completed_email_after', $lesson_id, $user_id );
		do_action( 'tutor_lesson_completed_after', $lesson_id, $user_id );
	}

	/**
	 * Render the lesson content
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function tutor_render_lesson_content() {
		tutor_utils()->checking_nonce();

		$lesson_id = Input::post( 'lesson_id', 0, Input::TYPE_INT );
		if ( ! $lesson_id ) {
			$this->response_bad_request( tutor_utils()->error_message( 'invalid_req' ) );
		}

		$course_id = tutor_utils()->get_course_id_by( 'lesson', $lesson_id );

		// Course must be public or current user must be enrolled to access this lesson.
		if ( ! Course_List::is_public( $course_id ) && ! EnrollmentModel::is_enrolled( $course_id ) ) {

			$allowed = User::is_admin() ? true : tutor_utils()->is_instructor_of_this_course( get_current_user_id(), $course_id );

			if ( ! $allowed ) {
				$this->response_bad_request( tutor_utils()->error_message() );
				exit;
			}
		}

		ob_start();
		global $post;

		$post = get_post( $lesson_id ); //phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		setup_postdata( $post );
		tutor_lesson_content();
		wp_reset_postdata();

		$html = ob_get_clean();

		wp_send_json_success( array( 'html' => $html ) );
	}

	/**
	 * Load next course item automatically
	 *
	 * @since 1.4.9
	 *
	 * @return void
	 */
	public function autoload_next_course_content() {
		tutor_utils()->checking_nonce();

		$post_id   = Input::post( 'post_id', 0, Input::TYPE_INT );
		$course_id = tutor_utils()->get_course_id_by_content( $post_id );
		if ( ! $course_id || ! EnrollmentModel::is_enrolled( $course_id ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$content_id = tutor_utils()->get_post_id( $post_id );
		$contents   = tutor_utils()->get_course_prev_next_contents_by_id( $content_id );

		$autoload_course_content = (bool) UserPreference::get( 'auto_play_next', false, get_current_user_id() );
		$next_url                = false;
		if ( $autoload_course_content && $contents->next_id ) {
			$next_url = get_the_permalink( $contents->next_id );
		}

		wp_send_json_success( array( 'next_url' => $next_url ) );
	}

	/**
	 * Load next course item after click complete button
	 *
	 * @since 1.5.3
	 *
	 * @param integer $content_id content ID.
	 * @return void
	 */
	public function tutor_lesson_completed_after( $content_id ) {
		$contents                = tutor_utils()->get_course_prev_next_contents_by_id( $content_id );
		$autoload_course_content = (bool) get_tutor_option( 'autoload_next_course_content' );
		if ( $autoload_course_content ) {
			wp_safe_redirect( get_the_permalink( $contents->next_id ) );
		} else {
			wp_safe_redirect( get_the_permalink( $content_id ) );
		}
		die();
	}

	/**
	 * Check user can post lesson comment.
	 *
	 * @since 4.0.0
	 *
	 * @param int $lesson_id lesson id.
	 * @param int $user_id user id. default 0 means current user id.
	 *
	 * @return bool true if user can post lesson comment, false otherwise.
	 */
	private static function can_post_lesson_comment( $lesson_id, $user_id = 0 ) {
		$course_id = tutor_utils()->get_course_id_by( 'lesson', $lesson_id );
		if ( ! $course_id ) {
			return false;
		}

		$can_post = EnrollmentModel::is_enrolled( $course_id, $user_id ) || tutor_utils()->can_user_manage( 'course', $course_id, $user_id );
		return $can_post;
	}

	/**
	 * Replay lesson comment
	 *
	 * @since 1.0.0
	 *
	 * @return void|null
	 */
	public function ajax_reply_lesson_comment() {
		tutor_utils()->checking_nonce();

		$lesson_id      = Input::post( 'comment_post_ID', 0, Input::TYPE_INT );
		$comment_parent = Input::post( 'comment_parent', 0, Input::TYPE_INT );
		$comment        = Input::post( 'comment', '', Input::TYPE_TEXTAREA );

		if ( ! $lesson_id || ! $comment_parent || 0 === strlen( $comment ) ) {
			wp_send_json_error( tutor_utils()->error_message( 'invalid_req' ) );
			return;
		}

		if ( ! self::can_post_lesson_comment( $lesson_id ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
			return;
		}

		$parent_comment = get_comment( $comment_parent );
		if ( ! $parent_comment || (int) $parent_comment->comment_post_ID !== $lesson_id ) {
			wp_send_json_error( tutor_utils()->error_message( 'invalid_req' ) );
			return;
		}

		$comment_data = array(
			'comment_content' => $comment,
			'comment_post_ID' => $lesson_id,
			'comment_parent'  => $comment_parent,
		);

		if ( ! self::is_comment_enabled_for_lesson( $comment_data['comment_post_ID'] ) ) {
			$this->response_bad_request( tutor_utils()->error_message( 'invalid_req' ) );
		}

		$comment_id = self::create_comment( $comment_data );
		if ( false === $comment_id ) {
			wp_send_json_error();
			return;
		}

		$reply = get_comment( $comment_id );
		do_action( 'tutor_reply_lesson_comment_thread', $comment_id, $comment_data );

		if ( ! $this->is_legacy_learning_mode ) {
			$lesson_id    = $comment_data['comment_post_ID'];
			$parent_id    = $comment_data['comment_parent'];
			$comment_item = get_comment( $parent_id );
			$user_id      = get_current_user_id();

			$replies = self::get_comments(
				array(
					'post_id' => $lesson_id,
					'parent'  => $parent_id,
					'order'   => 'ASC',
				)
			);

			$is_first_reply = ( is_array( $replies ) ? count( $replies ) : 0 ) === 1;

			ob_start();
			if ( $is_first_reply ) {
				// For the first reply, we need the wrapper and the toggle button.
				tutor_load_template(
					'learning-area.lesson.comment-replies',
					array(
						'lesson_id'    => $lesson_id,
						'comment_item' => $comment_item,
						'user_id'      => $user_id,
						'replies'      => $replies,
					)
				);
			} else {
				// Just the card for subsequent replies.
				tutor_load_template(
					'learning-area.lesson.comment-card',
					array(
						'comment_item' => get_comment( $comment_id ),
						'lesson_id'    => $lesson_id,
						'user_id'      => $user_id,
						'is_reply'     => true,
					)
				);
			}
			$html = ob_get_clean();

			$total_comments = self::get_comments(
				array(
					'post_id' => $lesson_id,
					'parent'  => 0,
					'count'   => true,
				)
			);

			wp_send_json_success(
				array(
					'html'           => $html,
					'count'          => $total_comments,
					'is_first_reply' => $is_first_reply,
				)
			);
		}

		ob_start();
		?>
		<div class="tutor-comments-list tutor-child-comment tutor-mt-32" id="lesson-comment-<?php echo esc_attr( $reply->comment_ID ); ?>">
			<div class="comment-avatar">
				<img src="<?php echo esc_url( get_avatar_url( $reply->user_id ) ); ?>" alt="">
			</div>
			<div class="tutor-single-comment">
				<div class="tutor-actual-comment tutor-mb-12">
					<div class="tutor-comment-author">
						<span class="tutor-fs-6 tutor-fw-bold">
							<?php echo esc_html( $reply->comment_author ); ?>
						</span>
						<span class="tutor-fs-7 tutor-ml-0 tutor-ml-sm-10">
							<?php echo esc_html( human_time_diff( strtotime( $reply->comment_date ), tutor_time() ) . __( ' ago', 'tutor' ) ); ?>
						</span>
					</div>
					<div class="tutor-comment-text tutor-fs-6 tutor-mt-4">
						<?php echo esc_html( $reply->comment_content ); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
		$html = ob_get_clean();
		wp_send_json_success( array( 'html' => $html ) );
	}

	/**
	 * Get comments
	 *
	 * @since 2.0.6
	 * @see Checkout arguments details: https://developer.wordpress.org/reference/classes/wp_comment_query/__construct/
	 *
	 * @param array $args arguments.
	 * @return mixed  based on arguments
	 */
	public static function get_comments( array $args ) {
		$args['type'] = 'comment';
		$comments     = get_comments( $args );
		return $comments;
	}

	/**
	 * Get comment replies
	 *
	 * @since 4.0.0
	 *
	 * @param int    $comment_id comment id.
	 * @param string $order order.
	 *
	 * @return mixed comment replies on success, false on failure
	 */
	public static function get_comment_replies( int $comment_id, string $order = 'DESC' ) {
		return get_comments(
			array(
				'parent' => $comment_id,
				'order'  => QueryHelper::get_valid_sort_order( $order ),
			)
		);
	}

	/**
	 * Create comment
	 *
	 * @since 1.0.0
	 *
	 * @param array $request request.
	 * @return mixed comment id on success, false on failure
	 */
	public static function create_comment( array $request ) {
		$current_user = wp_get_current_user();
		$default_data = array(
			'comment_content'      => '',
			'comment_post_ID'      => '',
			'comment_parent'       => '',
			'user_id'              => $current_user->ID,
			'comment_author'       => $current_user->user_login,
			'comment_author_email' => $current_user->user_email,
			'comment_author_url'   => $current_user->user_url,
			'comment_agent'        => 'Tutor',
		);
		$comment_data = wp_parse_args( $request, $default_data );
		return wp_insert_comment( $comment_data );
	}

	/**
	 * Check if lesson has content
	 *
	 * @since 3.9.0
	 *
	 * @param int $lesson_id Lesson ID.
	 *
	 * @return bool True if lesson has content, false otherwise.
	 */
	public static function has_lesson_content( $lesson_id ) {
		/**
		 * If lesson has no content, lesson tab will be hidden.
		 * To enable elementor and SCORM, only admin can see lesson tab.
		 *
		 * @since 2.2.2
		 */
		return apply_filters(
			'tutor_has_lesson_content',
			User::is_admin() || ! in_array( trim( get_the_content() ), array( null, '', '&nbsp;' ), true ),
			$lesson_id
		);
	}

	/**
	 * Check if lesson has attachments
	 *
	 * @since 3.9.0
	 *
	 * @param int $lesson_id Lesson ID.
	 *
	 * @return bool True if lesson has attachments, false otherwise.
	 */
	public static function has_lesson_attachment( $lesson_id ) {
		return count( tutor_utils()->get_attachments( $lesson_id ) ) > 0;
	}

	/**
	 * Check if comments are enabled for lessons
	 *
	 * @since 3.9.0
	 *
	 * @return bool True if comments are enabled, false otherwise.
	 */
	public static function is_comment_enabled() {
		return tutor_utils()->get_option( 'enable_comment_for_lesson' );
	}

	/**
	 * Check if comments are enabled for lessons
	 *
	 * @since 4.0.0
	 *
	 * @param int|object $lesson Lesson ID or object. If null, current lesson will be used.
	 *
	 * @return bool True if comments are enabled, false otherwise.
	 */
	public static function is_comment_enabled_for_lesson( $lesson = null ) {
		return self::is_comment_enabled() && comments_open( $lesson );
	}

	/**
	 * Check if lesson has comments
	 *
	 * @since 3.9.0
	 *
	 * @param int $lesson_id Lesson ID.
	 *
	 * @return int Number of comments for the lesson.
	 */
	public static function has_lesson_comment( $lesson_id ) {
		return (int) get_comments_number( $lesson_id );
	}

	/**
	 * Get navigation items for lesson single page
	 *
	 * @since 3.9.0
	 *
	 * @param int $lesson_id Lesson ID.
	 *
	 * @return array navigation items array
	 */
	public static function get_nav_items( $lesson_id ) {
		$is_legacy = ( new self( false ) )->is_legacy_learning_mode;
		$nav_items = array();

		$attachments = tutor_utils()->get_attachments( $lesson_id );

		$definitions = array(
			'overview' => array(
				'condition' => self::has_lesson_content( $lesson_id ),
				'legacy'    => array(
					'label'    => __( 'Overview', 'tutor' ),
					'value'    => 'overview',
					'icon'     => 'document-text',
					'template' => 'single.lesson.parts.overview',
				),
				'default'   => array(
					'id'       => 'overview',
					'label'    => __( 'Overview', 'tutor' ),
					'icon'     => Icon::COURSES,
					'template' => 'learning-area.lesson.overview',
				),
			),
			'files'    => array(
				'condition' => tutor_utils()->count( $attachments ),
				'legacy'    => array(
					'label'    => __( 'Exercise Files', 'tutor' ),
					'value'    => 'files',
					'icon'     => 'paperclip',
					'template' => 'single.lesson.parts.files',
				),
				'default'   => array(
					'id'       => 'exercise_files',
					'label'    => __( 'Exercise Files', 'tutor' ),
					'icon'     => Icon::FILE_ATTACHEMENT,
					'template' => 'learning-area.lesson.exercise-files',
				),
			),
			'comments' => array(
				'condition' => self::is_comment_enabled_for_lesson( $lesson_id ) && is_user_logged_in(),
				'legacy'    => array(
					'label'    => __( 'Comments', 'tutor' ),
					'value'    => 'comments',
					'icon'     => 'comment',
					'template' => 'single.lesson.parts.comments',
				),
				'default'   => array(
					'id'       => 'comments',
					'label'    => __( 'Comments', 'tutor' ),
					'icon'     => Icon::COMMENTS,
					'template' => 'learning-area.lesson.comments',
				),
			),
		);

		foreach ( $definitions as $key => $def ) {
			if ( empty( $def['condition'] ) ) {
				continue;
			}
			$item = $is_legacy ? $def['legacy'] : $def['default'];
			if ( null !== $item ) {
				$nav_items[ $key ] = $item;
			}
		}

		$nav_items = apply_filters( 'tutor_lesson_single_nav_items', $nav_items );
		$nav_items = array_values( $nav_items );

		return $nav_items;
	}

	/**
	 * Return lesson title as nav item to print on the learning area
	 *
	 * @since 4.0.0
	 *
	 * @param WP_Post $lesson Lesson post object.
	 * @param bool    $can_access Can user access this content.
	 *
	 * @return void
	 */
	public function render_nav_item( $lesson, $can_access ): void {
		tutor_load_template(
			'learning-area.lesson.nav-item',
			array(
				'lesson'     => $lesson,
				'can_access' => $can_access,
			)
		);
	}

	/**
	 * Render content for the a single lesson
	 *
	 * @since 4.0.0
	 *
	 * @param WP_Post $lesson Lesson post object.
	 *
	 * @return void
	 */
	public function render_single_content( $lesson ): void {
		tutor_load_template(
			'learning-area.lesson.content',
			array(
				'lesson' => $lesson,
			)
		);
	}

	/**
	 * Load lesson comments
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function ajax_load_lesson_comments() {
		tutor_utils()->check_nonce();

		$lesson_id    = Input::post( 'lesson_id', 0, Input::TYPE_INT );
		$current_page = Input::post( 'current_page', 1, Input::TYPE_INT );
		$offset       = Input::post( 'offset', -1, Input::TYPE_INT );
		$order        = QueryHelper::get_valid_sort_order( Input::post( 'order', 'DESC' ) );

		if ( ! self::is_comment_enabled_for_lesson( $lesson_id ) ) {
			$this->response_bad_request( tutor_utils()->error_message( 'invalid_req' ) );
		}

		$user_id       = get_current_user_id();
		$item_per_page = tutor_utils()->get_option( 'pagination_per_page', 10 );

		$query_args = array(
			'post_id' => $lesson_id,
			'parent'  => 0,
			'number'  => $item_per_page,
			'order'   => $order,
		);

		if ( $offset >= 0 ) {
			$query_args['offset'] = $offset;
		} else {
			$query_args['paged'] = $current_page;
		}

		$comment_list = self::get_comments( $query_args );

		// Get total comment count to determine if there are more pages.
		$total_comments = self::get_comments(
			array(
				'post_id' => $lesson_id,
				'parent'  => 0,
				'count'   => true,
			)
		);

		ob_start();
		tutor_load_template(
			'learning-area.lesson.comment-list',
			compact( 'comment_list', 'lesson_id', 'user_id' )
		);
		$html = ob_get_clean();

		// Calculate if there are more items.
		if ( $offset >= 0 ) {
			$items_loaded = $offset + count( $comment_list );
		} else {
			$items_loaded = $current_page * $item_per_page;
		}
		$has_more = $items_loaded < $total_comments;

		wp_send_json_success(
			array(
				'html'     => $html,
				'has_more' => $has_more,
				'count'    => $total_comments,
			)
		);
	}

	/**
	 * Load comment replies for dashboard.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function ajax_load_comment_replies() {
		tutor_utils()->check_nonce();

		$comment_id    = Input::post( 'comment_id', 0, Input::TYPE_INT );
		$replies_order = Input::post( 'order', 'DESC' );

		if ( ! $comment_id ) {
			$this->response_bad_request( __( 'Invalid comment ID', 'tutor' ) );
		}

		$user_id = get_current_user_id();
		$replies = self::get_comment_replies( $comment_id, $replies_order );

		ob_start();
		tutor_load_template(
			'dashboard.discussions.comment-replies',
			array(
				'replies'       => $replies,
				'replies_order' => $replies_order,
				'user_id'       => $user_id,
			)
		);
		$html = ob_get_clean();

		wp_send_json_success( array( 'html' => $html ) );
	}

	/**
	 * Get lesson content type info with video duration
	 *
	 * @since 4.0.0
	 *
	 * @param WP_Post $lesson Lesson post.
	 *
	 * @return string
	 */
	public static function get_content_type_info( WP_Post $lesson ) {
		$video_info  = tutor_utils()->get_video_info( $lesson->ID );
		$lesson_type = __( 'Reading', 'tutor' );

		if ( $video_info ) {
			$lesson_type = __( 'Video', 'tutor' );
			$playtime    = $video_info->playtime ?? '';

			// Check if the playtime is actually a valid, positive duration.
			if ( ! empty( $playtime ) ) {
				/* translators: %s: duration in minutes */
				$lesson_type = sprintf( __( 'Video - %s mins', 'tutor' ), $playtime );
			}
		}

		return $lesson_type;
	}
}
