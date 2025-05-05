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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Tutor\Helpers\HttpHelper;
use Tutor\Helpers\ValidationHelper;
use Tutor\Models\LessonModel;
use Tutor\Traits\JsonResponse;

/**
 * Lesson class
 *
 * @since 1.0.0
 */
class Lesson extends Tutor_Base {
	use JsonResponse;

	/**
	 * Register hooks
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct();

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
		add_action( 'wp_ajax_tutor_single_course_lesson_load_more', array( $this, 'tutor_single_course_lesson_load_more' ) );
		add_action( 'wp_ajax_tutor_create_lesson_comment', array( $this, 'tutor_single_course_lesson_load_more' ) );
		add_action( 'wp_ajax_tutor_reply_lesson_comment', array( $this, 'reply_lesson_comment' ) );
	}

	/**
	 * Manage load more & comment create
	 *
	 * @since 2.0.6
	 * @return void  send wp json data
	 */
	public function tutor_single_course_lesson_load_more() {
		tutor_utils()->checking_nonce();
		$comment = Input::post( 'comment', '', Input::TYPE_KSES_POST );
		if ( 'tutor_create_lesson_comment' === Input::post( 'action' ) && strlen( $comment ) > 0 ) {
			$comment_data = array(
				'comment_content' => $comment,
				'comment_post_ID' => Input::post( 'comment_post_ID', 0, Input::TYPE_INT ),
				'comment_parent'  => Input::post( 'comment_parent', 0, Input::TYPE_INT ),
			);
			self::create_comment( $comment_data );
			do_action( 'tutor_new_comment_added', $comment_data );
		}
		ob_start();
		tutor_load_template( 'single.lesson.comment' );
		$html = ob_get_clean();
		wp_send_json_success( array( 'html' => $html ) );
	}

	/**
	 * Saving lesson meta and assets
	 *
	 * @since 1.0.0
	 *
	 * @param integer $post_ID post ID.
	 * @return void
	 */
	public function save_lesson_meta( $post_ID ) {
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
			$this->json_response( tutor_utils()->error_message( 'nonce' ), null, HttpHelper::STATUS_BAD_REQUEST );
		}

		$topic_id  = Input::post( 'topic_id', 0, Input::TYPE_INT );
		$lesson_id = Input::post( 'lesson_id', 0, Input::TYPE_INT );

		if ( ! tutor_utils()->can_user_manage( 'topic', $topic_id ) ) {
			$this->json_response(
				tutor_utils()->error_message(),
				null,
				HttpHelper::STATUS_FORBIDDEN
			);
		}

		if ( 0 !== $lesson_id ) {
			if ( ! tutor_utils()->can_user_manage( 'lesson', $lesson_id ) ) {
				$this->json_response(
					tutor_utils()->error_message(),
					null,
					HttpHelper::STATUS_FORBIDDEN
				);
			}
		}

		$post = get_post( $lesson_id, ARRAY_A );

		if ( $post ) {
			$post['thumbnail_id'] = get_post_meta( $lesson_id, '_thumbnail_id', true );
			$post['thumbnail']    = get_the_post_thumbnail_url( $lesson_id );
			$post['attachments']  = tutor_utils()->get_attachments( $lesson_id );

			$video = maybe_unserialize( get_post_meta( $lesson_id, '_video', true ) );
			if ( $video ) {
				$source = $video['source'] ?? '';
				if ( 'html5' === $source ) {
					$poster_url            = wp_get_attachment_url( $video['poster'] ?? 0 );
					$source_html5          = wp_get_attachment_url( $video['source_video_id'] ?? 0 );
					$video['poster_url']   = $poster_url;
					$video['source_html5'] = $source_html5;
				}
			}
			$post['video'] = $video;
		} else {
			$post = array();
		}

		$data = apply_filters( 'tutor_lesson_details_response', $post, $lesson_id );

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
			$this->json_response( tutor_utils()->error_message( 'nonce' ), null, HttpHelper::STATUS_BAD_REQUEST );
		}

		/**
		 * Allow iframe inside lesson content to support
		 * embed video & other stuff
		 *
		 * @since 2.1.6
		 */
		add_filter( 'wp_kses_allowed_html', Input::class . '::allow_iframe', 10, 2 );

		$is_update = false;

		$lesson_id = Input::post( 'lesson_id', 0, Input::TYPE_INT );
		$topic_id  = Input::post( 'topic_id', 0, Input::TYPE_INT );
		$course_id = tutor_utils()->get_course_id_by( 'topic', $topic_id );

		if ( $lesson_id ) {
			$is_update = true;
		}

		if ( ! tutor_utils()->can_user_manage( 'topic', $topic_id ) ) {
			$this->json_response(
				tutor_utils()->error_message(),
				null,
				HttpHelper::STATUS_FORBIDDEN
			);
		}

		$title        = Input::post( 'title' );
		$description  = Input::post( 'description', '', Input::TYPE_KSES_POST );
		$thumbnail_id = Input::post( 'thumbnail_id', 0, Input::TYPE_INT );

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
			'post_title'     => $title,
			'post_name'      => sanitize_title( $title ),
			'post_content'   => $post_content,
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

		if ( $thumbnail_id ) {
			update_post_meta( $lesson_id, '_thumbnail_id', $thumbnail_id );
		} else {
			delete_post_meta( $lesson_id, '_thumbnail_id' );
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

		if ( ! tutor_utils()->can_user_manage( 'lesson', $lesson_id ) ) {
			$this->json_response(
				tutor_utils()->error_message(),
				null,
				HttpHelper::STATUS_FORBIDDEN
			);
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
		// Checking nonce.
		tutor_utils()->checking_nonce();

		$user_id = get_current_user_id();

		// TODO: need to show view if not signed_in.
		if ( ! $user_id ) {
			die( esc_html__( 'Please Sign-In', 'tutor' ) );
		}

		$lesson_id = Input::post( 'lesson_id', 0, Input::TYPE_INT );

		if ( ! $lesson_id ) {
			return;
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

		$ancestors = get_post_ancestors( $lesson_id );
		$course_id = ! empty( $ancestors ) ? array_pop( $ancestors ) : $lesson_id;

		// Course must be public or current user must be enrolled to access this lesson.
		if ( get_post_meta( $course_id, '_tutor_is_public_course', true ) !== 'yes' && ! tutor_utils()->is_enrolled( $course_id ) ) {

			$is_admin = tutor_utils()->has_user_role( 'administrator' );
			$allowed  = $is_admin ? true : tutor_utils()->is_instructor_of_this_course( get_current_user_id(), $course_id );

			if ( ! $allowed ) {
				http_response_code( 400 );
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

		$post_id    = Input::post( 'post_id', 0, Input::TYPE_INT );
		$content_id = tutor_utils()->get_post_id( $post_id );
		$contents   = tutor_utils()->get_course_prev_next_contents_by_id( $content_id );

		$autoload_course_content = (bool) get_tutor_option( 'autoload_next_course_content' );
		$next_url                = false;
		if ( $autoload_course_content ) {
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
	 * Replay lesson comment
	 *
	 * @since 1.0.0
	 *
	 * @return void|null
	 */
	public function reply_lesson_comment() {
		tutor_utils()->checking_nonce();
		$comment = Input::post( 'comment', '', Input::TYPE_KSES_POST );
		if ( 0 === strlen( $comment ) ) {
			wp_send_json_error();
			return;
		}

		$comment_data = array(
			'comment_content' => $comment,
			'comment_post_ID' => Input::post( 'comment_post_ID', 0, Input::TYPE_INT ),
			'comment_parent'  => Input::post( 'comment_parent', 0, Input::TYPE_INT ),
		);
		$comment_id   = self::create_comment( $comment_data );
		if ( false === $comment_id ) {
			wp_send_json_error();
			return;
		}
		$reply = get_comment( $comment_id );
		do_action( 'tutor_reply_lesson_comment_thread', $comment_id, $comment_data );

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
		$comments = get_comments( $args );
		return $comments;
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

}
