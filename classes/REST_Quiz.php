<?php
/*
@REST API for quiz
@author : themeum
*/

namespace TUTOR;
use WP_REST_Request;

if(!defined ('ABSPATH'))
exit;

class REST_Quiz
{
	use REST_Response;

	private $post_type = "tutor_quiz";
	private $post_parent;
	private $t_quiz_question = "tutor_quiz_questions";
	private $t_quiz_ques_ans = "tutor_quiz_question_answers";
	private $t_quiz_attempt = "tutor_quiz_attempts";
	private $t_quiz_att_ans = "tutor_quiz_attempt_answers";

	public function quiz_with_settings(WP_REST_Request $request):object
	{
		$this->post_parent = $request->get_param('id');

		global $wpdb;

		$table = $wpdb->prefix."posts";

		$quizs = $wpdb->get_results(
			$wpdb->prepare("SELECT ID, post_title, post_content, post_name FROM $table WHERE post_type = %s AND post_parent = %d", $this->post_type, $this->post_parent)
		);	
		$data = [];

		if(count($quizs)>0)
		{
			foreach($quizs as $quiz)
			{
				$quiz->quiz_settings = get_post_meta($quiz->ID,'tutor_quiz_option',false);

				array_push($data, $quiz);

				$response = array(
					'status_code'=> 'success',
					'message'=> "Quiz retrieved successfully",
					'data'=> $data
				);

				return self::send($response);
			}
		}	
		$response = array(
			'status_code'=> 'not_found',
			'message'=> "Quiz not found for given ID",
			'data'=> $data
		);
		return self::send($response);
	}

	public function quiz_question_ans(WP_REST_Request $request):object
	{
		global $wpdb;

		$this->post_parent = $request->get_param('id');


		$q_t = $wpdb->prefix.$this->t_quiz_question;//question table

		$q_a_t = $wpdb->prefix.$this->t_quiz_ques_ans;//question answer table

		$quizs = $wpdb->get_results(
			$wpdb->prepare("SELECT question_id,question_title, question_description, question_type, question_mark, question_settings FROM $q_t WHERE quiz_id = %d", $this->post_parent)
		);			
		$data = [];

		if(count($quizs)>0)
		{

			//get question ans by question_id
			foreach ($quizs as $quiz) {
				//unserialized question settings
				$quiz->question_settings = maybe_unserialize($quiz->question_settings);

				//question options with correct ans
				$options = $wpdb->get_results(
					$wpdb->prepare("SELECT answer_title,is_correct FROM $q_a_t WHERE belongs_question_id = %d", $quiz->question_id)
				);

				//set question_answers as quiz property
				$quiz->question_answers = $options;

				array_push($data, $quiz);

			}

			$response = array(
				'status_code'=> 'success',
				'message'=> 'Question retrieved successfully',
				'data'=> $data
			);

			return self::send($response);
		}

			$response = array(
				'status_code'=> 'not_found',
				'message'=> 'Question not found for given ID',
				'data'=> []
			);

			return self::send($response);		
	}
}

?>