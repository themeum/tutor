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
		add_action('wp_ajax_quiz_export_data', 			array($this, 'quiz_export_data_callback'));
		add_action('wp_ajax_quiz_import_data', 			array($this, 'quiz_import_data_callback'));
		add_action('wp_ajax_tutor_quiz_builder_import', array($this, 'tutor_quiz_builder_import_callback'));
	}

	public function tutor_quiz_builder_import_callback() {

		
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
			
			$content_post = get_post($quiz_id);
			$meta = get_post_meta($quiz_id, 'tutor_quiz_option', true);
			
			$_temp = array();
			
			$_temp[] = 'settings';
			$_temp[] = $content_post->post_title;
			$_temp[] = $content_post->post_content;
			$_temp[] = isset($meta['time_limit']['time_value']) ? $meta['time_limit']['time_value'] : '';
			$_temp[] = isset($meta['time_limit']['time_type']) ? $meta['time_limit']['time_type'] : '';
			$_temp[] = isset($meta['hide_quiz_time_display']) ? $meta['hide_quiz_time_display'] : '';
			$_temp[] = isset($meta['attempts_allowed']) ? $meta['attempts_allowed'] : '';
			$_temp[] = isset($meta['passing_grade']) ? $meta['passing_grade'] : '';
			$_temp[] = isset($meta['max_questions_for_answer']) ? $meta['max_questions_for_answer'] : '';
			$_temp[] = isset($meta['quiz_auto_start']) ? $meta['quiz_auto_start'] : '';
			$_temp[] = isset($meta['question_layout_view']) ? $meta['question_layout_view'] : '';
			$_temp[] = isset($meta['questions_order']) ? $meta['questions_order'] : '';
			$_temp[] = isset($meta['hide_question_number_overview']) ? $meta['hide_question_number_overview'] : '';
			$_temp[] = isset($meta['short_answer_characters_limit']) ? $meta['short_answer_characters_limit'] : '';
			$final_data[] = $_temp;

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
		
		if (isset($_FILES["csv_file"])) {
			if( $_FILES["csv_file"]["type"] = 'text/csv' && $_FILES["csv_file"]["size"] < 20000 ) {
				if ($_FILES["csv_file"]["size"] > 0) {
					
					$topic_id = sanitize_text_field($_POST["topic_id"]);

					$fileName = $_FILES["csv_file"]["tmp_name"];
					$file = fopen($fileName, "r");
					$quiz_id = $question_id = $question_type = $quiz_title = '';

					while (($column = fgetcsv($file, 10000, ",")) !== FALSE) {

						if($column[0] == 'settings') {
							$next_order_id = tutor_utils()->get_next_course_content_order_id($topic_id);
							$quiz_title = $column[1];
							$quiz_id = wp_insert_post(array(
								'post_type'=>'tutor_quiz', 
								'post_title'=> $quiz_title,
								'post_content'=> $column[2],
								'post_status'   => 'publish',
								'post_author'   => get_current_user_id(),
								'post_parent'   => $topic_id,
								'menu_order'    => $next_order_id,
							));
							$_time_value = isset($column[3]) ? $column[3] : '';
							$_time_type = isset($column[4]) ? $column[4] : '';
							$_time = isset($column[5]) ? $column[5] : '';
							$_attempts = isset($column[6]) ? $column[6] : '';
							$_grade = isset($column[7]) ? $column[7] : '';
							$_max_q = isset($column[8]) ? $column[8] : '';
							$_start = isset($column[9]) ? $column[9] : '';
							$_layout = isset($column[10]) ? $column[10] : '';
							$_order = isset($column[11]) ? $column[11] : '';
							$_overview = isset($column[12]) ? $column[12] : '';
							$_limit = isset($column[13]) ? $column[13] : '';

							if($_time_value||$_time_type||$_time||$_attempts||$_grade||$_max_q||$_start||$_layout||$_order||$_overview||$_limit){
								$temp = array();
								if($_time_value||$_time_type){
									$temp['time_limit'] = array(
										'time_value' => $_time_value ? $_time_value : '0',
										'time_type' => $_time_type ? $_time_type : 'minutes'
									);
									$temp['attempts_allowed'] = $_attempts ? $_attempts : 10;
									$temp['passing_grade'] = $_grade ? $_grade : 80;
									$temp['max_questions_for_answer'] = $_max_q ? $_max_q : 10;
									$temp['question_layout_view'] = $_layout ? $_layout : '';
									$temp['questions_order'] = $_order ? $_order : 'rand';
									$temp['short_answer_characters_limit'] = $_limit ? $_limit : 200;
									$temp['hide_quiz_time_display'] = $_time ? $_time : 0;
									$temp['quiz_auto_start'] = $_start ? $_start : 0;
									$temp['hide_question_number_overview'] = $_overview ? $_overview : 0;

									update_post_meta($quiz_id, 'tutor_quiz_option', $temp);
								}
							}
						}
					
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

					ob_start();
					?>
					<div id="tutor-quiz-<?php echo $quiz_id; ?>" class="course-content-item tutor-quiz tutor-quiz-<?php echo $topic_id; ?> ui-sortable-handle">
						<div class="tutor-lesson-top">
							<i class="tutor-icon-move"></i>
							<a href="javascript:;" class="open-tutor-quiz-modal" data-quiz-id="<?php echo $quiz_id; ?>" data-topic-id="<?php echo $topic_id; ?>">
								<i class=" tutor-icon-doubt"></i>[QUIZ] <?php echo $quiz_title; ?></a>
							<a href="#quiz-builder-export" class="btn-csv-download" data-id="<?php echo $quiz_id; ?>">
								<svg id="Solid" height="512" viewBox="0 0 512 512" width="512" xmlns="http://www.w3.org/2000/svg"><path d="m232 271.431v-223.431a24 24 0 0 1 48 0v223.431l-24 24zm40.971 97.54 144-144a24 24 0 0 0 -33.942-33.942l-127.029 127.03-127.029-127.03a24 24 0 0 0 -33.942 33.942l144 144a24 24 0 0 0 33.942 0zm215.029 95.029v-112a24 24 0 0 0 -48 0v88h-368v-88a24 24 0 0 0 -48 0v112a24 24 0 0 0 24 24h416a24 24 0 0 0 24-24z"></path></svg>
								<?php _e('Export', 'tutor'); ?>
							</a>
							<a href="javascript:;" class="tutor-delete-quiz-btn" data-quiz-id="<?php echo $quiz_id; ?>"><i class="tutor-icon-garbage"></i></a>
						</div>
					</div>
					<?php
					$output_quiz_row = ob_get_clean();
					wp_send_json_success(array('output_quiz_row' => $output_quiz_row));
					//wp_send_json_success( 'Data Insert Done' );

				}
				wp_send_json_error();
			}
			wp_send_json_error();
		} else {
			wp_send_json_error();
		}
	}
}