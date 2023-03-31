<?php
/**
 * Quiz class
 *
 * @package Tutor\QuestionAnswer
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Tutor\Helpers\QueryHelper;
use Tutor\Models\CourseModel;
use Tutor\Models\QuizModel;
/**
 * Manage quiz operations.
 *
 * @since 1.0.0
 */
class Quiz {

	/**
	 * Allowed attrs
	 *
	 * @var array
	 */
	private $allowed_attributes = array(
		'src'      => array(),
		'style'    => array(),
		'class'    => array(),
		'id'       => array(),
		'href'     => array(),
		'alt'      => array(),
		'title'    => array(),
		'type'     => array(),
		'controls' => array(),
		'muted'    => array(),
		'loop'     => array(),
		'poster'   => array(),
		'preload'  => array(),
		'autoplay' => array(),
		'width'    => array(),
		'height'   => array(),
	);

	/**
	 * Allowed HTML tags
	 *
	 * @var array
	 */
	private $allowed_html = array( 'img', 'b', 'i', 'br', 'a', 'audio', 'video', 'source' );

	/**
	 * Register hooks
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'save_post_tutor_quiz', array( $this, 'save_quiz_meta' ) );
		add_action( 'wp_ajax_remove_quiz_from_post', array( $this, 'remove_quiz_from_post' ) );

		add_action( 'wp_ajax_tutor_quiz_timeout', array( $this, 'tutor_quiz_timeout' ) );

		// User take the quiz.
		add_action( 'template_redirect', array( $this, 'start_the_quiz' ) );
		add_action( 'template_redirect', array( $this, 'answering_quiz' ) );
		add_action( 'template_redirect', array( $this, 'finishing_quiz_attempt' ) );

		add_action( 'wp_ajax_review_quiz_answer', array( $this, 'review_quiz_answer' ) );
		// Instructor Feedback Action.
		add_action( 'wp_ajax_tutor_instructor_feedback', array( $this, 'tutor_instructor_feedback' ) );

		/**
		 * New Design Quiz
		 */

		add_action( 'wp_ajax_tutor_quiz_save', array( $this, 'tutor_quiz_save' ) );
		add_action( 'wp_ajax_tutor_delete_quiz_by_id', array( $this, 'tutor_delete_quiz_by_id' ) );
		add_action( 'wp_ajax_tutor_load_quiz_builder_modal', array( $this, 'tutor_load_quiz_builder_modal' ), 10, 0 );
		add_action( 'wp_ajax_tutor_quiz_builder_get_question_form', array( $this, 'tutor_quiz_builder_get_question_form' ) );
		add_action( 'wp_ajax_tutor_quiz_modal_update_question', array( $this, 'tutor_quiz_modal_update_question' ) );
		add_action( 'wp_ajax_tutor_quiz_builder_question_delete', array( $this, 'tutor_quiz_builder_question_delete' ) );
		add_action( 'wp_ajax_tutor_quiz_question_answer_editor', array( $this, 'tutor_quiz_question_answer_editor' ) );
		add_action( 'wp_ajax_tutor_save_quiz_answer_options', array( $this, 'tutor_save_quiz_answer_options' ), 10, 0 );
		add_action( 'wp_ajax_tutor_update_quiz_answer_options', array( $this, 'tutor_update_quiz_answer_options' ) );
		add_action( 'wp_ajax_tutor_quiz_builder_change_type', array( $this, 'tutor_quiz_builder_change_type' ) );
		add_action( 'wp_ajax_tutor_quiz_builder_delete_answer', array( $this, 'tutor_quiz_builder_delete_answer' ) );
		add_action( 'wp_ajax_tutor_quiz_question_sorting', array( $this, 'tutor_quiz_question_sorting' ) );
		add_action( 'wp_ajax_tutor_quiz_answer_sorting', array( $this, 'tutor_quiz_answer_sorting' ) );
		add_action( 'wp_ajax_tutor_mark_answer_as_correct', array( $this, 'tutor_mark_answer_as_correct' ) );

		/**
		 * Frontend Stuff
		 */
		add_action( 'wp_ajax_tutor_render_quiz_content', array( $this, 'tutor_render_quiz_content' ) );

		/**
		 * Quiz abandon action
		 *
		 * @since 1.9.6
		 */
		add_action( 'wp_ajax_tutor_quiz_abandon', array( $this, 'tutor_quiz_abandon' ) );

		$this->prepare_allowed_html();

		/**
		 * Delete quiz attempt
		 *
		 * @since 2.1.0
		 */
		add_action( 'wp_ajax_tutor_attempt_delete', array( $this, 'attempt_delete' ) );
	}

	/**
	 * Prepare allowed HTML
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function prepare_allowed_html() {

		$allowed = array();

		foreach ( $this->allowed_html as $tag ) {
			$allowed[ $tag ] = $this->allowed_attributes;
		}

		$this->allowed_html = $allowed;
	}

	/**
	 * Instructor feedback ajax request handler
	 *
	 * @since 1.0.0
	 *
	 * @return void | send json response
	 */
	public function tutor_instructor_feedback() {
		tutor_utils()->checking_nonce();

		// Check if user is privileged.
		if ( ! current_user_can( 'administrator' ) || ! current_user_can( tutor()->instructor_role ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$attempt_details = self::attempt_details( Input::post( 'attempt_id', 0, Input::TYPE_INT ) );
		$feedback        = Input::post( 'feedback', '', Input::TYPE_KSES_POST );
		$attempt_info    = isset( $attempt_details->attempt_info ) ? $attempt_details->attempt_info : false;
		if ( $attempt_info ) {
			//phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
			$unserialized = unserialize( $attempt_details->attempt_info );
			if ( is_array( $unserialized ) ) {
				$unserialized['instructor_feedback'] = $feedback;

				do_action( 'tutor_quiz/attempt/submitted/feedback', $attempt_details->attempt_id );
				//phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
				$update = self::update_attempt_info( $attempt_details->attempt_id, serialize( $unserialized ) );
				if ( $update ) {
					wp_send_json_success();
				} else {
					wp_send_json_error();
				}
			} else {
				wp_send_json_error( __( 'Invalid quiz info' ) );
			}
		}
		wp_send_json_error();
	}

	/**
	 * Update quiz meta
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_ID post id.
	 * @return void
	 */
	public function save_quiz_meta( $post_ID ) {
		//phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( isset( $_POST['quiz_option'] ) ) {
			$quiz_option = tutor_utils()->sanitize_array( $_POST['quiz_option'] ); //phpcs:ignore
			update_post_meta( $post_ID, 'tutor_quiz_option', $quiz_option );
		}
	}

	/**
	 * Remove quiz from post
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function remove_quiz_from_post() {
		tutor_utils()->checking_nonce();

		global $wpdb;
		$quiz_id = Input::post( 'quiz_id', 0, Input::TYPE_INT );

		if ( ! tutor_utils()->can_user_manage( 'quiz', $quiz_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied', 'tutor' ) ) );
		}

		$wpdb->update( $wpdb->posts, array( 'post_parent' => 0 ), array( 'ID' => $quiz_id ) );
		wp_send_json_success();
	}

	/**
	 * Start Quiz from here...
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function start_the_quiz() {
		if ( Input::post( 'tutor_action' ) !== 'tutor_start_quiz' ) {
			return;
		}
		// Checking nonce.
		tutor_utils()->checking_nonce();

		if ( ! is_user_logged_in() ) {
			// TODO: need to set a view in the next version.
			die( 'Please sign in to do this operation' );
		}

		global $wpdb;

		$user_id = get_current_user_id();
		$user    = get_userdata( $user_id );

		$quiz_id = Input::post( 'quiz_id', 0, Input::TYPE_INT );

		$quiz   = get_post( $quiz_id );
		$course = CourseModel::get_course_by_quiz( $quiz_id );
		if ( empty( $course->ID ) ) {
			die( 'There is something went wrong with course, please check if quiz attached with a course' );
		}

		do_action( 'tutor_quiz/start/before', $quiz_id, $user_id );

		$date = date( 'Y-m-d H:i:s', tutor_time() );

		$tutor_quiz_option = (array) maybe_unserialize( get_post_meta( $quiz_id, 'tutor_quiz_option', true ) );
		$attempts_allowed  = tutor_utils()->get_quiz_option( $quiz_id, 'attempts_allowed', 0 );

		$time_limit         = tutor_utils()->get_quiz_option( $quiz_id, 'time_limit.time_value' );
		$time_limit_seconds = 0;
		$time_type          = 'seconds';
		if ( $time_limit ) {
			$time_type = tutor_utils()->get_quiz_option( $quiz_id, 'time_limit.time_type' );

			switch ( $time_type ) {
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

		$max_question_allowed                                  = tutor_utils()->max_questions_for_take_quiz( $quiz_id );
		$tutor_quiz_option['time_limit']['time_limit_seconds'] = $time_limit_seconds;

		$attempt_data = array(
			'course_id'                => $course->ID,
			'quiz_id'                  => $quiz_id,
			'user_id'                  => $user_id,
			'total_questions'          => $max_question_allowed,
			'total_answered_questions' => 0,
			'attempt_info'             => maybe_serialize( $tutor_quiz_option ),
			'attempt_status'           => 'attempt_started',
			'attempt_ip'               => tutor_utils()->get_ip(),
			'attempt_started_at'       => $date,
		);

		$wpdb->insert( $wpdb->prefix . 'tutor_quiz_attempts', $attempt_data );
		$attempt_id = (int) $wpdb->insert_id;

		do_action( 'tutor_quiz/start/after', $quiz_id, $user_id, $attempt_id );

		wp_safe_redirect( get_permalink( $quiz_id ) );
		die();
	}

	/**
	 * Answering quiz
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function answering_quiz() {

		if ( Input::post( 'tutor_action' ) !== 'tutor_answering_quiz_question' ) {
			return;
		}
		// submit quiz attempts.
		self::tutor_quiz_attemp_submit();

		wp_safe_redirect( get_the_permalink() );
		die();
	}

	/**
	 * Quiz abandon submission handler
	 *
	 * @since 1.9.6
	 *
	 * @return JSON response
	 */
	public function tutor_quiz_abandon() {
		if ( Input::post( 'tutor_action' ) !== 'tutor_answering_quiz_question' ) {
			return;
		}
		// submit quiz attempts.
		if ( self::tutor_quiz_attemp_submit() ) {
			wp_send_json_success();
		} else {
			wp_send_json_error();
		}
	}

	/**
	 * This is  a unified method for handling normal quiz submit or abandon submit
	 * It will handle ajax or normal form submit and can be used with different hooks
	 *
	 * @since 1.9.6
	 *
	 * @return true | false
	 */
	public static function tutor_quiz_attemp_submit() {
		// Check logged in.
		if ( ! is_user_logged_in() ) {
			die( 'Please sign in to do this operation' );
		}

		// Check nonce.
		tutor_utils()->checking_nonce();

		// Prepare attempt info.
		global $wpdb;
		$user_id    = get_current_user_id();
		$attempt_id = Input::post( 'attempt_id', 0, Input::TYPE_INT );
		$attempt    = tutor_utils()->get_attempt( $attempt_id );
		$course_id  = CourseModel::get_course_by_quiz( $attempt->quiz_id )->ID;

		// Sanitize data by helper method.
		$attempt_answers = isset( $_POST['attempt'] ) ? tutor_sanitize_data( $_POST['attempt'] ) : false; //phpcs:ignore
		$attempt_answers = is_array( $attempt_answers ) ? $attempt_answers : array();

		// Check if has access to the attempt.
		if ( ! $attempt || $user_id != $attempt->user_id ) {
			die( 'Operation not allowed, attempt not found or permission denied' );
		}

		// Before ook.
		do_action( 'tutor_quiz/attempt_analysing/before', $attempt_id );

		// Loop through every single attempt answer
		// Single quiz can have multiple question. So multiple answer should be saved.
		foreach ( $attempt_answers as $attempt_id => $attempt_answer ) {

			/**
			 * Get total marks of all question comes
			 */
			$question_ids = tutor_utils()->avalue_dot( 'quiz_question_ids', $attempt_answer );
			$question_ids = array_filter(
				$question_ids,
				function( $id ) {
					return (int) $id;
				}
			);

			// Calculate and set the total marks in attempt table for this question.
			if ( is_array( $question_ids ) && count( $question_ids ) ) {
				$question_ids_string = QueryHelper::prepare_in_clause( $question_ids );

				// Get total marks of the questions from question table.
				$total_question_marks = $wpdb->get_var(
					"SELECT SUM(question_mark)
						FROM {$wpdb->prefix}tutor_quiz_questions
						WHERE question_id IN({$question_ids_string});
					"
				);

				// Set the the total mark in the attempt table for the question.
				$wpdb->update(
					$wpdb->prefix . 'tutor_quiz_attempts',
					array( 'total_marks' => $total_question_marks ),
					array( 'attempt_id' => $attempt_id )
				);
			}

			$total_marks     = 0;
			$review_required = false;
			$quiz_answers    = tutor_utils()->avalue_dot( 'quiz_question', $attempt_answer );

			if ( tutor_utils()->count( $quiz_answers ) ) {

				foreach ( $quiz_answers as $question_id => $answers ) {
					$question      = QuizModel::get_quiz_question_by_id( $question_id );
					$question_type = $question->question_type;

					$is_answer_was_correct = false;
					$given_answer          = '';

					if ( 'true_false' === $question_type || 'single_choice' === $question_type ) {

						if ( ! is_numeric( $answers ) || ! $answers ) {
							wp_send_json_error();
							exit;
						}

						$given_answer          = $answers;
						$is_answer_was_correct = (bool) $wpdb->get_var(
							$wpdb->prepare(
								"SELECT is_correct
									FROM {$wpdb->prefix}tutor_quiz_question_answers
									WHERE answer_id = %d
								",
								$answers
							)
						);

					} elseif ( 'multiple_choice' === $question_type ) {

						$given_answer = (array) ( $answers );

						$given_answer         = array_filter(
							$given_answer,
							function( $id ) {
								return is_numeric( $id ) && $id > 0;
							}
						);
						$get_original_answers = (array) $wpdb->get_col(
							$wpdb->prepare(
								"SELECT
								answer_id
							FROM
								{$wpdb->prefix}tutor_quiz_question_answers
							WHERE belongs_question_id = %d
								AND belongs_question_type = %s
								AND is_correct = 1 ;
							",
								$question->question_id,
								$question_type
							)
						);

						if ( count( array_diff( $get_original_answers, $given_answer ) ) === 0 && count( $get_original_answers ) === count( $given_answer ) ) {
							$is_answer_was_correct = true;
						}
						$given_answer = maybe_serialize( $answers );

					} elseif ( 'fill_in_the_blank' === $question_type ) {

						$get_original_answer = $wpdb->get_row(
							$wpdb->prepare(
								"SELECT * 
									FROM {$wpdb->prefix}tutor_quiz_question_answers
									WHERE belongs_question_id = %d
										AND belongs_question_type = %s ;
								",
								$question->question_id,
								$question_type
							)
						);

						/**
						 * Answers stored in DB
						 */
						$gap_answer = (array) explode( '|', $get_original_answer->answer_two_gap_match );
						$gap_answer = maybe_serialize( array_map( function ( $ans) {
							return wp_slash( trim( $ans ) );
						}, $gap_answer ) );

						/**
						 * Answers from user input
						 */
						$given_answer = (array) array_map( 'sanitize_text_field', $answers );
						$given_answer = maybe_serialize( $given_answer );
						
						/**
						 * Compare answer's by making both case-insensitive.
						 */
						if ( strtolower( $given_answer ) == strtolower( $gap_answer ) ) {
							$is_answer_was_correct = true;
						}
					} elseif ( 'open_ended' === $question_type || 'short_answer' === $question_type ) {
						$review_required = true;
						$given_answer    = wp_kses_post( $answers );

					} elseif ( 'ordering' === $question_type || 'matching' === $question_type || 'image_matching' === $question_type ) {

						$given_answer = (array) array_map( 'sanitize_text_field', tutor_utils()->avalue_dot( 'answers', $answers ) );
						$given_answer = maybe_serialize( $given_answer );

						$get_original_answers = (array) $wpdb->get_col(
							$wpdb->prepare(
								"SELECT answer_id
									FROM {$wpdb->prefix}tutor_quiz_question_answers
									WHERE belongs_question_id = %d 
										AND belongs_question_type = %s 
									ORDER BY answer_order ASC ;
								",
								$question->question_id,
								$question_type
							)
						);

						$get_original_answers = array_map( 'sanitize_text_field', $get_original_answers );

						if ( maybe_serialize( $get_original_answers ) == $given_answer ) {
							$is_answer_was_correct = true;
						}
					} elseif ( 'image_answering' === $question_type ) {
						$image_inputs          = tutor_utils()->avalue_dot( 'answer_id', $answers );
						$image_inputs          = (array) array_map( 'sanitize_text_field', $image_inputs );
						$given_answer          = maybe_serialize( $image_inputs );
						$is_answer_was_correct = false;
						/**
						 * For the image_answering question type result
						 * remain pending in spite of correct answer & required
						 * review of admin/instructor. Since it's
						 * pending we need to mark it as incorrect. Otherwise if
						 * mark it correct then earned mark will be updated. then
						 * again when instructor/admin review & mark it as correct
						 * extra mark is adding. In this case, student
						 * getting double mark for the same question.
						 *
						 * For now code is commenting will be removed later on
						 *
						 * @since 2.1.5
						 */

						// $db_answer = $wpdb->get_col(
						// 	$wpdb->prepare(
						// 		"SELECT answer_title
						// 			FROM {$wpdb->prefix}tutor_quiz_question_answers
						// 			WHERE belongs_question_id = %d
						// 				AND belongs_question_type = 'image_answering'
						// 			ORDER BY answer_order asc ;",
						// 		$question_id
						// 	)
						// );

						// if ( is_array( $db_answer ) && count( $db_answer ) ) {
						// 	$is_answer_was_correct = ( strtolower( maybe_serialize( array_values( $image_inputs ) ) ) == strtolower( maybe_serialize( $db_answer ) ) );
						// }
					}

					$question_mark = $is_answer_was_correct ? $question->question_mark : 0;
					$total_marks  += $question_mark;

					$answers_data = array(
						'user_id'         => $user_id,
						'quiz_id'         => $attempt->quiz_id,
						'question_id'     => $question_id,
						'quiz_attempt_id' => $attempt_id,
						'given_answer'    => $given_answer,
						'question_mark'   => $question->question_mark,
						'achieved_mark'   => $question_mark,
						'minus_mark'      => 0,
						'is_correct'      => $is_answer_was_correct ? 1 : 0,
					);

					/**
					 * Check if question_type open ended or short ans the set
					 * is_correct default value null before saving
					 */
					if ( in_array( $question_type, array( 'open_ended', 'short_answer', 'image_answering' ) ) ) {
						$answers_data['is_correct'] = null;
						$review_required            = true;
					}

					$wpdb->insert( $wpdb->prefix . 'tutor_quiz_attempt_answers', $answers_data );
				}
			}

			$attempt_info = array(
				'total_answered_questions' => tutor_utils()->count( $quiz_answers ),
				'earned_marks'             => $total_marks,
				'attempt_status'           => 'attempt_ended',
				'attempt_ended_at'         => date( 'Y-m-d H:i:s', tutor_time() ),
			);

			if ( $review_required ) {
				$attempt_info['attempt_status'] = 'review_required';
			}

			$wpdb->update( $wpdb->prefix . 'tutor_quiz_attempts', $attempt_info, array( 'attempt_id' => $attempt_id ) );
		}

		// After hook.
		do_action( 'tutor_quiz/attempt_ended', $attempt_id, $course_id, $user_id );

		return true;
	}


	/**
	 * Quiz attempt will be finish here
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function finishing_quiz_attempt() {

		if ( Input::post( 'tutor_action' ) !== 'tutor_finish_quiz_attempt' ) {
			return;
		}
		// Checking nonce.
		tutor_utils()->checking_nonce();

		if ( ! is_user_logged_in() ) {
			die( 'Please sign in to do this operation' );
		}

		global $wpdb;

		$quiz_id    = Input::post( 'quiz_id', 0, Input::TYPE_INT );
		$attempt    = tutor_utils()->is_started_quiz( $quiz_id );
		$attempt_id = $attempt->attempt_id;

		$attempt_info = array(
			'total_answered_questions' => 0,
			'earned_marks'             => 0,
			'attempt_status'           => 'attempt_ended',
			'attempt_ended_at'         => date( 'Y-m-d H:i:s', tutor_time() ),
		);

		do_action( 'tutor_quiz_before_finish', $attempt_id, $quiz_id, $attempt->user_id );
		$wpdb->update( $wpdb->prefix . 'tutor_quiz_attempts', $attempt_info, array( 'attempt_id' => $attempt_id ) );
		do_action( 'tutor_quiz_finished', $attempt_id, $quiz_id, $attempt->user_id );

		wp_redirect( tutor_utils()->input_old( '_wp_http_referer' ) );
	}

	/**
	 * Quiz timeout by ajax
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function tutor_quiz_timeout() {
		tutils()->checking_nonce();

		global $wpdb;

		$quiz_id = Input::post( 'quiz_id', 0, Input::TYPE_INT );
		$attempt = tutor_utils()->is_started_quiz( $quiz_id );

		if ( $attempt ) {
			$attempt_id = $attempt->attempt_id;

			$data = array(
				'attempt_status'   => 'attempt_timeout',
				'attempt_ended_at' => date( 'Y-m-d H:i:s', tutor_time() ),
			);
			$wpdb->update( $wpdb->prefix . 'tutor_quiz_attempts', $data, array( 'attempt_id' => $attempt->attempt_id ) );

			do_action( 'tutor_quiz_timeout', $attempt_id, $quiz_id, $attempt->user_id );

			wp_send_json_success();
		}

		wp_send_json_error( __( 'Quiz has been timeout already', 'tutor' ) );
	}

	/**
	 * Review quiz answer
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function review_quiz_answer() {

		tutor_utils()->checking_nonce();

		global $wpdb;

		$attempt_id        = Input::post( 'attempt_id', 0, Input::TYPE_INT );
		$context           = Input::post( 'context' );
		$attempt_answer_id = Input::post( 'attempt_answer_id', 0, Input::TYPE_INT );
		$mark_as           = Input::post( 'mark_as' );

		if ( ! tutor_utils()->can_user_manage( 'attempt', $attempt_id ) || ! tutor_utils()->can_user_manage( 'attempt_answer', $attempt_answer_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied', 'tutor' ) ) );
		}

		$attempt_answer = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * 
					FROM {$wpdb->prefix}tutor_quiz_attempt_answers
					WHERE attempt_answer_id = %d
				",
				$attempt_answer_id
			)
		);

		$attempt      = tutor_utils()->get_attempt( $attempt_id );
		$question     = QuizModel::get_quiz_question_by_id( $attempt_answer->question_id );
		$course_id    = $attempt->course_id;
		$student_id   = $attempt->user_id;
		$previous_ans = $attempt_answer->is_correct;

		do_action( 'tutor_quiz_review_answer_before', $attempt_answer_id, $attempt_id, $mark_as );

		if ( 'correct' === $mark_as ) {

			$answer_update_data = array(
				'achieved_mark' => $attempt_answer->question_mark,
				'is_correct'    => 1,
			);
			$wpdb->update( $wpdb->prefix . 'tutor_quiz_attempt_answers', $answer_update_data, array( 'attempt_answer_id' => $attempt_answer_id ) );
			if ( 0 == $previous_ans || null == $previous_ans ) {
				// if previous answer was wrong or in review then add point as correct.
				$attempt_update_data = array(
					'earned_marks'         => $attempt->earned_marks + $attempt_answer->question_mark,
					'is_manually_reviewed' => 1,
					'manually_reviewed_at' => date( 'Y-m-d H:i:s', tutor_time() ),
				);
			}

			if ( 'open_ended' === $question->question_type || 'short_answer' === $question->question_type ) {
				$attempt_update_data['attempt_status'] = 'attempt_ended';
			}
			$wpdb->update( $wpdb->prefix . 'tutor_quiz_attempts', $attempt_update_data, array( 'attempt_id' => $attempt_id ) );

		} elseif ( 'incorrect' === $mark_as ) {

			$answer_update_data = array(
				'achieved_mark' => '0.00',
				'is_correct'    => 0,
			);
			$wpdb->update( $wpdb->prefix . 'tutor_quiz_attempt_answers', $answer_update_data, array( 'attempt_answer_id' => $attempt_answer_id ) );

			if ( 1 == $previous_ans ) {
				// If previous ans was right then mynus.
				$attempt_update_data = array(
					'earned_marks'         => $attempt->earned_marks - $attempt_answer->question_mark,
					'is_manually_reviewed' => 1,
					'manually_reviewed_at' => date( 'Y-m-d H:i:s', tutor_time() ),
				);
			}
			if ( 'open_ended' === $question->question_type || 'short_answer' === $question->question_type ) {
				$attempt_update_data['attempt_status'] = 'attempt_ended';
			}

			$wpdb->update( $wpdb->prefix . 'tutor_quiz_attempts', $attempt_update_data, array( 'attempt_id' => $attempt_id ) );
		}
		do_action( 'tutor_quiz_review_answer_after', $attempt_answer_id, $attempt_id, $mark_as );
		do_action( 'tutor_quiz/answer/review/after', $attempt_answer_id, $course_id, $student_id );

		ob_start();
		tutor_load_template_from_custom_path(
			tutor()->path . '/views/quiz/attempt-details.php',
			array(
				'attempt_id' => $attempt_id,
				'user_id'    => $student_id,
				'context'    => $context,
				'back_url'   => Input::post( 'back_url' ),
			)
		);
		wp_send_json_success( array( 'html' => ob_get_clean() ) );
	}

	/**
	 * Save single quiz into database and send html response
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function tutor_quiz_save() {
		tutor_utils()->checking_nonce();
		// Prepare args.
		$topic_id         = Input::post( 'topic_id', 0, Input::TYPE_INT );
		$ex_quiz_id       = Input::post( 'quiz_id', 0, Input::TYPE_INT );
		$quiz_title       = Input::post( 'quiz_title' );
		$quiz_description = isset( $_POST['quiz_description'] ) ? wp_kses( wp_unslash( $_POST['quiz_description'] ), $this->allowed_html ) : ''; //phpcs:ignore

		$next_order_id = tutor_utils()->get_next_course_content_order_id( $topic_id, $ex_quiz_id );

		// Check edit privilege.
		if ( ! tutor_utils()->can_user_manage( 'topic', $topic_id ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Access Denied', 'tutor' ),
					'data'    => array(),
				)
			);
		}

		// Prepare quiz data to save in database.
		$post_arr = array(
			'ID'           => $ex_quiz_id,
			'post_type'    => 'tutor_quiz',
			'post_title'   => $quiz_title,
			'post_content' => $quiz_description,
			'post_status'  => 'publish',
			'post_author'  => get_current_user_id(),
			'post_parent'  => $topic_id,
			'menu_order'   => $next_order_id,
		);

		// Insert quiz and run hook.
		$quiz_id = wp_insert_post( $post_arr );
		do_action( ( $ex_quiz_id ? 'tutor_quiz_updated' : 'tutor_initial_quiz_created' ), $quiz_id );

		// Sanitize by helper method & save quiz settings.
		$quiz_option = tutor_utils()->sanitize_array( $_POST['quiz_option'] ); //phpcs:ignore
		update_post_meta( $quiz_id, 'tutor_quiz_option', $quiz_option );
		do_action( 'tutor_quiz_settings_updated', $quiz_id );

		// Generate quiz modal to show in modal.
		$output = $this->tutor_load_quiz_builder_modal(
			array(
				'topic_id' => $topic_id,
				'quiz_id'  => $quiz_id,
			),
			true
		);

		// Generate quiz list to show under topic as sub list.
		ob_start();
		tutor_load_template_from_custom_path(
			tutor()->path . '/views/fragments/quiz-list-single.php',
			array(
				'quiz_id'    => $quiz_id,
				'topic_id'   => $topic_id,
				'quiz_title' => $quiz_title,
			),
			false
		);
		$output_quiz_row = ob_get_clean();

		wp_send_json_success(
			array(
				'output'          => $output,
				'output_quiz_row' => $output_quiz_row,
			)
		);
	}

	/**
	 * Delete quiz by id
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function tutor_delete_quiz_by_id() {
		tutor_utils()->checking_nonce();

		global $wpdb;

		$quiz_id = Input::post( 'quiz_id', 0, Input::TYPE_INT );
		$post    = get_post( $quiz_id );

		if ( ! tutils()->can_user_manage( 'quiz', $quiz_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied', 'tutor' ) ) );
		}

		if ( 'tutor_quiz' === $post->post_type ) {
			do_action( 'tutor_delete_quiz_before', $quiz_id );

			$wpdb->delete( $wpdb->prefix . 'tutor_quiz_attempts', array( 'quiz_id' => $quiz_id ) );
			$wpdb->delete( $wpdb->prefix . 'tutor_quiz_attempt_answers', array( 'quiz_id' => $quiz_id ) );

			$questions_ids = $wpdb->get_col( $wpdb->prepare( "SELECT question_id FROM {$wpdb->prefix}tutor_quiz_questions WHERE quiz_id = %d ", $quiz_id ) );

			if ( is_array( $questions_ids ) && count( $questions_ids ) ) {
				$in_question_ids = QueryHelper::prepare_in_clause( $questions_ids );
				$wpdb->query(
					"DELETE 
						FROM {$wpdb->prefix}tutor_quiz_question_answers
						WHERE belongs_question_id IN({$in_question_ids})
					"
				);
			}

			$wpdb->delete( $wpdb->prefix . 'tutor_quiz_questions', array( 'quiz_id' => $quiz_id ) );

			wp_delete_post( $quiz_id, true );

			do_action( 'tutor_delete_quiz_after', $quiz_id );

			wp_send_json_success();
		}

		wp_send_json_error();
	}

	/**
	 * Load quiz Modal on add/edit click
	 *
	 * @since 1.0.0
	 *
	 * @param array   $params params.
	 * @param boolean $return should return or not.
	 *
	 * @return mixed
	 */
	public function tutor_load_quiz_builder_modal( $params = array(), $return = false ) {
		tutor_utils()->checking_nonce();

		//phpcs:ignore WordPress.Security.NonceVerification.Missing
		$data      = array_merge( $_POST, $params );
		$quiz_id   = isset( $data['quiz_id'] ) ? sanitize_text_field( $data['quiz_id'] ) : 0;
		$topic_id  = isset( $data['topic_id'] ) ? sanitize_text_field( $data['topic_id'] ) : 0;
		$quiz      = $quiz_id ? get_post( $quiz_id ) : null;
		$course_id = Input::post( 'course_id', 0, Input::TYPE_INT );

		if ( $quiz_id && ! tutor_utils()->can_user_manage( 'quiz', $quiz_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Quiz Permission Denied', 'tutor' ) ) );
		}

		ob_start();
		include tutor()->path . 'views/modal/edit_quiz.php';
		$output = ob_get_clean();

		if ( $return ) {
			return $output;
		}

		wp_send_json_success( array( 'output' => $output ) );
	}

	/**
	 * Load quiz question form for quiz
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function tutor_quiz_builder_get_question_form() {
		tutor_utils()->checking_nonce();

		global $wpdb;
		$quiz_id     = Input::post( 'quiz_id', 0, Input::TYPE_INT );
		$topic_id    = Input::post( 'topic_id', 0, Input::TYPE_INT );
		$question_id = Input::post( 'question_id', 0, Input::TYPE_INT );

		// check if the user can manage the quiz.
		if ( ! tutor_utils()->can_user_manage( 'quiz', $quiz_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied', 'tutor' ) ) );
		}

		// If question ID not provided, then create new before rendering the form.
		if ( ! $question_id ) {
			$next_question_id    = QuizModel::quiz_next_question_id();
			$next_question_order = QuizModel::quiz_next_question_order_id( $quiz_id );
			$question_title      = __( 'Question', 'tutor' ) . ' ' . $next_question_id;

			$new_question_data = array(
				'quiz_id'              => $quiz_id,
				'question_title'       => $question_title,
				'question_description' => '',
				'question_type'        => 'true_false',
				'question_mark'        => 1,
				'question_settings'    => maybe_serialize( array() ),
				'question_order'       => esc_sql( $next_question_order ),
			);

			$wpdb->insert( $wpdb->prefix . 'tutor_quiz_questions', $new_question_data );
			$question_id = $wpdb->insert_id;

			// Add default true/false options for this question since it is by default true/false type.
			$question_array = array(
				$question_id => array(
					'Question'             => $question_title,
					'question_type'        => 'true_false',
					'question_mark'        => '1.00',
					'question_description' => '',
				),
			);

			$answer_array = array(
				$question_id => array(
					'true_false' => true,
				),
			);

			$this->tutor_save_quiz_answer_options( $question_array, $answer_array, false );
		}

		// Now get all data by this question id.
		$question = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}tutor_quiz_questions
			WHERE question_id = %d ",
				$question_id
			)
		);

		// Render the question form finally.
		ob_start();
		require tutor()->path . 'views/modal/question_form.php';
		$output = ob_get_clean();

		wp_send_json_success( array( 'output' => $output ) );
	}

	/**
	 * Update quiz modal
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function tutor_quiz_modal_update_question() {
		tutor_utils()->checking_nonce();

		global $wpdb;
		// Sanitize $_POST below before using.
		$quiz_question_id = Input::post( 'tutor_quiz_question_id', 0, Input::TYPE_INT );
		if ( ! $quiz_question_id ) {
			wp_send_json_error( __( 'Invalid quiz question ID', 'tutor' ) );
		}

		/**
		 * Sanitize $_POST[tutor_quiz_question] data through array_walk
		 * it will override & sanitize all the question data.
		 *
		 * @since 2.1.3
		 */
		if ( isset( $_POST['tutor_quiz_question'][ $quiz_question_id ] ) ) {
			array_walk(
				$_POST['tutor_quiz_question'][ $quiz_question_id ], // phpcs:ignore
				function( $v, $k ) use ( $quiz_question_id ) {
					if ( 'question_description' === $k ) {
						$_POST['tutor_quiz_question'][ $quiz_question_id ][ $k ] = wp_kses_post( wp_unslash( $v ) );
					} else {
						$_POST['tutor_quiz_question'][ $quiz_question_id ][ $k ] = sanitize_text_field( wp_unslash( $v ) );
					}
				}
			);
		} else {
			wp_send_json_error( __( 'Invalid quiz question ID', 'tutor' ) );
		}

		$question_data = wp_unslash( $_POST['tutor_quiz_question'] ); //phpcs:ignore
		$requires_answeres = array(
			'multiple_choice',
			'single_choice',
			'true_false',
			'fill_in_the_blank',
			'matching',
			'image_matching',
			'image_answering',
			'ordering',
		);

		$need_correct = array(
			'multiple_choice',
			'single_choice',
			'true_false',
		);

		foreach ( $question_data as $question_id => $question ) {
			// Make sure the quiz has answers.
			if ( isset( $question['question_type'] ) && in_array( $question['question_type'], $requires_answeres ) ) {
				$require_correct = in_array( $question['question_type'], $need_correct );
				$all_answers     = $this->get_answers_by_q_id( $question_id, $question['question_type'] );
				$correct_answers = $this->get_answers_by_q_id( $question_id, $question['question_type'], $require_correct );

				if ( ! empty( $all_answers ) && empty( $correct_answers ) ) {
					wp_send_json_error( array( 'message' => __( 'Please make sure the question has answer' ) ) );
					exit;
				}
			}

			if ( ! tutor_utils()->can_user_manage( 'question', $question_id ) ) {
				continue;
			}
			// Data already sanitize above.
			$question_title       = $question['question_title'] ?? '';
			$question_description = $question['question_description'];
			$question_type        = $question['question_type'] ?? '';
			$question_mark        = $question['question_mark'] ?? '';

			unset( $question['question_title'] );
			unset( $question['question_description'] );

			$data = array(
				'question_title'       => $question_title,
				'question_description' => $question_description,
				'question_type'        => $question_type,
				'question_mark'        => $question_mark,
				'question_settings'    => maybe_serialize( $question ),
			);

			$wpdb->update( $wpdb->prefix . 'tutor_quiz_questions', $data, array( 'question_id' => $question_id ) );
		}

		wp_send_json_success();
	}

	/**
	 * Delete quiz questions
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function tutor_quiz_builder_question_delete() {
		tutor_utils()->checking_nonce();

		global $wpdb;

		$question_id = Input::post( 'question_id', 0, Input::TYPE_INT );

		if ( ! tutor_utils()->can_user_manage( 'question', $question_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied', 'tutor' ) ) );
		}

		if ( $question_id ) {
			$wpdb->delete( $wpdb->prefix . 'tutor_quiz_questions', array( 'question_id' => esc_sql( $question_id ) ) );
		}

		wp_send_json_success();
	}

	/**
	 * Get answers options form for quiz question
	 *
	 * @since 1.0.0
	 *
	 * @return void send wp_json response
	 */
	public function tutor_quiz_question_answer_editor() {
		tutor_utils()->checking_nonce();

		$question_id   = Input::post( 'question_id', 0, Input::TYPE_INT );
		$answer_id     = Input::post( 'answer_id', 0, Input::TYPE_INT );
		$quiz_option   = isset( $_POST['tutor_quiz_question'] ) ? tutor_utils()->sanitize_array( wp_unslash( $_POST['tutor_quiz_question'] ) ) : array(); //phpcs:ignore
		$question      = tutor_utils()->avalue_dot( $question_id, $quiz_option );
		$question_type = $question['question_type'];

		if ( ! tutor_utils()->can_user_manage( 'question', $question_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied', 'tutor' ) ) );
		}

		if ( $answer_id ) {
			$old_answer = tutor_utils()->get_answer_by_id( $answer_id );
			//phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedForeach
			foreach ( $old_answer as $old_answer ) {
			}
		}

		ob_start();
		include tutor()->path . 'views/modal/question_answer_form.php';
		$output = ob_get_clean();

		wp_send_json_success( array( 'output' => $output ) );
	}

	/**
	 * Undocumented function
	 *
	 * @since 1.0.0
	 *
	 * @param mixed   $questions questions.
	 * @param mixed   $answers answers.
	 * @param boolean $response should send json response.
	 *
	 * @return void
	 */
	public function tutor_save_quiz_answer_options( $questions = null, $answers = null, $response = true ) {
		tutor_utils()->checking_nonce();

		global $wpdb;
		$questions = $questions ? $questions : tutor_utils()->sanitize_array( wp_unslash( $_POST['tutor_quiz_question'] ) ); //phpcs:ignore
		$answers   = $answers ? $answers : tutor_utils()->sanitize_array( wp_unslash( $_POST['quiz_answer'] ) ); //phpcs:ignore

		foreach ( $answers as $question_id => $answer ) {
			if ( ! tutor_utils()->can_user_manage( 'question', $question_id ) ) {
				continue;
			}

			$question      = tutor_utils()->avalue_dot( $question_id, $questions );
			$question_type = $question['question_type'];

			// Getting next sorting order.
			$next_order_id = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT MAX(answer_order)
						FROM {$wpdb->prefix}tutor_quiz_question_answers
						WHERE belongs_question_id = %d
							AND belongs_question_type = %s
					",
					$question_id,
					esc_sql( $question_type )
				)
			);

			//phpcs:ignore Squiz.Operators.IncrementDecrementUsage.Found
			$next_order_id = $next_order_id + 1;

			if ( $question ) {
				if ( 'true_false' === $question_type ) {
					$wpdb->delete(
						$wpdb->prefix . 'tutor_quiz_question_answers',
						array(
							'belongs_question_id'   => $question_id,
							'belongs_question_type' => $question_type,
						)
					);
					$data_true_false = array(
						array(
							'belongs_question_id'   => esc_sql( $question_id ),
							'belongs_question_type' => $question_type,
							'answer_title'          => __( 'True', 'tutor' ),
							'is_correct'            => 'true' == $answer['true_false'] ? 1 : 0,
							'answer_two_gap_match'  => 'true',
						),
						array(
							'belongs_question_id'   => esc_sql( $question_id ),
							'belongs_question_type' => $question_type,
							'answer_title'          => __( 'False', 'tutor' ),
							'is_correct'            => 'false' === $answer['true_false'] ? 1 : 0,
							'answer_two_gap_match'  => 'false',
						),
					);

					foreach ( $data_true_false as $true_false_data ) {
						$wpdb->insert( $wpdb->prefix . 'tutor_quiz_question_answers', $true_false_data );
					}
				} elseif ( 'multiple_choice' === $question_type ||
					'single_choice' === $question_type ||
					'ordering' === $question_type ||
					'matching' === $question_type ||
					'image_matching' === $question_type ||
					'image_answering' === $question_type ) {

					$answer_data = array(
						'belongs_question_id'   => sanitize_text_field( $question_id ),
						'belongs_question_type' => $question_type,
						'answer_title'          => sanitize_text_field( $answer['answer_title'] ),
						'image_id'              => isset( $answer['image_id'] ) ? $answer['image_id'] : 0,
						'answer_view_format'    => isset( $answer['answer_view_format'] ) ? $answer['answer_view_format'] : 0,
						'answer_order'          => $next_order_id,
					);
					if ( isset( $answer['matched_answer_title'] ) ) {
						$answer_data['answer_two_gap_match'] = sanitize_text_field( $answer['matched_answer_title'] );
					}

					$wpdb->insert( $wpdb->prefix . 'tutor_quiz_question_answers', $answer_data );

				} elseif ( 'fill_in_the_blank' === $question_type ) {
					$wpdb->delete(
						$wpdb->prefix . 'tutor_quiz_question_answers',
						array(
							'belongs_question_id'   => $question_id,
							'belongs_question_type' => $question_type,
						)
					);
					$answer_data = array(
						'belongs_question_id'   => sanitize_text_field( $question_id ),
						'belongs_question_type' => $question_type,
						'answer_title'          => sanitize_text_field( $answer['answer_title'] ),
						'answer_two_gap_match'  => isset( $answer['answer_two_gap_match'] ) ? sanitize_text_field( trim( $answer['answer_two_gap_match'] ) ) : null,
					);
					$wpdb->insert( $wpdb->prefix . 'tutor_quiz_question_answers', $answer_data );
				}
			}
		}

		// Send response to browser if not internal call.
		if ( $response ) {
			wp_send_json_success();
			exit;
		}
	}

	/**
	 * Tutor Update Answer
	 *
	 * @since 1.0.0
	 *
	 * @return void send wp_json response
	 */
	public function tutor_update_quiz_answer_options() {
		tutor_utils()->checking_nonce();

		global $wpdb;

		$answer_id = Input::post( 'tutor_quiz_answer_id', 0, Input::TYPE_INT );

		if ( ! tutor_utils()->can_user_manage( 'quiz_answer', $answer_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied', 'tutor' ) ) );
		}

		// Data sanitizing by helper method.
		$questions = tutor_sanitize_data( wp_unslash( $_POST['tutor_quiz_question'] ) ); //phpcs:ignore
		$answers   = tutor_sanitize_data( wp_unslash( $_POST['quiz_answer'] ) ); //phpcs:ignore

		foreach ( $answers as $question_id => $answer ) {
			$question      = tutor_utils()->avalue_dot( $question_id, $questions );
			$question_type = $question['question_type'];

			if ( $question ) {
				if ( 'multiple_choice' === $question_type ||
				'single_choice' === $question_type ||
				'ordering' === $question_type ||
				'matching' === $question_type ||
				'image_matching' === $question_type ||
				'fill_in_the_blank' === $question_type ||
				'image_answering' === $question_type ) {

					$answer_data = array(
						'belongs_question_id'   => $question_id,
						'belongs_question_type' => $question_type,
						'answer_title'          => sanitize_text_field( $answer['answer_title'] ),
						'image_id'              => isset( $answer['image_id'] ) ? $answer['image_id'] : 0,
						'answer_view_format'    => isset( $answer['answer_view_format'] ) ? sanitize_text_field( $answer['answer_view_format'] ) : '',
					);
					if ( isset( $answer['matched_answer_title'] ) ) {
						$answer_data['answer_two_gap_match'] = sanitize_text_field( $answer['matched_answer_title'] );
					}

					if ( 'fill_in_the_blank' === $question_type ) {
						$answer_data['answer_two_gap_match'] = isset( $answer['answer_two_gap_match'] ) ? sanitize_text_field( trim( $answer['answer_two_gap_match'] ) ) : null;
					}

					$wpdb->update( $wpdb->prefix . 'tutor_quiz_question_answers', $answer_data, array( 'answer_id' => $answer_id ) );
				}
			}
		}
		wp_send_json_success();
	}

	/**
	 * Get answers by quiz id
	 *
	 * @since 1.0.0
	 *
	 * @param int     $question_id question id.
	 * @param mixed   $question_type type of question.
	 * @param boolean $is_correct only correct answers or not.
	 *
	 * @return wpdb:get_results
	 */
	private function get_answers_by_q_id( $question_id, $question_type, $is_correct = false ) {
		global $wpdb;

		$correct_clause = $is_correct ? ' AND is_correct=1 ' : '';

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}tutor_quiz_question_answers
					WHERE belongs_question_id = %d 
						AND belongs_question_type = %s 
						{$correct_clause}
					ORDER BY answer_order ASC;
				",
				$question_id,
				esc_sql( $question_type )
			)
		);
	}

	/**
	 * Quiz builder changed type
	 *
	 * @since 1.0.0
	 *
	 * @return void send wp_json response
	 */
	public function tutor_quiz_builder_change_type() {
		tutor_utils()->checking_nonce();

		global $wpdb;
		$question_id   = Input::post( 'question_id', 0, Input::TYPE_INT );
		$question_type = Input::post( 'question_type' );

		if ( ! tutor_utils()->can_user_manage( 'question', $question_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied', 'tutor' ) ) );
		}

		// Get question data by question ID.
		$question = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * 
					FROM {$wpdb->prefix}tutor_quiz_questions
					WHERE question_id = %d
				",
				$question_id
			)
		);

		// Get answers by question ID.
		$answers = $this->get_answers_by_q_id( $question_id, $question_type );

		ob_start();
		require tutor()->path . '/views/modal/question_answer_list.php';
		$output = ob_get_clean();

		wp_send_json_success( array( 'output' => $output ) );
	}

	/**
	 * Delete quiz question's answer
	 *
	 * @since 1.0.0
	 *
	 * @return void send wp_json response
	 */
	public function tutor_quiz_builder_delete_answer() {
		tutor_utils()->checking_nonce();

		global $wpdb;
		$answer_id = Input::post( 'answer_id', 0, Input::TYPE_INT );

		if ( ! tutor_utils()->can_user_manage( 'quiz_answer', $answer_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied', 'tutor' ) ) );
		}

		$wpdb->delete( $wpdb->prefix . 'tutor_quiz_question_answers', array( 'answer_id' => esc_sql( $answer_id ) ) );
		wp_send_json_success();
	}

	/**
	 * Save quiz questions sorting
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function tutor_quiz_question_sorting() {
		tutor_utils()->checking_nonce();

		global $wpdb;

		// Data sanitizing by helper method.
		$question_ids = tutor_utils()->avalue_dot( 'sorted_question_ids', tutor_sanitize_data( $_POST ) ); //phpcs:ignore
		if ( is_array( $question_ids ) && count( $question_ids ) ) {
			$i = 0;
			foreach ( $question_ids as $key => $question_id ) {
				if ( tutor_utils()->can_user_manage( 'question', $question_id ) ) {
					$i++;
					$wpdb->update( $wpdb->prefix . 'tutor_quiz_questions', array( 'question_order' => $i ), array( 'question_id' => $question_id ) );
				}
			}
		}
	}

	/**
	 * Save sorting data for quiz answers
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function tutor_quiz_answer_sorting() {
		tutor_utils()->checking_nonce();

		global $wpdb;
		$answer_ids = Input::post( 'sorted_answer_ids', array(), Input::TYPE_ARRAY );
		if ( count( $answer_ids ) ) {
			$i = 0;
			foreach ( $answer_ids as $key => $answer_id ) {
				if ( tutor_utils()->can_user_manage( 'quiz_answer', $answer_id ) ) {
					$i++;
					$wpdb->update( $wpdb->prefix . 'tutor_quiz_question_answers', array( 'answer_order' => $i ), array( 'answer_id' => $answer_id ) );
				}
			}
		}
	}

	/**
	 * Mark answer as correct
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function tutor_mark_answer_as_correct() {
		tutor_utils()->checking_nonce();

		global $wpdb;

		$answer_id = Input::post( 'answer_id', 0, Input::TYPE_INT );
		// get question info.
		$belong_question = $wpdb->get_row(
			$wpdb->prepare(
				" SELECT belongs_question_id, belongs_question_type
				FROM {$wpdb->tutor_quiz_question_answers}
				WHERE answer_id = %d
				LIMIT 1
			",
				$answer_id
			)
		);
		if ( $belong_question ) {
			// if question found update all answer is_correct to 0 except post answer.
			$question_type = $belong_question->belongs_question_type;
			$question_id   = $belong_question->belongs_question_id;
			if ( 'true_false' === $question_type || 'single_choice' === $question_type ) {
				$update = $wpdb->query(
					$wpdb->prepare(
						"UPDATE {$wpdb->tutor_quiz_question_answers}
						SET is_correct = 0
						WHERE belongs_question_id = %d
							AND answer_id != %d
					",
						$question_id,
						$answer_id
					)
				);
			}
		}

		$input_value = Input::post( 'inputValue', '' );

		if ( ! tutor_utils()->can_user_manage( 'quiz_answer', $answer_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied', 'tutor' ) ) );
		}

		$answer = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * 
					FROM {$wpdb->prefix}tutor_quiz_question_answers 
					WHERE answer_id = %d 
					LIMIT 0,1 ;
				",
				$answer_id
			)
		);
		if ( 'single_choice' === $answer->belongs_question_type ) {
			$wpdb->update(
				$wpdb->prefix . 'tutor_quiz_question_answers',
				array( 'is_correct' => 0 ),
				array( 'belongs_question_id' => esc_sql( $answer->belongs_question_id ) )
			);
		}
		$wpdb->update(
			$wpdb->prefix . 'tutor_quiz_question_answers',
			array( 'is_correct' => esc_sql( $input_value ) ),
			array( 'answer_id' => esc_sql( $answer_id ) )
		);
	}

	/**
	 * Rendering quiz for frontend
	 *
	 * @since 1.0.0
	 *
	 * @return void send wp_json response
	 */
	public function tutor_render_quiz_content() {

		tutor_utils()->checking_nonce();

		$quiz_id = Input::post( 'quiz_id', 0, Input::TYPE_INT );

		if ( ! tutor_utils()->has_enrolled_content_access( 'quiz', $quiz_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied.', 'tutor' ) ) );
		}

		ob_start();
		global $post;

		$post = get_post( $quiz_id );
		setup_postdata( $post );

		single_quiz_contents();
		wp_reset_postdata();

		$html = ob_get_clean();
		wp_send_json_success( array( 'html' => $html ) );
	}

	/**
	 * Get attempt details
	 *
	 * @since 1.0.0
	 *
	 * @param int $attempt_id required attempt id to get details.
	 *
	 * @return mixed object on success, null on failure
	 */
	public static function attempt_details( int $attempt_id ) {
		global $wpdb;
		$attempt_details = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT *
					FROM {$wpdb->prefix}tutor_quiz_attempts
					WHERE attempt_id = %d
				",
				$attempt_id
			)
		);
		return $attempt_details;
	}

	/**
	 * Update quiz attempt info
	 *
	 * @since 1.0.0
	 *
	 * @param int   $attempt_id attempt id.
	 * @param mixed $attempt_info serialize data.
	 *
	 * @return bool, true on success, false on failure
	 */
	public static function update_attempt_info( int $attempt_id, $attempt_info ) {
		global $wpdb;
		$table       = $wpdb->prefix . 'tutor_quiz_attempts';
		$update_info = $wpdb->update(
			$table,
			array( 'attempt_info' => $attempt_info ),
			array( 'attempt_id' => $attempt_id )
		);
		return $update_info ? true : false;
	}

	/**
	 * Attempt delete ajax request handler
	 *
	 * @since 2.1.0
	 *
	 * @return void  wp_json response
	 */
	public function attempt_delete() {
		tutor_utils()->checking_nonce();
		if ( current_user_can( 'administrator' ) || current_user_can( tutor()->instructor_role ) ) {
			$attempt_id = Input::post( 'id', 0, Input::TYPE_INT );
			if ( $attempt_id ) {
				QuizModel::delete_quiz_attempt( $attempt_id );
				wp_send_json_success( __( 'Attempt deleted successfully!', 'tutor' ) );
			} else {
				wp_send_json_error( __( 'Invalid attempt ID', 'tutor' ) );
			}
		} else {
			wp_send_json_error( __( 'You are not authorized to perform this action!', 'tutor' ) );
		}
	}

}
