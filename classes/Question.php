<?php


namespace TUTOR;


class Question {

	public function __construct() {
		add_action( 'add_meta_boxes', array($this, 'register_meta_box') );
		//save question type during first add question
		add_action('save_post_tutor_question', array($this, 'save_question_type'), 10, 1);

		add_action('wp_ajax_quiz_page_add_new_question', array($this, 'quiz_page_add_new_question'));
		add_action('wp_ajax_update_tutor_question', array($this, 'update_tutor_question'));
		add_action('wp_ajax_quiz_add_answer_to_question', array($this, 'quiz_add_answer_to_question'));
		add_action('wp_ajax_quiz_delete_answer_option', array($this, 'quiz_delete_answer_option'));
		add_action('wp_ajax_quiz_question_type_changed', array($this, 'quiz_question_type_changed'));
		add_action('wp_ajax_quiz_question_delete', array($this, 'quiz_question_delete'));
		add_action('wp_ajax_sorting_quiz_questions', array($this, 'sorting_quiz_questions'));


		add_filter( "manage_tutor_question_posts_columns", array($this, 'add_column'), 10,1 );
		add_action( "manage_tutor_question_posts_custom_column" , array($this, 'custom_question_column'), 10, 2 );

	}

	public function register_meta_box(){
		add_meta_box( 'tutor-question', __( 'Question', 'tutor' ), array($this, 'quiz_question'), 'tutor_question' );
	}

	public function save_question_type($post_ID){
		$question_type = get_post_meta($post_ID, '_question_type', true);
		if ( ! $question_type){
			update_post_meta($post_ID, '_question_type', 'true_false');
		}
	}

	public function quiz_question(){
		global $post;
		$question = $post;

		$is_question_edit_page = true;

		include tutor()->path."views/metabox/quiz/single-question-item.php";
	}

	public function quiz_questions(){
		include  tutor()->path.'views/metabox/quiz_questions.php';
	}

	public function quiz_page_add_new_question(){
		global $wpdb;

		$question_title = sanitize_text_field($_POST['question_title']);
		$question_type = sanitize_text_field($_POST['question_type']);
		$quiz_id = (int) sanitize_text_field($_POST['quiz_id']);

		$question_html = '';

		$next_question_order = tutor_utils()->quiz_next_question_order_id($quiz_id);

		$post_arr = array(
			'post_type'     => 'tutor_question',
			'post_title'    => $question_title,
			'post_status'   => 'publish',
			'post_author'   => get_current_user_id(),
			'post_parent'   => $quiz_id,
			'menu_order'    => $next_question_order,
		);
		$question_id = wp_insert_post( $post_arr );

		if ($question_id){
			update_post_meta($question_id,'_question_type', $question_type);

			/**
			 * Insert True/False
			 */
			if ($question_type === 'true_false') {
				$answer_option = array(
					'answer_option_text' => __( 'True', 'tutor' ),
					'is_correct'         => '1',
				);
				$data = apply_filters( 'tutor_quiz_adding_answer_option_to_question', array(
					'comment_post_ID'  => $question_id,
					'comment_content'  => json_encode( $answer_option ),
					'comment_approved' => 'approved',
					'comment_agent'    => 'TutorLMSPlugin',
					'comment_type'     => 'quiz_answer_option',
				) );
				$wpdb->insert( $wpdb->comments, $data );

				$answer_option = array(
					'answer_option_text' => __( 'False', 'tutor' ),
					'is_correct'         => '0',
				);
				$data = apply_filters( 'tutor_quiz_adding_answer_option_to_question', array(
					'comment_post_ID'  => $question_id,
					'comment_content'  => json_encode( $answer_option ),
					'comment_approved' => 'approved',
					'comment_agent'    => 'TutorLMSPlugin',
					'comment_type'     => 'quiz_answer_option',
				) );
				$wpdb->insert( $wpdb->comments, $data );
			}

			ob_start();
			$question = get_post($question_id);
			include tutor()->path."views/metabox/quiz/single-question-item.php";
			$question_html = ob_get_clean();
		}

		wp_send_json_success(array('question_html' => $question_html));
	}


	public function update_tutor_question(){
		global $wpdb;
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

			/**
			 * Answer Option
			 */
			if ($type === 'true_false'){
				//If true/false, reset answer
				$previous_answers = tutor_utils()->get_quiz_answer_options_by_question($question_ID);

				if ($previous_answers){
					foreach ($previous_answers as $previous_answer){
						$answer_content = json_decode($previous_answer->comment_content, true);
						$answer_content['is_correct'] = '0';
						$wpdb->update($wpdb->comments, array('comment_content' => json_encode($answer_content)), array('comment_ID' => $previous_answer->comment_ID) );
					}
				}
			}

			$answer_options = tutor_utils()->avalue_dot('answer_option', $question_data);
			$answer_corrects = tutor_utils()->avalue_dot('answer_option_is_correct', $question_data);

			if (is_array($answer_options) && count($answer_options)){
				foreach ($answer_options as $answer_option_ID => $answer_option){
					$is_correct = '0';

					if ($type === 'multiple_choice'){
						$is_correct = isset($answer_corrects[$answer_option_ID]) && $answer_corrects[$answer_option_ID] == '1' ? '1' : '0';
					}elseif ($type === 'single_choice' || $type === 'true_false'){
						$correct_answer_id = sanitize_text_field(tutor_utils()->avalue_dot('answer_option_is_correct', $question_data));
						$is_correct = $correct_answer_id == $answer_option_ID ? '1' : '0';
					}

					$update_data = array(
						'answer_option_text'    => $answer_option,
						'is_correct'            => $is_correct,
					);
					$wpdb->update($wpdb->comments, array('comment_content' => json_encode($update_data)), array('comment_ID' =>$answer_option_ID ) );
				}
			}
		}

		wp_send_json_success();
	}

	public function quiz_add_answer_to_question(){
		global $wpdb;


		$question_id = (int) sanitize_text_field($_POST['question_id']);
		$question_type = get_post_meta($question_id, '_question_type', true);

		$answer_option = array(
			'answer_option_text'    => __('New answer option', 'tutor'),
			'is_correct'               => '0',
		);

		if ($question_type === 'true_false'){
			$answer_option['answer_option_text'] = __('True/False', 'tutor');
		}


		$data = apply_filters('tutor_quiz_adding_answer_option_to_question', array(
			'comment_post_ID'   => $question_id,
			'comment_content'   => json_encode($answer_option),
			'comment_approved'  => 'approved',
			'comment_agent'     => 'TutorLMSPlugin',
			'comment_type'      => 'quiz_answer_option',
		));

		$wpdb->insert($wpdb->comments, $data);
		$answer_option_id = (int) $wpdb->insert_id;

		$quiz_answer_option = (object) array_merge(array('comment_ID' => $answer_option_id), $data );

		ob_start();
		include tutor()->path."views/metabox/quiz/individual-answer-option-{$question_type}-tr.php";
		$answer_option_tr = ob_get_clean();

		wp_send_json_success(array('data_tr' => $answer_option_tr));
	}


	public function quiz_delete_answer_option(){
		global $wpdb;
		$answer_option_id = (int) sanitize_text_field($_POST['answer_option_id']);
		$wpdb->delete($wpdb->comments, array('comment_ID' => $answer_option_id));
		wp_send_json_success();
	}

	public function quiz_question_type_changed(){
		global $wpdb;

		$question_id = (int) sanitize_text_field($_POST['question_id']);
		$question_type = sanitize_text_field($_POST['question_type']);

		$question = get_post($question_id);

		/**
		 * If we found true false type, we will keep only 2 answer options
		 */

		if ($question_type === 'true_false'){
			$quiz_answer_options = tutor_utils()->get_quiz_answer_options_by_question($question->ID);
			$quiz_answer_options = array_slice($quiz_answer_options, 0, 2);

			$keep_answer_ids = wp_list_pluck($quiz_answer_options, 'comment_ID');
			$keep_answer_ids = implode( ',', array_map( 'absint', $keep_answer_ids ) );
			$wpdb->query( "DELETE FROM {$wpdb->comments} WHERE comment_post_ID = {$question_id} AND comment_type = 'quiz_answer_option' AND comment_ID NOT IN($keep_answer_ids)" );
		}

		ob_start();
		include tutor()->path."views/metabox/quiz/multi-answer-options.php";
		$answer_options = ob_get_clean();

		wp_send_json_success(array('multi_answer_options' =>$answer_options ));
	}

	public function quiz_question_delete(){
		global $wpdb;

		$question_id = (int) sanitize_text_field($_POST['question_id']);
		wp_delete_post($question_id, true);

		wp_send_json_success();
	}

	/**
	 * Sorting Order
	 */

	public function sorting_quiz_questions(){
		global $wpdb;
		$questions = tutor_utils()->avalue_dot('questions', $_POST);
		$question_ids = wp_list_pluck($questions, 'question_id');

		$i = 1;
		foreach ($question_ids as $question_id){
			$wpdb->update($wpdb->posts, array('menu_order' => $i), array('ID'=> $question_id) );
			$i++;
		}
	}



	public function add_column($columns){
		$date_col = $columns['date'];
		unset($columns['date']);
		$columns['quiz'] = __('Quiz', 'tutor');
		$columns['date'] = $date_col;

		return $columns;
	}

	public function custom_question_column($column, $post_id ){
		if ($column === 'quiz'){
			$quiz_id = tutor_utils()->get_quiz_id_by_question($post_id);

			if ($quiz_id){
				echo '<a href="'.admin_url('post.php?post='.$quiz_id.'&action=edit').'">'.get_the_title($quiz_id).'</a>';
			}
		}
	}

}