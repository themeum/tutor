<?php

/**
 * Class Instructor
 * @package TUTOR
 *
 * @since v.1.0.0
 */

namespace TUTOR;


class Student {

	protected $error_msgs = '';
	public function __construct() {
		add_action('template_redirect', array($this, 'register_student'));
		add_action('template_redirect', array($this, 'update_profile'));

		add_filter('get_avatar_url', array($this, 'filter_avatar'), 10, 3);
	}

	/**
	 * Register new user and mark him as student
	 *
	 * @since v.1.0.0
	 */
	public function register_student(){
		if ( ! isset($_POST['tutor_action'])  ||  $_POST['tutor_action'] !== 'tutor_register_student' ){
			return;
		}
		//Checking nonce
		tutor_utils()->checking_nonce();

		$required_fields = apply_filters('tutor_student_registration_required_fields', array(
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
			add_filter('tutor_student_register_validation_errors', array($this, 'tutor_student_form_validation_errors'));
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
			//'role'          =>  tutor()->student_role,
			'user_pass'     =>  $password,
		);

		$user_id = wp_insert_user( $userdata ) ;
		if ( ! is_wp_error($user_id)){
			update_user_meta($user_id, 'phone_number', $phone_number);
			update_user_meta($user_id, 'description', $tutor_profile_bio);
			update_user_meta($user_id, '_tutor_profile_bio', $tutor_profile_bio);

			$user = get_user_by( 'id', $user_id );
			if( $user ) {
				wp_set_current_user( $user_id, $user->user_login );
				wp_set_auth_cookie( $user_id );
			}

			$dashboard_url = tutor_utils()->tutor_dashboard_url();
			wp_redirect($dashboard_url);
			die();
		}else{
			$this->error_msgs = $user_id->get_error_messages();
			add_filter('tutor_student_register_validation_errors', array($this, 'tutor_student_form_validation_errors'));
			return;
		}

		$registration_page = tutor_utils()->student_register_url();
		wp_redirect($registration_page);
		die();
	}

	public function tutor_student_form_validation_errors(){
		return $this->error_msgs;
	}

	public function update_profile(){
		if ( ! isset($_POST['tutor_action'])  ||  $_POST['tutor_action'] !== 'tutor_profile_edit' ){
			return;
		}

		//Checking nonce
		tutor_utils()->checking_nonce();

		$user_id = get_current_user_id();
		$first_name     = sanitize_text_field(tutor_utils()->input_old('first_name'));
		$last_name      = sanitize_text_field(tutor_utils()->input_old('last_name'));
		$phone_number   = sanitize_text_field(tutor_utils()->input_old('phone_number'));
		$tutor_profile_bio = wp_kses_post(tutor_utils()->input_old('tutor_profile_bio'));

		$userdata = array(
			'ID'            =>  $user_id,
			'first_name'    =>  $first_name,
			'last_name'     =>  $last_name,
		);
		$user_id  = wp_update_user( $userdata );

		if ( ! is_wp_error( $user_id ) ) {
			$_tutor_profile_photo = sanitize_text_field(tutor_utils()->avalue_dot('tutor_profile_photo_id', $_POST));

			update_user_meta($user_id, 'phone_number', $phone_number);
			update_user_meta($user_id, '_tutor_profile_bio', $tutor_profile_bio);
			update_user_meta($user_id, '_tutor_profile_photo', $_tutor_profile_photo);
		}

		wp_redirect(wp_get_raw_referer());
		die();
	}

	/**
	 * @param $url
	 * @param $id_or_email
	 * @param $args
	 *
	 * @return false|string
	 *
	 * Change avatar URL with Tutor User Photo
	 */

	public function filter_avatar( $url, $id_or_email, $args){
		global $wpdb;

		$finder = false;

        if ( is_numeric( $id_or_email ) ) {
            $finder = absint( $id_or_email ) ;
        } elseif ( is_string( $id_or_email ) ) {
            $finder = $id_or_email;
        } elseif ( $id_or_email instanceof WP_User ) {
            // User Object
            $finder = $id_or_email->ID;
        } elseif ( $id_or_email instanceof WP_Post ) {
            // Post Object
            $finder = (int) $id_or_email->post_author;
        } elseif ( $id_or_email instanceof WP_Comment ) {
            return $url;
        }

        if ( ! $finder){
            return $url;
        }

		$user_id = (int) $wpdb->get_var("SELECT ID FROM {$wpdb->users} WHERE ID = '{$finder}' OR user_email = '{$finder}' ");
		if ($user_id){
			$profile_photo = get_user_meta($user_id, '_tutor_profile_photo', true);
			if ($profile_photo){
				$url = wp_get_attachment_image_url($profile_photo, 'thumbnail');
			}
		}
		return $url;
	}

}