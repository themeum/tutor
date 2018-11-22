<?php
/**
 * Class Shortcode
 * @package DOZENT
 *
 * @since v.1.0.0
 */

namespace DOZENT;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Shortcode {

	public function __construct() {
		add_shortcode('dozent_student_registration_form', array($this, 'student_registration_form'));
		add_shortcode('dozent_student_dashboard', array($this, 'dozent_student_dashboard'));
		add_shortcode('dozent_teacher_registration_form', array($this, 'teacher_registration_form'));
	}

	/**
	 * @return mixed
	 *
	 * Teacher Registration Shortcode
	 *
	 * @since v.1.0.0
	 */
	public function student_registration_form(){
		ob_start();
		if (is_user_logged_in()){
			dozent_load_template( 'dashboard.student.logged-in' );
		}else{
			dozent_load_template( 'dashboard.student.registration' );
		}
		return apply_filters( 'dozent/student/register', ob_get_clean() );
	}

	/**
	 * @return mixed
	 *
	 * Dozent Dashboard for students
	 *
	 * @since v.1.0.0
	 */
	public function dozent_student_dashboard(){
		ob_start();
		if (is_user_logged_in()){
			dozent_load_template( 'dashboard.student.index' );
		}else{
			dozent_load_template( 'global.login' );
		}
		return apply_filters( 'dozent_dashboard/student/index', ob_get_clean() );
	}

	/**
	 * @return mixed
	 *
	 * Teacher Registration Shortcode
	 *
	 * @since v.1.0.0
	 */
	public function teacher_registration_form(){
		ob_start();
		if (is_user_logged_in()){
			dozent_load_template( 'dashboard.teacher.logged-in' );
		}else{
			dozent_load_template( 'dashboard.teacher.registration' );
		}
		return apply_filters( 'dozent_dashboard/student/index', ob_get_clean() );
	}

}