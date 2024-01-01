<?php
/**
 * Class Student
 *
 * @package Tutor\Student
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manage students
 *
 * @since 1.0.0
 */
class Student {

	/**
	 * Bagged error messages
	 *
	 * @since 1.0.0
	 *
	 * @var $error_msgs
	 */
	protected $error_msgs = '';

	/**
	 * Handle Hooks
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'template_redirect', array( $this, 'register_student' ) );
		add_filter( 'get_avatar_url', array( $this, 'filter_avatar' ), 10, 3 );
		add_action( 'tutor_action_tutor_social_profile', array( $this, 'tutor_social_profile' ) );
		add_action( 'wp_ajax_tutor_profile_password_reset', array( $this, 'tutor_reset_password' ) );
		add_action( 'wp_ajax_tutor_update_profile', array( $this, 'update_profile' ) );
	}

	/**
	 * Register new user and mark him as student
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_student() {
		if ( 'tutor_register_student' !== Input::post( 'tutor_action', '' ) || ! get_option( 'users_can_register', false ) ) {
			// Action must be register, and registration must be enabled in dashboard.
			return;
		}

		// Checking nonce.
		tutor_utils()->checking_nonce();

		$required_fields = apply_filters(
			'tutor_student_registration_required_fields',
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

		// Registration error push into validation_errors.
		$errors = apply_filters( 'registration_errors', new \WP_Error(), '', '' );
		foreach ( $errors->errors as $key => $value ) {
			$validation_errors[ $key ] = $value[0];

		}

		foreach ( $required_fields as $required_key => $required_value ) {
			if ( empty( Input::post( $required_key, '' ) ) ) {
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
			add_filter( 'tutor_student_register_validation_errors', array( $this, 'tutor_student_form_validation_errors' ) );
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

		$user_id        = wp_insert_user( $userdata );
		$enroll_attempt = Input::post( 'tutor_course_enroll_attempt', '' );

		if ( is_wp_error( $user_id ) ) {
			$this->error_msgs = $user_id->get_error_messages();
			add_filter( 'tutor_student_register_validation_errors', array( $this, 'tutor_student_form_validation_errors' ) );
			return;
		}

		$user = get_user_by( 'id', $user_id );

		$is_req_email_verification = apply_filters( 'tutor_require_email_verification', false );
		if ( $is_req_email_verification ) {
			do_action( 'tutor_send_verification_mail', $user, $enroll_attempt );
			$reg_done = apply_filters( 'tutor_registration_done', true );
			if ( ! $reg_done ) {
				$wpdb->query( 'ROLLBACK' );
				return;
			} else {
				$wpdb->query( 'COMMIT' );
			}
		} else {
			/**
			 * Tutor Free - reqular student reg process.
			 */
			$wpdb->query( 'COMMIT' );

			wp_set_current_user( $user_id, $user->user_login );
			wp_set_auth_cookie( $user_id );

			do_action( 'tutor_after_student_signup', $user_id );
			// since 1.9.8 do enroll if guest attempt to enroll.
			if ( ! empty( $enroll_attempt ) ) {
				do_action( 'tutor_do_enroll_after_login_if_attempt', $enroll_attempt, $user_id );
			}

			// Redirect page.
			$redirect_page = tutor_utils()->array_get( 'redirect_to', $_REQUEST ); //phpcs:ignore
			if ( ! $redirect_page ) {
				$redirect_page = tutor_utils()->tutor_dashboard_url();
			}
			wp_safe_redirect( apply_filters( 'tutor_student_register_redirect_url', $redirect_page, $user ) );
			die();
		}

		$registration_page = tutor_utils()->student_register_url();
		wp_safe_redirect( $registration_page );
		die();
	}

	/**
	 * Get validation error messages
	 *
	 * @since 1.0.0
	 *
	 * @return mixed error messages
	 */
	public function tutor_student_form_validation_errors() {
		return $this->error_msgs;
	}

	/**
	 * Update profile
	 *
	 * @since 1.0.0
	 *
	 * @return void send wp_json response
	 */
	public function update_profile() {
		// Checking nonce.
		tutor_utils()->checking_nonce();

		$user_id = get_current_user_id();
		do_action( 'tutor_profile_update_before', $user_id );

		$first_name              = sanitize_text_field( tutor_utils()->input_old( 'first_name' ) );
		$last_name               = sanitize_text_field( tutor_utils()->input_old( 'last_name' ) );
		$phone_number            = sanitize_text_field( tutor_utils()->input_old( 'phone_number' ) );
		$tutor_profile_bio       = Input::post( 'tutor_profile_bio', '', Input::TYPE_KSES_POST );
		$tutor_profile_job_title = sanitize_text_field( tutor_utils()->input_old( 'tutor_profile_job_title' ) );

		$display_name = sanitize_text_field( tutor_utils()->input_old( 'display_name' ) );

		$userdata = array(
			'ID'           => $user_id,
			'first_name'   => $first_name,
			'last_name'    => $last_name,
			'display_name' => $display_name,
		);
		$user_id  = wp_update_user( $userdata );

		if ( ! is_wp_error( $user_id ) ) {
			update_user_meta( $user_id, 'phone_number', $phone_number );
			update_user_meta( $user_id, '_tutor_profile_bio', $tutor_profile_bio );
			update_user_meta( $user_id, '_tutor_profile_job_title', $tutor_profile_job_title );

			$tutor_user_social = tutor_utils()->tutor_user_social_icons();
			foreach ( $tutor_user_social as $key => $social ) {
				$user_social_value = sanitize_text_field( tutor_utils()->input_old( $key ) );
				if ( $user_social_value ) {
					update_user_meta( $user_id, $key, $user_social_value );
				} else {
					delete_user_meta( $user_id, $key );
				}
			}
		}
		do_action( 'tutor_profile_update_after', $user_id );

		wp_send_json_success( array( 'message' => __( 'Profile Updated', 'tutor' ) ) );
	}

	/**
	 * Filter Avatar, Change avatar URL with Tutor User Photo
	 *
	 * @since 1.0.0
	 *
	 * @param string $url url.
	 * @param mixed  $id_or_email id or email.
	 * @param array  $args extra args.
	 *
	 * @return false|string
	 */
	public function filter_avatar( $url, $id_or_email, $args ) {

		$finder = false;
		$is_id  = is_numeric( $id_or_email );

		if ( $is_id ) {
			$finder = absint( $id_or_email );
		} elseif ( is_string( $id_or_email ) ) {
			$finder = $id_or_email;
		} elseif ( $id_or_email instanceof \WP_User ) {
			// User Object.
			$finder = $id_or_email->ID;
		} elseif ( $id_or_email instanceof \WP_Post ) {
			// Post Object.
			$finder = (int) $id_or_email->post_author;
		} elseif ( $id_or_email instanceof \WP_Comment ) {
			return $url;
		}

		if ( ! $finder ) {
			return $url;
		}

		$user = get_user_by( $is_id ? 'ID' : 'email', $finder );

		if ( $user ) {
			$profile_photo = get_user_meta( $user->ID, '_tutor_profile_photo', true );
			if ( $profile_photo ) {
				$size = isset( $args['size'] ) ? $args['size'] : 'thumbnail';
				$url  = wp_get_attachment_image_url( $profile_photo, $size );
			}
		}
		return $url;
	}

	/**
	 * Password Rest
	 *
	 * @since 1.0.0
	 *
	 * @return void send wp_json response
	 */
	public function tutor_reset_password() {
		// Checking nonce.
		tutor_utils()->checking_nonce();

		$user = wp_get_current_user();

		$previous_password    = Input::post( 'previous_password', '' );
		$new_password         = Input::post( 'new_password', '' );
		$confirm_new_password = Input::post( 'confirm_new_password', '' );

		$previous_password_checked = wp_check_password( $previous_password, $user->user_pass, $user->ID );

		$validation_errors = array();
		if ( ! $previous_password_checked ) {
			$validation_errors['incorrect_previous_password'] = __( 'Incorrect Previous Password', 'tutor' );
		}
		if ( empty( $new_password ) ) {
			$validation_errors['new_password_required'] = __( 'New Password Required', 'tutor' );
		}
		if ( empty( $confirm_new_password ) ) {
			$validation_errors['confirm_password_required'] = __( 'Confirm Password Required', 'tutor' );
		}
		if ( $new_password !== $confirm_new_password ) {
			$validation_errors['password_not_matched'] = __( 'New password and confirm password does not matched', 'tutor' );
		}

		if ( $previous_password_checked && ! empty( $new_password ) && $new_password === $confirm_new_password ) {
			wp_set_password( $new_password, $user->ID );
			wp_send_json_success( array( 'message' => __( 'Password Changed', 'tutor' ) ) );
		}

		$first_message = count( $validation_errors ) ? $validation_errors[ array_keys( $validation_errors )[0] ] : __( 'Something went wrong!', 'tutor' );
		wp_send_json_error( array( 'message' => $first_message ) );
	}

	/**
	 * Handle social links
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function tutor_social_profile() {
		tutor_utils()->checking_nonce();

		$user_id           = get_current_user_id();
		$tutor_user_social = tutor_utils()->tutor_user_social_icons();

		foreach ( $tutor_user_social as $key => $social ) {
			$user_social_value = sanitize_text_field( tutor_utils()->input_old( $key ) );
			if ( '' !== $user_social_value ) {
				update_user_meta( $user_id, $key, $user_social_value );
			} else {
				delete_user_meta( $user_id, $key );
			}
		}
		wp_safe_redirect( wp_get_raw_referer() );
		die();
	}
}
