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

use Tutor\Helpers\HttpHelper;
use Tutor\Helpers\QueryHelper;
use Tutor\Models\CourseModel;
use Tutor\Models\QuizModel;
use Tutor\Traits\JsonResponse;

/**
 * Manage quiz operations.
 *
 * @since 1.0.0
 */
class Quiz {
	use JsonResponse;

	const META_QUIZ_OPTION = 'tutor_quiz_option';

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

		add_action( 'wp_ajax_tutor_quiz_save', array( $this, 'ajax_quiz_save' ) );
		add_action( 'wp_ajax_tutor_quiz_delete', array( $this, 'ajax_quiz_delete' ) );
		add_action( 'wp_ajax_tutor_quiz_details', array( $this, 'ajax_quiz_details' ) );

		add_action( 'wp_ajax_tutor_quiz_question_create', array( $this, 'ajax_quiz_question_create' ) );
		add_action( 'wp_ajax_tutor_quiz_question_update', array( $this, 'ajax_quiz_question_update' ) );
		add_action( 'wp_ajax_tutor_quiz_question_delete', array( $this, 'ajax_quiz_question_delete' ) );
		add_action( 'wp_ajax_tutor_quiz_question_sorting', array( $this, 'ajax_quiz_question_sorting' ) );

		add_action( 'wp_ajax_tutor_quiz_question_answer_save', array( $this, 'ajax_quiz_question_answer_save' ) );
		add_action( 'wp_ajax_tutor_quiz_question_answer_delete', array( $this, 'ajax_quiz_question_answer_delete' ) );
		add_action( 'wp_ajax_tutor_quiz_question_answer_sorting', array( $this, 'ajax_quiz_question_answer_sorting' ) );
		add_action( 'wp_ajax_tutor_mark_answer_as_correct', array( $this, 'ajax_mark_answer_as_correct' ) );

		add_action( 'wp_ajax_tutor_load_quiz_builder_modal', array( $this, 'tutor_load_quiz_builder_modal' ), 10, 0 );
		add_action( 'wp_ajax_tutor_quiz_builder_get_question_form', array( $this, 'tutor_quiz_builder_get_question_form' ) );
		add_action( 'wp_ajax_tutor_quiz_modal_update_question', array( $this, 'tutor_quiz_modal_update_question' ) );
		add_action( 'wp_ajax_tutor_quiz_question_answer_editor', array( $this, 'tutor_quiz_question_answer_editor' ) );
		add_action( 'wp_ajax_tutor_save_quiz_answer_options', array( $this, 'tutor_save_quiz_answer_options' ), 10, 0 );
		add_action( 'wp_ajax_tutor_update_quiz_answer_options', array( $this, 'tutor_update_quiz_answer_options' ) );
		add_action( 'wp_ajax_tutor_quiz_builder_change_type', array( $this, 'tutor_quiz_builder_change_type' ) );

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

		add_action( 'tutor_quiz/answer/review/after', array( $this, 'do_auto_course_complete' ), 10, 3 );
	}

	/**
	 * Get quiz time units options.
	 *
	 * @since 2.6.0
	 *
	 * @return array
	 */
	public static function quiz_time_units() {
		$time_units = array(
			'seconds' => __( 'Seconds', 'tutor' ),
			'minutes' => __( 'Minutes', 'tutor' ),
			'hours'   => __( 'Hours', 'tutor' ),
			'days'    => __( 'Days', 'tutor' ),
			'weeks'   => __( 'Weeks', 'tutor' ),
		);

		return apply_filters( 'tutor_quiz_time_units', $time_units );
	}

	/**
	 * Get quiz default settings.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public static function get_default_quiz_settings() {
		$settings = array(
			'time_limit'                         => array(
				'time_type'  => 'minutes',
				'time_value' => 0,
			),
			'attempts_allowed'                   => 10,
			'feedback_mode'                      => 'retry',
			'hide_question_number_overview'      => 0,
			'hide_quiz_time_display'             => 0,
			'max_questions_for_answer'           => 10,
			'open_ended_answer_characters_limit' => 500,
			'pass_is_required'                   => 0,
			'passing_grade'                      => 80,
			'question_layout_view'               => '',
			'questions_order'                    => 'rand',
			'quiz_auto_start'                    => 0,
			'short_answer_characters_limit'      => 200,
		);

		return apply_filters( 'tutor_quiz_default_settings', $settings );
	}

	/**
	 * Get question default settings.
	 *
	 * @since 3.0.0
	 *
	 * @param string $type type of question.
	 *
	 * @return array
	 */
	public static function get_default_question_settings( $type ) {
		$settings = array(
			'question_type'      => $type,
			'question_mark'      => 1,
			'answer_required'    => 0,
			'randomize_options'  => 0,
			'show_question_mark' => 0,
		);

		return apply_filters( 'tutor_question_default_settings', $settings );
	}

	/**
	 * Get quiz modes
	 *
	 * @since 2.6.0
	 *
	 * @return array
	 */
	public static function quiz_modes() {
		$modes = array(
			array(
				'key'         => 'default',
				'value'       => __( 'Default', 'tutor' ),
				'description' => __( 'Answers shown after quiz is finished', 'tutor' ),
			),
			array(
				'key'         => 'reveal',
				'value'       => __( 'Reveal Mode', 'tutor' ),
				'description' => __( 'Show result after the attempt.', 'tutor' ),
			),
			array(
				'key'         => 'retry',
				'value'       => __( 'Retry Mode', 'tutor' ),
				'description' => __( 'Reattempt quiz any number of times. Define Attempts Allowed below.', 'tutor' ),
			),
		);

		return apply_filters( 'tutor_quiz_modes', $modes );
	}

	/**
	 * Get quiz modes
	 *
	 * @since 2.6.0
	 *
	 * @return array
	 */
	public static function quiz_question_layouts() {
		$layouts = array(
			''                          => __( 'Set question layout view', 'tutor' ),
			'single_question'           => __( 'Single Question', 'tutor' ),
			'question_pagination'       => __( 'Question Pagination', 'tutor' ),
			'question_below_each_other' => __( 'Question below each other', 'tutor' ),
		);

		return apply_filters( 'tutor_quiz_layouts', $layouts );
	}

	/**
	 * Get quiz modes
	 *
	 * @since 2.6.0
	 *
	 * @return array
	 */
	public static function quiz_question_orders() {
		$orders = array(
			'rand'    => __( 'Random', 'tutor' ),
			'sorting' => __( 'Sorting', 'tutor' ),
			'asc'     => __( 'Ascending', 'tutor' ),
			'desc'    => __( 'Descending', 'tutor' ),
		);

		return apply_filters( 'tutor_quiz_layouts', $orders );
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
		if ( ! User::has_any_role( array( User::ADMIN, User::INSTRUCTOR ) ) ) {
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

		$user_id = get_current_user_id();
		$user    = get_userdata( $user_id );

		$quiz_id = Input::post( 'quiz_id', 0, Input::TYPE_INT );

		$quiz   = get_post( $quiz_id );
		$course = CourseModel::get_course_by_quiz( $quiz_id );

		self::quiz_attempt( $course->ID, $quiz_id, $user_id );
		wp_safe_redirect( get_permalink( $quiz_id ) );
		die();
	}

	/**
	 * Manage quiz attempt
	 *
	 * @since 2.6.1
	 *
	 * @param integer $course_id course id.
	 * @param integer $quiz_id quiz id.
	 * @param integer $user_id user id.
	 * @param string  $attempt_status attempt status.
	 *
	 * @return int inserted id|0
	 */
	public static function quiz_attempt( int $course_id, int $quiz_id, int $user_id, $attempt_status = 'attempt_started' ) {
		global $wpdb;

		if ( ! $course_id ) {
			die( 'There is something went wrong with course, please check if quiz attached with a course' );
		}

		do_action( 'tutor_quiz/start/before', $quiz_id, $user_id );

		$date = date( 'Y-m-d H:i:s', tutor_time() ); //phpcs:ignore

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
			'course_id'                => $course_id,
			'quiz_id'                  => $quiz_id,
			'user_id'                  => $user_id,
			'total_questions'          => $max_question_allowed,
			'total_answered_questions' => 0,
			'attempt_info'             => maybe_serialize( $tutor_quiz_option ),
			'attempt_status'           => $attempt_status,
			'attempt_ip'               => tutor_utils()->get_ip(),
			'attempt_started_at'       => $date,
		);

		$wpdb->insert( $wpdb->prefix . 'tutor_quiz_attempts', $attempt_data );
		$attempt_id = (int) $wpdb->insert_id;

		if ( $attempt_id ) {
			do_action( 'tutor_quiz/start/after', $quiz_id, $user_id, $attempt_id );
			return $attempt_id;
		} else {
			return 0;
		}
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
		tutor_utils()->checking_nonce();
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
		self::manage_attempt_answers( $attempt_answers, $attempt, $attempt_id, $course_id, $user_id );
		return true;
	}

	/**
	 * Manage attempt answers
	 *
	 * Evaluate each attempt answer and update the attempts table & insert in the attempt_answers table.
	 *
	 * @since 2.6.1
	 *
	 * @param array  $attempt_answers attempt answers.
	 * @param object $attempt single attempt.
	 * @param int    $attempt_id attempt id.
	 * @param int    $course_id course id.
	 * @param int    $user_id user id.
	 *
	 * @return void
	 */
	public static function manage_attempt_answers( $attempt_answers, $attempt, $attempt_id, $course_id, $user_id ) {
		global $wpdb;
		// Before hook.
		do_action( 'tutor_quiz/attempt_analysing/before', $attempt_id );

		// Single quiz can have multiple question. So multiple answer should be saved.
		foreach ( $attempt_answers as $attempt_id => $attempt_answer ) {
			// Get total marks of all question comes.
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
				//phpcs:disable
				$query = $wpdb->prepare(
					"SELECT SUM(question_mark)
						FROM {$wpdb->prefix}tutor_quiz_questions
						WHERE 1 = %d
							AND question_id IN({$question_ids_string});
					",
					1
				);
				$total_question_marks = $wpdb->get_var( $query );
				//phpcs:enable

				$total_question_marks = apply_filters( 'tutor_filter_update_before_question_mark', $total_question_marks, $question_ids, $user_id, $attempt_id );

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
						$gap_answer = maybe_serialize(
							array_map(
								function ( $ans ) {
									return wp_slash( trim( $ans ) );
								},
								$gap_answer
							)
						);

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

						//phpcs:disable

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
						//phpcs:enable
					}

					$question_mark = $is_answer_was_correct ? $question->question_mark : 0;
					$total_marks  += $question_mark;

					$total_marks = apply_filters( 'tutor_filter_quiz_total_marks', $total_marks, $question_id, $question_type, $user_id, $attempt_id );

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

					$answers_data = apply_filters( 'tutor_filter_quiz_answer_data', $answers_data, $question_id, $question_type, $user_id, $attempt_id );

					$wpdb->insert( $wpdb->prefix . 'tutor_quiz_attempt_answers', $answers_data );
				}
			}

			$attempt_info = array(
				'total_answered_questions' => tutor_utils()->count( $quiz_answers ),
				'earned_marks'             => $total_marks,
				'attempt_status'           => 'attempt_ended',
				'attempt_ended_at'         => date( 'Y-m-d H:i:s', tutor_time() ), //phpcs:ignore
			);

			if ( $review_required ) {
				$attempt_info['attempt_status'] = 'review_required';
			}

			$wpdb->update( $wpdb->prefix . 'tutor_quiz_attempts', $attempt_info, array( 'attempt_id' => $attempt_id ) );
		}

		// After hook.
		do_action( 'tutor_quiz/attempt_ended', $attempt_id, $course_id, $user_id );
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
			'attempt_ended_at'         => date( 'Y-m-d H:i:s', tutor_time() ), //phpcs:ignore
		);

		do_action( 'tutor_quiz_before_finish', $attempt_id, $quiz_id, $attempt->user_id );
		$wpdb->update( $wpdb->prefix . 'tutor_quiz_attempts', $attempt_info, array( 'attempt_id' => $attempt_id ) );
		do_action( 'tutor_quiz_finished', $attempt_id, $quiz_id, $attempt->user_id );

		wp_redirect( tutor_utils()->input_old( '_wp_http_referer' ) );
	}

	/**
	 * Get quiz total marks.
	 *
	 * @since 3.0.0
	 *
	 * @param int $quiz_id quiz id.
	 *
	 * @return int|float
	 */
	public static function get_quiz_total_marks( $quiz_id ) {
		global $wpdb;

		$total_marks = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(question_mark) total_marks 
				FROM {$wpdb->prefix}tutor_quiz_questions
				WHERE quiz_id=%d",
				$quiz_id
			)
		);

		return floatval( $total_marks );
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
				'total_marks'      => self::get_quiz_total_marks( $quiz_id ),
				'earned_marks'     => 0,
				'attempt_ended_at' => gmdate( 'Y-m-d H:i:s', tutor_time() ),
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
					'manually_reviewed_at' => date( 'Y-m-d H:i:s', tutor_time() ), //phpcs:ignore
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
					'manually_reviewed_at' => date( 'Y-m-d H:i:s', tutor_time() ),//phpcs:ignore
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
	 * Do auto course complete after review a quiz attempt.
	 *
	 * @since 2.4.0
	 *
	 * @param int $attempt_answer_id attempt answer id.
	 * @param int $course_id course id.
	 * @param int $user_id student id.
	 *
	 * @return void
	 */
	public function do_auto_course_complete( $attempt_answer_id, $course_id, $user_id ) {
		if ( CourseModel::can_autocomplete_course( $course_id, $user_id ) ) {
			CourseModel::mark_course_as_completed( $course_id, $user_id );
			Course::set_review_popup_data( $user_id, $course_id );
		}
	}

	/**
	 * Quiz create and update.
	 *
	 * @since 1.0.0
	 * @since 3.0.0 refactor and response change.
	 *
	 * @return void
	 */
	public function ajax_quiz_save() {
		if ( ! tutor_utils()->is_nonce_verified() ) {
			$this->json_response( tutor_utils()->error_message( 'nonce' ), null, HttpHelper::STATUS_BAD_REQUEST );
		}

		$is_update        = false;
		$topic_id         = Input::post( 'topic_id', 0, Input::TYPE_INT );
		$quiz_id          = Input::post( 'quiz_id', 0, Input::TYPE_INT );
		$quiz_title       = Input::post( 'quiz_title' );
		$quiz_description = isset( $_POST['quiz_description'] ) ? wp_kses( wp_unslash( $_POST['quiz_description'] ), $this->allowed_html ) : ''; //phpcs:ignore

		$next_order_id = tutor_utils()->get_next_course_content_order_id( $topic_id, $quiz_id );

		// Check edit privilege.
		if ( ! tutor_utils()->can_user_manage( 'topic', $topic_id ) ) {
			$this->json_response(
				tutor_utils()->error_message(),
				null,
				HttpHelper::STATUS_FORBIDDEN
			);
		}

		if ( 0 !== $topic_id && 0 !== $quiz_id ) {
			if ( ! tutor_utils()->can_user_manage( 'quiz', $quiz_id ) ) {
				$this->json_response(
					tutor_utils()->error_message(),
					null,
					HttpHelper::STATUS_FORBIDDEN
				);
			}
		}

		// Prepare quiz data to save in database.
		$post_arr = array(
			'post_type'    => 'tutor_quiz',
			'post_title'   => $quiz_title,
			'post_content' => $quiz_description,
			'post_status'  => 'publish',
			'post_author'  => get_current_user_id(),
			'post_parent'  => $topic_id,
			'menu_order'   => $next_order_id,
		);

		if ( $quiz_id ) {
			$is_update      = true;
			$post_arr['ID'] = $quiz_id;
		}

		// Insert quiz and run hook.
		$quiz_id = wp_insert_post( $post_arr );
		do_action( ( $is_update ? 'tutor_quiz_updated' : 'tutor_initial_quiz_created' ), $quiz_id );

		// Sanitize by helper method & save quiz settings.
		$quiz_option = tutor_utils()->sanitize_array( $_POST['quiz_option'] ); //phpcs:ignore
		update_post_meta( $quiz_id, 'tutor_quiz_option', $quiz_option );
		do_action( 'tutor_quiz_settings_updated', $quiz_id );

		if ( $is_update ) {
			$this->json_response(
				__( 'Quiz updated successfully', 'tutor' ),
				$quiz_id
			);
		} else {
			$this->json_response(
				__( 'Quiz created successfully', 'tutor' ),
				$quiz_id,
				HttpHelper::STATUS_CREATED
			);
		}
	}

	/**
	 * Get a quiz details by id
	 *
	 * @return void
	 */
	public function ajax_quiz_details() {
		tutor_utils()->check_nonce();

		$quiz_id = Input::post( 'quiz_id', 0, Input::TYPE_INT );
		if ( ! tutor_utils()->can_user_manage( 'quiz', $quiz_id ) ) {
			$this->json_response(
				tutor_utils()->error_message(),
				null,
				HttpHelper::STATUS_FORBIDDEN
			);
		}

		$data = QuizModel::get_quiz_details( $quiz_id );

		$this->json_response(
			__( 'Quiz data fetched successfully', 'tutor' ),
			$data
		);
	}

	/**
	 * Delete quiz by id
	 *
	 * @since 1.0.0
	 * @since 3.0.0 refactor and response change.
	 *
	 * @return void
	 */
	public function ajax_quiz_delete() {
		if ( ! tutor_utils()->is_nonce_verified() ) {
			$this->json_response( tutor_utils()->error_message( 'nonce' ), null, HttpHelper::STATUS_BAD_REQUEST );
		}

		global $wpdb;

		$quiz_id = Input::post( 'quiz_id', 0, Input::TYPE_INT );
		if ( ! tutor_utils()->can_user_manage( 'quiz', $quiz_id ) ) {
			$this->json_response(
				tutor_utils()->error_message(),
				null,
				HttpHelper::STATUS_FORBIDDEN
			);
		}

		$post = get_post( $quiz_id );
		if ( 'tutor_quiz' !== $post->post_type ) {
			$this->json_response(
				__( 'Invalid quiz', 'tutor' ),
				null,
				HttpHelper::STATUS_BAD_REQUEST
			);
		}

		do_action( 'tutor_delete_quiz_before', $quiz_id );

		$wpdb->delete( $wpdb->prefix . 'tutor_quiz_attempts', array( 'quiz_id' => $quiz_id ) );
		$wpdb->delete( $wpdb->prefix . 'tutor_quiz_attempt_answers', array( 'quiz_id' => $quiz_id ) );

		$questions_ids = $wpdb->get_col( $wpdb->prepare( "SELECT question_id FROM {$wpdb->prefix}tutor_quiz_questions WHERE quiz_id = %d ", $quiz_id ) );

		if ( is_array( $questions_ids ) && count( $questions_ids ) ) {
			$in_question_ids = QueryHelper::prepare_in_clause( $questions_ids );
			//phpcs:disable
			$wpdb->query(
				"DELETE 
					FROM {$wpdb->prefix}tutor_quiz_question_answers
					WHERE belongs_question_id IN({$in_question_ids})
				"
			);
			//phpcs:enable
		}

		$wpdb->delete( $wpdb->prefix . 'tutor_quiz_questions', array( 'quiz_id' => $quiz_id ) );

		wp_delete_post( $quiz_id, true );

		do_action( 'tutor_delete_quiz_after', $quiz_id );

		$this->json_response(
			__( 'Quiz deleted successfully', 'tutor' ),
			$quiz_id
		);
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
	 * Delete quiz question
	 *
	 * @since 1.0.0
	 * @since 3.0.0 refactor and response updated.
	 *
	 * @return void
	 */
	public function ajax_quiz_question_delete() {
		if ( ! tutor_utils()->is_nonce_verified() ) {
			$this->json_response( tutor_utils()->error_message( 'nonce' ), null, HttpHelper::STATUS_BAD_REQUEST );
		}

		global $wpdb;

		$question_id = Input::post( 'question_id', 0, Input::TYPE_INT );

		if ( ! tutor_utils()->can_user_manage( 'question', $question_id ) ) {
			$this->json_response(
				tutor_utils()->error_message(),
				null,
				HttpHelper::STATUS_FORBIDDEN
			);
		}

		if ( $question_id ) {
			$wpdb->delete( $wpdb->prefix . 'tutor_quiz_questions', array( 'question_id' => $question_id ) );
		}

		$this->json_response(
			__( 'Question successfully deleted', 'tutor' ),
			$question_id
		);

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
		//phpcs:disable
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
		//phpcs:enable
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
	 * Create quiz question
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function ajax_quiz_question_create() {
		if ( ! tutor_utils()->is_nonce_verified() ) {
			$this->json_response( tutor_utils()->error_message( 'nonce' ), null, HttpHelper::STATUS_BAD_REQUEST );
		}

		$quiz_id = Input::post( 'quiz_id', 0, Input::TYPE_INT );

		if ( ! tutor_utils()->can_user_manage( 'quiz', $quiz_id ) ) {
			$this->json_response( tutor_utils()->error_message(), null, HttpHelper::STATUS_FORBIDDEN );
		}

		global $wpdb;
		$next_question_sl    = QueryHelper::get_count( $wpdb->prefix . 'tutor_quiz_questions', array( 'quiz_id' => $quiz_id ), array(), '*' ) + 1;
		$next_question_order = QuizModel::quiz_next_question_order_id( $quiz_id );
		$question_title      = __( 'Question', 'tutor' ) . ' ' . $next_question_sl;

		$new_question_data = array(
			'quiz_id'              => $quiz_id,
			'question_title'       => $question_title,
			'question_description' => '',
			'question_type'        => 'true_false',
			'question_mark'        => 1,
			'question_settings'    => maybe_serialize( array() ),
			'question_order'       => esc_sql( $next_question_order ),
		);

		$new_question_data = apply_filters( 'tutor_quiz_question_data', $new_question_data );

		$wpdb->insert( $wpdb->prefix . 'tutor_quiz_questions', $new_question_data );
		$question_id = $wpdb->insert_id;

		// Add question with default true_false type and options.
		$this->add_true_false_options( $question_id );

		// Add created question object to response.
		$question                   = QuizModel::get_question( $question_id );
		$question->question_answers = QuizModel::get_question_answers( $question->question_id );
		if ( isset( $question->question_settings ) ) {
			$question->question_settings = maybe_unserialize( $question->question_settings );
		}

		$this->json_response(
			__( 'Question created successfully', 'tutor' ),
			$question,
			HttpHelper::STATUS_CREATED
		);
	}

	/**
	 * Update question
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function ajax_quiz_question_update() {
		if ( ! tutor_utils()->is_nonce_verified() ) {
			$this->json_response( tutor_utils()->error_message( 'nonce' ), null, HttpHelper::STATUS_BAD_REQUEST );
		}

		global $wpdb;

		$question_id = Input::post( 'question_id', 0, Input::TYPE_INT );
		if ( ! $question_id ) {
			$this->json_response( __( 'Invalid quiz question ID', 'tutor' ), null, HttpHelper::STATUS_BAD_REQUEST );
		}

		if ( ! tutor_utils()->can_user_manage( 'question', $question_id ) ) {
			$this->json_response( tutor_utils()->error_message(), null, HttpHelper::STATUS_FORBIDDEN );
		}

		$requires_answers = array(
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

		$question_title    = Input::post( 'question_title', '' );
		$question_type     = Input::post( 'question_type', 'true_false' );
		$question_mark     = Input::post( 'question_mark', 1, Input::TYPE_INT );
		$question_settings    = Input::sanitize_array( $_POST['question_settings'] ?? array() ); //phpcs:ignore

		add_filter( 'wp_kses_allowed_html', Input::class . '::allow_iframe', 10, 2 );
		$question_description = Input::post( 'question_description', '', Input::TYPE_KSES_POST );
		remove_filter( 'wp_kses_allowed_html', Input::class . '::allow_iframe', 10, 2 );

		if ( in_array( $question_type, $requires_answers, true ) ) {
			$require_correct = in_array( $question_type, $need_correct, true );
			$all_answers     = $this->get_answers_by_q_id( $question_id, $question_type );
			$correct_answers = $this->get_answers_by_q_id( $question_id, $question_type, $require_correct );

			if ( ! empty( $all_answers ) && empty( $correct_answers ) ) {
				$this->json_response(
					__( 'Please make sure the question has answer', 'tutor' ),
					null,
					HttpHelper::STATUS_BAD_REQUEST
				);
			}
		}

		if ( isset( $question_settings['question_title'] ) ) {
			unset( $question_settings['question_title'] );
		}

		if ( isset( $question_settings['question_description'] ) ) {
			unset( $question_settings['question_description'] );
		}

		$data = array(
			'question_title'       => $question_title,
			'question_description' => $question_description,
			'question_type'        => $question_type,
			'question_mark'        => $question_mark,
			'question_settings'    => maybe_serialize( $question_settings ),
		);

		$data = apply_filters( 'tutor_quiz_question_data', $data );

		$wpdb->update( $wpdb->prefix . 'tutor_quiz_questions', $data, array( 'question_id' => $question_id ) );

		$this->json_response(
			__( 'Question updated successfully', 'tutor' ),
			$question_id
		);
	}

	/**
	 * Save quiz questions sorting
	 *
	 * @since 1.0.0
	 * @since 3.0.0 refactor and update response.
	 *
	 * @return void
	 */
	public function ajax_quiz_question_sorting() {
		if ( ! tutor_utils()->is_nonce_verified() ) {
			$this->json_response( tutor_utils()->error_message( 'nonce' ), null, HttpHelper::STATUS_BAD_REQUEST );
		}

		$quiz_id      = Input::post( 'quiz_id', 0, Input::TYPE_INT );
		$question_ids = Input::post( 'sorted_question_ids', array(), Input::TYPE_ARRAY );

		if ( ! tutor_utils()->can_user_manage( 'quiz', $quiz_id ) ) {
			$this->json_response( tutor_utils()->error_message(), null, HttpHelper::STATUS_FORBIDDEN );
		}

		global $wpdb;

		$i = 0;
		foreach ( $question_ids as $question_id ) {
			$i++;
			$wpdb->update(
				$wpdb->prefix . 'tutor_quiz_questions',
				array( 'question_order' => $i ),
				array(
					'quiz_id'     => $quiz_id,
					'question_id' => $question_id,
				)
			);
		}

		$this->json_response( __( 'Question order successfully updated', 'tutor' ) );
	}

	/**
	 * Add true false type question answer options.
	 *
	 * @param int $question_id question id.
	 *
	 * @return void
	 */
	private function add_true_false_options( $question_id ) {
		global $wpdb;
		$question_type = 'true_false';

		$wpdb->delete(
			$wpdb->prefix . 'tutor_quiz_question_answers',
			array(
				'belongs_question_id'   => $question_id,
				'belongs_question_type' => $question_type,
			)
		);

		$data = array(
			array(
				'belongs_question_id'   => $question_id,
				'belongs_question_type' => $question_type,
				'answer_title'          => __( 'True', 'tutor' ),
				'is_correct'            => 1,
				'answer_two_gap_match'  => 'true',
			),
			array(
				'belongs_question_id'   => $question_id,
				'belongs_question_type' => $question_type,
				'answer_title'          => __( 'False', 'tutor' ),
				'is_correct'            => 0,
				'answer_two_gap_match'  => 'false',
			),
		);

		foreach ( $data as $row ) {
			$wpdb->insert( $wpdb->prefix . 'tutor_quiz_question_answers', $row );
		}
	}

	/**
	 * Save question answer
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function ajax_quiz_question_answer_save() {
		if ( ! tutor_utils()->is_nonce_verified() ) {
			$this->json_response( tutor_utils()->error_message( 'nonce' ), null, HttpHelper::STATUS_BAD_REQUEST );
		}

		$is_update   = false;
		$question_id = Input::post( 'question_id', 0, Input::TYPE_INT );
		$answer_id   = Input::post( 'answer_id', 0, Input::TYPE_INT );

		if ( $answer_id ) {
			$is_update = true;
		}

		if ( ! tutor_utils()->can_user_manage( 'question', $question_id ) ) {
			$this->json_response( tutor_utils()->error_message(), null, HttpHelper::STATUS_FORBIDDEN );
		}

		global $wpdb;

		$table_question = "{$wpdb->prefix}tutor_quiz_questions";
		$table_answer   = "{$wpdb->prefix}tutor_quiz_question_answers";

		$question = QueryHelper::get_row( $table_question, array( 'question_id' => $question_id ), 'question_id' );

		if ( ! $question ) {
			$this->json_response(
				__( 'Invalid question', 'tutor' ),
				null,
				HttpHelper::STATUS_BAD_REQUEST
			);
		}

		$question_type      = Input::post( 'question_type' );
		$answer_title       = Input::post( 'answer_title', '' );
		$image_id           = Input::post( 'image_id', 0, Input::TYPE_INT );
		$answer_view_format = Input::post( 'answer_view_format', '' );

		$answer_data = array(
			'belongs_question_id'   => $question_id,
			'belongs_question_type' => $question_type,
			'answer_title'          => $answer_title,
		);

		if ( ! $is_update ) {
			$answer_data['answer_order'] = QuizModel::get_next_answer_order( $question_id, $question_type );
		}

		$question_types = array(
			'single_choice',
			'multiple_choice',
			'ordering',
			'matching',
			'image_matching',
			'image_answering',
		);

		if ( in_array( $question_type, $question_types, true ) ) {
			$answer_data['image_id']           = $image_id;
			$answer_data['answer_view_format'] = $answer_view_format;

			if ( Input::has( 'matched_answer_title' ) ) {
				$answer_data['answer_two_gap_match'] = Input::post( 'matched_answer_title' );
			}
		} elseif ( 'fill_in_the_blank' === $question_type ) {
			$answer_data['answer_two_gap_match'] = Input::post( 'answer_two_gap_match' );
		}

		if ( $is_update ) {
			$wpdb->update( $table_answer, $answer_data, array( 'answer_id' => $answer_id ) );
		} else {
			$question_types[] = 'fill_in_the_blank';
			if ( ! in_array( $question_type, $question_types, true ) ) {
				$this->json_response( __( 'Invalid question type', 'tutor' ), null, HttpHelper::STATUS_BAD_REQUEST );
			}

			$answer_data['belongs_question_type'] = Input::post( 'question_type' );
			$wpdb->insert( $table_answer, $answer_data );
			$answer_id = $wpdb->insert_id;
		}

		if ( $is_update ) {
			$this->json_response(
				__( 'Question answer updated successfully', 'tutor' ),
				$answer_id
			);
		} else {
			$this->json_response(
				__( 'Question answer saved successfully', 'tutor' ),
				$answer_id,
				HttpHelper::STATUS_CREATED
			);
		}
	}

	/**
	 * Delete quiz question's answer
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function ajax_quiz_question_answer_delete() {
		if ( ! tutor_utils()->is_nonce_verified() ) {
			$this->json_response( tutor_utils()->error_message( 'nonce' ), null, HttpHelper::STATUS_BAD_REQUEST );
		}

		$answer_id = Input::post( 'answer_id', 0, Input::TYPE_INT );

		if ( ! tutor_utils()->can_user_manage( 'quiz_answer', $answer_id ) ) {
			$this->json_response( tutor_utils()->error_message(), null, HttpHelper::STATUS_FORBIDDEN );
		}

		global $wpdb;
		$wpdb->delete( $wpdb->prefix . 'tutor_quiz_question_answers', array( 'answer_id' => $answer_id ) );

		$this->json_response( __( 'Answer deleted successfully', 'tutor' ) );
	}

	/**
	 * Quiz question's answer shorting
	 *
	 * @since 1.0.0
	 * @since 3.0.0 refactor and response update.
	 *
	 * @return void
	 */
	public function ajax_quiz_question_answer_sorting() {
		if ( ! tutor_utils()->is_nonce_verified() ) {
			$this->json_response( tutor_utils()->error_message( 'nonce' ), null, HttpHelper::STATUS_BAD_REQUEST );
		}

		$question_id = Input::post( 'question_id', 0, Input::TYPE_INT );
		$answer_ids  = Input::post( 'sorted_answer_ids', array(), Input::TYPE_ARRAY );

		if ( ! tutor_utils()->can_user_manage( 'question', $question_id ) ) {
			$this->json_response( tutor_utils()->error_message(), null, HttpHelper::STATUS_FORBIDDEN );
		}

		global $wpdb;
		$i = 0;
		foreach ( $answer_ids as $answer_id ) {
			$i++;
			$wpdb->update(
				$wpdb->prefix . 'tutor_quiz_question_answers',
				array( 'answer_order' => $i ),
				array(
					'belongs_question_id' => $question_id,
					'answer_id'           => $answer_id,
				)
			);
		}

		$this->json_response( __( 'Question answer order successfully updated', 'tutor' ) );
	}

	/**
	 * Mark answer as correct
	 *
	 * @since 1.0.0
	 * @since 3.0.0 refactor and response updated.
	 *
	 * @return void
	 */
	public function ajax_mark_answer_as_correct() {
		if ( ! tutor_utils()->is_nonce_verified() ) {
			$this->json_response( tutor_utils()->error_message( 'nonce' ), null, HttpHelper::STATUS_BAD_REQUEST );
		}

		global $wpdb;

		$answer_id = Input::post( 'answer_id', 0, Input::TYPE_INT );

		if ( ! tutor_utils()->can_user_manage( 'quiz_answer', $answer_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied', 'tutor' ) ) );
		}

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

		$is_correct = Input::post( 'is_correct', 0, Input::TYPE_INT );

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
			array( 'is_correct' => $is_correct ),
			array( 'answer_id' => $answer_id )
		);

		$this->json_response(
			__( 'Answer mark as correct updated', 'tutor' ),
			$answer_id
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

		$post = get_post( $quiz_id ); //phpcs:ignore
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

		$attempt_id = Input::post( 'id', 0, Input::TYPE_INT );
		$attempt    = tutor_utils()->get_attempt( $attempt_id );
		if ( ! $attempt ) {
			wp_send_json_error( __( 'Invalid attempt ID', 'tutor' ) );
		}

		$user_id   = get_current_user_id();
		$course_id = $attempt->course_id;

		if ( tutor_utils()->can_user_edit_course( $user_id, $course_id ) ) {
			QuizModel::delete_quiz_attempt( $attempt_id );
			wp_send_json_success( __( 'Attempt deleted successfully!', 'tutor' ) );
		} else {
			wp_send_json_error( tutor_utils()->error_message() );
		}
	}

}
