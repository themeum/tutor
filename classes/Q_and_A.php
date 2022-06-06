<?php

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Q_and_A {

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

	public function tutor_qna_create_update() {
		tutor_utils()->checking_nonce();

		global $wpdb;

		$qna_text = wp_kses_post( $_POST['answer'] );
		if ( ! $qna_text ) {
			// Content validation
			wp_send_json_error( array( 'message' => __( 'Empty Content Not Allowed!', 'tutor' ) ) );
		}

		// Prepare course, question info
		$course_id   = (int) sanitize_text_field( $_POST['course_id'] );
		$question_id = (int) sanitize_text_field( $_POST['question_id'] );
		$context     = sanitize_text_field( $_POST['context'] );

		// Prepare user info
		$user_id = get_current_user_id();
		$user    = get_userdata( $user_id );
		$date    = date( 'Y-m-d H:i:s', tutor_time() );

		// Insert data prepare
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

		// Mark the question unseen if action made from student
		$asker_id = $this->get_asker_id( $question_id );
		$self     = $asker_id == $user_id;
		update_comment_meta( $question_id, 'tutor_qna_read' . ( $self ? '' : '_' . $asker_id ), 0 );

		do_action( 'tutor_after_asked_question', $data );

		// Provide the html now.
		ob_start();
		tutor_load_template_from_custom_path(
			tutor()->path . '/views/qna/qna-single.php',
			array(
				'question_id' => $question_id,
				'back_url'    => isset( $_POST['back_url'] ) ? esc_url( $_POST['back_url'] ) : '',
				'context'     => $context,
			)
		);

		wp_send_json_success( array( 'html' => ob_get_clean() ) );
	}

	/**
	 * Delete question [frontend dashboard]
	 *
	 * @since  v.1.6.4
	 */
	public function tutor_delete_dashboard_question() {
		tutor_utils()->checking_nonce();

		$question_id = intval( sanitize_text_field( $_POST['question_id'] ) );

		if ( ! $question_id || ! tutor_utils()->can_user_manage( 'qa_question', $question_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied', 'tutor' ) ) );
		}

		$this->delete_qna_permanently( array( $question_id ) );

		wp_send_json_success();
	}

	private function delete_qna_permanently( $question_ids ) {
		if ( count( $question_ids ) ) {
			global $wpdb;
			$question_ids = implode( ',', $question_ids );

			// Deleting question (comment), child question and question meta (comment meta)
			$wpdb->query( "DELETE FROM {$wpdb->comments} WHERE {$wpdb->comments}.comment_ID IN($question_ids)" );
			$wpdb->query( "DELETE FROM {$wpdb->comments} WHERE {$wpdb->comments}.comment_parent IN($question_ids)" );
			$wpdb->query( "DELETE FROM {$wpdb->commentmeta} WHERE {$wpdb->commentmeta}.comment_id IN($question_ids)" );
		}
	}

	function process_bulk_action() {
		tutor_utils()->checking_nonce();

		$user_id = get_current_user_id();
		$action  = isset( $_POST['bulk-action'] ) ? sanitize_text_field( $_POST['bulk-action'] ) : null;

		switch ( $action ) {
			case 'delete':
				$qa_ids = sanitize_text_field( $_POST['bulk-ids'] );
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

	private function get_asker_id( $question_id ) {
		global $wpdb;
		$author_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT user_id
			FROM {$wpdb->comments}
			WHERE comment_ID=%d",
				$question_id
			)
		);

		return $author_id;
	}

	public function tutor_qna_single_action() {
		tutor_utils()->checking_nonce();

		$question_id = intval( sanitize_text_field( $_POST['question_id'] ) );

		if ( ! tutor_utils()->can_user_manage( 'qa_question', $question_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission Denied!', 'tutor' ) ) );
		}

		// Get who asked the question
		$asker_id     = $this->get_asker_id( $question_id );
		$asker_prefix = ( isset( $_POST['context'] ) && $_POST['context'] == 'frontend-dashboard-qna-table-student' ) ? '_' . get_current_user_id() : '';

		// Get the existing value from meta
		$action = sanitize_text_field( $_POST['qna_action'] );

		// If current user asker, then make it unread for self
		// If it is instructor, then make unread for instructor side
		$meta_key = 'tutor_qna_' . $action . $asker_prefix;

		$current_value = (int) get_comment_meta( $question_id, $meta_key, true );

		$new_value = $current_value == 1 ? 0 : 1;

		// Update the reverted value
		update_comment_meta( $question_id, $meta_key, $new_value );

		// Transfer the new status
		wp_send_json_success( array( 'new_value' => $new_value ) );
	}

	/**
	 * Available tabs that will visible on the right side of page navbar
	 *
	 * @return array
	 * @since v2.0.0
	 */
	public static function tabs_key_value( $asker_id = null ) {

		$stats = array(
			'all'       => tutor_utils()->get_qa_questions( 0, 99999, '', null, null, $asker_id, null, true ),
			'read'      => tutor_utils()->get_qa_questions( 0, 99999, '', null, null, $asker_id, 'read', true ),
			'unread'    => tutor_utils()->get_qa_questions( 0, 99999, '', null, null, $asker_id, 'unread', true ),
			'important' => tutor_utils()->get_qa_questions( 0, 99999, '', null, null, $asker_id, 'important', true ),
			'archived'  => tutor_utils()->get_qa_questions( 0, 99999, '', null, null, $asker_id, 'archived', true ),
		);

		// Assign value, url etc to the tab array
		$tabs = array_map(
			function( $tab ) use ( $stats ) {
				return array(
					'key'   => $tab,
					'title' => __( ucwords( $tab ), 'tutor' ),
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
	 * @return void
	 */
	public static function load_more() {
		tutor_utils()->checking_nonce();
		ob_start();
		tutor_load_template( 'single.course.enrolled.question_and_answer' );
		$html = ob_get_clean();
		wp_send_json_success( array('html' => $html) );
	}
}
