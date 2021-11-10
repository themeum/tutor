<?php

namespace TUTOR;

if (!defined('ABSPATH'))
	exit;

class Q_and_A {

	public function __construct() {
		add_action('wp_ajax_tutor_qna_create_update', array($this, 'tutor_qna_create_update'));

		/**
		 * Delete question
		 * @since  v.1.6.4
		 */
		add_action('wp_ajax_tutor_delete_dashboard_question', array($this, 'tutor_delete_dashboard_question'));

		/**
		 * Take action against single qna
		 * @since v2.0.0
		 */
		add_action('wp_ajax_tutor_qna_single_action', array($this, 'tutor_qna_single_action'));
	}

	public function tutor_qna_create_update() {
		tutor_utils()->checking_nonce();

		global $wpdb;

		$qna_text = wp_kses_post($_POST['answer']);
		if(!$qna_text) {
			// Content validation
			wp_send_json_error( array('message' => __('Empty Cotent Not Allowed!', 'tutor')) );
		}

		// Prepare course, question info
		$course_id = (int) sanitize_text_field($_POST['course_id']);
		$question_id = (int) sanitize_text_field($_POST['question_id']);
		$context = sanitize_text_field($_POST['context']);

		// Prepare user info
		$user_id = get_current_user_id();
		$user = get_userdata($user_id);
		$date = date("Y-m-d H:i:s", tutor_time());

		// Insert data prepare
		$data = apply_filters('tutor_qna_insert_data', array(
			'comment_post_ID'   => $course_id,
			'comment_author'    => $user->user_login,
			'comment_date'      => $date,
			'comment_date_gmt'  => get_gmt_from_date($date),
			'comment_content'   => $qna_text,
			'comment_approved'  => 'approved',
			'comment_agent'     => 'TutorLMSPlugin',
			'comment_type'      => 'tutor_q_and_a',
			'comment_parent'    => $question_id,
			'user_id'           => $user_id,
		));

		// Insert new question/answer
		$wpdb->insert($wpdb->comments, $data);
		!$question_id ? $question_id = (int) $wpdb->insert_id : 0;
		
		// Provide the html now
		ob_start();
		tutor_load_template_from_custom_path(tutor()->path . '/views/qna/qna-single.php', array(
			'question_id' => $question_id,
			'context' => $context
		));
		wp_send_json_success(array('html' => ob_get_clean()));
	}

	/**
	 * Delete question [frontend dashboard]
	 * @since  v.1.6.4
	 */
	public function tutor_delete_dashboard_question() {
		tutor_utils()->checking_nonce();

		global $wpdb;
		$question_id = intval(sanitize_text_field($_POST['question_id']));
		
		if( !$question_id || !tutor_utils()->can_user_manage('qa_question', $question_id)) {
			wp_send_json_error( array('message'=>__('Access Denied', 'tutor')) );
		}

		//Deleting question (comment), child question and question meta (comment meta)
		$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->comments} WHERE {$wpdb->comments}.comment_ID = %d", $question_id));
		$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->comments} WHERE {$wpdb->comments}.comment_parent = %d", $question_id));
		$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->commentmeta} WHERE {$wpdb->commentmeta}.comment_id = %d", $question_id));
		
		wp_send_json_success();
	}

	public function tutor_qna_single_action() {
		tutor_utils()->checking_nonce();

		$question_id = intval(sanitize_text_field($_POST['question_id']));
		$action = sanitize_text_field( $_POST['qna_action'] );
		$current_value = intval(sanitize_text_field($_POST['current_value']));
		$new_value = $current_value==1 ? 0 : 1;

		if(!tutor_utils()->can_user_manage('qa_question', $question_id)) {
			wp_send_json_error( array('message' => __('Permission Denied!', 'tutor') ) );
		}

		$meta_key = 'tutor_qna_' . $action;
		update_comment_meta( $question_id, $meta_key, $new_value );

		wp_send_json_success( array('new_value' => $new_value) );
	}

	/**
	 * Available tabs that will visible on the right side of page navbar
	 * @return array
	 * @since v2.0.0
	 */
	public static function tabs_key_value() {
		$url = get_pagenum_link();
		$stats = array(
			'all' => $all = count( tutor_utils()->get_qa_questions(0, 99999) ),
			'read' => $read = count(tutor_utils()->get_qa_questions(0, 99999, '', null, array('tutor_qna_read'=>1))),
			'unread' => $all-$read,
			'important' => count(tutor_utils()->get_qa_questions(0, 99999, '', null, array('tutor_qna_important'=>1))),
			'archived' => count(tutor_utils()->get_qa_questions(0, 99999, '', null, array('tutor_qna_archived'=>1)))
		);

		$tabs = array(
			'all',
			'read',
			'unread',
			'important',
			'archived',
		);

		$tabs = array_map(function($tab) use($stats, $url) {
			return array(
				'key'   => $tab,
				'title' => __( ucwords( $tab ), 'tutor' ),
				'value' => $stats[$tab],
				'url'   => $url . '&data='.$tab,
			);
		}, $tabs);
		
		return $tabs;
	}
}
