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
		add_action( 'wp_ajax_tutor_quiz_timeout', array( $this, 'tutor_quiz_timeout' ) );

		// User take the quiz.
		add_action( 'template_redirect', array( $this, 'start_the_quiz' ) );
		add_action( 'template_redirect', array( $this, 'answering_quiz' ) );
		add_action( 'template_redirect', array( $this, 'finishing_quiz_attempt' ) );

		/**
		 * Instructor quiz review and feedback Ajax API.
		 */
		add_action( 'wp_ajax_review_quiz_answer', array( $this, 'review_quiz_answer' ) );
		add_action( 'wp_ajax_tutor_instructor_feedback', array( $this, 'tutor_instructor_feedback' ) );

		/**
		 * New quiz builder Ajax API.
		 */
		add_action( 'wp_ajax_tutor_quiz_details', array( $this, 'ajax_quiz_details' ) );
		add_action( 'wp_ajax_tutor_quiz_delete', array( $this, 'ajax_quiz_delete' ) );

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
			'randomize_question' => 0,
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
		$course_id       = tutor_utils()->avalue_dot( 'course_id', $attempt_details, 0 );
		$is_instructor   = tutor_utils()->is_instructor_of_this_course( get_current_user_id(), $course_id );
		if ( ! current_user_can( 'manage_options' ) && ! $is_instructor ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		if ( $attempt_info ) {
			//phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
			$unserialized = unserialize( $attempt_details->attempt_info );
			if ( is_array( $unserialized ) ) {
				$unserialized['instructor_feedback'] = $feedback;

				//phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
				$update = self::update_attempt_info( $attempt_details->attempt_id, serialize( $unserialized ) );
				if ( $update ) {
					do_action( 'tutor_quiz/attempt/submitted/feedback', $attempt_details->attempt_id );
					wp_send_json_success();
				} else {
					wp_send_json_error();
				}
			} else {
				wp_send_json_error( __( 'Invalid quiz info', 'tutor' ) );
			}
		}
		wp_send_json_error();
	}

	/**
	 * Start Quiz from here...
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function start_the_quiz() {
		if ( 'tutor_start_quiz' !== Input::post( 'tutor_action' ) ) {
			return;
		}

		tutor_utils()->checking_nonce();

		if ( ! is_user_logged_in() ) {
			die( esc_html__( 'Please sign in to do this operation', 'tutor' ) );
		}

		$user_id = get_current_user_id();
		$quiz_id = Input::post( 'quiz_id', 0, Input::TYPE_INT );
		$course  = CourseModel::get_course_by_quiz( $quiz_id );

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
		self::tutor_quiz_attempt_submit();

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
		if ( self::tutor_quiz_attempt_submit() ) {
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
	public static function tutor_quiz_attempt_submit() {
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

		if ( QuizModel::ATTEMPT_TIMEOUT === $attempt->attempt_status ) {
			return false;
		}

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
				function ( $id ) {
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
							function ( $id ) {
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

			$wpdb->update( $wpdb->tutor_quiz_attempts, $attempt_info, array( 'attempt_id' => $attempt_id ) );

			QuizModel::update_attempt_result( $attempt_id );
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
			$attempt_update_data = array();
			$answer_update_data  = array(
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

			if ( ! empty( $attempt_update_data ) ) {
				$wpdb->update( $wpdb->tutor_quiz_attempts, $attempt_update_data, array( 'attempt_id' => $attempt_id ) );
			}
		} elseif ( 'incorrect' === $mark_as ) {
			$attempt_update_data = array();
			$answer_update_data  = array(
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

			if ( ! empty( $attempt_update_data ) ) {
				$wpdb->update( $wpdb->tutor_quiz_attempts, $attempt_update_data, array( 'attempt_id' => $attempt_id ) );
			}
		}

		QuizModel::update_attempt_result( $attempt_id );

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

		$data = apply_filters( 'tutor_quiz_details_response', $data, $quiz_id );

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

	/**
	 * Get all quiz attempts for a user in a specific course.
	 *
	 * @since 3.8.1
	 *
	 * @param int $course_id The ID of the course.
	 * @param int $user_id The ID of the user.
	 *
	 * @return array Returns an array of quiz attempt objects with their answers, or an empty array on error.
	 */
	public function get_quiz_attempts_and_answers_by_course_id( int $course_id ): array {
		global $wpdb;

		$results = QueryHelper::get_all( $wpdb->tutor_quiz_attempts, array( 'course_id' => $course_id ), 'course_id', -1 );

		if ( empty( $results ) ) {
			return array();
		}

		return array_map(
			function ( $item ) {
				$item->quiz_attempt_answers = $this->get_quiz_attempt_answers_by_attempt_id( $item->attempt_id );
				return $item;
			},
			$results
		);
	}

	/**
	 * Get all quiz attempt answers for a specific quiz attempt.
	 *
	 * @since 3.8.1
	 *
	 * @param int $attempt_id The ID of the quiz attempt.
	 *
	 * @return array Returns an array of quiz attempt answers objects, or an empty array on error.
	 */
	private function get_quiz_attempt_answers_by_attempt_id( int $attempt_id ): array {
		global $wpdb;

		$results = QueryHelper::get_all( $wpdb->tutor_quiz_attempt_answers, array( 'quiz_attempt_id' => $attempt_id ), 'quiz_attempt_id', -1 );

		if ( empty( $results ) ) {
			return array();
		}

		return $results;
	}
}
