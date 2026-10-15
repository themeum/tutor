<?php
/**
 * Manage Q & A
 *
 * @package Tutor\Q_And_A
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace TUTOR;

defined( 'ABSPATH' ) || exit;

use Tutor\Helpers\QueryHelper;
use Tutor\Helpers\UrlHelper;
use Tutor\Models\EnrollmentModel;
use Tutor\Traits\JsonResponse;
/**
 * Question answer management
 *
 * @since 1.0.0
 */
class Q_And_A {
	use JsonResponse;

	/**
	 * List of all possible Q&A question statuses.
	 *
	 * @since 3.7.2
	 *
	 * @var string[]
	 */
	const STATUS_LIST = array(
		'all',
		'read',
		'unread',
		'important',
		'archived',
	);

	/**
	 * Register hooks
	 *
	 * @param boolean $register_hooks true/false to execute the hooks.
	 */
	public function __construct( $register_hooks = true ) {
		if ( ! $register_hooks ) {
			return;
		}

		add_filter( 'tutor_learning_area_sub_page_nav_item', array( $this, 'add_learning_area_menu' ), 10, 2 );

		add_action( 'wp_ajax_tutor_qna_create_update', array( $this, 'tutor_qna_create_update' ) );
		add_action( 'wp_ajax_tutor_qna_update', array( $this, 'ajax_qna_update' ) );
		add_action( 'wp_ajax_tutor_delete_dashboard_question', array( $this, 'tutor_delete_dashboard_question' ) );
		add_action( 'wp_ajax_tutor_qna_single_action', array( $this, 'tutor_qna_single_action' ) );
		add_action( 'wp_ajax_tutor_qna_bulk_action', array( $this, 'process_bulk_action' ) );
		add_action( 'wp_ajax_tutor_q_and_a_load_more', array( $this, 'load_more' ) );
		add_action( 'wp_ajax_tutor_qna_load_replies', array( $this, 'load_replies' ) );
	}

	/**
	 * Check if Q&A feature is enabled.
	 *
	 * @since 4.0.0
	 *
	 * @return bool
	 */
	public static function is_enabled() {
		return (bool) get_tutor_option( 'enable_q_and_a_on_course' );
	}

	/**
	 * Check if Q&A is enabled for a specific course.
	 *
	 * @since 4.0.0
	 *
	 * @param int $course_id course id.
	 *
	 * @return bool
	 */
	public static function is_enabled_for_course( $course_id ) {
		return self::is_enabled() && 'yes' === get_post_meta( $course_id, Course::COURSE_ENABLE_QA_META, true );
	}

	/**
	 * Add learning area menu
	 *
	 * @since 4.0.0
	 *
	 * @param array  $menu_items the array of nav items.
	 * @param string $base_url the base url.
	 *
	 * @return array
	 */
	public function add_learning_area_menu( $menu_items, $base_url ) {
		global $tutor_course_id;

		$user_id               = get_current_user_id();
		$is_enabled_for_course = self::is_enabled_for_course( $tutor_course_id );
		$can_access            = EnrollmentModel::is_enrolled( $tutor_course_id ) || tutor_utils()->has_user_course_content_access( $user_id, $tutor_course_id );

		if ( $is_enabled_for_course && $can_access ) {
			$qna_item = array(
				'qna' => array(
					'title'    => __( 'Q&A', 'tutor' ),
					'icon'     => Icon::QA,
					'url'      => UrlHelper::add_query_params( $base_url, array( 'subpage' => 'qna' ) ),
					'template' => tutor_get_template( 'learning-area.subpages.qna' ),
				),
			);

			// Remove existing Q&A if Tutor already added it.
			unset( $menu_items['qna'] );

			$menu_items = $qna_item + $menu_items;
		}

		return $menu_items;
	}

	/**
	 * Check user has access to QnA.
	 *
	 * @since 2.6.1
	 *
	 * @param int $user_id user id.
	 * @param int $course_id course id.
	 *
	 * @return boolean
	 */
	public static function has_qna_access( $user_id, $course_id ) {
		$is_public_course = Course_List::is_public( $course_id );

		$has_access = $is_public_course
						|| User::is_admin()
						|| tutor_utils()->is_instructor_of_this_course( $user_id, $course_id )
						|| EnrollmentModel::is_enrolled( $course_id, $user_id );
		return $has_access;
	}

	/**
	 * Handle QnA create/update via AJAX.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function tutor_qna_create_update() {
		tutor_utils()->checking_nonce();

		$user_id     = get_current_user_id();
		$course_id   = Input::post( 'course_id', 0, Input::TYPE_INT );
		$question_id = Input::post( 'question_id', 0, Input::TYPE_INT );
		$context     = Input::post( 'context' );

		if ( $question_id ) {
			$course_id = tutor_utils()->get_course_id_by( 'qa_question', $question_id );
		}

		if ( ! $course_id || ! $this->has_qna_access( $user_id, $course_id ) ) {
			$this->response_bad_request( tutor_utils()->error_message() );
		}

		$qna_text = Input::post( 'answer', '', tutor()->has_pro ? Input::TYPE_KSES_POST : Input::TYPE_TEXTAREA );

		if ( ! $qna_text ) {
			$this->response_bad_request( __( 'Empty Content Not Allowed!', 'tutor' ) );
		}

		// Prepare user info.
		$user = get_userdata( $user_id );
		$date = gmdate( 'Y-m-d H:i:s', tutor_time() );

		$qna_object              = new \stdClass();
		$qna_object->user_id     = $user_id;
		$qna_object->course_id   = $course_id;
		$qna_object->question_id = $question_id;
		$qna_object->qna_text    = $qna_text;
		$qna_object->user        = $user;
		$qna_object->date        = $date;

		$question_id = $this->inset_qna( $qna_object );

		// Provide the html now.
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		ob_start();
		tutor_load_template_from_custom_path(
			tutor()->path . '/views/qna/qna-single.php',
			array(
				'question_id' => $question_id,
				'back_url'    => isset( $_POST['back_url'] ) ? esc_url_raw( wp_unslash( $_POST['back_url'] ) ) : '',
				'context'     => $context,
			)
		);
		wp_send_json_success(
			array(
				'html'      => ob_get_clean(),
				'editor_id' => 'tutor_qna_reply_editor_' . $question_id,
			)
		);
	}

	/**
	 * Function to insert Q&A
	 *
	 * @param object $qna_object the object to insert.
	 * @return int
	 */
	public function inset_qna( $qna_object ) {
		$course_id   = $qna_object->course_id;
		$question_id = $qna_object->question_id;
		$qna_text    = $qna_object->qna_text;
		$user_id     = $qna_object->user_id;
		$user        = $qna_object->user;
		$date        = $qna_object->date;

		// Insert data prepare.
		$data = apply_filters(
			'tutor_qna_insert_data',
			array(
				'comment_post_ID'  => $course_id,
				'comment_author'   => $user->user_login,
				'comment_date'     => $date,
				'comment_date_gmt' => get_gmt_from_date( $date ),
				'comment_content'  => $qna_text,
				'comment_approved' => 'approved',
				'comment_agent'    => 'TutorLMSPlugin',
				'comment_type'     => 'tutor_q_and_a',
				'comment_parent'   => $question_id,
				'user_id'          => $user_id,
			)
		);

		global $wpdb;

		// Insert new question/answer.
		$wpdb->insert( $wpdb->comments, $data );
		! $question_id ? $question_id = (int) $wpdb->insert_id : 0;

		// Mark the question unseen if action made from student.
		$asker_id = $this->get_asker_id( $question_id );
		$self     = $asker_id == $user_id;
		update_comment_meta( $question_id, 'tutor_qna_read' . ( $self ? '' : '_' . $asker_id ), 0 );

		do_action( 'tutor_after_asked_question', $data );

		// question_id != 0 means it's a reply.
		$reply_id  = Input::post( 'question_id', 0, Input::TYPE_INT );
		$answer_id = (int) $wpdb->insert_id;
		if ( 0 !== $reply_id && ( current_user_can( 'administrator' ) || tutor_utils()->is_instructor_of_this_course( $user_id, $course_id ) ) ) {
			do_action( 'tutor_after_answer_to_question', $answer_id );
		}

		return $question_id;
	}

	/**
	 * Update question [frontend dashboard]
	 *
	 * @since 4.0.0
	 */
	public function ajax_qna_update() {
		tutor_utils()->checking_nonce();

		$question_id = Input::post( 'question_id', 0, Input::TYPE_INT );
		if ( ! $question_id || ! tutor_utils()->can_user_manage( 'qa_question', $question_id ) ) {
			$this->response_bad_request( tutor_utils()->error_message( 'authorization' ) );
		}

		$qna_text = Input::post( 'answer', '', tutor()->has_pro ? Input::TYPE_KSES_POST : Input::TYPE_TEXTAREA );
		if ( ! $qna_text ) {
			$this->response_bad_request( __( 'Empty Content Not Allowed!', 'tutor' ) );
		}

		$data = array(
			'comment_content' => $qna_text,
		);

		global $wpdb;
		$wpdb->update( $wpdb->comments, $data, array( 'comment_ID' => $question_id ) );

		$this->json_response( __( 'Comment edited successfully', 'tutor' ) );
	}

	/**
	 * Delete question [frontend dashboard]
	 *
	 * @since 1.6.4
	 */
	public function tutor_delete_dashboard_question() {
		tutor_utils()->checking_nonce();

		$question_id = Input::post( 'question_id', 0, Input::TYPE_INT );
		if ( ! $question_id || ! tutor_utils()->can_user_manage( 'qa_question', $question_id ) ) {
			$this->response_bad_request( tutor_utils()->error_message( 'authorization' ) );
		}

		$this->delete_qna_permanently( array( $question_id ) );

		$this->json_response( __( 'Comment deleted successfully.', 'tutor' ) );
	}

	/**
	 * Delete question permanently
	 *
	 * @since 1.6.4
	 *
	 * @param array $question_ids question ids.
	 *
	 * @return void
	 */
	public function delete_qna_permanently( $question_ids ) {
		if ( is_array( $question_ids ) && count( $question_ids ) ) {
			global $wpdb;
			// Prepare in clause.
			$question_ids = QueryHelper::prepare_in_clause( $question_ids );

			// Deleting question (comment), child question and question meta (comment meta).
			// phpcs:disable -- variable $question_ids is escaped.
			$wpdb->query(
				$wpdb->prepare(
					"DELETE
						FROM {$wpdb->comments}
						WHERE {$wpdb->comments}.comment_ID IN ($question_ids)
							AND 1 = %d
					",
					1
				)
			);

			$wpdb->query(
				$wpdb->prepare(
					"DELETE
						FROM {$wpdb->comments}
						WHERE {$wpdb->comments}.comment_parent IN ($question_ids)
							AND 1 = %d
					",
					1
				)
			);

			$wpdb->query(
				$wpdb->prepare(
					"DELETE
						FROM {$wpdb->commentmeta} 
						WHERE {$wpdb->commentmeta}.comment_id IN ($question_ids)
							AND 1 = %d
					",
					1
				)
			);
			// phpcs:enable
		}
	}

	/**
	 * Process bulk delete
	 *
	 * @since v1.0.0
	 *
	 * @return void send wp_json response
	 */
	public function process_bulk_action() {
		tutor_utils()->checking_nonce();

		$user_id = get_current_user_id();
		$action  = Input::post( 'bulk-action' );

		switch ( $action ) {
			case 'delete':
				$qa_ids = Input::post( 'bulk-ids', '' );
				$qa_ids = explode( ',', $qa_ids );
				$qa_ids = array_filter(
					$qa_ids,
					function ( $id ) use ( $user_id ) {
						return is_numeric( $id ) && tutor_utils()->can_user_manage( 'qa_question', $id, $user_id );
					}
				);

				$this->delete_qna_permanently( $qa_ids );
				break;
		}

		wp_send_json_success();
	}

	/**
	 * Get user id who asked
	 *
	 * @param int $question_id question id.
	 *
	 * @return string author id
	 */
	private function get_asker_id( $question_id ) {
		global $wpdb;
		$author_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT user_id
					FROM {$wpdb->comments}
					WHERE comment_ID = %d
				",
				$question_id
			)
		);
		return $author_id;
	}

	/**
	 * Update comment meta function
	 *
	 * @return void send wp_json response
	 */
	public function tutor_qna_single_action() {
		tutor_utils()->checking_nonce();

		$question_id = Input::post( 'question_id', 0, Input::TYPE_INT );

		if ( ! tutor_utils()->can_user_manage( 'qa_question', $question_id ) ) {
			$this->response_bad_request( tutor_utils()->error_message( 'authorization' ) );
		}

		// Get who asked the question.
		$context = Input::post( 'context', '' );
		$user_id = get_current_user_id();

		// Get the existing value from meta.
		$action = Input::post( 'qna_action', '' );

		$new_value = $this->trigger_qna_action( $question_id, $action, $context, $user_id );

		// Transfer the new status.
		wp_send_json_success( array( 'new_value' => $new_value ) );
	}

	/**
	 * Function to update Q&A action
	 *
	 * @since 2.6.2
	 *
	 * @param int    $question_id question id.
	 * @param string $action action name.
	 * @param string $context context name.
	 * @param int    $user_id user id.
	 *
	 * @return int
	 */
	public function trigger_qna_action( $question_id, $action, $context, $user_id ) {
		$asker_prefix = 'frontend-dashboard-qna-table-student' === $context ? '_' . $user_id : '';

		// If current user asker, then make it unread for self.
		// If it is instructor, then make unread for instructor side.
		$meta_key = 'tutor_qna_' . $action . $asker_prefix;

		$current_value = (int) get_comment_meta( $question_id, $meta_key, true );

		$new_value = 1 === $current_value ? 0 : 1;

		// Update the reverted value.
		update_comment_meta( $question_id, $meta_key, $new_value );

		return $new_value;
	}

	/**
	 * Available tabs that will visible on the right side of page navbar
	 *
	 * @since v2.0.0
	 *
	 * @param mixed $asker_id asker id.
	 *
	 * @return array
	 */
	public static function tabs_key_value( $asker_id = null ) {

		$args  = Input::has( 'course-id' ) ? array( 'course_id' => Input::get( 'course-id', 0, Input::TYPE_INT ) ) : array();
		$stats = array();

		// Loop through all predefined Q&A statuses to retrieve corresponding question statistics.
		foreach ( self::STATUS_LIST as $status ) {

			$label            = 'all' === $status ? null : $status;
			$stats[ $status ] = tutor_utils()->get_qa_questions( 0, 99999, '', null, null, $asker_id, $label, true, $args );
		}

		// Assign value, url etc to the tab array.
		$tabs = array_map(
			function ( $tab ) use ( $stats ) {
				return array(
					'key'   => 'all' === $tab ? '' : $tab,
					'title' => tutor_utils()->translate_dynamic_text( $tab ),
					'value' => $stats[ $tab ],
					'url'   => add_query_arg( array( 'data' => $tab ), remove_query_arg( 'data' ) ),
				);
			},
			array_keys( $stats )
		);

		return $tabs;
	}

	/**
	 * Load more q & a
	 *
	 * @since v2.0.6
	 *
	 * @return void send wp_json response
	 */
	public static function load_more() {
		tutor_utils()->checking_nonce();
		ob_start();
		tutor_load_template( 'single.course.enrolled.question_and_answer' );
		$html = ob_get_clean();
		wp_send_json_success( array( 'html' => $html ) );
	}

	/**
	 * Load replies
	 *
	 * @since 4.0.0
	 *
	 * @return void send wp_json response
	 */
	public function load_replies() {
		tutor_utils()->checking_nonce();

		$comment_id    = Input::post( 'comment_id', 0, Input::TYPE_INT );
		$replies_order = QueryHelper::get_valid_sort_order( Input::post( 'order', 'DESC' ) );
		$context       = Input::post( 'context', 'dashboard' );

		if ( ! $comment_id ) {
			$this->response_bad_request( __( 'Invalid comment ID', 'tutor' ) );
		}

		$user_id = get_current_user_id();
		$replies = tutor_utils()->get_qa_answer_by_question( $comment_id, $replies_order, 'frontend' );

		$template = 'dashboard.discussions.qna-replies';
		if ( 'learning-area' === $context ) {
			$template = 'learning-area.subpages.qna.replies';
		}

		ob_start();
		tutor_load_template(
			$template,
			array(
				'replies'       => $replies,
				'replies_order' => $replies_order,
				'user_id'       => $user_id,
			)
		);
		$html = ob_get_clean();

		$this->json_response( '', array( 'html' => $html ) );
	}
}
