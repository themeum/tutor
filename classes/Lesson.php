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

use Tutor\Models\LessonModel;

/**
 * Lesson class
 *
 * @since 1.0.0
 */
class Lesson extends Tutor_Base {

	/**
	 * Register hooks
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'add_meta_boxes', array( $this, 'register_meta_box' ) );
		add_action( 'save_post_' . $this->lesson_post_type, array( $this, 'save_lesson_meta' ) );

		add_action( 'wp_ajax_tutor_load_edit_lesson_modal', array( $this, 'tutor_load_edit_lesson_modal' ) );
		add_action( 'wp_ajax_tutor_modal_create_or_update_lesson', array( $this, 'tutor_modal_create_or_update_lesson' ) );
		add_action( 'wp_ajax_tutor_delete_lesson_by_id', array( $this, 'tutor_delete_lesson_by_id' ) );

		add_filter( 'get_sample_permalink', array( $this, 'change_lesson_permalink' ), 10, 2 );

		/**
		 * Add Column
		 */
		add_filter( "manage_{$this->lesson_post_type}_posts_columns", array( $this, 'add_column' ), 10, 1 );
		add_action( "manage_{$this->lesson_post_type}_posts_custom_column", array( $this, 'custom_lesson_column' ), 10, 2 );

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
	 * Registering metabox
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_meta_box() {
		$lesson_post_type = $this->lesson_post_type;

		tutor_meta_box_wrapper( 'tutor-course-select', __( 'Select Course', 'tutor' ), array( $this, 'lesson_metabox' ), $lesson_post_type, 'advanced', 'default', 'tutor-admin-post-meta' );

		tutor_meta_box_wrapper( 'tutor-lesson-videos', __( 'Lesson Video', 'tutor' ), array( $this, 'lesson_video_metabox' ), $lesson_post_type, 'advanced', 'default', 'tutor-admin-post-meta' );

		tutor_meta_box_wrapper( 'tutor-lesson-attachments', __( 'Attachments', 'tutor' ), array( $this, 'lesson_attachments_metabox' ), $lesson_post_type, 'advanced', 'default', 'tutor-admin-post-meta' );
	}

	/**
	 * Lesson metabox
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function lesson_metabox() {
		include tutor()->path . 'views/metabox/lesson-metabox.php';
	}

	/**
	 * Video metabox
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function lesson_video_metabox() {
		include tutor()->path . 'views/metabox/video-metabox.php';
	}

	/**
	 * Attachment metabox
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function lesson_attachments_metabox() {
		include tutor()->path . 'views/metabox/lesson-attachments-metabox.php';
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
	 * Load edit lesson modal
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function tutor_load_edit_lesson_modal() {
		tutor_utils()->checking_nonce();

		$lesson_id = Input::post( 'lesson_id', 0, Input::TYPE_INT );
		$topic_id  = Input::post( 'topic_id', 0, Input::TYPE_INT );

		if ( ! tutor_utils()->can_user_manage( 'topic', $topic_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied', 'tutor' ) ) );
		}

		/**
		 * If Lesson Not Exists, provide dummy
		 */
		$post_arr = array(
			'ID'           => 0,
			'post_content' => '',
			'post_type'    => $this->lesson_post_type,
			'post_title'   => __( 'Draft Lesson', 'tutor' ),
			'post_status'  => 'publish',
			'post_author'  => get_current_user_id(),
			'post_parent'  => $topic_id,
		);

		$post = $lesson_id ? get_post( $lesson_id ) : (object) $post_arr;

		ob_start();
		include tutor()->path . 'views/modal/edit-lesson.php';
		$output = ob_get_clean();

		wp_send_json_success( array( 'output' => $output ) );
	}

	/**
	 * Load lesson modal for create or update lesson
	 *
	 * @since 1.0.0
	 * @since 1.5.1 updated
	 *
	 * @return void
	 */
	public function tutor_modal_create_or_update_lesson() {
		tutor_utils()->checking_nonce();

		global $wpdb;

		/**
		 * Allow iframe inside lesson content to support
		 * embed video & other stuff
		 *
		 * @since 2.1.6
		 */
		add_filter( 'wp_kses_allowed_html', Input::class . '::allow_iframe', 10, 2 );

		$lesson_id        = Input::post( 'lesson_id', 0, Input::TYPE_INT );
		$topic_id         = Input::post( 'current_topic_id', 0, Input::TYPE_INT );
		$current_topic_id = $topic_id;
		$course_id        = tutor_utils()->get_course_id_by( 'topic', $topic_id );

		if ( ! tutor_utils()->can_user_manage( 'topic', $topic_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied', 'tutor' ) ) );
		}

		$title                = Input::post( 'lesson_title' );
		$_lesson_thumbnail_id = Input::post( '_lesson_thumbnail_id', 0, Input::TYPE_INT );
		$lesson_content       = Input::post( 'lesson_content', '', Input::TYPE_KSES_POST );
		$is_html_active       = Input::post( 'is_html_active' ) === 'true' ? true : false;
		$raw_html_content     = Input::post( 'tutor_lesson_modal_editor', '', Input::TYPE_KSES_POST );
		$post_content         = $is_html_active ? $raw_html_content : $lesson_content;

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

		if ( 0 === $lesson_id ) {
			$lesson_data['menu_order'] = tutor_utils()->get_next_course_content_order_id( $topic_id );
			$lesson_id                 = wp_insert_post( $lesson_data );

			if ( $lesson_id ) {
				do_action( 'tutor/lesson/created', $lesson_id );
			} else {
				wp_send_json_error( array( 'message' => __( 'Couldn\'t create lesson.', 'tutor' ) ) );
			}
		} else {
			$lesson_data['ID'] = $lesson_id;

			do_action( 'tutor/lesson_update/before', $lesson_id );
			wp_update_post( $lesson_data );
			if ( $_lesson_thumbnail_id ) {
				update_post_meta( $lesson_id, '_thumbnail_id', $_lesson_thumbnail_id );
			} else {
				delete_post_meta( $lesson_id, '_thumbnail_id' );
			}

			do_action( 'tutor/lesson_update/after', $lesson_id );
		}

		ob_start();
		include tutor()->path . 'views/metabox/course-contents.php';
		$course_contents = ob_get_clean();

		wp_send_json_success( array( 'course_contents' => $course_contents ) );
	}

	/**
	 * Delete Lesson from course builder by ID
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function tutor_delete_lesson_by_id() {
		tutor_utils()->checking_nonce();

		$lesson_id = Input::post( 'lesson_id', 0, Input::TYPE_INT );

		if ( ! tutor_utils()->can_user_manage( 'lesson', $lesson_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied', 'tutor' ) ) );
		}

		wp_delete_post( $lesson_id, true );
		wp_send_json_success();
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
	 * Add column to lesson HTML table
	 *
	 * @since 1.0.0
	 *
	 * @param array $columns columns.
	 * @return array
	 */
	public function add_column( $columns ) {
		$date_col = $columns['date'];
		unset( $columns['date'] );
		$columns['course'] = __( 'Course', 'tutor' );
		$columns['date']   = $date_col;

		return $columns;
	}

	/**
	 * Add custom lesson column.
	 *
	 * @since 1.0.0
	 *
	 * @param string  $column column name.
	 * @param integer $post_id post ID.
	 *
	 * @return mixed
	 */
	public function custom_lesson_column( $column, $post_id ) {
		if ( 'course' === $column ) {

			$course_id = tutor_utils()->get_course_id_by( 'lesson', $post_id );
			if ( $course_id ) {
				echo wp_kses(
					'<a href="' . admin_url( 'post.php?post=' . $course_id . '&action=edit' ) . '">' . get_the_title( $course_id ) . '</a>',
					array( 'a' => array( 'href' => true ) )
				);
			}
		}
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


