<?php
/**
 * Class Email Notification
 * @package TUTOR
 *
 * @since v.1.0.0
 */

namespace TUTOR_EMAIL;

if ( ! defined( 'ABSPATH' ) )
	exit;

class EmailNotification{

	public function __construct() {
		add_action('admin_menu', array($this, 'register_menu'));

		add_action('tutor_quiz_finished_after', array($this, 'quiz_finished_send_email_to_student'), 10, 1);
		add_action('tutor_course_complete_after', array($this, 'course_complete_email_to_student'), 10, 1);
		add_action('tutor_course_complete_after', array($this, 'course_complete_email_to_teacher'), 10, 1);
		add_action('tutor_after_enroll', array($this, 'course_enroll_email'), 10, 1);
		add_action('tutor_after_add_question', array($this, 'tutor_after_add_question'), 10, 2);
		add_action('tutor_lesson_completed_after', array($this, 'tutor_lesson_completed_after'), 10, 1);
	}

	public function register_menu(){
		add_submenu_page('tutor', __('E-Mails', 'tutor'), __('E-Mails', 'tutor'), 'manage_tutor', 'tutor_emails', array($this, 'tutor_emails') );
	}
	public function tutor_emails(){
		include TUTOR_EMAIL()->path.'views/pages/tutor_emails.php';
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

	/**
	 * @return string
	 *
	 * Get content type
	 */
	public function get_content_type() {
		return apply_filters('tutor_email_content_type', 'text/html');
	}


	public function get_message($message = '', $search = array(), $replace = array()){

		$email_footer_text = tutor_utils()->get_option('email_footer_text');

		$message = str_replace($search, $replace, $message);
		if ($email_footer_text){
			$message .= $email_footer_text;
		}

		return $message;
	}


	/**
	 * @param $course_id
	 * 
	 * Send course completion E-Mail to Student
	 */
	public function course_complete_email_to_student($course_id){
		$course_completed_to_student = tutor_utils()->get_option('email_to_students.completed_course');

		if ( ! $course_completed_to_student){
			return;
		}

		$user_id = get_current_user_id();

		$course = get_post($course_id);
		$student = get_userdata($user_id);

		$completion_time = tutor_utils()->is_completed_course($course_id);
		$completion_time = $completion_time ? $completion_time : time();

		$completion_time_format = date_i18n(get_option('date_format'), $completion_time).' '.date_i18n(get_option('time_format'), $completion_time);

		$file_tpl_variable = array(
			'{student_username}',
			'{course_name}',
			'{completion_time}',
			'{course_url}',
		);

		$replace_data = array(
			$student->display_name,
			$course->post_title,
			$completion_time_format,
			get_the_permalink($course_id),
		);

		$subject = __('You just completed '.$course->post_title, 'tutor');
		
		ob_start();
		tutor_load_template( 'email.to_student_course_completed' );
		$email_tpl = apply_filters( 'tutor_email_tpl/course_completed', ob_get_clean() );
		$message = $this->get_message($email_tpl, $file_tpl_variable, $replace_data );
		
		$header = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header = apply_filters('student_course_completed_email_header', $header, $course_id);

		$this->send($student->user_email, $subject, $message, $header);
	}


	public function course_complete_email_to_teacher($course_id){
		$course_completed_to_teacher = tutor_utils()->get_option('email_to_teachers.a_student_completed_course');

		if ( ! $course_completed_to_teacher){
			return;
		}

		$user_id = get_current_user_id();
		$student = get_userdata($user_id);

		$course = get_post($course_id);
		$teacher = get_userdata($course->post_author);

		$completion_time = tutor_utils()->is_completed_course($course_id);
		$completion_time = $completion_time ? $completion_time : time();

		$completion_time_format = date_i18n(get_option('date_format'), $completion_time).' '.date_i18n(get_option('time_format'), $completion_time);


		$file_tpl_variable = array(
			'{teacher_username}',
			'{student_username}',
			'{course_name}',
			'{completion_time}',
			'{course_url}',
		);

		$replace_data = array(
			$teacher->display_name,
			$student->display_name,
			$course->post_title,
			$completion_time_format,
			get_the_permalink($course_id),
		);

		$subject = __($student->display_name.' just completed '.$course->post_title, 'tutor');

		ob_start();
		tutor_load_template( 'email.to_teacher_course_completed' );
		$email_tpl = apply_filters( 'tutor_email_tpl/course_completed', ob_get_clean() );
		$message = $this->get_message($email_tpl, $file_tpl_variable, $replace_data );

		$header = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header = apply_filters('student_course_completed_email_header', $header, $course_id);

		$this->send($teacher->user_email, $subject, $message, $header);
	}


	/**
	 * Send the quiz to Student
	 *
	 * @param $attempt_id
	 */

	public function quiz_finished_send_email_to_student($attempt_id){
		$quiz_completed = tutor_utils()->get_option('email_to_students.quiz_completed');
		if ( ! $quiz_completed){
			return;
		}
		
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

		ob_start();
		tutor_load_template( 'email.to_student_quiz_completed' );
		$email_tpl = apply_filters( 'tutor_email_tpl/quiz_completed', ob_get_clean() );

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
		
		$message = $this->get_message($email_tpl, $file_tpl_variable, $replace_data );

		$subject = apply_filters('student_quiz_completed_email_subject', __("Thank you for {$quiz_name}  answers, we have received", "tutor"));
		$header = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header = apply_filters('student_quiz_completed_email_header', $header, $attempt_id);

		$this->send($user->user_email, $subject, $message, $header );
	}


	public function course_enroll_email($course_id){
		$enroll_notification = tutor_utils()->get_option('email_to_teachers.a_student_enrolled_in_course');

		if ( ! $enroll_notification){
			return;
		}

		$user_id = get_current_user_id();
		$student = get_userdata($user_id);

		$course = get_post($course_id);
		$teacher = get_userdata($course->post_author);

		$enroll_time = time();
		$enroll_time_format = date_i18n(get_option('date_format'), $enroll_time).' '.date_i18n(get_option('time_format'), $enroll_time);

		$file_tpl_variable = array(
			'{teacher_username}',
			'{student_username}',
			'{course_name}',
			'{enroll_time}',
			'{course_url}',
		);

		$replace_data = array(
			$teacher->display_name,
			$student->display_name,
			$course->post_title,
			$enroll_time_format,
			get_the_permalink($course_id),
		);

		$subject = __($student->display_name.' enrolled '.$course->post_title, 'tutor');

		ob_start();
		tutor_load_template( 'email.to_teacher_course_enrolled' );
		$email_tpl = apply_filters( 'tutor_email_tpl/to_teacher_course_enrolled', ob_get_clean() );
		$message = $this->get_message($email_tpl, $file_tpl_variable, $replace_data );

		$header = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header = apply_filters('student_course_completed_email_header', $header, $course_id);

		$this->send($teacher->user_email, $subject, $message, $header);
	}


	public function tutor_after_add_question($course_id, $comment_id){
		$enroll_notification = tutor_utils()->get_option('email_to_teachers.a_student_placed_question');
		if ( ! $enroll_notification){
			return;
		}

		$user_id = get_current_user_id();
		$student = get_userdata($user_id);

		$course = get_post($course_id);
		$teacher = get_userdata($course->post_author);

		$get_comment = tutor_utils()->get_qa_question($comment_id);
		$question = $get_comment->comment_content;
		$question_title = $get_comment->question_title;

		$file_tpl_variable = array(
			'{teacher_username}',
			'{student_username}',
			'{course_name}',
			'{course_url}',
			'{question_title}',
			'{question}',
		);

		$replace_data = array(
			$teacher->display_name,
			$student->display_name,
			$course->post_title,
			get_the_permalink($course_id),
			$question_title,
			$question,
		);

		$subject = __(sprintf('%s Asked a question to %s', $student->display_name, $course->post_title), 'tutor');

		ob_start();
		tutor_load_template( 'email.to_teacher_asked_question_by_student' );
		$email_tpl = apply_filters( 'tutor_email_tpl/to_teacher_asked_question_by_student', ob_get_clean() );
		$message = $this->get_message($email_tpl, $file_tpl_variable, $replace_data );

		$header = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header = apply_filters('to_teacher_asked_question_by_student_email_header', $header, $course_id);

		$this->send($teacher->user_email, $subject, $message, $header);
	}


	public function tutor_lesson_completed_after($lesson_id){
		$course_completed_to_teacher = tutor_utils()->get_option('email_to_teachers.a_student_completed_lesson');

		if ( ! $course_completed_to_teacher){
			return;
		}
		
		$user_id = get_current_user_id();
		$student = get_userdata($user_id);
		
		
		$course_id = tutor_utils()->get_course_id_by_lesson($lesson_id);

		$lesson = get_post($lesson_id);
		$course = get_post($course_id);
		$teacher = get_userdata($course->post_author);

		$completion_time =  time();
		$completion_time_format = date_i18n(get_option('date_format'), $completion_time).' '.date_i18n(get_option('time_format'), $completion_time);
		
		$file_tpl_variable = array(
			'{teacher_username}',
			'{student_username}',
			'{course_name}',
			'{lesson_name}',
			'{completion_time}',
			'{lesson_url}',
		);

		$replace_data = array(
			$teacher->display_name,
			$student->display_name,
			$course->post_title,
			$lesson->post_title,
			$completion_time_format,
			get_the_permalink($lesson_id),
		);

		$subject = __($student->display_name.' just completed lesson '.$course->post_title, 'tutor');

		ob_start();
		tutor_load_template( 'email.to_teacher_lesson_completed' );
		$email_tpl = apply_filters( 'tutor_email_tpl/lesson_completed', ob_get_clean() );
		$message = $this->get_message($email_tpl, $file_tpl_variable, $replace_data );

		$header = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header = apply_filters('student_lesson_completed_email_header', $header, $lesson_id);

		$this->send($teacher->user_email, $subject, $message, $header);
	}


}