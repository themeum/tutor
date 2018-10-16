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
		$quiz_option = tutor_utils()->sanitize_array($_POST['quiz_option']);
		update_post_meta($post_ID, 'tutor_quiz_option', $quiz_option);
	}

}