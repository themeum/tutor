<?php


namespace TUTOR;


class Question {

	public function __construct() {

		add_action('wp_ajax_quiz_page_add_new_question', array($this, 'quiz_page_add_new_question'));
		add_action('wp_ajax_update_tutor_question', array($this, 'update_tutor_question'));

	}


	public function quiz_page_add_new_question(){
		$question_title = sanitize_text_field($_POST['question_title']);
		$question_type = sanitize_text_field($_POST['question_type']);
		$quiz_id = (int) sanitize_text_field($_POST['quiz_id']);

		$post_arr = array(
			'post_type'    => 'tutor_question',
			'post_title'   => $question_title,
			'post_status'  => 'publish',
			'post_author'  => get_current_user_id(),
			'post_parent'  => $quiz_id,
		);
		$question_id = wp_insert_post( $post_arr );

		if ($question_id){
			update_post_meta($question_id,'_question_type', $question_type);
		}

		wp_send_json_success();

	}


	public function update_tutor_question(){
		$questions = $_POST['tutor_question'];

		if ( ! is_array($questions) || ! count($questions)){
			wp_send_json_error();
		}

		//die(print_r($_POST['tutor_question']));

		foreach ($questions as $question_ID => $question_data){
			$title = sanitize_text_field(tutor_utils()->avalue_dot('question_title', $question_data));
			$description = wp_kses_post(tutor_utils()->avalue_dot('question_description', $question_data));
			
			$type = sanitize_text_field(tutor_utils()->avalue_dot('question_type', $question_data));
			$mark = sanitize_text_field(tutor_utils()->avalue_dot('question_mark', $question_data));
			$hints = sanitize_text_field(tutor_utils()->avalue_dot('question_hints', $question_data));

			$post_arr = array(
				'ID'            => $question_ID,
				'post_title'    => $title,
				'post_content'  => $description,
			);
			wp_update_post($post_arr);

			update_post_meta($question_ID, '_question_hints', $hints);
			update_post_meta($question_ID, '_question_mark', $mark);
			update_post_meta($question_ID, '_question_type', $type);
		}

		wp_send_json_success();
	}



}