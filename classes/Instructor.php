<?php
/**
 * Manage Instructor
 *
 * @package Tutor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace TUTOR;

defined( 'ABSPATH' ) || exit;

use DateInterval;
use DateTime;
use Tutor\GDPR\Controllers\LegalConsent;
use Tutor\Helpers\DateTimeHelper;
use Tutor\Helpers\QueryHelper;
use Tutor\Models\CourseModel;
use Tutor\Traits\JsonResponse;

/**
 * Instructor class
 *
 * @since 1.0.0
 */
class Instructor {
	use JsonResponse;

	/**
	 * Transient key to store dashboard course completion rate
	 *
	 * Current user id will be append with the key
	 *
	 * @since 4.0.8
	 *
	 * @var string
	 */
	const DASHBOARD_COURSE_COMPLETION_RATE_TRANSIENT = 'tutor_dashboard_course_completion_rate_';

	/**
	 * Error message
	 *
	 * @var string
	 */
	protected $error_msgs = '';

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @param bool $register_hook register hook or not.
	 *
	 * @return void
	 */
	public function __construct( $register_hook = true ) {
		if ( ! $register_hook ) {
			return;
		}
		add_action( 'template_redirect', array( $this, 'register_instructor' ) );
		add_action( 'template_redirect', array( $this, 'apply_instructor' ) );

		// Add instructor from admin panel.
		add_action( 'wp_ajax_tutor_add_instructor', array( $this, 'add_new_instructor' ) );

		/**
		 * Instructor Approval
		 * Block Unblock
		 *
		 * @since 1.5.3
		 */
		add_action( 'wp_ajax_instructor_approval_action', array( $this, 'instructor_approval_action' ) );

		/**
		 * Check if instructor can publish courses
		 *
		 * @since 1.5.9
		 */
		add_action( 'tutor_option_save_after', array( $this, 'can_publish_tutor_courses' ) );

		/**
		 * Hide instructor rejection message
		 *
		 * @since 1.9.2
		 */
		add_action( 'wp_loaded', array( $this, 'hide_instructor_notice' ) );

		add_action( 'wp_ajax_tutor_save_instructor_home_sections_order', array( $this, 'ajax_save_home_sections_order' ) );
		add_action( 'wp_ajax_tutor_save_instructor_home_sections_visibility', array( $this, 'ajax_save_home_section_visibility' ) );
	}

	/**
	 * Template Redirect Callback
	 * For Register new user and mark him as instructor
	 *
	 * @since 1.0.0
	 * @return void|null
	 */
	public function register_instructor() {
		// Here tutor_action checking required before nonce checking.
		if ( 'tutor_register_instructor' !== Input::post( 'tutor_action' ) || ! get_option( 'users_can_register', false ) ) {
			return;
		}

		// Checking nonce.
		tutor_utils()->checking_nonce();
		$required_fields = apply_filters(
			'tutor_instructor_registration_required_fields',
			array(
				'first_name'            => __( 'First name field is required', 'tutor' ),
				'last_name'             => __( 'Last name field is required', 'tutor' ),
				'email'                 => __( 'E-Mail field is required', 'tutor' ),
				'user_login'            => __( 'User Name field is required', 'tutor' ),
				'password'              => __( 'Password field is required', 'tutor' ),
				'password_confirmation' => __( 'Password Confirmation field is required', 'tutor' ),
			)
		);

		$validation_errors = array();

		/*
		* Push into validation_errors
		* Error registration_errors
		*/
		$errors = apply_filters( 'registration_errors', new \WP_Error(), '', '' );
		foreach ( $errors->errors as $key => $value ) {
			$validation_errors[ $key ] = $value[0];
		}

		foreach ( $required_fields as $required_key => $required_value ) {
			if ( empty( $_POST[ $required_key ] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
				$validation_errors[ $required_key ] = $required_value;
			}
		}

		$validate_consent = LegalConsent::validate_consent( LegalConsent::DISPLAY_ON_INS_REG, $_POST );
		if ( is_wp_error( $validate_consent ) ) {
			$validation_errors[ $validate_consent->get_error_code() ] = $validate_consent->get_error_message();
		}

		if ( ! filter_var( tutor_utils()->input_old( 'email' ), FILTER_VALIDATE_EMAIL ) ) {
			$validation_errors['email'] = __( 'Valid E-Mail is required', 'tutor' );
		}

		if ( tutor_utils()->input_old( 'password' ) !== tutor_utils()->input_old( 'password_confirmation' ) ) {
			$validation_errors['password_confirmation'] = __( 'Your passwords should match each other. Please recheck.', 'tutor' );
		}

		if ( count( $validation_errors ) ) {
			$this->error_msgs = $validation_errors;
			add_filter( 'tutor_instructor_register_validation_errors', array( $this, 'tutor_instructor_form_validation_errors' ) );
			return;
		}

		$first_name = sanitize_text_field( tutor_utils()->input_old( 'first_name' ) );
		$last_name  = sanitize_text_field( tutor_utils()->input_old( 'last_name' ) );
		$email      = sanitize_text_field( tutor_utils()->input_old( 'email' ) );
		$user_login = sanitize_text_field( tutor_utils()->input_old( 'user_login' ) );
		$password   = sanitize_text_field( tutor_utils()->input_old( 'password' ) );

		$userdata = array(
			'user_login' => $user_login,
			'user_email' => $email,
			'first_name' => $first_name,
			'last_name'  => $last_name,
			'user_pass'  => $password,
		);

		global $wpdb;
		$wpdb->query( 'START TRANSACTION' );
		$user_id = wp_insert_user( $userdata );
		if ( is_wp_error( $user_id ) ) {
			$this->error_msgs = $user_id->get_error_messages();
			add_filter( 'tutor_instructor_register_validation_errors', array( $this, 'tutor_instructor_form_validation_errors' ) );
			return;
		}

		$user = get_user_by( 'id', $user_id );

		$is_req_email_verification = apply_filters( 'tutor_require_email_verification', false );
		if ( $is_req_email_verification ) {
			do_action( 'tutor_send_verification_mail', get_userdata( $user_id ), 'instructor-registration' );
			$reg_done = apply_filters( 'tutor_registration_done', true );
			if ( ! $reg_done ) {
				$wpdb->query( 'ROLLBACK' );
				return;
			} else {
				$wpdb->query( 'COMMIT' );
			}
		} else {
			/**
			 * Tutor Free - regular instructor reg process.
			 */
			$this->update_instructor_meta( $user_id );
			$wpdb->query( 'COMMIT' );

			if ( $user ) {
				wp_set_current_user( $user_id, $user->user_login );
				wp_set_auth_cookie( $user_id );
				do_action( 'tutor_after_instructor_signup', $user_id );
			}
		}

		if ( $user ) {
			do_action( 'tutor_new_instructor_registered', $user_id, $validate_consent );
		}

		wp_safe_redirect( tutor_utils()->get_nocache_url( tutor_utils()->input_old( '_wp_http_referer' ) ) );
		die();
	}

	/**
	 * Get instructor reg validation errors.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function tutor_instructor_form_validation_errors() {
		return $this->error_msgs;
	}

	/**
	 * Template Redirect Callback
	 * for instructor applying when a user already logged in
	 *
	 * @since 1.0.0
	 * @return void|null
	 */
	public function apply_instructor() {
		// Here tutor_action checking required before nonce checking.
		if ( 'tutor_apply_instructor' !== Input::post( 'tutor_action' ) ) {
			return;
		}

		// Checking nonce.
		tutor_utils()->checking_nonce();

		$user_id = get_current_user_id();
		if ( $user_id ) {
			if ( tutor_utils()->is_instructor() ) {
				die( esc_html__( 'Already applied for instructor', 'tutor' ) );
			} else {
				update_user_meta( $user_id, '_is_tutor_instructor', tutor_time() );
				update_user_meta( $user_id, '_tutor_instructor_status', apply_filters( 'tutor_initial_instructor_status', 'pending' ) );
				update_user_meta( $user_id, User::APPLICATION_SOURCE_META, User::SOURCE_STUDENT_DASHBOARD );

				do_action( 'tutor_new_instructor_after', $user_id );
			}
		} else {
			die( esc_html__( 'Permission denied', 'tutor' ) );
		}

		wp_redirect( tutor_utils()->input_old( '_wp_http_referer' ) );
		die();
	}


	/**
	 * Add new instructor
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function add_new_instructor() {
		tutor_utils()->checking_nonce();

		// Only admin should be able to add instructor.
		if ( ! current_user_can( 'manage_options' ) || ! get_option( 'users_can_register', false ) ) {
			wp_send_json_error();
		}

		$required_fields = apply_filters(
			'tutor_instructor_registration_required_fields',
			array(
				'first_name'            => __( 'First name field is required', 'tutor' ),
				'last_name'             => __( 'Last name field is required', 'tutor' ),
				'email'                 => __( 'E-Mail field is required', 'tutor' ),
				'user_login'            => __( 'User Name field is required', 'tutor' ),
				'password'              => __( 'Password field is required', 'tutor' ),
				'password_confirmation' => __( 'Your passwords should match each other. Please recheck.', 'tutor' ),
			)
		);

		$validation_errors = array();
		foreach ( $required_fields as $required_key => $required_value ) {
			if ( empty( $_POST[ $required_key ] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
				$validation_errors[ $required_key ] = $required_value;
			}
		}

		if ( ! filter_var( tutor_utils()->input_old( 'email' ), FILTER_VALIDATE_EMAIL ) ) {
			$validation_errors['email'] = __( 'Valid E-Mail is required', 'tutor' );
		}
		if ( tutor_utils()->input_old( 'password' ) !== tutor_utils()->input_old( 'password_confirmation' ) ) {
			$validation_errors['password_confirmation'] = __( 'Your passwords should match each other. Please recheck.', 'tutor' );
		}

		if ( count( $validation_errors ) ) {
			wp_send_json_error( array( 'errors' => $validation_errors ) );
		}

		$first_name              = sanitize_text_field( tutor_utils()->input_old( 'first_name' ) );
		$last_name               = sanitize_text_field( tutor_utils()->input_old( 'last_name' ) );
		$email                   = sanitize_text_field( tutor_utils()->input_old( 'email' ) );
		$user_login              = sanitize_text_field( tutor_utils()->input_old( 'user_login' ) );
		$phone_number            = sanitize_text_field( tutor_utils()->input_old( 'phone_number' ) );
		$password                = sanitize_text_field( tutor_utils()->input_old( 'password' ) );
		$tutor_profile_bio       = Input::post( 'tutor_profile_bio', '', Input::TYPE_KSES_POST );
		$tutor_profile_job_title = sanitize_text_field( tutor_utils()->input_old( 'tutor_profile_job_title' ) );

		$userdata = apply_filters(
			'add_new_instructor_data',
			array(
				'user_login' => $user_login,
				'user_email' => $email,
				'first_name' => $first_name,
				'last_name'  => $last_name,
				'role'       => tutor()->instructor_role,
				'user_pass'  => $password,
			)
		);

		do_action( 'tutor_add_new_instructor_before' );

		$user_id = wp_insert_user( $userdata );
		if ( ! is_wp_error( $user_id ) ) {
			update_user_meta( $user_id, 'phone_number', $phone_number );
			update_user_meta( $user_id, 'description', $tutor_profile_bio );
			update_user_meta( $user_id, '_tutor_profile_bio', $tutor_profile_bio );
			update_user_meta( $user_id, '_tutor_profile_job_title', $tutor_profile_job_title );
			update_user_meta( $user_id, '_is_tutor_instructor', tutor_time() );
			update_user_meta( $user_id, '_tutor_instructor_status', apply_filters( 'tutor_initial_instructor_status', 'approved' ) );

			do_action( 'tutor_add_new_instructor_after', $user_id );

			wp_send_json_success( array( 'msg' => __( 'Instructor has been added successfully', 'tutor' ) ) );
		}

		wp_send_json_error( array( 'errors' => $user_id ) );
	}

	/**
	 * Handle instructor approval action
	 * This function not used maybe, will be removed
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function instructor_approval_action() {
		tutor_utils()->checking_nonce();

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied', 'tutor' ) ) );
		}

		$instructor_id = Input::post( 'instructor_id', 0, Input::TYPE_INT );
		$action        = Input::post( 'action_name' );

		if ( 'approve' === $action ) {
			do_action( 'tutor_before_approved_instructor', $instructor_id );

			update_user_meta( $instructor_id, '_tutor_instructor_status', 'approved' );
			update_user_meta( $instructor_id, '_tutor_instructor_approved', tutor_time() );
			update_user_meta( $instructor_id, User::INSTRUCTOR_APPROVAL_NOTICE_META, true );

			$instructor = new \WP_User( $instructor_id );
			$instructor->add_role( tutor()->instructor_role );

			// Send E-Mail to this user about instructor approval via hook.
			do_action( 'tutor_after_approved_instructor', $instructor_id );
		}

		if ( 'blocked' === $action ) {
			do_action( 'tutor_before_blocked_instructor', $instructor_id );
			update_user_meta( $instructor_id, '_tutor_instructor_status', 'blocked' );

			$instructor = new \WP_User( $instructor_id );
			$instructor->remove_role( tutor()->instructor_role );
			do_action( 'tutor_after_blocked_instructor', $instructor_id );

			// TODO: send E-Mail to this user about instructor blocked, should via hook.
		}

		if ( 'remove-instructor' === $action ) {
			do_action( 'tutor_before_rejected_instructor', $instructor_id );

			$user = new \WP_User( $instructor_id );
			$user->remove_role( tutor()->instructor_role );

			tutor_utils()->remove_instructor_role( $instructor_id );
			update_user_meta( $instructor_id, '_is_tutor_instructor_rejected', tutor_time() );
			update_user_meta( $instructor_id, 'tutor_instructor_show_rejection_message', true );

			// Send E-Mail to this user about instructor rejection via hook.
			do_action( 'tutor_after_rejected_instructor', $instructor_id );
		}

		wp_send_json_success();
	}

	/**
	 * Hide instructor notice
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function hide_instructor_notice() {
		if ( 'hide_instructor_notice' === Input::get( 'tutor_action' ) ) {
			delete_user_meta( get_current_user_id(), 'tutor_instructor_show_rejection_message' );
		} elseif ( 'hide_instructor_approval_notice' === Input::get( 'tutor_action' ) ) {
			delete_user_meta( get_current_user_id(), User::INSTRUCTOR_APPROVAL_NOTICE_META );
		}
	}

	/**
	 * Can instructor publish courses directly
	 * Fixed in Gutenberg
	 *
	 * @since 1.5.9
	 * @return void
	 */
	public function can_publish_tutor_courses() {
		$can_publish_course = (bool) tutor_utils()->get_option( 'instructor_can_publish_course' );

		$instructor_role = tutor()->instructor_role;
		$instructor      = get_role( $instructor_role );

		if ( $can_publish_course ) {
			$instructor->add_cap( 'publish_tutor_courses' );
		} else {
			$instructor->remove_cap( 'publish_tutor_courses' );
		}
	}

	/**
	 * Update instructor meta just after register
	 *
	 * @since 2.1.9
	 *
	 * @param integer $user_id user id.
	 *
	 * @return void
	 */
	public function update_instructor_meta( int $user_id ) {
		update_user_meta( $user_id, '_is_tutor_instructor', tutor_time() );
		update_user_meta( $user_id, '_tutor_instructor_status', apply_filters( 'tutor_initial_instructor_status', 'pending' ) );
		update_user_meta( $user_id, User::APPLICATION_SOURCE_META, User::SOURCE_INSTRUCTOR_REGISTRATION );

		do_action( 'tutor_new_instructor_after', $user_id );
	}

	/**
	 * Calculate the previous comparison date range based on a selected date range.
	 *
	 * @since 4.0.0
	 *
	 * @param string|null $selected_start_date Selected start date (Y-m-d).
	 * @param string|null $selected_end_date   Selected end date (Y-m-d).
	 *
	 * @return array {
	 *     @type string $previous_start_date Previous period start date (Y-m-d).
	 *     @type string $previous_end_date   Previous period end date (Y-m-d).
	 * }
	 */
	public static function get_comparison_date_range( $selected_start_date, $selected_end_date ) {

		$format = DateTimeHelper::FORMAT_DATE;

		if ( empty( $selected_start_date ) && empty( $selected_end_date ) ) {

			$now = DateTimeHelper::now();
			return array(
				'previous_start_date' => $now->create( 'first day of this month' )->format( $format ),
				'previous_end_date'   => $now->create( 'last day of this month' )->format( $format ),
			);
		}

		$start = new DateTime( $selected_start_date );
		$end   = new DateTime( $selected_end_date );
		$days  = $start->diff( $end )->days + 1;

		$previous_start_date = $start->sub( DateInterval::createFromDateString( "$days days" ) )->format( $format );
		$previous_end_date   = $end->sub( DateInterval::createFromDateString( "$days days" ) )->format( $format );

		return array(
			'previous_start_date' => $previous_start_date,
			'previous_end_date'   => $previous_end_date,
		);
	}

	/**
	 * Get course completion distribution data for a specific instructor.
	 *
	 * If the user is Admin then this method will consider all the course, considering
	 * specific courses when user is an instructor
	 *
	 * @since 4.0.0
	 *
	 * @since 4.0.8 Instead of course id getting user id.
	 *
	 * @param int $user_id User id to get course completion rate.
	 *
	 * @return array {
	 *     Enrollment distribution counts.
	 *
	 *     @type int $enrolled   Total number of enrollments.
	 *     @type int $completed  Number of fully completed enrollments (100% progress).
	 *     @type int $inprogress Number of enrollments with partial progress (>0 and <100).
	 *     @type int $inactive   Number of enrollments with no progress (0%).
	 *     @type int $cancelled  Number of cancelled enrollments.
	 * }
	 */
	public static function get_course_completion_distribution_data_by_instructor( int $user_id = 0 ) {
		global $wpdb;

		$user_id = $user_id ? $user_id : get_current_user_id();

		$counts = array(
			'enrolled'   => 0,
			'completed'  => 0,
			'inprogress' => 0,
			'inactive'   => 0,
			'cancelled'  => 0,
		);

		if ( ! User::is_admin( $user_id ) && ! User::is_instructor( $user_id, true ) ) {
			return $counts;
		}

		$cache_key = self::DASHBOARD_COURSE_COMPLETION_RATE_TRANSIENT . $user_id;

		$cached_data = get_transient( $cache_key );
		if ( $cached_data && is_array( $cached_data ) ) {
			return $cached_data;
		}

		$instructor_course_ids = array();
		if ( ! User::is_admin() && User::is_instructor( $user_id, true ) ) {
			$instructor_course_ids = CourseModel::get_courses_by_args(
				array(
					'post_author'    => $user_id,
					'posts_per_page' => -1,
					'fields'         => 'ids',
				)
			)->posts;

			$instructor_course_ids = array_filter(
				array_map( 'absint', (array) $instructor_course_ids )
			);
		}

		$topic_type      = tutor()->topics_post_type;
		$lesson_type     = tutor()->lesson_post_type;
		$quiz_type       = tutor()->quiz_post_type;
		$assignment_type = tutor()->assignment_post_type;
		$zoom_type       = tutor()->zoom_post_type;
		$meet_type       = tutor()->meet_post_type;

		$placeholders = implode(
			',',
			array_fill( 0, count( $instructor_course_ids ), '%d' )
		);

		$topic_post_parent_clause      = '';
		$enrollment_post_parent_clause = '';
		if ( tutor_utils()->count( $instructor_course_ids ) ) {
			$topic_post_parent_clause      = "AND topic.post_parent IN ({$placeholders})";
			$enrollment_post_parent_clause = "AND e.post_parent IN ({$placeholders})";
		}

		$sql = "
			SELECT
				COUNT(DISTINCT e.ID) AS enrolled,

				COUNT(
					DISTINCT CASE
						WHEN e.post_status IN ('cancel', 'canceled', 'cancelled')
						THEN e.ID
					END
				) AS cancelled,

				COUNT(
					DISTINCT CASE
						WHEN e.post_status = 'completed'
							AND completion.user_id IS NOT NULL
						THEN e.ID
					END
				) AS completed,

				COUNT(
					DISTINCT CASE
						WHEN e.post_status = 'completed'
							AND completion.user_id IS NULL
							AND (
								lesson_progress.user_id IS NOT NULL
								OR quiz_progress.user_id IS NOT NULL
								OR assignment_progress.user_id IS NOT NULL
								OR meeting_progress.user_id IS NOT NULL
							)
						THEN e.ID
					END
				) AS inprogress
	
			FROM {$wpdb->posts} AS e
	
			LEFT JOIN (
				SELECT DISTINCT
					c.comment_post_ID AS course_id,
					c.user_id
				FROM {$wpdb->comments} AS c
				WHERE c.comment_agent = 'TutorLMSPlugin'
					AND c.comment_type = 'course_completed'
			) AS completion
				ON completion.course_id = e.post_parent
				AND completion.user_id = e.post_author
	
			LEFT JOIN (
				SELECT DISTINCT
					um.user_id,
					topic.post_parent AS course_id
				FROM {$wpdb->usermeta} AS um
				INNER JOIN {$wpdb->posts} AS lesson
					ON um.meta_key = CONCAT('_tutor_completed_lesson_id_', lesson.ID)
				INNER JOIN {$wpdb->posts} AS topic
					ON lesson.post_parent = topic.ID
				WHERE 1 = 1
				 	{$topic_post_parent_clause}
					AND topic.post_type = %s
					AND lesson.post_type = %s
					AND lesson.post_status = 'publish'
			) AS lesson_progress
				ON lesson_progress.user_id = e.post_author
				AND lesson_progress.course_id = e.post_parent
	
			LEFT JOIN (
				SELECT DISTINCT
					qa.user_id,
					topic.post_parent AS course_id
				FROM {$wpdb->tutor_quiz_attempts} AS qa
				INNER JOIN {$wpdb->posts} AS quiz
					ON quiz.ID = qa.quiz_id
				INNER JOIN {$wpdb->posts} AS topic
					ON quiz.post_parent = topic.ID
				WHERE 1 = 1
					{$topic_post_parent_clause}
					AND topic.post_type = %s
					AND quiz.post_type = %s
					AND quiz.post_status = 'publish'
			) AS quiz_progress
				ON quiz_progress.user_id = e.post_author
				AND quiz_progress.course_id = e.post_parent
	
			LEFT JOIN (
				SELECT DISTINCT
					c.user_id,
					topic.post_parent AS course_id
				FROM {$wpdb->comments} AS c
				INNER JOIN {$wpdb->posts} AS assignment
					ON assignment.ID = c.comment_post_ID
				INNER JOIN {$wpdb->posts} AS topic
					ON assignment.post_parent = topic.ID
				WHERE c.comment_type = 'tutor_assignment'
					AND c.comment_approved = 'submitted'
					{$topic_post_parent_clause}
					AND topic.post_type = %s
					AND assignment.post_type = %s
					AND assignment.post_status = 'publish'
			) AS assignment_progress
				ON assignment_progress.user_id = e.post_author
				AND assignment_progress.course_id = e.post_parent
	
			LEFT JOIN (
				SELECT DISTINCT
					um.user_id,
					topic.post_parent AS course_id
				FROM {$wpdb->usermeta} AS um
				INNER JOIN {$wpdb->posts} AS meeting
					ON um.meta_key = CONCAT('_tutor_completed_lesson_id_', meeting.ID)
				INNER JOIN {$wpdb->posts} AS topic
					ON meeting.post_parent = topic.ID
				WHERE 1 = 1
					{$topic_post_parent_clause}
					AND topic.post_type = %s
					AND meeting.post_type IN (%s, %s)
					AND meeting.post_status = 'publish'
			) AS meeting_progress
				ON meeting_progress.user_id = e.post_author
				AND meeting_progress.course_id = e.post_parent
	
			WHERE e.post_type = 'tutor_enrolled'
				AND e.post_status IN (
					'completed',
					'cancel',
					'canceled',
					'cancelled'
				)
				{$enrollment_post_parent_clause}
		";

		$prepare_args = array_merge(
			// lesson_progress.
			$instructor_course_ids,
			array(
				$topic_type,
				$lesson_type,
			),
			// quiz_progress.
			$instructor_course_ids,
			array(
				$topic_type,
				$quiz_type,
			),
			// assignment_progress.
			$instructor_course_ids,
			array(
				$topic_type,
				$assignment_type,
			),
			// meeting_progress.
			$instructor_course_ids,
			array(
				$topic_type,
				$zoom_type,
				$meet_type,
			),
			// Enrollment.
			$instructor_course_ids
		);

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$row = $wpdb->get_row(
			$wpdb->prepare( $sql, $prepare_args ),
			ARRAY_A
		);

		if ( $row ) {
			$counts['enrolled']   = (int) $row['enrolled'];
			$counts['cancelled']  = (int) $row['cancelled'];
			$counts['completed']  = (int) $row['completed'];
			$counts['inprogress'] = (int) $row['inprogress'];
		}

		$counts['inactive'] = max(
			0,
			$counts['enrolled']
				- $counts['cancelled']
				- $counts['completed']
				- $counts['inprogress']
		);

		// Cache data for 10min.
		set_transient( $cache_key, $counts, MINUTE_IN_SECONDS * 10 );

		return $counts;
	}

	/**
	 * Retrieve the top-performing courses for a given instructor.
	 *
	 * @since 4.0.0
	 *
	 * @param int   $instructor_id Instructor  user ID.
	 * @param array $args {
	 *     Optional query arguments.
	 *
	 *     @type string $start_date Optional start date (Y-m-d).
	 *     @type string $end_date   Optional end date (Y-m-d).
	 *     @type string $order_by   Sorting criteria. Accepts 'revenue' or 'student'.
	 * }

	 * @param int   $limit         Maximum number of courses to return. Default 4.
	 *
	 * @return array List of course objects containing:
	 *               - course_id (int)
	 *               - course_title (string)
	 *               - total_revenue (float)
	 *               - total_student (int)
	 *
	 * @throws \Exception When a database error occurs.
	 */
	public static function get_top_performing_courses_by_instructor( $instructor_id, $args, $limit = 4 ) {

		global $wpdb;

		$start_date = $args['start_date'] ?? null;
		$end_date   = $args['end_date'] ?? null;
		$order_by   = 'revenue' === $args['order_by'] ? 'total_revenue' : 'total_student';

		$complete_status = tutor_utils()->get_earnings_completed_statuses();

		$amount_type = User::is_admin() && is_admin() ? 'earnings.admin_amount' : 'earnings.instructor_amount';
		$amount_rate = User::is_admin() && is_admin() ? 'earnings.admin_rate' : 'earnings.instructor_rate';

		$amount_condition = "CASE
			WHEN orders.tax_type = 'inclusive' AND earnings.course_price_grand_total > 0
				THEN ( earnings.course_price_grand_total - orders.tax_amount ) * ( $amount_rate/100 )
			ELSE $amount_type
			END";

		$earning_where_clause = array(
			'earnings.user_id'      => $instructor_id,
			'earnings.order_status' => array( 'IN', $complete_status ),
		);

		$enrollment_where_clause = array(
			'post_type'   => 'tutor_enrolled',
			'post_status' => 'completed',
		);

		if ( ! empty( $start_date ) && ! empty( $end_date ) ) {
			$earning_where_clause['earnings.created_at'] = array( 'BETWEEN', array( $start_date, $end_date ) );
			$enrollment_where_clause['post_date']        = array( 'BETWEEN', array( $start_date, $end_date ) );
		}

		$earning_where_clause    = QueryHelper::prepare_where_clause( $earning_where_clause );
		$enrollment_where_clause = QueryHelper::prepare_where_clause( $enrollment_where_clause );

		$earnings_sql = "SELECT
							earnings.course_id,
							SUM($amount_condition) AS total_revenue
						FROM {$wpdb->tutor_earnings} earnings
						LEFT JOIN {$wpdb->tutor_orders} orders ON orders.id = earnings.order_id
						WHERE {$earning_where_clause}
						GROUP BY earnings.course_id";

		$enrollment_sql = QueryHelper::prepare_raw_query(
			"SELECT 
				post_parent AS course_id, 
				COUNT(DISTINCT post_author) AS total_student
			FROM {$wpdb->posts}
			WHERE {$enrollment_where_clause}
			GROUP BY post_parent",
			array()
		);

		//phpcs:disabled
		$result = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
					post.ID AS course_id,
					post.post_title AS course_title,
					COALESCE(earnings.total_revenue, 0)  AS total_revenue,
					COALESCE(enrollments.total_student, 0) AS total_student
				FROM {$wpdb->posts} post
				INNER JOIN ({$earnings_sql}) earnings 
					ON earnings.course_id = post.ID
				LEFT JOIN ({$enrollment_sql}) enrollments 
					ON enrollments.course_id = post.ID
				WHERE post.post_type = %s
				ORDER BY {$order_by} DESC
				LIMIT %d",
				tutor()->course_post_type,
				$limit
			)
		);
		//phpcs:enable

		// If error occurred then throw new exception.
		if ( $wpdb->last_error ) {
			throw new \Exception( $wpdb->last_error ); //phpcs:ignore.
		}

		return $result;
	}

	/**
	 * Format top performing instructor courses for presentation.
	 *
	 * @since 4.0.0
	 *
	 * @param array $top_courses List of course objects returned from analytics.
	 *
	 * @return array Formatted top performing courses data.
	 */
	public static function format_instructor_top_performing_courses( $top_courses ) {

		if ( empty( $top_courses ) ) {
			return array();
		}

		return array_map(
			function ( $course ) {
				return array(
					'name'     => $course->course_title,
					'url'      => get_permalink( $course->course_id ),
					'revenue'  => tutor_utils()->tutor_price( $course->total_revenue ?? 0 ),
					'students' => $course->total_student ?? 0,
				);
			},
			$top_courses
		);
	}

	/**
	 * Retrieve upcoming live session tasks (Zoom / Google Meet) for an instructor.
	 *
	 * @since 4.0.0
	 *
	 * @param int $instructor_id Instructor (author) user ID.
	 *
	 * @return array List of upcoming live task posts.
	 */
	public static function get_instructor_upcoming_live_tasks( $instructor_id ) {

		$is_google_meet_enable = tutor_utils()->is_addon_enabled( 'google-meet' );
		$is_zoom_enable        = tutor_utils()->is_addon_enabled( 'tutor-zoom' );

		$meta_keys = array_filter(
			array(
				$is_google_meet_enable ? 'tutor-google-meet-start-datetime' : null,
				$is_zoom_enable ? '_tutor_zm_start_datetime' : null,
			)
		);

		$post_types = array_filter(
			array(
				$is_google_meet_enable ? tutor()->meet_post_type : null,
				$is_zoom_enable ? tutor()->zoom_post_type : null,
			)
		);

		if ( empty( $meta_keys ) ) {
			return array();
		}

		return get_posts(
			array(
				'post_type'   => $post_types,
				'post_status' => 'publish',
				'author'      => $instructor_id,
				'numberposts' => 5,
				'meta_query'  => array(
					array(
						'key'     => $meta_keys,
						'value'   => gmdate( 'Y-m-d H:i:s', strtotime( 'now' ) ),
						'compare' => '>=',
						'type'    => 'DATETIME',
					),
				),
			)
		);
	}

	/**
	 * Format upcoming instructor live tasks for presentation.
	 *
	 * @since 4.0.0
	 *
	 * @param array $upcoming_live_tasks List of live task post objects.
	 *
	 * @return array Formatted upcoming live tasks data.
	 */
	public static function format_instructor_upcoming_live_tasks( $upcoming_live_tasks ) {

		if ( empty( $upcoming_live_tasks ) ) {
			return array();
		}

		return array_map(
			function ( $task ) {

				$is_zoom = tutor()->zoom_post_type === $task->post_type;
				$is_meet = tutor()->meet_post_type === $task->post_type;

				$live_meta_key = $is_zoom ? '_tutor_zm_start_datetime'
								: ( $is_meet ? 'tutor-google-meet-start-datetime' : '' );

				$url = $is_zoom ? ( json_decode( get_post_meta( $task->ID, '_tutor_zm_data' )[0] )->join_url ?? '' )
								: ( $is_meet ? get_post_meta( $task->ID, 'tutor-google-meet-link', true ) : '' );

				$start_date = get_post_meta( $task->ID, $live_meta_key, true );

				return array(
					'name'      => $task->post_title,
					'date'      => wp_date( 'Y-m-d h:i A', strtotime( $start_date ) ),
					'url'       => $url,
					'post_type' => $task->post_type,
				);
			},
			$upcoming_live_tasks
		);
	}

	/**
	 * Format recent instructor reviews for display.
	 *
	 * @since 4.0.0
	 *
	 * @param array $reviews List of review objects.
	 *
	 * @return array Formatted recent reviews data.
	 */
	public static function format_instructor_recent_reviews( $reviews ) {

		if ( empty( $reviews ) ) {
			return array();
		}

		return array_map(
			function ( $review ) {
				return array(
					'user'        => array(
						'id'     => $review->user_id,
						'name'   => $review->display_name,
						'avatar' => tutor_utils()->get_user_avatar_url( $review->user_id ),
					),
					'course_name' => get_the_title( $review->comment_post_ID ),
					'date'        => $review->comment_date,
					'rating'      => $review->rating,
					'review_text' => $review->comment_content,
				);
			},
			$reviews
		);
	}

	/**
	 *
	 * Calculates percentage change and UI metadata for a statistics card.
	 *
	 * @since 4.0.0
	 *
	 * @param float $current_data  Current period value.
	 * @param float $previous_data Previous period value.
	 *
	 * @return array{
	 *     percentage: string,
	 *     icon: string,
	 *     class: string,
	 *     icon_class: string
	 * }
	 */
	public static function get_stat_card_details( float $current_data, float $previous_data ) {

		if ( empty( $previous_data ) && empty( $current_data ) ) {
			return array(
				'percentage' => '',
				'icon'       => Icon::MINUS,
				'class'      => 'tutor-text-primary',
			);
		}

		if ( empty( $previous_data ) ) {
			$percentage = 100;
		} else {
			$percentage = ( ( $current_data - $previous_data ) / $previous_data ) * 100;
		}

		$is_negative = $percentage < 0;
		$icon        = $is_negative ? Icon::ARROW_DOWN : Icon::ARROW_UP;
		$class       = $is_negative ? 'tutor-p2 tutor-actions-critical-primary' : 'tutor-p2 tutor-actions-success-primary';

		return array(
			'percentage' => number_format( abs( $percentage ), 2 ) . '%',
			'icon'       => $icon,
			'class'      => $class,
			'icon_class' => '-tutor-mb-1',
		);
	}

	/**
	 * Save the instructor home sections order for the current user.
	 *
	 * @since 4.0.0
	 *
	 * @return void Sends JSON success or error response and exits.
	 */
	public function ajax_save_home_sections_order() {
		tutor_utils()->check_nonce();

		if ( ! User::is_instructor() ) {
			$this->response_bad_request( tutor_utils()->error_message() );
		}

		$order = Input::post( 'order', array(), Input::TYPE_ARRAY );
		$order = array_values( array_map( 'sanitize_key', $order ) );

		update_user_meta( get_current_user_id(), '_tutor_instructor_home_sections_order', array_flip( $order ) );

		$this->response_success( __( 'Settings saved successfully', 'tutor' ) );
	}

	/**
	 * Save the visibility state of instructor home sections.
	 *
	 * @since 4.0.0
	 *
	 * @return void Sends JSON success or error response and exits.
	 */
	public function ajax_save_home_section_visibility() {
		tutor_utils()->check_nonce();

		if ( ! User::is_instructor() ) {
			$this->response_bad_request( tutor_utils()->error_message() );
		}

		$items = Input::post( 'items', '', Input::TYPE_STRING );
		$items = array_map( 'rest_sanitize_boolean', (array) json_decode( $items ) );

		update_user_meta( get_current_user_id(), '_tutor_instructor_home_sections_visibility', $items );

		$this->response_success( __( 'Settings saved successfully', 'tutor' ) );
	}
}
