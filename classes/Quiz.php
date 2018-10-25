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
		add_action( 'add_meta_boxes', array($this, 'register_meta_box') );
		add_action('save_post_tutor_quiz', array($this, 'save_quiz_meta'));

		add_action('wp_ajax_tutor_load_quiz_modal', array($this, 'tutor_load_quiz_modal'));
		add_action('wp_ajax_tutor_add_quiz_to_post', array($this, 'tutor_add_quiz_to_post'));
		add_action('wp_ajax_remove_quiz_from_post', array($this, 'remove_quiz_from_post'));


		//User take the quiz
		add_action('template_redirect', array($this, 'start_the_quiz'));
		add_action('template_redirect', array($this, 'answering_quiz'));
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

		wp_send_json_success(array('output' => $output));
	}

	public function tutor_add_quiz_to_post(){
		global $wpdb;

		$quiz_data = tutor_utils()->avalue_dot('quiz_for', $_POST);

		$output = '';
		$post_id = 0;
		if ($quiz_data){
			foreach ($quiz_data as $post_id => $quiz_ids_a);

			$quiz_ids = tutor_utils()->avalue_dot('quiz_id', $quiz_ids_a);
			foreach ($quiz_ids as $quiz_id){
				$wpdb->update($wpdb->posts, array('post_parent' => $post_id), array('ID' => $quiz_id) );
			}
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
		if ( ! is_user_logged_in()){
		    //TODO: need to set a view in the next version
		    die('Please sign in to do this operation');
        }

		if ( ! isset($_POST['tutor_action'])  ||  $_POST['tutor_action'] !== 'tutor_start_quiz' ){
			return;
		}
		//Checking nonce
		tutor_utils()->checking_nonce();

		global $wpdb;

		$user_id = get_current_user_id();
		$user = get_userdata($user_id);


		$quiz_id = (int) sanitize_text_field($_POST['quiz_id']);
		$quiz = get_post($quiz_id);
		$date = date("Y-m-d H:i:s");

		$attempts_allowed = tutor_utils()->get_quiz_option($quiz_id, 'attempts_allowed', 0);

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

		$quiz_attempt_info = array(
            'time_limit'            => $time_limit,
            'time_type'             => $time_type,
            'time_limit_seconds'    => $time_limit_seconds,
            'total_question'        => 0,
            'answered_question'     => 0,
            'current_question'      => 0,
            'marks_earned'          => 0,
            'answers'               => array(),
        );

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
        */

		update_comment_meta($attempt_id, 'quiz_attempt_info', $quiz_attempt_info);
        update_comment_meta($attempt_id, 'grade', 'N/A');

		do_action('tutor_after_start_quiz', $quiz_id, $attempt_id);


		wp_redirect(tutor_utils()->input_old('_wp_http_referer'));
		die();
    }


    public function answering_quiz(){
	    if ( ! is_user_logged_in()){
		    die('Please sign in to do this operation');
	    }

	    if ( ! isset($_POST['tutor_action'])  ||  $_POST['tutor_action'] !== 'tutor_answering_quiz_question' ){
		    return;
	    }
	    //Checking nonce
	    tutor_utils()->checking_nonce();

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

	    if ($given_answers){
		    $question_type = get_post_meta($post_question_id, '_question_type', true);
		    $question_mark = get_post_meta($post_question_id, '_question_mark', true);

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

		    // echo '<pre>';
		    // print_r($corrects_answer_ids);
		    // die(print_r($saved_answers));
		    // die(var_dump($question_mark));


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
            }else{
		        //TODO: Do operation for incorrect answer
            }

		    $answers = array(
			    'questionID' => $post_question_id, 'status' => 'answered', 'has_correct' => 0, //or 0 for false, 'questionSiNo' => 2
                'plus_mark' => $plus_mark,
                'minus_mark' => $minus_mark,
                'answers_list' => array(
				    'answer_type' => $question_type,
				    'answer_ids' => $given_answers
			    )
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

        if ($is_answer_corrected){
	        if (isset($attempt_info['marks_earned'])){
	            //If not found
		        $attempt_info['marks_earned'] = $attempt_info['marks_earned'] + $plus_mark;
            }else{
		        $attempt_info['marks_earned'] = $plus_mark;
	        }


        }else{
            //Todo: mark minus if necessary
        }

	    $attempt_info['answers'][] = $answers;


	    echo '<pre>';
        //print_r($_POST);
        print_r($attempt_info);


	    die();

	    wp_redirect(tutor_utils()->input_old('_wp_http_referer'));
	    die();
    }

}