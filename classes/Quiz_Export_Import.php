<?php

/**
 * Quiz Export Import class
 *
 * @author: themeum
 * @author_uri: https://themeum.com
 * @package Tutor
 * @since v.
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Quiz_Export_Import {

	public function __construct() {

		add_action('wp_ajax_quiz_export_data', array($this, 'quiz_export_data_callback'));
		add_action('wp_ajax_nopriv_quiz_export_data', array($this, 'quiz_export_data_callback'));

		add_action('wp_ajax_quiz_import_data', array($this, 'quiz_import_data_callback'));
		add_action('wp_ajax_nopriv_quiz_import_data', array($this, 'quiz_import_data_callback'));

	}


	/**
	 * Quiz Export Data
	 */
	public function quiz_export_data_callback() {

		global $wpdb;
		$quiz_id = sanitize_text_field($_POST["quiz_id"]);
	
		if( $quiz_id ) {
			$sql = "SELECT question_id, question_title, question_description, question_type, question_mark, question_settings, question_order".
				" FROM {$wpdb->prefix}tutor_quiz_questions".
				" WHERE quiz_id = {$quiz_id}";
	
			$results = $wpdb->get_results( $sql );
	
			$final_data = array();
			if(!empty($results)) {
				foreach ($results as $key => $value) {
					$temp = array();
					$sql = "SELECT answer_title, answer_view_format, is_correct, image_id, answer_two_gap_match, answer_order".
					" FROM {$wpdb->prefix}tutor_quiz_question_answers".
					" WHERE belongs_question_id = {$value->question_id}";
					$question_results = $wpdb->get_results( $sql );
	
					$settings = maybe_unserialize($value->question_settings);
					$temp[] = 'question';
					$temp[] = $value->question_title;
					$temp[] = $value->question_description;
					$temp[] = $value->question_type;
					$temp[] = $value->question_mark;
					$temp[] = $value->question_order;
					$temp[] = isset($settings['answer_required']) ? 1 : '';
					$temp[] = isset($settings['randomize_question']) ? 1 : '';
					$temp[] = isset($settings['show_question_mark']) ? 1 : '';
					
					$final_data[] = $temp;
	
					if(!empty($question_results)) {
						foreach ($question_results as $key => $value) {
							$answer_temp = array();
							$answer_temp[] = 'answer';
							$answer_temp[] = $value->answer_title;
							$answer_temp[] = $value->answer_view_format;
							$answer_temp[] = $value->is_correct;
							$answer_temp[] = $value->image_id;
							$answer_temp[] = $value->answer_two_gap_match;
							$answer_temp[] = $value->answer_order;
							$final_data[] = $answer_temp;
						}
					}
				}
			}
			wp_send_json_success($final_data);
		} else {
			wp_send_json_error();
		}
		die();
	}


	/**
	 * Quiz Import Data
	 */
	public function quiz_import_data_callback() {
		global $wpdb;
		$quiz_id = sanitize_text_field($_POST["quiz_id"]);
		if ($quiz_id) {
			if (isset($_FILES["csv_file"])) {
				if( $_FILES["csv_file"]["type"] = 'text/csv' && $_FILES["csv_file"]["size"] < 10000 ) {
					if ($_FILES["csv_file"]["size"] > 0) {
						
						$fileName = $_FILES["csv_file"]["tmp_name"];
						$file = fopen($fileName, "r");
						$question_id = $question_type = '';

						while (($column = fgetcsv($file, 10000, ",")) !== FALSE) {

							if($column[0] == 'question') {
								$question_type = $column[3];
								$question_data = array(
									'quiz_id' => $quiz_id,
									'question_title' => $column[1],
									'question_description' => $column[2],
									'question_type' => $question_type,
									'question_mark' => $column[4],
									'question_settings' => maybe_serialize(
										array(
											'question_type' => $column[3],
											'answer_required' => $column[6],
											'randomize_question' => $column[7],
											'question_mark' => $column[4],
											'show_question_mark' => $column[8]
										)
									),
									'question_order' => $column[5]
								);
								$wpdb->insert($wpdb->prefix.'tutor_quiz_questions', $question_data);
								$question_id = $wpdb->insert_id;
							}

							if($column[0] == 'answer') {
								$answer_data = array(
									'belongs_question_id' => $question_id,
									'belongs_question_type' => $question_type,
									'answer_title' => $column[1],
									'is_correct' => $column[3],
									'image_id' => $column[4],
									'answer_two_gap_match' => $column[5],
									'answer_view_format' => $column[2],
									'answer_settings' => '',
									'answer_order' => $column[6],
								);
								$wpdb->insert($wpdb->prefix.'tutor_quiz_question_answers', $answer_data);
							}
						}
					}
				}
				wp_send_json_success( 'Data Insert Done' );
			} else {
				wp_send_json_error();
			}
		} else {
			wp_send_json_error('Quiz ID not found.');
		}
	}

}