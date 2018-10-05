<?php

/**
 * Class Teacher
 * @package TUTOR
 *
 * @since v.1.0.0
 */

namespace TUTOR;


class Teacher {

	protected $error_msgs = '';
	public function __construct() {
		add_action('template_redirect', array($this, 'register_teacher'));
		add_action('template_redirect', array($this, 'apply_teacher'));
	}


	/**
	 * Register new user and mark him as teacher
	 *
	 * @since v.1.0.0
	 */
	public function register_teacher(){
		if ( ! isset($_POST['tutor_action'])  ||  $_POST['tutor_action'] !== 'tutor_register_teacher' ){
			return;
		}
		//Checking nonce
		tutor_utils()->checking_nonce();

		$required_fields = apply_filters('tutor_teacher_registration_required_fields', array(
			'first_name'                => __('First name field is required', 'tutor'),
			'last_name'                 =>  __('Last name field is required', 'tutor'),
			'email'                     => __('E-Mail field is required', 'tutor'),
			'user_login'                => __('User Name field is required', 'tutor'),
			'phone_number'              => __('Phone Number field is required', 'tutor'),
			'password'                  => __('Password field is required', 'tutor'),
			'password_confirmation'     => __('Password Confirmation field is required', 'tutor'),
		));

		$validation_errors = array();
		foreach ($required_fields as $required_key => $required_value){
			if (empty($_POST[$required_key])){
				$validation_errors[$required_key] = $required_value;
			}
		}

		if (!filter_var(tutor_utils()->input_old('email'), FILTER_VALIDATE_EMAIL)) {
			$validation_errors['email'] = __('Valid E-Mail is required', 'tutor');
		}
		if (tutor_utils()->input_old('password') !== tutor_utils()->input_old('password_confirmation')){
			$validation_errors['password_confirmation'] = __('Confirm password does not matched with Password field', 'tutor');
		}

		if (count($validation_errors)){
			$this->error_msgs = $validation_errors;
			add_filter('tutor_teacher_register_validation_errors', array($this, 'tutor_teacher_form_validation_errors'));
			return;
		}

		$first_name     = sanitize_text_field(tutor_utils()->input_old('first_name'));
		$last_name      = sanitize_text_field(tutor_utils()->input_old('last_name'));
		$email          = sanitize_text_field(tutor_utils()->input_old('email'));
		$user_login     = sanitize_text_field(tutor_utils()->input_old('user_login'));
		$phone_number   = sanitize_text_field(tutor_utils()->input_old('phone_number'));
		$password       = sanitize_text_field(tutor_utils()->input_old('password'));
		$tutor_profile_bio = wp_kses_post(tutor_utils()->input_old('tutor_profile_bio'));

		$userdata = array(
			'user_login'    =>  $user_login,
			'user_email'    =>  $email,
			'first_name'    =>  $first_name,
			'last_name'     =>  $last_name,
			//'role'          =>  tutor()->teacher_role,
			'user_pass'     =>  $password,
		);

		$user_id = wp_insert_user( $userdata ) ;
		if ( ! is_wp_error($user_id)){
			update_user_meta($user_id, 'phone_number', $phone_number);
			update_user_meta($user_id, 'description', $tutor_profile_bio);

			update_user_meta($user_id, '_is_tutor_teacher', time());
			update_user_meta($user_id, '_tutor_teacher_status', apply_filters('tutor_initial_teacher_status', 'pending'));

			$user = get_user_by( 'id', $user_id );
			if( $user ) {
				wp_set_current_user( $user_id, $user->user_login );
				wp_set_auth_cookie( $user_id );
			}
		}else{
			$this->error_msgs = $user_id->get_error_messages();
			add_filter('tutor_teacher_register_validation_errors', array($this, 'tutor_teacher_form_validation_errors'));
			return;
		}

		wp_redirect(tutor_utils()->input_old('_wp_http_referer'));
		die();
	}

	public function tutor_teacher_form_validation_errors(){
		return $this->error_msgs;
	}

	/**
	 *
	 * Usage for teacher applying when a user already logged in
	 *
	 * @since v.1.0.0
	 */
	public function apply_teacher(){
		if ( ! isset($_POST['tutor_action'])  ||  $_POST['tutor_action'] !== 'tutor_apply_teacher' ){
			return;
		}
		//Checking nonce
		tutor_utils()->checking_nonce();

		$user_id = get_current_user_id();
		if ($user_id){
			if (tutor_utils()->is_teacher()){
				die(__('Already applied for teacher', 'tutor'));
			}else{
				update_user_meta($user_id, '_is_tutor_teacher', time());
				update_user_meta($user_id, '_tutor_teacher_status', apply_filters('tutor_initial_teacher_status', 'pending'));
			}
		}else{
			die(__('Permission denied', 'tutor'));
		}

		wp_redirect(tutor_utils()->input_old('_wp_http_referer'));
		die();
	}

}