<?php
/**
 * Manage Q & A
 *
 * @package Tutor\Q_and_A
 * @category Q_and_A
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace TUTOR;

use Tutor\Helpers\QueryHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Question answer management
 *
 * @since 1.0.0
 */
class Q_and_A {

	/**
	 * Register hooks
	 */
	public function __construct() {
		add_action( 'wp_ajax_tutor_qna_create_update', array( $this, 'tutor_qna_create_update' ) );

		/**
		 * Delete question
		 *
		 * @since  v.1.6.4
		 */
		add_action( 'wp_ajax_tutor_delete_dashboard_question', array( $this, 'tutor_delete_dashboard_question' ) );

		/**
		 * Take action against single qna
		 *
		 * @since v2.0.0
		 */
		add_action( 'wp_ajax_tutor_qna_single_action', array( $this, 'tutor_qna_single_action' ) );
		add_action( 'wp_ajax_tutor_qna_bulk_action', array( $this, 'process_bulk_action' ) );
		/**
		 * Q & A load more
		 *
		 * @since v2.0.6
		 */
		add_action( 'wp_ajax_tutor_q_and_a_load_more', __CLASS__ . '::load_more' );
	}

	/**
	 * Undocumented function
	 *
	 * @since v1.0.0
	 *
	 * @return void
	 */
	public function tutor_qna_create_update() {
		tutor_utils()->checking_nonce();
		global $wpdb;
		$qna_text = Input::post( 'answer', '', Input::TYPE_KSES_POST );
		if ( ! $qna_text ) {
			// Content validation.
			wp_send_json_error( array( 'message' => __( 'Empty Content Not Allowed!', 'tutor' ) ) );
		}

		// Prepare course, question info.
		$course_id   = Input::post( 'course_id', 0, Input::TYPE_INT );
		$question_id = Input::post( 'question_id', 0, Input::TYPE_INT );
		$context     = Input::post( 'context' );

		// Prepare user info.
		$user_id = get_current_user_id();
		$user    = get_userdata( $user_id );
		$date    = gmdate( 'Y-m-d H:i:s', tutor_time() );

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
	 * Delete question [frontend dashboard]
	 *
	 * @since  v.1.6.4
	 */
	public function tutor_delete_dashboard_question() {
		tutor_utils()->checking_nonce();

		$question_id = Input::post( 'question_id', 0, Input::TYPE_INT );
		if ( ! $question_id || ! tutor_utils()->can_user_manage( 'qa_question', $question_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied', 'tutor' ) ) );
		}

		$this->delete_qna_permanently( array( $question_id ) );

		wp_send_json_success();
	}

	/**
	 * Undocumented function
	 *
	 * @param array $question_ids question ids.
	 *
	 * @return void
	 */
	private function delete_qna_permanently( $question_ids ) {
		if ( is_array( $question_ids ) && count( $question_ids ) ) {
			global $wpdb;
			// Prepare in clause.
			$question_ids = QueryHelper::prepare_in_clause( $question_ids );

			// Deleting question (comment), child question and question meta (comment meta).
			$wpdb->query(
				$wpdb->prepare(
					"DELETE
						FROM {$wpdb->comments}
						WHERE {$wpdb->comments}.comment_ID IN($question_ids)
							AND 1 = %d
					",
					1
				)
			);

			$wpdb->query(
				$wpdb->prepare(
					"DELETE
						FROM {$wpdb->comments}
						WHERE {$wpdb->comments}.comment_parent IN($question_ids)
							AND 1 = %d
					",
					1
				)
			);

			$wpdb->query(
				$wpdb->prepare(
					"DELETE
						FROM {$wpdb->commentmeta} 
						WHERE {$wpdb->commentmeta}.comment_id IN($question_ids)
							AND 1 = %d
					",
					1
				)
			);
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
					function( $id ) use ( $user_id ) {
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
			wp_send_json_error( array( 'message' => __( 'Permission Denied!', 'tutor' ) ) );
		}

		// Get who asked the question.
		$context      = Input::post( 'context', '' );
		$asker_prefix = 'frontend-dashboard-qna-table-student' === $context ? '_' . get_current_user_id() : '';

		// Get the existing value from meta.
		$action = Input::post( 'qna_action', '' );

		// If current user asker, then make it unread for self.
		// If it is instructor, then make unread for instructor side.
		$meta_key = 'tutor_qna_' . $action . $asker_prefix;

		$current_value = (int) get_comment_meta( $question_id, $meta_key, true );

		$new_value = 1 === $current_value ? 0 : 1;

		// Update the reverted value.
		update_comment_meta( $question_id, $meta_key, $new_value );

		// Transfer the new status.
		wp_send_json_success( array( 'new_value' => $new_value ) );
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

		$stats = array(
			'all'       => tutor_utils()->get_qa_questions( 0, 99999, '', null, null, $asker_id, null, true ),
			'read'      => tutor_utils()->get_qa_questions( 0, 99999, '', null, null, $asker_id, 'read', true ),
			'unread'    => tutor_utils()->get_qa_questions( 0, 99999, '', null, null, $asker_id, 'unread', true ),
			'important' => tutor_utils()->get_qa_questions( 0, 99999, '', null, null, $asker_id, 'important', true ),
			'archived'  => tutor_utils()->get_qa_questions( 0, 99999, '', null, null, $asker_id, 'archived', true ),
		);

		// Assign value, url etc to the tab array.
		$tabs = array_map(
			function( $tab ) use ( $stats ) {
				return array(
					'key'   => $tab,
					'title' => tutor_utils()->translate_dynamic_text( $tab ),
					'value' => $stats[ $tab ],
					'url'   => add_query_arg( array( 'tab' => $tab ), remove_query_arg( 'tab' ) ),
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
}
