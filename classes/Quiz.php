<?php

/**
 * Quize class
 *
 * @author: themeum
 * @author_uri: https://themeum.com
 * @package Tutor
 * @since v.1.0.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Quiz {

	public function __construct() {
		add_filter( "manage_tutor_quiz_posts_columns", array($this, 'add_column'), 10,1 );
		add_action( "manage_tutor_quiz_posts_custom_column" , array($this, 'custom_question_column'), 10, 2 );

		add_action( 'add_meta_boxes', array($this, 'register_meta_box') );
		add_action('save_post_tutor_quiz', array($this, 'save_quiz_meta'));

		//Depricated at alpha version
		add_action('wp_ajax_tutor_load_quiz_modal', array($this, 'tutor_load_quiz_modal'));

		add_action('wp_ajax_tutor_load_quiz_builder_modal', array($this, 'tutor_load_quiz_builder_modal'));
		add_action('wp_ajax_tutor_add_quiz_to_post', array($this, 'tutor_add_quiz_to_post'));
		add_action('wp_ajax_remove_quiz_from_post', array($this, 'remove_quiz_from_post'));

		add_action('wp_ajax_tutor_quiz_timeout', array($this, 'tutor_quiz_timeout'));

		//User take the quiz
		add_action('template_redirect', array($this, 'start_the_quiz'));
		add_action('template_redirect', array($this, 'answering_quiz'));
		add_action('template_redirect', array($this, 'finishing_quiz_attempt'));

		add_action('admin_action_review_quiz_answer', array($this, 'review_quiz_answer'));

		/**
		 * New Design Quiz
		 */

		add_action('wp_ajax_tutor_create_quiz_and_load_modal', array($this, 'tutor_create_quiz_and_load_modal'));
		add_action('wp_ajax_tutor_quiz_builder_quiz_update', array($this, 'tutor_quiz_builder_quiz_update'));
		add_action('wp_ajax_tutor_load_edit_quiz_modal', array($this, 'tutor_load_edit_quiz_modal'));
		add_action('wp_ajax_tutor_quiz_builder_get_question_form', array($this, 'tutor_quiz_builder_get_question_form'));
		add_action('wp_ajax_tutor_quiz_modal_update_question', array($this, 'tutor_quiz_modal_update_question'));
		add_action('wp_ajax_tutor_quiz_builder_question_delete', array($this, 'tutor_quiz_builder_question_delete'));
		add_action('wp_ajax_tutor_quiz_add_question_answers', array($this, 'tutor_quiz_add_question_answers'));
		add_action('wp_ajax_tutor_save_quiz_answer_options', array($this, 'tutor_save_quiz_answer_options'));
		add_action('wp_ajax_tutor_quiz_builder_get_answers_by_question', array($this, 'tutor_quiz_builder_get_answers_by_question'));
		add_action('wp_ajax_tutor_quiz_builder_delete_answer', array($this, 'tutor_quiz_builder_delete_answer'));
		add_action('wp_ajax_tutor_quiz_answer_sorting', array($this, 'tutor_quiz_answer_sorting'));
		add_action('wp_ajax_tutor_quiz_modal_update_settings', array($this, 'tutor_quiz_modal_update_settings'));



		/**
         * Frontend Stuff
         */

		add_action('wp_ajax_tutor_render_quiz_content', array($this, 'tutor_render_quiz_content'));

	}

	public function add_column($columns){
		$date_col = $columns['date'];
		unset($columns['date']);
		$columns['quiz'] = __('Course', 'tutor');
		$columns['questions'] = __('Questions', 'tutor');
		$columns['date'] = $date_col;

		return $columns;
	}

	public function custom_question_column($column, $post_id ){
		if ($column === 'quiz'){
			$quiz = tutor_utils()->get_course_by_quiz($post_id);

			if ($quiz){
				echo '<a href="'.admin_url('post.php?post='.$quiz->ID.'&action=edit').'">'.get_the_title($quiz->ID).'</a>';
			}
		}

		if ($column === 'questions'){
			echo tutor_utils()->total_questions_for_student_by_quiz($post_id);
		}
	}

	public function register_meta_box(){
		add_meta_box( 'tutor-quiz-questions', __( 'Questions', 'tutor' ), array($this, 'quiz_questions'), 'tutor_quiz' );
		add_meta_box( 'tutor-quiz-settings', __( 'Settings', 'tutor' ), array($this, 'quiz_settings'), 'tutor_quiz' );
	}

	public function quiz_questions(){
		include  tutor()->path.'views/metabox/quiz_questions.php';
	}

	public function quiz_settings(){
		include  tutor()->path.'views/metabox/quizzes.php';
	}

	public function save_quiz_meta($post_ID){
		if (isset($_POST['quiz_option'])){
			$quiz_option = tutor_utils()->sanitize_array($_POST['quiz_option']);
			update_post_meta($post_ID, 'tutor_quiz_option', $quiz_option);
		}
	}

	/**
	 * @depricated at alpha version
	 * Check tutor_load_quiz_builder_modal instead of this method
	 */
	public function tutor_load_quiz_modal(){
		$quiz_for_post_id = (int) sanitize_text_field($_POST['quiz_for_post_id']);

		$search_terms = sanitize_text_field(tutor_utils()->avalue_dot('search_terms', $_POST));
		$quizzes = tutor_utils()->get_unattached_quiz(array('search_term' => $search_terms));

		$output = '';
		if ($quizzes){
			foreach ($quizzes as $quiz){
				$output .= "<p><label><input type='checkbox' name='quiz_for[{$quiz_for_post_id}][quiz_id][]' value='{$quiz->ID}' > {$quiz->post_title} </label></p>";
			}
			$output .= '<p class="quiz-search-suggest-text">Search the quiz to get specific quiz</p>';
		}else{
			$add_question_url = admin_url('post-new.php?post_type=tutor_quiz');
			$output .= sprintf('No quiz available right now, please %s add some quiz %s', '<a href="'.$add_question_url.'" target="_blank">', '</a>'  );
		}

		ob_start();
		?>
        <div class="tutor-option-field-row">
            <div class="tutor-option-field-label">
                <label for="">
					<?php _e('New quiz title', 'tutor'); ?>
                </label>
            </div>
            <div class="tutor-option-field">
                <input type="text" name="quiz_title" placeholder="<?php _e('Place quiz title to create new quiz', 'tutor'); ?>" >
                <p class="desc"><?php _e('Provide a quiz title to create a quiz from here.'); ?></p>
            </div>
        </div>

		<?php
		$output .= ob_get_clean();

		wp_send_json_success(array('output' => $output));
	}

	/**
	 * Tutor Quiz Builder Modal
	 */
	public function tutor_load_quiz_builder_modal(){
		ob_start();
		include  tutor()->path.'views/modal/add_quiz.php';
		$output = ob_get_clean();

		wp_send_json_success(array('output' => $output));

	}

	public function tutor_add_quiz_to_post(){
		global $wpdb;

		$quiz_data = tutor_utils()->avalue_dot('quiz_for', $_POST);

		$output = '';
		$post_id = (int) sanitize_text_field(tutor_utils()->avalue_dot('parent_post_id', $_POST)) ;
		if ($quiz_data){
			foreach ($quiz_data as $post_id => $quiz_ids_a);

			$quiz_ids = tutor_utils()->avalue_dot('quiz_id', $quiz_ids_a);
			foreach ($quiz_ids as $quiz_id){
				$wpdb->update($wpdb->posts, array('post_parent' => $post_id), array('ID' => $quiz_id) );
			}
		}

		$quiz_title = sanitize_text_field(tutor_utils()->avalue_dot('quiz_title', $_POST));
		if ($quiz_title){
			wp_insert_post(array(
				'post_parent'   => $post_id,
				'post_title'    => $quiz_title,
				'post_type'     => 'tutor_quiz',
				'post_status'   => 'publish',
			));
		}

		if ($post_id) {
			ob_start();
			$attached_quizzes = tutor_utils()->get_attached_quiz( $post_id );
			if ( $attached_quizzes ) {
				foreach ( $attached_quizzes as $attached_quiz ) {
					?>
                    <div id="added-quiz-id-<?php echo $attached_quiz->ID; ?>" class="added-quiz-item added-quiz-item-<?php echo $attached_quiz->ID; ?>" data-quiz-id="<?php echo $attached_quiz->ID; ?>">
                        <span class="quiz-icon"><i class="dashicons dashicons-clock"></i></span>
                        <span class="quiz-name">
							<?php edit_post_link( $attached_quiz->post_title, null, null, $attached_quiz->ID ); ?>
						</span>
                        <span class="quiz-control">
							<a href="javascript:;" class="tutor-quiz-delete-btn"><i class="dashicons dashicons-trash"></i></a>
						</span>
                    </div>
					<?php
				}
			}
			$output .= ob_get_clean();
		}

		wp_send_json_success(array('output' => $output));
	}

	public function remove_quiz_from_post(){
		global $wpdb;
		$quiz_id = (int) tutor_utils()->avalue_dot('quiz_id', $_POST);
		$wpdb->update($wpdb->posts, array('post_parent' => 0), array('ID' => $quiz_id) );
		wp_send_json_success();
	}

	public function start_the_quiz(){
		if ( ! isset($_POST['tutor_action'])  ||  $_POST['tutor_action'] !== 'tutor_start_quiz' ){
			return;
		}
		//Checking nonce
		tutor_utils()->checking_nonce();

		if ( ! is_user_logged_in()){
			//TODO: need to set a view in the next version
			die('Please sign in to do this operation');
		}

		global $wpdb;

		$user_id = get_current_user_id();
		$user = get_userdata($user_id);

		$quiz_id = (int) sanitize_text_field($_POST['quiz_id']);
		$quiz = get_post($quiz_id);
		$date = date("Y-m-d H:i:s");


		$tutor_quiz_option = maybe_unserialize(get_post_meta($quiz_id, 'tutor_quiz_option', true));

		echo '<pre>';
		//die(print_r($tutor_quiz_option));


		$attempts_allowed = tutor_utils()->get_quiz_option($quiz_id, 'attempts_allowed', 0);


		/*
		do_action('tutor_before_start_quiz', $quiz_id);
		$data = array(
			'comment_post_ID'   => $quiz_id, //QuizID
			'comment_author'    => $user->user_login,
			'comment_date'      => $date,
			'comment_date_gmt'  => get_gmt_from_date($date),
			'comment_approved'  => 'quiz_started', //quiz_timeup, quiz_complete
			'comment_agent'     => 'TutorLMSPlugin',
			'comment_type'      => 'tutor_quiz_attempt',
			'comment_parent'    => $quiz->post_parent, //Quiz Parent Attached Course || Lesson || Topic
			'user_id'           => $user_id,
		);

		$wpdb->insert($wpdb->comments, $data);

		$attempt_id = (int) $wpdb->insert_id;


		*/

		$time_limit = tutor_utils()->get_quiz_option($quiz_id, 'time_limit.time_value');
		$time_limit_seconds = 0;
		$time_type = 'seconds';
		if ($time_limit){
			$time_type = tutor_utils()->get_quiz_option($quiz_id, 'time_limit.time_type');

			switch ($time_type){
				case 'seconds':
					$time_limit_seconds = $time_limit;
					break;
				case 'minutes':
					$time_limit_seconds = $time_limit * 60;
					break;
				case 'hours':
					$time_limit_seconds = $time_limit * 60 * 60;
					break;
				case 'days':
					$time_limit_seconds = $time_limit * 60 * 60 * 24;
					break;
				case 'weeks':
					$time_limit_seconds = $time_limit * 60 * 60 * 24 * 7;
					break;
			}
		}

		$max_question_allowed = tutor_utils()->max_questions_for_take_quiz($quiz_id);
		$quiz_attempt_info = array(
			'time_limit'            => $time_limit,
			'time_type'             => $time_type,
			'time_limit_seconds'    => $time_limit_seconds,
			'total_question'        => $max_question_allowed,
			'answered_question'     => 0,
			'current_question'      => 0,
			'marks_earned'          => 0,
			'answers'               => array(),
		);

		$tutor_quiz_option['time_limit']['time_limit_seconds'] = $time_limit_seconds;


		$attempt_data = array(
		        'quiz_id'                   => $quiz_id,
		        'user_id'                   => $user_id,
		        'total_questions'           => $tutor_quiz_option['max_questions_for_answer'],
		        'total_answered_questions'  => 0,
		        'attempt_info'              => maybe_serialize($tutor_quiz_option),
		        'attempt_status'            => 'attempt_started',
		        'attempt_ip'                => tutor_utils()->get_ip(),
		        'attempt_started_at'        => $date,
        );

		$wpdb->insert($wpdb->prefix.'tutor_quiz_attempts', $attempt_data);
		$attempt_id = (int) $wpdb->insert_id;




		die(print_r($tutor_quiz_option));


		//answers format
		/*
		array(
				'0' => array( 'questionID' => 344, 'has_correct' => 1, //or 0 for false, 'questionSiNo' => 1
					'answers_list' => array(
							'answers_id' => array('selected_answerId_1', 'selected_answerId_2', 'or_line_answer_text')
					)
				),

				 '1' => array( 'questionID' => 654, 'has_correct' => 0, //or 0 for false, 'questionSiNo' => 2
					'answers_list' => array(
							'answers_id' => array('selected_answerId_1', 'selected_answerId_2', 'or_line_answer_text')
					)
				),
		);

		update_comment_meta($attempt_id, 'quiz_attempt_info', $quiz_attempt_info);
		update_comment_meta($attempt_id, 'earned_mark_percent', '0');

		do_action('tutor_after_start_quiz', $quiz_id, $attempt_id);
		*/



		wp_redirect(tutor_utils()->input_old('_wp_http_referer'));
		die();
	}


	public function answering_quiz(){
		if ( ! isset($_POST['tutor_action'])  ||  $_POST['tutor_action'] !== 'tutor_answering_quiz_question' ){
			return;
		}
		//Checking nonce
		tutor_utils()->checking_nonce();

		if ( ! is_user_logged_in()){
			die('Please sign in to do this operation');
		}

		global $wpdb;

		$user_id = get_current_user_id();
		$attempt_id = (int) sanitize_text_field(tutor_utils()->avalue_dot('attempt_id', $_POST));
		$post_question_id = (int) sanitize_text_field(tutor_utils()->avalue_dot('quiz_question_id', $_POST));
		$attempt = tutor_utils()->get_attempt($attempt_id);

		if ( ! $attempt || $user_id != $attempt->user_id){
			die('Operation not allowed, attempt not found or permission denied');
		}

		$attempt_info = tutor_utils()->quiz_attempt_info($attempt_id);
		$given_answers = tutor_utils()->avalue_dot("attempt.{$attempt_id}.quiz_question.{$post_question_id}", $_POST);

		$plus_mark = 0;
		$minus_mark = 0;
		$is_answer_corrected = false;

		$answers = array(
			'questionID' => $post_question_id,
		);

		$question_type = get_post_meta($post_question_id, '_question_type', true);
		$question_mark = get_post_meta($post_question_id, '_question_mark', true);

		if ($given_answers){
			$answers['status'] = 'answered';  //or 0 for false, 'questionSiNo' => 2
			$answers['has_correct'] = 0;

			$saved_answers = tutor_utils()->get_quiz_answer_options_by_question($post_question_id);
			$corrects_answer_ids = array();
			if (is_array($saved_answers) && count($saved_answers)){
				foreach ($saved_answers as $saved_answer){
					$saved_answer_info = json_decode($saved_answer->comment_content);

					if ( ! empty($saved_answer_info->is_correct) && $saved_answer_info->is_correct){
						$corrects_answer_ids[] = $saved_answer->comment_ID;
					}
				}
			}

			if ($question_type === 'multiple_choice'){
				$given_answers = (array) $given_answers;
			}

			//TODO: need to provide support for question type more if we add
			//Checking if all answer corrects
			if ($question_type === 'true_false' || $question_type === 'multiple_choice' || $question_type === 'single_choice'){
				if ($question_type === 'multiple_choice') {
					$is_answer_corrected = count(array_intersect($given_answers, $corrects_answer_ids)) == count($given_answers);
				}else{
					$is_answer_corrected = in_array($given_answers, $corrects_answer_ids);
				}
			}

			if ($is_answer_corrected){
				$plus_mark = $question_mark;
				$answers['has_correct'] = 1;
			}else{
				//TODO: Do operation for incorrect answer
			}

			$answers['plus_mark'] = $plus_mark;
			$answers['minus_mark'] = $minus_mark;

			$answers['answers_list'] = array(
				'answer_type' => $question_type,
				'answer_ids' => $given_answers
			);
		}else{
			//If not answered, that means users skipped the questions
			$answers = array(
				'questionID' => $post_question_id, 'status' => 'skipped', 'has_correct' => 0, //or 0 for false, 'questionSiNo' => 2
				'plus_mark' => 0,
				'minus_mark' => 0,
				'answers_list' => array()
			);
		}

		$answers['question_mark'] = $question_mark;

		if ($is_answer_corrected){
			if (isset($attempt_info['marks_earned'])){
				//If not found
				$attempt_info['marks_earned'] = $attempt_info['marks_earned'] + $plus_mark;
			}else{
				$attempt_info['marks_earned'] = $plus_mark;
			}
		}else{
			if ( ! isset($attempt_info['marks_earned'])){
				$attempt_info['marks_earned'] = 0;
			}

			//Todo: mark minus if necessary
		}

		$attempt_info['answers'][] = $answers;
		tutor_utils()->quiz_update_attempt_info($attempt_id, $attempt_info);

		wp_redirect(tutor_utils()->input_old('_wp_http_referer'));
		die();
	}

	/**
	 * Quiz attempt will be finish here
	 *
	 */

	public function finishing_quiz_attempt(){
		if ( ! isset($_POST['tutor_action'])  ||  $_POST['tutor_action'] !== 'tutor_finish_quiz_attempt' ){
			return;
		}
		//Checking nonce
		tutor_utils()->checking_nonce();

		if ( ! is_user_logged_in()){
			die('Please sign in to do this operation');
		}


		global $wpdb;

		$quiz_id = (int) sanitize_text_field($_POST['quiz_id']);

		$is_started_quiz = tutor_utils()->is_started_quiz($quiz_id);
		$attempt_id = $is_started_quiz->comment_ID;

		if ($is_started_quiz) {
			do_action('tutor_quiz_finished_before', $attempt_id);

			$quiz_attempt_info = tutor_utils()->quiz_attempt_info( $attempt_id );
			$answers = tutor_utils()->avalue_dot('answers', $quiz_attempt_info);

			$total_marks = 0;
			if (is_array($answers)){
				$total_marks = array_sum(wp_list_pluck($answers, 'question_mark'));
			}

			$quiz_attempt_info['total_marks'] = $total_marks;
			$pass_mark_percent = tutor_utils()->get_quiz_option($quiz_id,'passing_grade');
			$quiz_attempt_info['pass_mark_percent'] = $pass_mark_percent;
			$quiz_attempt_info['submission_time'] = time();

			//Updating Attempt Info
			tutor_utils()->quiz_update_attempt_info($attempt_id, $quiz_attempt_info);

			$wpdb->update($wpdb->comments, array('comment_approved' => 'quiz_finished'), array('comment_ID' => $attempt_id));

			do_action('tutor_quiz_finished_after', $attempt_id);
		}

		wp_redirect(tutor_utils()->input_old('_wp_http_referer'));
		die();
	}

	/**
	 * Quiz timeout by ajax
	 */
	public function tutor_quiz_timeout(){
		global $wpdb;

		$quiz_id = (int) sanitize_text_field($_POST['quiz_id']);

		$is_started_quiz = tutor_utils()->is_started_quiz($quiz_id);
		$attempt_id = $is_started_quiz->comment_ID;

		if ($is_started_quiz) {
			$quiz_attempt_info = tutor_utils()->quiz_attempt_info( $attempt_id );
			$answers = tutor_utils()->avalue_dot('answers', $quiz_attempt_info);

			$total_marks = 0;
			if (is_array($answers)){
				$total_marks = array_sum(wp_list_pluck($answers, 'question_mark'));
			}

			$quiz_attempt_info['total_marks'] = $total_marks;
			$pass_mark_percent = tutor_utils()->get_quiz_option($quiz_id,'passing_grade');
			$quiz_attempt_info['pass_mark_percent'] = $pass_mark_percent;

			//Updating Attempt Info
			tutor_utils()->quiz_update_attempt_info($attempt_id, $quiz_attempt_info);

			$wpdb->update($wpdb->comments, array('comment_approved' => 'quiz_timeout'), array('comment_ID' => $attempt_id));
			wp_send_json_success();
		}

		wp_send_json_error(__('Quiz has been timeout already', 'tutor'));
	}

	/**
	 * Review the answer and change individual answer result
	 */

	public function review_quiz_answer(){
		$attempt_id = (int) sanitize_text_field($_GET['attempt_id']);
		$answer_index = (int) sanitize_text_field($_GET['answer_index']);
		$mark_as = sanitize_text_field($_GET['mark_as']);

		$attempt_info = tutor_utils()->quiz_attempt_info($attempt_id);

		$previous_answer = $attempt_info['answers'][$answer_index];
		$previous_correct = tutor_utils()->avalue_dot('has_correct', $previous_answer);

		if ($mark_as === 'correct' && ! $previous_correct ){
			$previous_answer['has_correct'] = 1;
			$previous_answer['plus_mark'] = $previous_answer['question_mark'];
			$previous_answer['minus_mark'] = 0;
			$attempt_info['marks_earned'] = $attempt_info['marks_earned'] + $previous_answer['question_mark'];

		}elseif($mark_as === 'incorrect' && $previous_correct){
			$previous_answer['has_correct'] = 0;
			$previous_answer['plus_mark'] = 0;
			$previous_answer['minus_mark'] = 0;
			$attempt_info['marks_earned'] = $attempt_info['marks_earned'] - $previous_answer['question_mark'];
		}

		$attempt_info['answers'][$answer_index] = $previous_answer;
		$attempt_info['manual_reviewed'] = time();

		tutor_utils()->quiz_update_attempt_info($attempt_id, $attempt_info);

		wp_redirect(admin_url("admin.php?page=tutor_quiz_attempts&sub_page=view_attempt&attempt_id=".$attempt_id));
		die();
	}


	/**
	 * New Design Quiz
	 */
	public function tutor_create_quiz_and_load_modal(){
		$topic_id           = sanitize_text_field($_POST['topic_id']);
		$quiz_title         = sanitize_text_field($_POST['quiz_title']);
		$quiz_description   = sanitize_text_field($_POST['quiz_description']);
		$next_order_id      = tutor_utils()->get_next_course_content_order_id($topic_id);

		$post_arr = array(
			'post_type'     => 'tutor_quiz',
			'post_title'    => $quiz_title,
			'post_content'  => $quiz_description,
			'post_status'   => 'publish',
			'post_author'   => get_current_user_id(),
			'post_parent'   => $topic_id,
			'menu_order'    => $next_order_id,
		);
		$quiz_id = wp_insert_post( $post_arr );

		ob_start();
		include  tutor()->path.'views/modal/edit_quiz.php';
		$output = ob_get_clean();

		ob_start();
		?>
        <div id="tutor-quiz-<?php echo $quiz_id; ?>" class="course-content-item tutor-quiz tutor-quiz-<?php echo $quiz_id; ?>">
            <div class="tutor-lesson-top">
                <i class="tutor-icon-move"></i>
                <a href="javascript:;" class="open-tutor-quiz-modal" data-quiz-id="<?php echo $quiz_id; ?>" data-topic-id="<?php echo $topic_id;
				?>"> <i class=" tutor-icon-doubt"></i>[QUIZ] <?php echo $quiz_title; ?> </a>
                <a href="javascript:;" class="tutor-delete-quiz-btn" data-quiz-id="<?php echo $quiz_id; ?>"><i class="tutor-icon-garbage"></i></a>
            </div>
        </div>
		<?php
		$output_quiz_row = ob_get_clean();

		wp_send_json_success(array('output' => $output, 'output_quiz_row' => $output_quiz_row));
	}

	/**
	 * Update Quiz from quiz builder modal
	 *
	 * @since v.1.0.0
	 */
	public function tutor_quiz_builder_quiz_update(){
		$quiz_id         = sanitize_text_field($_POST['quiz_id']);
		$topic_id         = sanitize_text_field($_POST['topic_id']);
		$quiz_title         = sanitize_text_field($_POST['quiz_title']);
		$quiz_description   = sanitize_text_field($_POST['quiz_description']);

		$post_arr = array(
			'ID'    => $quiz_id,
			'post_title'    => $quiz_title,
			'post_content'  => $quiz_description,

		);
		$quiz_id = wp_update_post( $post_arr );

		ob_start();
		?>
        <div class="tutor-lesson-top">
            <i class="tutor-icon-move"></i>
            <a href="javascript:;" class="open-tutor-quiz-modal" data-quiz-id="<?php echo $quiz_id; ?>" data-topic-id="<?php echo $topic_id;
			?>"> <i class=" tutor-icon-doubt"></i>[QUIZ] <?php echo $quiz_title; ?> </a>
            <a href="javascript:;" class="tutor-delete-quiz-btn" data-quiz-id="<?php echo $quiz_id; ?>"><i class="tutor-icon-garbage"></i></a>
        </div>
		<?php
		$output_quiz_row = ob_get_clean();

		wp_send_json_success(array('output_quiz_row' => $output_quiz_row));
	}

	/**
	 * Load quiz Modal for edit quiz
	 *
	 * @since v.1.0.0
	 */
	public function tutor_load_edit_quiz_modal(){
		$quiz_id           = sanitize_text_field($_POST['quiz_id']);

		ob_start();
		include  tutor()->path.'views/modal/edit_quiz.php';
		$output = ob_get_clean();

		wp_send_json_success(array('output' => $output));
	}

	/**
	 * Load quiz question form for quiz
	 *
	 * @since v.1.0.0
	 */
	public function tutor_quiz_builder_get_question_form(){
		global $wpdb;
		$quiz_id = sanitize_text_field($_POST['quiz_id']);
		$question_id = sanitize_text_field(tutor_utils()->avalue_dot('question_id', $_POST));

		if ( ! $question_id){
			$next_question_id = tutor_utils()->quiz_next_question_id();
			$next_question_order = tutor_utils()->quiz_next_question_order_id($quiz_id);

			$new_question_data = array(
				'quiz_id'               => $quiz_id,
				'question_title'        => __('Question ').$next_question_id,
				'question_description'  => '',
				'question_type'         => 'true_false',
				'question_mark'         => 1,
				'question_settings'     => maybe_serialize(array()),
				'question_order'        => $next_question_order,
			);

			$wpdb->insert($wpdb->prefix.'tutor_quiz_questions', $new_question_data);
			$question_id = $wpdb->insert_id;
		}

		$question = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}tutor_quiz_questions where question_id = {$question_id} ");

		ob_start();
		include  tutor()->path.'views/modal/question_form.php';
		$output = ob_get_clean();

		wp_send_json_success(array('output' => $output));
	}

	public function tutor_quiz_modal_update_question(){
		global $wpdb;

		$question_data = $_POST['tutor_quiz_question'];

		foreach ($question_data as $question_id => $question){
			$question_title         = $question['question_title'];
			$question_description   = $question['question_description'];
			$question_type          = $question['question_type'];
			$question_mark          = $question['question_mark'];

			unset($question['question_title']);
			unset($question['question_description']);

			$data = array(
				'question_title'        => $question_title,
				'question_description'  => $question_description,
				'question_type'         => $question_type,
				'question_mark'         => $question_mark,
				'question_settings'     => maybe_serialize($question),
			);

			$wpdb->update($wpdb->prefix.'tutor_quiz_questions', $data, array('question_id' => $question_id) );
		}

		wp_send_json_success();
	}

	public function tutor_quiz_builder_question_delete(){
		global $wpdb;

		$question_id = sanitize_text_field(tutor_utils()->avalue_dot('question_id', $_POST));
		if ($question_id){
			$wpdb->delete($wpdb->prefix.'tutor_quiz_questions', array('question_id' => $question_id));
		}

		wp_send_json_success();
	}

	/**
	 * Get answers options form for quiz question
	 *
	 * @since v.1.0.0
	 */
	public function tutor_quiz_add_question_answers(){
		$question_id = sanitize_text_field($_POST['question_id']);
		$question = tutor_utils()->avalue_dot($question_id, $_POST['tutor_quiz_question']);
		$question_type = $question['question_type'];

		ob_start();
		include  tutor()->path.'views/modal/question_answer_form.php';
		$output = ob_get_clean();

		wp_send_json_success(array('output' => $output));
	}

	public function tutor_save_quiz_answer_options(){
		global $wpdb;

		$questions = $_POST['tutor_quiz_question'];
		$answers = $_POST['quiz_answer'];

		foreach ($answers as $question_id => $answer){
			$question = tutor_utils()->avalue_dot($question_id, $questions);
			$question_type = $question['question_type'];
			
			//TODO: need to get next sorting order by a query

			if ($question){
				if ($question_type === 'true_false'){
					//$ifAnyPreviousData = $wpdb->get_var("SELECT COUNT(answer_id) FROM {$wpdb->prefix}tutor_quiz_question_answers where belongs_question_id ={$question_id} AND belongs_question_type = '{$question_type}' ");

					$wpdb->delete($wpdb->prefix.'tutor_quiz_question_answers', array('belongs_question_id' => $question_id, 'belongs_question_type' => $question_type));
					$data_true_false = array(
						array(
							'belongs_question_id'   => $question_id,
							'belongs_question_type' => $question_type,
							'answer_title'          => __('True', 'tutor'),
							'is_correct'            => $answer['true_false'] == 'true' ? 1 : 0,
						),
						array(
							'belongs_question_id'   => $question_id,
							'belongs_question_type' => $question_type,
							'answer_title'          => __('False', 'tutor'),
							'is_correct'            => $answer['true_false'] == 'false' ? 1 : 0,
						),
					);

					foreach ($data_true_false as $true_false_data){
						$wpdb->insert($wpdb->prefix.'tutor_quiz_question_answers', $true_false_data);
					}

				}elseif($question_type === 'multiple_choice' || $question_type === 'single_choice' || $question_type === 'ordering' ){

				    //Getting next sorting order
				    $next_order_id = (int) $wpdb->get_var("SELECT MAX(answer_order) FROM {$wpdb->prefix}tutor_quiz_question_answers where belongs_question_id = {$question_id} AND belongs_question_type = '{$question_type}' ");
					$next_order_id = $next_order_id + 1;

					$answer_data = array(
						'belongs_question_id'   => $question_id,
						'belongs_question_type' => $question_type,
						'answer_title'          => $answer['answer_title'],
						'image_id'              => isset($answer['image_id']) ? $answer['image_id'] : 0,
						'answer_view_format'    => isset($answer['answer_view_format']) ? $answer['answer_view_format'] : 0,
						'answer_order'          => $next_order_id,
					);
					if (isset($answer['is_correct_answer'])){
						$answer_data['is_correct'] = $answer['is_correct_answer'];
                    }

					$wpdb->insert($wpdb->prefix.'tutor_quiz_question_answers', $answer_data);

				}elseif($question_type === 'fill_in_the_blank'){
					$wpdb->delete($wpdb->prefix.'tutor_quiz_question_answers', array('belongs_question_id' => $question_id, 'belongs_question_type' => $question_type));
					$answer_data = array(
						'belongs_question_id'   => $question_id,
						'belongs_question_type' => $question_type,
						'answer_title'          => __('Fill In The Gap', 'tutor'),
						'gape_answer'           => isset($answer['gape_answer']) ? strtolower(trim($answer['gape_answer'])) : null,
					);
					$wpdb->insert($wpdb->prefix.'tutor_quiz_question_answers', $answer_data);
				}
			}
		}

		//die(print_r($_POST));
		wp_send_json_success();
	}

	public function tutor_quiz_builder_get_answers_by_question(){
		global $wpdb;
		$question_id = sanitize_text_field($_POST['question_id']);
		$question_type = sanitize_text_field($_POST['question_type']);

		$question = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}tutor_quiz_questions WHERE question_id = {$question_id} ");
		$answers = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}tutor_quiz_question_answers where belongs_question_id = {$question_id} AND belongs_question_type = '{$question_type}' order by answer_order asc ;");

		ob_start();

		switch ($question_type){
			case 'true_false':
				echo '<label>'.__('Answer options &amp; mark correct', 'tutor').'</label>';
				break;
			case 'ordering':
				echo '<label>'.__('Student should order below items exact this order, make sure your answer is in right order, you can re-order them', 'tutor').'</label>';
				break;
		}

		if (is_array($answers) && count($answers)){
			foreach ($answers as $answer){
				?>
                <div class="tutor-quiz-answer-wrap" data-answer-id="<?php echo $answer->answer_id; ?>">
                    <div class="tutor-quiz-answer">
                        <span class="tutor-quiz-answer-title">
                            <?php
                            if ($answer->is_correct){
                                echo '<i class="tutor-icon-mark"></i>';
                            }
                            echo $answer->answer_title; ?>
                        </span>
                        <span class="tutor-quiz-answer-sort-icon"><i class="tutor-icon-menu-2"></i> </span>
                    </div>

                    <div class="tutor-quiz-answer-trash-wrap">
                        <a href="javascript:;" class="answer-trash-btn" data-answer-id="<?php echo $answer->answer_id; ?>"><i class="tutor-icon-garbage"></i> </a>
                    </div>
                </div>
				<?php
			}
		}
		$output = ob_get_clean();

		wp_send_json_success(array('output' => $output));
	}

	public function tutor_quiz_builder_delete_answer(){
		global $wpdb;
		$answer_id = sanitize_text_field($_POST['answer_id']);

		$wpdb->delete($wpdb->prefix.'tutor_quiz_question_answers', array('answer_id' => $answer_id));
		wp_send_json_success();
	}

	/**
	 * Save sorting data for quiz answers
	 */
	public function tutor_quiz_answer_sorting(){
	    global $wpdb;

	    if ( ! empty($_POST['sorted_answer_ids']) && is_array($_POST['sorted_answer_ids']) && count($_POST['sorted_answer_ids']) ){
	        $answer_ids = $_POST['sorted_answer_ids'];
	        $i = 0;
	        foreach ($answer_ids as $key => $answer_id){
	            $i++;
		        $wpdb->update($wpdb->prefix.'tutor_quiz_question_answers', array('answer_order' => $i), array('answer_id' => $answer_id));
            }
        }

    }

	/**
	 * Update quiz settings from modal
	 *
	 * @since : v.1.0.0
	 */
	public function tutor_quiz_modal_update_settings(){
		$quiz_id = sanitize_text_field($_POST['quiz_id']);

		$quiz_option = tutor_utils()->sanitize_array($_POST['quiz_option']);
		update_post_meta($quiz_id, 'tutor_quiz_option', $quiz_option);

		wp_send_json_success();
	}


	//=========================//
    // Front end stuffs
    //=========================//

	/**
	 * Rendering quiz for frontend
     *
     * @since v.1.0.0
	 */

	public function tutor_render_quiz_content(){
		$quiz_id = (int) sanitize_text_field(tutor_utils()->avalue_dot('quiz_id', $_POST));

		ob_start();
		global $post;

		$post = get_post($quiz_id);
		setup_postdata($post);
		//tutor_lesson_content();

		single_quiz_contents();

		wp_reset_postdata();


		$html = ob_get_clean();
		wp_send_json_success(array('html' => $html));
	}


}