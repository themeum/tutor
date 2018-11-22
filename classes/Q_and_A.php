<?php
namespace DOZENT;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Q_and_A{

	public function __construct() {
		add_action('admin_post_dozent_place_answer', array($this, 'place_answer'));
	}


	public function place_answer(){
		dozent_utils()->checking_nonce();

		global $wpdb;

		$answer = wp_kses_post($_POST['answer']);
		$question_id = (int) sanitize_text_field($_POST['question_id']);
		$question = dozent_utils()->get_qa_question($question_id);

		$user_id = get_current_user_id();
		$user = get_userdata($user_id);
		$date = date("Y-m-d H:i:s");

		do_action('dozent_before_answer_to_question');

		$data = apply_filters('dozent_answer_to_question_data', array(
			'comment_post_ID'   => $question->comment_post_ID,
			'comment_author'    => $user->user_login,
			'comment_date'      => $date,
			'comment_date_gmt'  => get_gmt_from_date($date),
			'comment_content'   => $answer,
			'comment_approved'  => 'approved',
			'comment_agent'     => 'DozentLMSPlugin',
			'comment_type'      => 'dozent_q_and_a',
			'comment_parent'    => $question_id,
			'user_id'           => $user_id,
		));


		$wpdb->insert($wpdb->comments, $data);
		$answer_id = (int) $wpdb->insert_id;

		if ($answer_id){
			$wpdb->update($wpdb->comments, array('comment_approved' => 'answered'), array('comment_ID' =>$question_id ) );
			do_action('dozent_after_answer_to_question', $answer_id );
		}

		wp_redirect(wp_get_referer());
	}

}