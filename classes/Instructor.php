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

use DateTime;
use DateInterval;
use Tutor\Models\CourseModel;
use Tutor\Helpers\QueryHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Instructor class
 *
 * @since 1.0.0
 */
class Instructor {

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
			$user = get_user_by( 'id', $user_id );
			if ( $user ) {
				wp_set_current_user( $user_id, $user->user_login );
				wp_set_auth_cookie( $user_id );
				do_action( 'tutor_after_instructor_signup', $user_id );
			}
		}

		wp_redirect( tutor_utils()->input_old( '_wp_http_referer' ) );
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

		do_action( 'tutor_new_instructor_after', $user_id );
	}

	/**
	 * Generate a comparison subtitle for a stat card.
	 * If no date range is provided, the subtitle defaults to "this month".
	 *
	 * @since 4.0.0
	 *
	 * @param string $start_date     Selected start date (Y-m-d).
	 * @param string $end_date       Selected end date (Y-m-d).
	 * @param float  $current_data   Current period value.
	 * @param float  $previous_data  Previous period value.
	 * @param bool   $price          Whether to format the difference as a price.
	 *
	 * @return string Comparison subtitle string.
	 */
	public static function get_stat_card_comparison_subtitle(
		string $start_date,
		string $end_date,
		float $current_data,
		float $previous_data,
		bool $price = true
	): string {

		$diff   = ! empty( $start_date ) && ! empty( $end_date ) ? $current_data - $previous_data : $previous_data;
		$symbol = $diff < 0 ? '-' : ( $diff > 0 ? '+' : '' );
		$diff   = $price ? wp_kses_post( tutor_utils()->tutor_price( abs( $diff ) ) ) : abs( $diff );

		$start = new DateTime( $start_date );
		$end   = new DateTime( $end_date );
		$days  = (int) $start->diff( $end )->days;

		$time_span = empty( $start_date ) && empty( $end_date )
						? __( 'this month', 'tutor' )
						: self::get_comparison_period_label( $start_date, $end_date, $days );

		return "{$symbol}{$diff} {$time_span}";
	}

	/**
	 * Get a human-readable label for a comparison date range.
	 *
	 * @since 4.0.0
	 *
	 * @param string $start_date Start date (Y-m-d).
	 * @param string $end_date   End date (Y-m-d).
	 * @param int    $days       Number of days between start and end dates.
	 *
	 * @return string Comparison period label.
	 */
	public static function get_comparison_period_label( string $start_date, string $end_date, int $days ): string {

		$time_zone = wp_timezone();
		$now       = new DateTime( 'now', $time_zone );
		$today     = wp_date( 'Y-m-d', null, $time_zone );

		$this_month_start = $now->modify( 'first day of this month' )->format( 'Y-m-d' );
		$this_month_end   = $now->modify( 'last day of this month' )->format( 'Y-m-d' );

		$last_month_start = $now->modify( 'first day of last month' )->format( 'Y-m-d' );
		$last_month_end   = $now->modify( 'last day of last month' )->format( 'Y-m-d' );

		$last_year_start = $now->modify( 'first day of January last year' )->format( 'Y-m-d' );
		$last_year_end   = $now->modify( 'last day of December last year' )->format( 'Y-m-d' );

		if ( $start_date === $today && $end_date === $today ) {
			return __( 'today', 'tutor' );
		}

		switch ( true ) {

			case ( 0 === $days && $today !== $start_date ):
				return __( 'from yesterday', 'tutor' );

			case ( 6 === $days ):
				return __( 'from last 7 days', 'tutor' );

			case ( 13 === $days ):
				return __( 'from last 14 days', 'tutor' );

			case ( 29 === $days ):
				return __( 'from last 30 days', 'tutor' );

			case ( $start_date === $this_month_start && $end_date === $this_month_end ):
				return __( 'from this month', 'tutor' );

			case ( $start_date === $last_month_start && $end_date === $last_month_end ):
				return __( 'from last month', 'tutor' );

			case ( $start_date === $last_year_start && $end_date === $last_year_end ):
				return __( 'from last year', 'tutor' );

			default:
				return sprintf(
					/* translators: 1: formatted start date, 2: formatted end date */
					__( 'from %1$s–%2$s', 'tutor' ),
					wp_date( 'M j', strtotime( $start_date ), $time_zone ),
					wp_date( 'M j', strtotime( $end_date ), $time_zone )
				);
		}
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

		if ( empty( $selected_start_date ) && empty( $selected_end_date ) ) {
			$now = new DateTime();
			return array(
				'previous_start_date' => $now->modify( 'first day of this month' )->format( 'Y-m-d' ),
				'previous_end_date'   => $now->modify( 'last day of this month' )->format( 'Y-m-d' ),
			);
		}

		$start = new DateTime( $selected_start_date );
		$end   = new DateTime( $selected_end_date );
		$days  = $start->diff( $end )->days + 1;

		$previous_start_date = $start->sub( DateInterval::createFromDateString( "$days days" ) )->format( 'Y-m-d' );
		$previous_end_date   = $end->sub( DateInterval::createFromDateString( "$days days" ) )->format( 'Y-m-d' );

		return array(
			'previous_start_date' => $previous_start_date,
			'previous_end_date'   => $previous_end_date,
		);
	}

	/**
	 * Get the total number of students enrolled in an instructor's courses
	 * within a given date range.
	 *
	 * @since 4.0.0
	 *
	 * @param string|null $start_date Start date (Y-m-d).
	 * @param string|null $end_date   End date (Y-m-d).
	 * @param int         $user_id    Instructor user ID.
	 *
	 * @return int Total number of enrolled students.
	 */
	public static function get_instructor_total_students_by_date_range( $start_date, $end_date, $user_id ) {

		global $wpdb;

		$primary_table  = "{$wpdb->posts} AS enrollment";
		$joining_table  = array(
			array(
				'type'  => 'INNER',
				'table' => "{$wpdb->posts} AS course",
				'on'    => 'enrollment.post_parent=course.ID',
			),
		);
		$select_columns = array( 'COUNT(enrollment.ID) AS students' );

		$where = array(
			'course.post_author'     => $user_id,
			'course.post_type'       => tutor()->course_post_type,
			'course.post_status'     => CourseModel::STATUS_PUBLISH,
			'enrollment.post_type'   => 'tutor_enrolled',
			'enrollment.post_status' => 'completed',
		);

		if ( ! empty( $start_date ) && ! empty( $end_date ) ) {
			$where['enrollment.post_date'] = array( 'BETWEEN', array( $start_date, $end_date ) );
		}

		$result = QueryHelper::get_joined_data(
			$primary_table,
			$joining_table,
			$select_columns,
			$where,
			array(),
			'',
			-1,
			0,
			'DESC',
			OBJECT,
			true
		);

		return $result->students ?? 0;
	}

	/**
	 * Get the total number of students who completed courses for a given instructor
	 * within an optional date range.
	 *
	 * @since 4.0.0
	 *
	 * @param string|null $start_date    Start date (Y-m-d). Optional.
	 * @param string|null $end_date      End date (Y-m-d). Optional.
	 * @param int         $instructor_id Instructor ID.
	 *
	 * @return int Number of completed students for the instructor in the given date range.
	 *
	 * @throws \Exception If a database error occurs while counting comments.
	 */
	public static function get_instructor_completed_students_course_count_by_date( $start_date, $end_date, $instructor_id, $course_ids = array() ) {

		global $wpdb;

		if ( empty( $course_ids ) ) {
			$common_args = array(
				'post_author'    => $instructor_id,
				'posts_per_page' => -1,
				'fields'         => 'ids',
			);

			$courses    = CourseModel::get_courses_by_args( $common_args );
			$course_ids = $courses->posts;
		}

		$where = array(
			'comment_post_ID' => array( 'IN', $course_ids ),
			'comment_type'    => CourseModel::COURSE_COMPLETED,
		);

		if ( ! empty( $start_date ) && ! empty( $end_date ) ) {
			$where['comment_date'] = array( 'BETWEEN', array( $start_date, $end_date ) );
		}

		return QueryHelper::get_count( $wpdb->comments, $where, array(), 'comment_ID' );
	}
}
