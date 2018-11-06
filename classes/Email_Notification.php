<?php
/**
 * Class Email Notification
 * @package TUTOR
 *
 * @since v.1.0.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Email_Notification{

	public function __construct() {
		$quiz_completed = tutor_utils()->get_option('email_to_students.quiz_completed');
		if ($quiz_completed){
			add_action('tutor_quiz_finished_before', array($this, 'tutor_quiz_finished_before'), 10, 1);
		}

	}

	/**
	 * Send the quiz to Student
	 *
	 * @param $attempt_id
	 */

	public function tutor_quiz_finished_before($attempt_id){
		$attempt = tutor_utils()->get_attempt($attempt_id);
		$attempt_info = tutor_utils()->quiz_attempt_info($attempt_id);

		$submission_time = tutor_utils()->avalue_dot('submission_time', $attempt_info);
		$submission_time = $submission_time ? $submission_time : time();

		$quiz_id = tutor_utils()->avalue_dot('comment_post_ID', $attempt);
		$quiz_name = get_the_title($quiz_id);
		$course = tutor_utils()->get_course_by_quiz($quiz_id);
		$course_id = tutor_utils()->avalue_dot('ID', $course);
		$course_title = get_the_title($course_id);
		$submission_time_format = date_i18n(get_option('date_format'), $submission_time).' '.date_i18n(get_option('time_format'), $submission_time);

		$quiz_url = get_the_permalink($quiz_id);
		$user = get_userdata(tutor_utils()->avalue_dot('user_id', $attempt));

		print_r($user->user_email);

		ob_start();
		tutor_load_template( 'email.to_student_quiz_completed' );
		$email_tmpl = apply_filters( 'tutor_email_tpl/quiz_completed', ob_get_clean() );

		$file_tpl_variable = array(
			'{username}',
			'{quiz_name}',
			'{course_name}',
			'{submission_time}',
			'{quiz_url}',
		);

		$replace_data = array(
			$user->display_name,
			$quiz_name,
			$course_title,
			$submission_time_format,
			"<a href='{$quiz_url}'>{$quiz_url}</a>",
		);

		$email_footer_text = tutor_utils()->get_option('email_footer_text');

		$email_tmpl = str_replace($file_tpl_variable, $replace_data, $email_tmpl);
		if ($email_footer_text){
			$email_tmpl .= $email_footer_text;
		}


		$subject = apply_filters('student_quiz_completed_email_subject', __("We have safely received {$quiz_name}  answers", "tutor"));
		$header = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header = apply_filters('student_quiz_completed_email_header', $header, $attempt_id);

		$this->send($user->user_email, $subject, $email_tmpl, $header );

	}


	/**
	 * @param $to
	 * @param $subject
	 * @param $message
	 * @param $headers
	 * @param array $attachments
	 *
	 * @return bool
	 *
	 *
	 * Send E-Mail Notification for Tutor Event
	 */

	public function send( $to, $subject, $message, $headers, $attachments = array() ) {
		add_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		add_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );

		$message = apply_filters( 'tutor_mail_content', $message );
		$return  = wp_mail( $to, $subject, $message, $headers, $attachments );

		remove_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		remove_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		remove_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );

		return $return;
	}



	/**
	 * Get the from name for outgoing emails from tutor
	 *
	 * @return string
	 */
	public function get_from_name() {
		$email_from_name = tutor_utils()->get_option('email_from_name');
		$from_name = apply_filters( 'tutor_email_from_name', $email_from_name );
		return wp_specialchars_decode( esc_html( $from_name ), ENT_QUOTES );
	}

	/**
	 * Get the from name for outgoing emails from tutor
	 *
	 * @return string
	 */
	public function get_from_address() {
		$email_from_address = tutor_utils()->get_option('email_from_address');
		$from_address = apply_filters( 'tutor_email_from_address', $email_from_address );
		return sanitize_email( $from_address );
	}



	public function get_content_type() {
		return 'text/html';
	}


}