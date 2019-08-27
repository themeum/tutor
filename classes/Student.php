<?php

/**
 * Class Instructor
 * @package TUTOR
 *
 * @since v.1.0.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) )
	exit;


class Student {

	protected $error_msgs = '';
	protected $success_msgs = '';
	public function __construct() {
		add_action('template_redirect', array($this, 'register_student'));
		add_action('template_redirect', array($this, 'update_profile'));

		add_filter('get_avatar_url', array($this, 'filter_avatar'), 10, 3);

		add_action('tutor_action_tutor_reset_password', array($this, 'tutor_reset_password'));

		add_action('wp_ajax_tutor_json_search_students', array($this, 'tutor_json_search_students'));
		add_action('tutor_action_enrol_student', array($this, 'enrol_student'));
	}

	/**
	 * Register new user and mark him as student
	 *
	 * @since v.1.0.0
	 */
	public function register_student(){
		if ( tutils()->array_get('tutor_action', $_POST) !== 'tutor_register_student' ){
			return;
		}
		//Checking nonce
		tutor_utils()->checking_nonce();

		$required_fields = apply_filters('tutor_student_registration_required_fields', array(
			'first_name'                => __('First name field is required', 'tutor'),
			'last_name'                 =>  __('Last name field is required', 'tutor'),
			'email'                     => __('E-Mail field is required', 'tutor'),
			'user_login'                => __('User Name field is required', 'tutor'),
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
		$password       = sanitize_text_field(tutor_utils()->input_old('password'));

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
			$user = get_user_by( 'id', $user_id );
			if( $user ) {
				wp_set_current_user( $user_id, $user->user_login );
				wp_set_auth_cookie( $user_id );
			}

			//Redirect page
			$redirect_page = tutils()->array_get('redirect_to', $_REQUEST);
			if ( ! $redirect_page){
				$redirect_page = tutor_utils()->tutor_dashboard_url();
			}
			wp_redirect($redirect_page);
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
		if (tutils()->array_get('tutor_action', $_POST) !== 'tutor_profile_edit' ){
			return;
		}

		//Checking nonce
		tutor_utils()->checking_nonce();
        do_action('tutor_profile_update_before');

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
			$_tutor_profile_photo = sanitize_text_field(tutils()->array_get('tutor_profile_photo_id', $_POST));

			update_user_meta($user_id, 'phone_number', $phone_number);
			update_user_meta($user_id, '_tutor_profile_bio', $tutor_profile_bio);
			update_user_meta($user_id, '_tutor_profile_photo', $_tutor_profile_photo);

            $tutor_user_social = tutils()->tutor_user_social_icons();
            foreach ($tutor_user_social as $key => $social){
                $user_social_value = sanitize_text_field(tutor_utils()->input_old($key));
                if($user_social_value){
                    update_user_meta($user_id, $key, $user_social_value);
                }else{
                    delete_user_meta($user_id, $key);
                }
            }
		}
        do_action('tutor_profile_update_after', $user_id);
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

	public function tutor_reset_password(){
		//Checking nonce
		tutor_utils()->checking_nonce();

		$user = wp_get_current_user();

		$previous_password = sanitize_text_field($_POST['previous_password']);
		$new_password = sanitize_text_field($_POST['new_password']);
		$confirm_new_password = sanitize_text_field($_POST['confirm_new_password']);

		$previous_password_checked = wp_check_password( $previous_password, $user->user_pass, $user->ID);

		$validation_errors = array();
		if ( ! $previous_password_checked){
			$validation_errors['incorrect_previous_password'] = __('Incorrect Previous Password', 'tutor');
		}
		if (empty($new_password)){
			$validation_errors['new_password_required'] = __('New Password Required', 'tutor');
		}
		if (empty($confirm_new_password)){
			$validation_errors['confirm_password_required'] = __('Confirm Password Required', 'tutor');
		}
		if ( $new_password !== $confirm_new_password){
			$validation_errors['password_not_matched'] = __('New password and confirm password does not matched', 'tutor');
		}
		if (count($validation_errors)){
			$this->error_msgs = $validation_errors;
			add_filter('tutor_reset_password_validation_errors', array($this, 'tutor_student_form_validation_errors'));
			return;
		}

		if ($previous_password_checked && ! empty($new_password) && $new_password === $confirm_new_password){
			wp_set_password($new_password, $user->ID);
			tutor_utils()->set_flash_msg('Password set successfully');
		}

		wp_redirect(wp_get_raw_referer());
		die();
	}


	public function tutor_json_search_students(){
		global $wpdb;

		$term = sanitize_text_field(tutils()->array_get('term', $_POST));

		$student_res = $wpdb->get_results("SELECT * FROM {$wpdb->users} WHERE  display_name LIKE '%{$term}%' OR user_email LIKE '%{$term}%' ");
		$students = array();

		if (tutils()->count($student_res)){
			foreach ($student_res as $student){
				$students[$student->ID] = sprintf(
					esc_html__( '%1$s (#%2$s - %3$s)', 'tutor' ),
					$student->display_name,
					$student->ID,
					$student->user_email
				);
			}
		}

		wp_send_json($students);
	}

	/**
	 * Manually enrol a student this this course
	 * By Course ID, Student ID
	 *
	 * @since v.1.4.0
	 */
	public function enrol_student(){
		$user_id = (int) sanitize_text_field(tutils()->array_get('student_id', $_POST));
		$course_id = (int) sanitize_text_field(tutils()->array_get('course_id', $_POST));

		$isEnrolled = tutils()->is_enrolled($course_id, $user_id);
		if ($isEnrolled){
			$this->success_msgs = get_tnotice(__('This user has been already enrolled on this course', 'tutor'), 'Error', 'danger');
		}else{
			/**
			 * Enroll Now
			 */

			do_action('tutor_before_enroll', $course_id);
			$title = __('Course Enrolled', 'tutor')." &ndash; ".date_i18n(get_option('date_format')) .' @ '.date_i18n(get_option('time_format') ) ;
			$enroll_data = apply_filters('tutor_enroll_data',
				array(
					'post_type'     => 'tutor_enrolled',
					'post_title'    => $title,
					'post_status'   => 'completed',
					'post_author'   => $user_id,
					'post_parent'   => $course_id,
				)
			);

			// Insert the post into the database
			$isEnrolled = wp_insert_post( $enroll_data );
			if ($isEnrolled) {
				do_action('tutor_after_enroll', $course_id, $isEnrolled);
				//Mark Current User as Students with user meta data
				update_user_meta( $user_id, '_is_tutor_student', time() );
			}

			$this->success_msgs = get_tnotice(__('Enrolment has been done', 'tutor'), 'Success', 'success');
		}

		add_filter('student_enrolled_to_course_msg', array($this, 'return_message'));
	}

	public function return_message(){
		return $this->success_msgs;
	}

}