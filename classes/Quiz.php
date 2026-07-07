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

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\Button;
use Tutor\Components\ConfirmationModal;
use Tutor\Components\Modal;
use Tutor\Components\SvgIcon;
use Tutor\Components\Table;
use Tutor\Helpers\HttpHelper;
use Tutor\Helpers\QueryHelper;
use Tutor\Models\CourseModel;
use Tutor\Models\QuizModel;
use Tutor\Traits\JsonResponse;
use WP_Post;

/**
 * Manage quiz operations.
 *
 * @since 1.0.0
 */
class Quiz {
	use JsonResponse;

	/**
	 * Quiz post type
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	private $post_type;

	const META_QUIZ_OPTION = 'tutor_quiz_option';

	/**
	 * Quiz feedback mode: show result after the attempt.
	 *
	 * @since 4.0.0
	 */
	const QUIZ_FEEDBACK_MODE_REVEAL = 'reveal';

	/**
	 * Quiz feedback mode: reattempt quiz any number of times.
	 *
	 * @since 4.0.0
	 */
	const QUIZ_FEEDBACK_MODE_RETRY = 'retry';

	/**
	 * Quiz feedback mode: answers shown after quiz is finished.
	 *
	 * @since 4.0.0
	 */
	const QUIZ_FEEDBACK_MODE_DEFAULT = 'default';

	/**
	 * URL Query param
	 *
	 * @since 4.0.0
	 */
	const ACTION_VIEW_DETAILS = 'view_details';

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
	 * @since 4.0.0 $register_hooks param added
	 *
	 * @param bool $register_hooks To register hooks.
	 *
	 * @return void
	 */
	public function __construct( $register_hooks = true ) {
		$this->post_type = tutor()->quiz_post_type;
		$this->prepare_allowed_html();

		if ( ! $register_hooks ) {
			return;
		}

		add_action( 'wp_ajax_tutor_quiz_timeout', array( $this, 'tutor_quiz_timeout' ) );

		// User take the quiz.
		add_action( 'template_redirect', array( $this, 'start_the_quiz' ) );
		add_action( 'template_redirect', array( $this, 'answering_quiz' ) );
		add_action( 'template_redirect', array( $this, 'finishing_quiz_attempt' ) );

		/**
		 * Instructor quiz review and feedback Ajax API.
		 */
		add_action( 'wp_ajax_review_quiz_answer', array( $this, 'review_quiz_answer' ) );
		add_action( 'wp_ajax_tutor_review_quiz_answers', array( $this, 'review_quiz_answers' ) );
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

		/**
		 * Delete quiz attempt
		 *
		 * @since 2.1.0
		 */
		add_action( 'wp_ajax_tutor_attempt_delete', array( $this, 'attempt_delete' ) );

		add_action( 'tutor_quiz/answer/review/after', array( $this, 'do_auto_course_complete' ), 10, 3 );

		// Add quiz title as nav item & render single content on the learning area.
		add_action( "tutor_learning_area_nav_item_{$this->post_type}", array( $this, 'render_nav_item' ), 10, 2 );
		add_action( "tutor_single_content_{$this->post_type}", array( $this, 'render_single_content' ) );

		/**
		 * Slugs listed in tutor_quiz_templates_not_in_core have no file under wp-content/plugins/tutor/templates/.
		 * Without this, tutor_load_template() still runs from generic quiz templates and tutor_get_template()
		 * prints "The file you are trying to load does not exist…". Returning false here exits before that lookup.
		 * Add-ons ship their own files and load them outside this path (e.g. direct include from the add-on).
		 *
		 * @since 4.0.0
		 */
		add_filter( 'should_tutor_load_template', array( $this, 'skip_addon_only_question_partials' ), 5, 3 );
	}

	/**
	 * Skip loading templates that are not packaged with core Tutor LMS.
	 *
	 * @since 4.0.0
	 *
	 * @param bool   $load      Whether to load the template.
	 * @param string $template  Template name in dot notation.
	 * @param array  $variables Template variables.
	 *
	 * @return bool
	 */
	public function skip_addon_only_question_partials( $load, $template, $variables ) {
		$addons_only = apply_filters(
			'tutor_quiz_templates_not_in_core',
			array(
				'learning-area.quiz.questions.draw-image',
				'learning-area.quiz.questions.scale',
				'learning-area.quiz.questions.pin-image',
				'learning-area.quiz.questions.coordinates',
				'learning-area.quiz.questions.puzzle',
				'shared.components.quiz.attempt-details.questions.draw-image',
				'shared.components.quiz.attempt-details.questions.scale',
				'shared.components.quiz.attempt-details.questions.pin-image',
				'shared.components.quiz.attempt-details.questions.coordinates',
				'shared.components.quiz.attempt-details.questions.puzzle',
			),
			$template,
			$variables
		);

		if ( in_array( $template, $addons_only, true ) ) {
			return false;
		}

		return $load;
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
			'answers_reveal_duration'            => 5,
			'auto_start_delay'                   => 5,
			'enable_answer_reveal'               => '0',
			'enable_pagination'                  => '0',
			'hide_question_number_overview'      => 0,
			'hide_previous_button'               => '0',
			'hide_quiz_time_display'             => 0,
			'limit_attempts_allowed'             => '0',
			'max_questions_for_answer'           => 10,
			'open_ended_answer_characters_limit' => 500,
			'pass_is_required'                   => 0,
			'pagination_type'                    => 'shape',
			'passing_grade'                      => 80,
			'question_layout_view'               => '',
			'questions_order'                    => 'rand',
			'quiz_auto_start'                    => 0,
			'short_answer_characters_limit'      => 200,
		);

		return apply_filters( 'tutor_quiz_default_settings', $settings );
	}

	/**
	 * Normalize quiz settings.
	 *
	 * V4 stores reveal and pagination as explicit flags, but older quizzes may
	 * still contain legacy keys.
	 *
	 * @since 4.0.0
	 *
	 * @param array $settings Quiz settings.
	 *
	 * @return array
	 */
	public static function normalize_quiz_settings( array $settings ): array {
		$defaults                   = self::get_default_quiz_settings();
		$has_enable_pagination      = array_key_exists( 'enable_pagination', $settings );
		$has_enable_answer_reveal   = array_key_exists( 'enable_answer_reveal', $settings );
		$has_limit_attempts_allowed = array_key_exists( 'limit_attempts_allowed', $settings );

		$settings = wp_parse_args( $settings, $defaults );

		$settings['time_limit'] = wp_parse_args(
			is_array( $settings['time_limit'] ?? null ) ? $settings['time_limit'] : array(),
			$defaults['time_limit']
		);

		$question_layout_view = (string) ( $settings['question_layout_view'] ?? '' );
		$question_layout_view = '' !== $question_layout_view ? $question_layout_view : 'single_question';

		$supported_pagination_types = array( 'shape', 'radio', 'number' );
		$pagination_type            = $settings['pagination_type'] ?? $settings['question_pagination_style'] ?? 'shape';
		$pagination_type            = in_array( $pagination_type, $supported_pagination_types, true ) ? $pagination_type : 'shape';

		$enable_pagination = $has_enable_pagination
			? '1' === (string) $settings['enable_pagination']
			: 'question_pagination' === $question_layout_view;

		$enable_answer_reveal   = $has_enable_answer_reveal
			? '1' === (string) $settings['enable_answer_reveal']
			: self::QUIZ_FEEDBACK_MODE_REVEAL === (string) ( $settings['feedback_mode'] ?? '' );
		$limit_attempts_allowed = $has_limit_attempts_allowed
			? '1' === (string) $settings['limit_attempts_allowed']
			: self::QUIZ_FEEDBACK_MODE_RETRY === (string) ( $settings['feedback_mode'] ?? '' );

		if ( 'question_pagination' === $question_layout_view ) {
			$question_layout_view = 'single_question';
			$enable_pagination    = true;
		}

		if ( 'question_below_each_other' === $question_layout_view ) {
			$settings['hide_question_number_overview'] = '0';
			$enable_answer_reveal                      = false;
		}

		if ( $enable_pagination ) {
			$settings['hide_previous_button'] = '0';
		}

		$settings['question_layout_view']   = $question_layout_view;
		$settings['enable_pagination']      = $enable_pagination ? '1' : '0';
		$settings['pagination_type']        = $pagination_type;
		$settings['enable_answer_reveal']   = $enable_answer_reveal ? '1' : '0';
		$settings['limit_attempts_allowed'] = $limit_attempts_allowed ? '1' : '0';

		unset( $settings['feedback_mode'], $settings['question_pagination_style'] );

		return apply_filters( 'tutor_quiz_normalized_settings', $settings );
	}

	/**
	 * Determine if quiz retry is allowed for the current attempt count.
	 *
	 * @since 4.0.0
	 *
	 * @param mixed $limit_attempts_allowed If attempts are limited.
	 * @param int   $attempts_allowed Allowed attempts.
	 * @param int   $attempted_count Attempted count.
	 *
	 * @return bool
	 */
	public static function can_retry_quiz( $limit_attempts_allowed, int $attempts_allowed, int $attempted_count ): bool {
		$limit_attempts_allowed = '1' === (string) $limit_attempts_allowed;

		if ( ! $limit_attempts_allowed ) {
			return false;
		}

		return 0 === $attempts_allowed || $attempted_count < $attempts_allowed;
	}

	/**
	 * Get effective attempt count for quiz display/runtime.
	 *
	 * @since 4.0.0
	 *
	 * @param bool $limit_attempts_allowed If attempts are limited.
	 * @param int  $attempts_allowed Allowed attempts.
	 *
	 * @return int
	 */
	public static function get_effective_attempts_allowed( bool $limit_attempts_allowed, int $attempts_allowed ): int {
		return $limit_attempts_allowed ? $attempts_allowed : 1;
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

		$tutor_quiz_option = (array) tutor_utils()->get_quiz_option( $quiz_id );

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
		if ( 'tutor_answering_quiz_question' !== Input::post( 'tutor_action' ) ) {
			return;
		}

		self::tutor_quiz_attempt_submit();

		wp_safe_redirect( get_the_permalink() );
		die();
	}

	/**
	 * Quiz abandon submission handler
	 *
	 * @since 1.9.6
	 *
	 * @return void JSON response
	 */
	public function tutor_quiz_abandon() {
		if ( 'tutor_answering_quiz_question' !== Input::post( 'tutor_action' ) ) {
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
	 * Validate quiz attempt
	 *
	 * @since 3.9.13
	 *
	 * @param int $attempt_id attempt id.
	 * @param int $user_id user id.
	 *
	 * @return object|false attempt object if valid otherwise false
	 */
	private static function validate_attempt( $attempt_id, $user_id ) {
		$attempt = tutor_utils()->get_attempt( $attempt_id );

		if ( ! $attempt || ! is_object( $attempt ) || (int) $attempt->user_id !== (int) $user_id ) {
			return false;
		}

		return $attempt;
	}

	/**
	 * This is  a unified method for handling normal quiz submit or abandon submit
	 * It will handle ajax or normal form submit and can be used with different hooks
	 *
	 * @since 1.9.6
	 *
	 * @return bool true if quiz attempt submit successfully otherwise false
	 */
	public static function tutor_quiz_attempt_submit() {
		if ( ! is_user_logged_in() ) {
			die( 'Please sign in to do this operation' );
		}

		tutor_utils()->checking_nonce();

		$user_id    = get_current_user_id();
		$attempt_id = Input::post( 'attempt_id', 0, Input::TYPE_INT );
		$attempt    = self::validate_attempt( $attempt_id, $user_id );

		if ( ! $attempt ) {
			die( 'Operation not allowed, attempt not found or permission denied' );
		}

		if ( QuizModel::ATTEMPT_TIMEOUT === $attempt->attempt_status ) {
			return false;
		}

		// Sanitize data by helper method.
		$attempt_answers = isset( $_POST['attempt'] ) ? tutor_sanitize_data( $_POST['attempt'] ) : false; //phpcs:ignore
		$attempt_answers = is_array( $attempt_answers ) ? $attempt_answers : array();

		self::manage_attempt_answers( $attempt_answers, $attempt, $attempt_id, $attempt->course_id, $user_id );
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
		if ( ! is_array( $attempt_answers ) || ! self::validate_attempt( $attempt_id, $user_id ) ) {
			return;
		}

		global $wpdb;
		// Before hook.
		do_action( 'tutor_quiz/attempt_analysing/before', $attempt_id );

		// Single quiz can have multiple question. So multiple answer should be saved.
		foreach ( $attempt_answers as $posted_attempt_id => $attempt_answer ) {
			if ( ! self::validate_attempt( $posted_attempt_id, $user_id ) ) {
				continue;
			}

			// Get total marks of all question comes.
			$question_ids = tutor_utils()->avalue_dot( 'quiz_question_ids', $attempt_answer );
			$question_ids = array_filter( $question_ids, fn ( $id ) => is_numeric( $id ) && intval( $id ) > 0 );

			// Calculate and set the total marks in attempt table for this question.
			if ( tutor_utils()->count( $question_ids ) ) {
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
						$given_answer = array_filter( $given_answer, fn ( $id ) => is_numeric( $id ) && intval( $id ) > 0 );

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
						$given_answer = is_array( $answers ) ? array_map( 'sanitize_text_field', $answers ) : array( sanitize_text_field( $answers ) );
						$given_answer = maybe_serialize( $given_answer );

						/**
						 * Compare answer's by making both case-insensitive.
						 */
						if ( strtolower( $given_answer ) === strtolower( $gap_answer ) ) {
							$is_answer_was_correct = true;
						}
					} elseif ( 'open_ended' === $question_type || 'short_answer' === $question_type ) {
						$review_required = true;
						$given_answer    = wp_kses_post( $answers );

					} elseif ( 'ordering' === $question_type || 'matching' === $question_type || 'image_matching' === $question_type ) {
						$answers = (array) tutor_utils()->avalue_dot( 'answers', $answers );

						$given_answer = (array) array_map( 'sanitize_text_field', $answers );
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
					} else {
						$custom_answer_data    = array(
							'given_answer'          => $given_answer,
							'is_answer_was_correct' => $is_answer_was_correct,
						);
						$custom_answer_data    = apply_filters( 'tutor_quiz_process_custom_question_answer', $custom_answer_data, $question_type, $answers, $question, $question_id, $attempt_id );
						$given_answer          = $custom_answer_data['given_answer'];
						$is_answer_was_correct = $custom_answer_data['is_answer_was_correct'];
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

					$answers_data = apply_filters( 'tutor_filter_quiz_answer_data', $answers_data, $question_id, $question_type, $user_id, $attempt_id );

					// Filter total marks after grading. Runs after answers_data is built and graded,
					// so add-ons (e.g. H5P, pin_image, draw_image) can add their achieved marks.
					$total_marks = apply_filters( 'tutor_filter_quiz_total_marks', $total_marks, $question_id, $question_type, $user_id, $attempt_id, $answers_data );

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

		$attempt_id        = Input::post( 'attempt_id', 0, Input::TYPE_INT );
		$context           = Input::post( 'context' );
		$attempt_answer_id = Input::post( 'attempt_answer_id', 0, Input::TYPE_INT );
		$question_id       = Input::post( 'question_id', 0, Input::TYPE_INT );
		$mark_as           = Input::post( 'mark_as' );

		if ( ! tutor_utils()->can_user_manage( 'attempt', $attempt_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied', 'tutor' ) ) );
		}

		if ( $attempt_answer_id && ! tutor_utils()->can_user_manage( 'attempt_answer', $attempt_answer_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied', 'tutor' ) ) );
		}

		$attempt_answer = $this->resolve_attempt_answer_for_review( $attempt_id, $attempt_answer_id, $question_id );
		$review_data    = $attempt_answer ? $this->apply_quiz_answer_review( $attempt_id, $attempt_answer, $mark_as ) : null;

		if ( ! $review_data ) {
			wp_send_json_error( array( 'message' => __( 'Review update failed', 'tutor' ) ) );
		}

		QuizModel::update_attempt_result( $attempt_id );

		ob_start();
		tutor_load_template_from_custom_path(
			tutor()->path . '/views/quiz/attempt-details.php',
			array(
				'attempt_id' => $attempt_id,
				'user_id'    => $review_data['student_id'],
				'context'    => $context,
				'back_url'   => Input::post( 'back_url' ),
			)
		);
		wp_send_json_success( array( 'html' => ob_get_clean() ) );
	}

	/**
	 * Review quiz answers in bulk for v4 dashboard flow.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function review_quiz_answers() {
		tutor_utils()->checking_nonce();

		$attempt_id      = Input::post( 'attempt_id', 0, Input::TYPE_INT );
		$review_statuses = Input::post( 'review_statuses', array(), Input::TYPE_ARRAY );

		$this->review_quiz_answers_bulk( $attempt_id, $review_statuses );
	}

	/**
	 * Review quiz answers in bulk for v4 dashboard flow.
	 *
	 * @since 4.0.0
	 *
	 * @param int   $attempt_id Attempt ID.
	 * @param array $review_statuses Review statuses keyed by question ID.
	 *
	 * @return void
	 */
	private function review_quiz_answers_bulk( int $attempt_id, array $review_statuses ) {
		if ( ! tutor_utils()->can_user_manage( 'attempt', $attempt_id ) ) {
			$this->response_fail( __( 'Access Denied', 'tutor' ), 403 );
		}

		$attempt_answers        = QuizModel::get_quiz_answers_by_attempt_id( $attempt_id );
		$answers_by_question_id = array();

		if ( is_array( $attempt_answers ) ) {
			foreach ( $attempt_answers as $attempt_answer ) {
				$question_id = (int) ( $attempt_answer->question_id ?? 0 );

				if ( $question_id > 0 ) {
					$answers_by_question_id[ $question_id ] = $attempt_answer;
				}
			}
		}

		foreach ( $review_statuses as $question_id => $mark_as ) {
			$question_id = (int) $question_id;
			$mark_as     = (string) $mark_as;

			if ( ! in_array( $mark_as, array( 'correct', 'incorrect' ), true ) ) {
				continue;
			}

			$attempt_answer = $answers_by_question_id[ $question_id ] ?? null;

			if ( ! $attempt_answer ) {
				$attempt_answer = $this->resolve_attempt_answer_for_review( $attempt_id, 0, $question_id );
			}

			if ( ! $attempt_answer ) {
				continue;
			}

			$this->apply_quiz_answer_review( $attempt_id, $attempt_answer, $mark_as );
		}

		QuizModel::update_attempt_result( $attempt_id );

		$this->response_success( __( 'Review updated successfully', 'tutor' ) );
	}

	/**
	 * Get attempt answer record by ID.
	 *
	 * @since 4.0.0
	 *
	 * @param int $attempt_answer_id Attempt answer ID.
	 *
	 * @return object|null
	 */
	public static function get_attempt_answer( int $attempt_answer_id ) {
		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT *
				FROM {$wpdb->prefix}tutor_quiz_attempt_answers
				WHERE attempt_answer_id = %d",
				$attempt_answer_id
			)
		);
	}

	/**
	 * Get attempt answer by attempt and question IDs.
	 *
	 * @since 4.0.0
	 *
	 * @param int $attempt_id  Attempt ID.
	 * @param int $question_id Question ID.
	 *
	 * @return object|null
	 */
	private function get_attempt_answer_by_attempt_and_question( int $attempt_id, int $question_id ) {
		if ( $attempt_id <= 0 || $question_id <= 0 ) {
			return null;
		}

		return QueryHelper::get_row(
			'tutor_quiz_attempt_answers',
			array(
				'quiz_attempt_id' => $attempt_id,
				'question_id'     => $question_id,
			),
			'attempt_answer_id',
			'ASC'
		);
	}

	/**
	 * Create a placeholder attempt answer for a skipped question.
	 *
	 * @since 4.0.0
	 *
	 * @param int $attempt_id  Attempt ID.
	 * @param int $question_id Question ID.
	 *
	 * @return object|null
	 */
	private function create_skipped_attempt_answer( int $attempt_id, int $question_id ) {
		$attempt = tutor_utils()->get_attempt( $attempt_id );
		if ( ! $attempt ) {
			return null;
		}

		$question = QuizModel::get_quiz_question_by_id( $question_id );
		if ( ! $question || (int) $question->quiz_id !== (int) $attempt->quiz_id ) {
			return null;
		}

		$attempt_answer = $this->get_attempt_answer_by_attempt_and_question( $attempt_id, $question_id );
		if ( $attempt_answer ) {
			return $attempt_answer;
		}

		try {
			$inserted_id = QueryHelper::insert(
				'tutor_quiz_attempt_answers',
				array(
					'user_id'         => (int) $attempt->user_id,
					'quiz_id'         => (int) $attempt->quiz_id,
					'question_id'     => $question_id,
					'quiz_attempt_id' => $attempt_id,
					'given_answer'    => '',
					'question_mark'   => $question->question_mark,
					'achieved_mark'   => 0,
					'minus_mark'      => 0,
					'is_correct'      => 0,
				)
			);
		} catch ( \Exception $exception ) {
			return null;
		}

		if ( $inserted_id <= 0 ) {
			return null;
		}

		return self::get_attempt_answer( $inserted_id );
	}

	/**
	 * Resolve the attempt answer used for instructor review.
	 *
	 * @since 4.0.0
	 *
	 * @param int $attempt_id        Attempt ID.
	 * @param int $attempt_answer_id Attempt answer ID.
	 * @param int $question_id       Question ID.
	 *
	 * @return object|null
	 */
	private function resolve_attempt_answer_for_review( int $attempt_id, int $attempt_answer_id = 0, int $question_id = 0 ) {
		$attempt_answer = $attempt_answer_id ? self::get_attempt_answer( $attempt_answer_id ) : null;

		if ( $attempt_answer ) {
			return $attempt_answer;
		}

		if ( $question_id <= 0 ) {
			return null;
		}

		$attempt_answer = $this->get_attempt_answer_by_attempt_and_question( $attempt_id, $question_id );

		if ( $attempt_answer ) {
			return $attempt_answer;
		}

		return $this->create_skipped_attempt_answer( $attempt_id, $question_id );
	}

	/**
	 * Apply quiz answer review update.
	 *
	 * @since 4.0.0
	 *
	 * @param int    $attempt_id Attempt ID.
	 * @param object $attempt_answer Attempt answer row.
	 * @param string $mark_as Review status.
	 *
	 * @return array|null
	 */
	private function apply_quiz_answer_review( int $attempt_id, $attempt_answer, string $mark_as ) {
		global $wpdb;

		if ( ! $attempt_answer || ! in_array( $mark_as, array( 'correct', 'incorrect' ), true ) ) {
			return null;
		}

		$attempt = tutor_utils()->get_attempt( $attempt_id );

		if ( ! $attempt ) {
			return null;
		}

		$attempt_answer_id = (int) $attempt_answer->attempt_answer_id;
		$question          = QuizModel::get_quiz_question_by_id( $attempt_answer->question_id );
		$course_id         = (int) $attempt->course_id;
		$student_id        = (int) $attempt->user_id;
		$previous_ans      = $attempt_answer->is_correct;

		do_action( 'tutor_quiz_review_answer_before', $attempt_answer_id, $attempt_id, $mark_as );

		$mark_as = apply_filters( 'tutor_quiz_review_mark_as', $mark_as, $attempt_answer_id, $attempt_id, $question );

		if ( 'correct' === $mark_as ) {
			$attempt_update_data = array();
			$answer_update_data  = array(
				'achieved_mark' => $attempt_answer->question_mark,
				'is_correct'    => 1,
			);

			$wpdb->update( $wpdb->prefix . 'tutor_quiz_attempt_answers', $answer_update_data, array( 'attempt_answer_id' => $attempt_answer_id ) );

			if ( 0 == $previous_ans || null == $previous_ans ) {
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
				$attempt_update_data = array(
					'earned_marks'         => $attempt->earned_marks - $attempt_answer->question_mark,
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
		}

		do_action( 'tutor_quiz_review_answer_after', $attempt_answer_id, $attempt_id, $mark_as );
		do_action( 'tutor_quiz/answer/review/after', $attempt_answer_id, $course_id, $student_id );

		return array(
			'course_id'  => $course_id,
			'student_id' => $student_id,
		);
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

		// Collect file paths from all question types that store files before deleting rows (files deleted after DB for safety).
		$attempts_for_quiz  = QueryHelper::get_all( 'tutor_quiz_attempts', array( 'quiz_id' => $quiz_id ), 'attempt_id', -1 );
		$attempt_file_paths = array();
		if ( ! empty( $attempts_for_quiz ) ) {
			$attempt_ids        = array_map(
				function ( $row ) {
					return (int) $row->attempt_id;
				},
				$attempts_for_quiz
			);
			$attempt_file_paths = apply_filters( 'tutor_quiz/attempt_file_paths_for_deletion', array(), $attempt_ids );
			$attempt_file_paths = is_array( $attempt_file_paths ) ? array_values( array_filter( array_unique( $attempt_file_paths ) ) ) : array();
		}

		$wpdb->delete( $wpdb->prefix . 'tutor_quiz_attempts', array( 'quiz_id' => $quiz_id ) );
		$wpdb->delete( $wpdb->prefix . 'tutor_quiz_attempt_answers', array( 'quiz_id' => $quiz_id ) );

		QuizModel::delete_files_by_paths( $attempt_file_paths );

		// Collect instructor file paths before deleting question data (e.g. draw_image / pin_image masks).
		/**
		 * Filter to get file paths for quiz deletion.
		 * Pro and other add-ons register their question types via this filter.
		 *
		 * @param string[] $file_paths Paths collected so far.
		 * @param int      $quiz_id   Quiz post ID.
		 */
		$quiz_file_paths = apply_filters( 'tutor_quiz_quiz_file_paths_for_deletion', array(), $quiz_id );

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

		QuizModel::delete_files_by_paths( $quiz_file_paths );

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

	/**
	 * Render quiz title as nav item to show on the learning area
	 *
	 * @since 4.0.0
	 *
	 * @param WP_Post $quiz Quiz post object.
	 * @param bool    $can_access Can user access this content.
	 *
	 * @return void
	 */
	public function render_nav_item( WP_Post $quiz, bool $can_access ): void {
		tutor_load_template(
			'learning-area.quiz.nav-item',
			array(
				'quiz'       => $quiz,
				'can_access' => $can_access,
			)
		);
	}

	/**
	 * Render content for the a single quiz
	 *
	 * @since 4.0.0
	 *
	 * @param WP_Post $quiz Quiz post object.
	 *
	 * @return void
	 */
	public function render_single_content( WP_Post $quiz ): void {
		tutor_load_template(
			'learning-area.quiz.content',
			array(
				'quiz' => $quiz,
			)
		);
	}

	/**
	 * Render quiz summary
	 *
	 * @since 4.0.0
	 *
	 * @param int    $total_questions Total questions.
	 * @param string $quiz_item_readable Readable time.
	 * @param int    $total_marks Total Marks.
	 * @param string $passing_grade Passing grade.
	 * @param string $earned_marks Earned marks.
	 * @param string $attempts_allowed Total Attempts allowed.
	 *
	 * @return void
	 */
	public static function render_quiz_summary( $total_questions, $quiz_item_readable, $total_marks, $passing_grade, $earned_marks, $attempts_allowed ) {
		$quiz_summary = array(
			array(
				'columns' => array(
					array(
						'content' => '<div class="tutor-flex tutor-gap-3 tutor-items-center">
							' . SvgIcon::make()->name( Icon::QUESTION_CIRCLE )->size( 20 )->get() . __( 'Questions', 'tutor' ) . '
						</div>',
					),
					array( 'content' => $total_questions ),
				),
			),
		);

		if ( ! empty( $quiz_item_readable ) ) {
			$quiz_summary[] = array(
				'columns' => array(
					array(
						'content' => '<div class="tutor-flex tutor-gap-3 tutor-items-center">
							' . SvgIcon::make()->name( Icon::TIME )->size( 20 )->get() . __( 'Quiz Time', 'tutor' ) . '
						</div>',
					),
					array( 'content' => $quiz_item_readable ),
				),
			);
		}

		$quiz_summary[] = array(
			'columns' => array(
				array(
					'content' => '<div class="tutor-flex tutor-gap-3 tutor-items-center">
						' . SvgIcon::make()->name( Icon::PRIME_CHECK_CIRCLE )->size( 20 )->get() . __( 'Total Marks', 'tutor' ) . '
					</div>',
				),
				array( 'content' => $total_marks ),
			),
		);

		$quiz_summary[] = array(
			'columns' => array(
				array(
					'content' => '<div class="tutor-flex tutor-gap-3 tutor-items-center">
						' . SvgIcon::make()->name( Icon::PASSED )->size( 20 )->get() . __( 'Passing Grade', 'tutor' ) . '
					</div>',
				),
				array( 'content' => $passing_grade . '%' ),
			),
		);

		if ( $earned_marks ) {
			$quiz_summary[] = array(
				'columns' => array(
					array(
						'content' => '<div class="tutor-flex tutor-gap-3 tutor-items-center">
						' . SvgIcon::make()->name( Icon::STAR )->size( 20 )->get() . __( 'Earned Grade', 'tutor' ) . '
					</div>',
					),
					array( 'content' => $earned_marks . '%' ),
				),
			);
		}

		if ( 1 !== $attempts_allowed ) {
			$quiz_summary[] = array(
				'columns' => array(
					array(
						'content' => '<div class="tutor-flex tutor-gap-3 tutor-items-center">
							' . SvgIcon::make()->name( Icon::TARGET )->size( 20 )->get() . __( 'Total Attempts', 'tutor' ) . '
						</div>',
					),
					array( 'content' =>  0 === $attempts_allowed ? __( 'No Limit', 'tutor' ) : $attempts_allowed ),
				),
			);
		}

		Table::make()->contents( $quiz_summary )->render();
	}

	/**
	 * Render quiz attempts
	 *
	 * @since 4.0.0
	 *
	 * @param int $quiz_id Quiz ID.
	 *
	 * @return void
	 */
	public static function render_quiz_attempts( $quiz_id ) {
		$quiz_id = tutor_utils()->get_post_id( $quiz_id );
		if ( ! $quiz_id ) {
			return;
		}

		$user_id    = get_current_user_id();
		$quiz_model = new QuizModel();
		$attempts   = $quiz_model->quiz_attempts( $quiz_id, $user_id );

		if ( empty( $attempts ) ) {
			return;
		}

		$attempts_list = QuizModel::format_quiz_attempts( $attempts, '' );

		if ( empty( $attempts_list ) ) {
			return;
		}

		$attempts_count   = count( $attempts_list );
		$quiz_attempt_obj = new Quiz_Attempts_List( false );
		?>
			<div class="tutor-quiz-attempts tutor-border tutor-rounded-2xl">
				<div class="tutor-quiz-attempts-header">
					<div class="tutor-quiz-attempts-header-item">
						<?php esc_html_e( 'Attempts', 'tutor' ); ?>
					</div>
					<div class="tutor-quiz-attempts-header-item">
						<?php esc_html_e( 'Marks', 'tutor' ); ?>
					</div>
					<div class="tutor-quiz-attempts-header-item">
						<?php esc_html_e( 'Time', 'tutor' ); ?>
					</div>
					<div class="tutor-quiz-attempts-header-item">
						<?php esc_html_e( 'Result', 'tutor' ); ?>
					</div>
				</div>

				<div class="tutor-quiz-attempts-list">
					<?php
					foreach ( $attempts_list as $index => $attempt ) {
						$attempt_number = $attempts_count - $index;
						?>
						<div class="tutor-quiz-attempts-item-wrapper">
							<?php
							tutor_load_template(
								'shared.components.student-quiz-attempt-row',
								array(
									'attempt'          => $attempt,
									'attempt_number'   => $attempt_number,
									'quiz_id'          => $attempt['quiz_id'] ?? 0,
									'course_id'        => $attempt['course_id'] ?? 0,
									'quiz_attempt_obj' => $quiz_attempt_obj,
									'is_previous'      => false,
									'is_learning_area' => true,
								)
							);
							?>
						</div>
						<?php
					}
					?>
				</div>
			</div>
		<?php
	}

	/**
	 * Render quiz actions
	 *
	 * @since 4.0.0
	 *
	 * @param int $quiz_id Quiz ID.
	 *
	 * @return void
	 */
	public static function render_quiz_actions( $quiz_id ) {
		$quiz_id = tutor_utils()->get_post_id( $quiz_id );
		if ( ! $quiz_id ) {
			return;
		}

		$quiz_settings          = tutor_utils()->get_quiz_option( $quiz_id );
		$user_id                = get_current_user_id();
		$quiz_model             = new QuizModel();
		$attempts               = $quiz_model->quiz_attempts( $quiz_id, $user_id );
		$attempted_count        = (int) tutor_utils()->count( $attempts );
		$limit_attempts_allowed = '1' === (string) ( $quiz_settings['limit_attempts_allowed'] ?? '0' );
		$attempts_allowed       = (int) ( $quiz_settings['attempts_allowed'] ?? 0 );
		$can_start_quiz         = 0 === $attempted_count || self::can_retry_quiz( $limit_attempts_allowed, $attempts_allowed, $attempted_count );
		$quiz_auto_start        = $quiz_settings['quiz_auto_start'] ?? 0;
		$auto_start_delay       = (int) ( $quiz_settings['auto_start_delay'] ?? 5 );
		$should_auto_start      = 1 === (int) $quiz_auto_start && 0 === (int) $attempted_count;

		global $tutor_current_post, $tutor_course_id;
		$current_content_id  = $tutor_current_post ? $tutor_current_post->ID : $quiz_id;
		$course_id           = $tutor_course_id ? $tutor_course_id : tutor_utils()->get_course_id_by_subcontent( $current_content_id );
		$contents            = tutor_utils()->get_course_prev_next_contents_by_id( $current_content_id );
		$next_id             = $contents ? $contents->next_id : 0;
		$skip_url            = get_the_permalink( $next_id ? $next_id : $course_id );
		$skip_modal_id       = 'tutor-quiz-skip-to-next';
		$auto_start_modal_id = 'tutor-quiz-autostart-modal';
		$retry_modal_id      = 'tutor-quiz-retry-modal-' . $quiz_id;
		$show_retry_modal    = $attempted_count > 0;

		$can_skip_quiz  = ( 0 === $attempted_count );
		$show_continue  = ( $attempted_count > 0 && $next_id );
		$has_any_action = $can_skip_quiz || $show_continue || $can_start_quiz;

		if ( ! $has_any_action ) {
			return;
		}
		?>
		<div class="tutor-learning-area-footer">
			<?php
			if ( $can_skip_quiz ) {
				Button::make()
					->label( __( 'Skip Quiz', 'tutor' ) )
					->variant( Variant::GHOST )
					->attr( '@click', "TutorCore.modal.showModal('$skip_modal_id')" )
					->render();

				$skip_modal_confirm_button = Button::make()
					->tag( 'a' )
					->label( __( 'Yes, Skip This', 'tutor' ) )
					->variant( Variant::DESTRUCTIVE )
					->size( Size::SMALL )
					->attr( 'href', esc_url( $skip_url ) )
					->get();

				$skip_modal_cancel_button = Button::make()
					->label( __( 'Cancel', 'tutor' ) )
					->variant( Variant::SECONDARY )
					->size( Size::SMALL )
					->attr( '@click', "TutorCore.modal.closeModal('$skip_modal_id')" )
					->get();

				ConfirmationModal::make()
					->id( $skip_modal_id )
					->icon( tutor_utils()->get_themed_svg( 'images/illustrations/warning.svg' ), 80, 80, ConfirmationModal::ICON_TYPE_HTML )
					->title( __( 'Do You Want to Skip This Quiz?', 'tutor' ) )
					->message( __( 'Are you sure you want to skip this quiz? Please confirm your choice.', 'tutor' ) )
					->confirm_button( $skip_modal_confirm_button )
					->cancel_button( $skip_modal_cancel_button )
					->render();
			}
			?>

			<?php if ( $can_start_quiz ) : ?>
				<?php if ( $show_retry_modal ) : ?>
					<?php
					Button::make()
						->label( __( 'Retry Quiz', 'tutor' ) )
						->variant( $show_continue ? Variant::GHOST : Variant::PRIMARY )
						->attr( 'type', 'button' )
						->attr(
							'@click',
							sprintf(
								'TutorCore.modal.showModal("%s", { data: %s });',
								$retry_modal_id,
								wp_json_encode(
									array(
										'quizID'      => $quiz_id,
										'redirectURL' => get_post_permalink( $quiz_id ),
									)
								)
							)
						)
						->render();
					?>
				<?php else : ?>
				<form
					x-data="tutorQuizAutoStart({
						quizID: <?php echo esc_attr( $quiz_id ); ?>,
						autoStart: <?php echo $should_auto_start ? 'true' : 'false'; ?>,
						autoStartModalId: '<?php echo esc_attr( $auto_start_modal_id ); ?>',
						countdownSeconds: <?php echo esc_attr( $auto_start_delay ); ?>,
					})"
					@submit.prevent="handleStartQuiz()"
				>
					<?php
					Button::make()
						->label( __( 'Start Quiz', 'tutor' ) )
						->attr( 'x-bind:disabled', 'startQuizMutation?.isPending' )
						->attr( ':class', "{ 'tutor-btn-loading': startQuizMutation?.isPending }" )
						->render();
					?>
				</form>
				<?php endif; ?>
			<?php endif; ?>

			<?php
			if ( $show_continue ) {
				Button::make()
				->tag( 'a' )
				->label( __( 'Continue Lesson', 'tutor' ) )
				->attr( 'href', esc_url( get_the_permalink( $next_id ) ) )
				->render();
			}
			?>
		</div>

		<?php if ( $show_retry_modal ) : ?>
		<div x-data="tutorQuizRetryAttempt()">
			<?php
			ConfirmationModal::make()
				->id( $retry_modal_id )
				->title( __( 'Retry Quiz?', 'tutor' ) )
				->icon( tutor_utils()->get_themed_svg( 'images/illustrations/quiz-retry.svg' ), 80, 80, ConfirmationModal::ICON_TYPE_HTML )
				->message( __( 'Retrying this quiz will reset your current attempt. Your answers and score from this attempt will be lost.', 'tutor' ) )
				->confirm_handler( 'retryMutation?.mutate({...payload?.data})' )
				->confirm_text( __( 'Retry Quiz', 'tutor' ) )
				->mutation_state( 'retryMutation' )
				->render();
			?>
		</div>
		<?php endif; ?>

		<?php
		Modal::make()
			->id( $auto_start_modal_id )
			->closeable( false )
			->width( '268px' )
			->template(
				tutor()->path . 'templates/learning-area/quiz/modals/auto-start.php',
				array(
					'countdown_seconds' => $auto_start_delay,
					'modal_id'          => $auto_start_modal_id,
				)
			)
			->render();
		?>
		<?php
	}

	/**
	 * Render individual question template
	 *
	 * @since 4.0.0
	 *
	 * @param object $question Question data.
	 * @param int    $index Question index.
	 *
	 * @return void
	 */
	public static function render_question( $question, $index = 0 ) {
		$question_settings = maybe_unserialize( $question->question_settings );
		$question_type     = $question->question_type;
		$rand_choice       = ! empty( $question_settings['randomize_question'] )
			&& '1' === $question_settings['randomize_question'];

		// Normalize question type + settings.
		switch ( $question->question_type ) {
			case 'short_answer':
				$question->question_type = 'open_ended';
				break;

			case 'single_choice':
				$question->question_type                          = 'multiple_choice';
				$question_settings['has_multiple_correct_answer'] = '0';
				break;

			case 'image_matching':
				$question->question_type                = 'matching';
				$question_settings['is_image_matching'] = '1';
				break;
		}

		$template = str_replace( '_', '-', $question->question_type );
		$answers  = self::prepare_question_answers( (int) $question->question_id, $question_type, $rand_choice );

		$question->index                       = $index;
		$question->question_settings           = $question_settings;
		$question->question_answers            = $answers['question_answers'];
		$question->question_randomized_answers = $answers['question_randomized_answers'];

		tutor_load_template(
			'learning-area.quiz.question',
			array(
				'question'          => $question,
				'question_settings' => $question_settings,
				'question_type'     => $template,
			)
		);
	}

	/**
	 * Get question answers prepared for render.
	 *
	 * @since 4.0.0
	 *
	 * @param int    $question_id  Question ID.
	 * @param string $question_type Original question type.
	 * @param bool   $rand_choice  Whether randomized choices are enabled.
	 *
	 * @return array{question_answers: array, question_randomized_answers: array}
	 */
	private static function prepare_question_answers( int $question_id, string $question_type, bool $rand_choice ): array {
		$question_answers            = QuizModel::get_answers_by_quiz_question( $question_id );
		$question_randomized_answers = array();

		// Ordering questions always use a randomized answer list.
		// Matching and image matching keep the drop-zone items ordered and only shuffle the draggable choices.

		if ( 'ordering' === $question_type ) {
			$question_answers = QuizModel::get_answers_by_quiz_question( $question_id, true );
		} elseif ( 'matching' === $question_type || 'image_matching' === $question_type ) {
			$question_randomized_answers = QuizModel::get_answers_by_quiz_question( $question_id, $rand_choice );
		} elseif ( $rand_choice ) {
			$question_answers = QuizModel::get_answers_by_quiz_question( $question_id, true );
		}

		$question_answers            = array_map(
			static fn( $answer ) => (array) $answer,
			$question_answers
		);
		$question_randomized_answers = array_map(
			static fn( $answer ) => (array) $answer,
			$question_randomized_answers
		);

		return array(
			'question_answers'            => $question_answers,
			'question_randomized_answers' => $question_randomized_answers,
		);
	}

	/**
	 * Renders the assignment status icon for the learning area navigation.
	 *
	 * @since 4.0.0
	 *
	 * @param WP_Post $quiz The assignment post object.
	 * @param bool    $can_access Whether the current user can access the assignment.
	 * @param int     $tutor_current_content_id Current content id.
	 *
	 * @return void
	 */
	public static function render_sidebar_nav( WP_Post $quiz, $can_access, $tutor_current_content_id ) {
		$quiz_title = $quiz->post_title;

		$active_class   = $tutor_current_content_id === $quiz->ID ? 'active' : '';
		$disabled_class = $can_access ? '' : 'disabled';

		$quiz_status = '';
		$icon_name   = Icon::QUIZ_2;
		if ( ! $can_access ) {
			$icon_name = Icon::LOCK_STROKE_2;
		} else {
			$last_attempt  = ( new QuizModel() )->get_first_or_last_attempt( $quiz->ID );
			$attempt_ended = is_object( $last_attempt ) && QuizModel::ATTEMPT_STARTED !== $last_attempt->attempt_status;

			$quiz_result = QuizModel::get_quiz_result( $quiz->ID );
			if ( $attempt_ended && QuizModel::ATTEMPT_STARTED !== $last_attempt->attempt_status ) {
				if ( QuizModel::RESULT_FAIL === $quiz_result ) {
					$icon_name   = Icon::CROSS_COLORIZE;
					$quiz_status = QuizModel::RESULT_FAIL;
				} elseif ( QuizModel::RESULT_PENDING === $quiz_result ) {
					$icon_name   = Icon::INFO_COLORIZE;
					$quiz_status = QuizModel::RESULT_PENDING;
				} elseif ( QuizModel::RESULT_PASS === $quiz_result ) {
					$icon_name = Icon::COMPLETED_COLORIZE;
				}
			}
		}
		?>

		<a
			href="<?php echo esc_url( $can_access ? get_permalink( $quiz->ID ) : '#' ); ?>" 
			title="<?php echo esc_attr( $quiz_title ); ?>"
			class="<?php echo esc_html( sprintf( 'tutor-learning-nav-item %s %s %s', $active_class, $disabled_class, $quiz_status ) ); ?>"
			<?php echo ! $can_access ? 'aria-disabled="true"' : ''; ?>
		>
			<?php SvgIcon::make()->name( $icon_name )->size( 20 )->render(); ?>
			<div class="tutor-overflow-hidden">
				<div class="tutor-truncate"><?php echo esc_html( $quiz_title ); ?></div>
				<div class="tutor-tiny-2 tutor-text-subdued"><?php esc_html_e( 'Quiz', 'tutor' ); ?></div>
			</div>
		</a>
		<?php
	}
}
