<?php
/**
 * Class Shortcode
 * @package TUTOR
 *
 * @since v.1.0.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Shortcode {

	public function __construct() {
		add_shortcode('tutor_student_registration_form', array($this, 'student_registration_form'));
		add_shortcode('tutor_dashboard', array($this, 'tutor_dashboard'));
		add_shortcode('tutor_instructor_registration_form', array($this, 'instructor_registration_form'));
	}

	/**
	 * @return mixed
	 *
	 * Instructor Registration Shortcode
	 *
	 * @since v.1.0.0
	 */
	public function student_registration_form(){
		ob_start();
		if (is_user_logged_in()){
			tutor_load_template( 'dashboard.logged-in' );
		}else{
			tutor_load_template( 'dashboard.registration' );
		}
		return apply_filters( 'tutor/student/register', ob_get_clean() );
	}

	/**
	 * @return mixed
	 *
	 * Tutor Dashboard for students
	 *
	 * @since v.1.0.0
	 */
	public function tutor_dashboard(){
		ob_start();
		if (is_user_logged_in()){
			tutor_load_template( 'dashboard.index' );
		}else{
			tutor_load_template( 'global.login' );
		}
		return apply_filters( 'tutor_dashboard/index', ob_get_clean() );
	}

	/**
	 * @return mixed
	 *
	 * Instructor Registration Shortcode
	 *
	 * @since v.1.0.0
	 */
	public function instructor_registration_form(){
		ob_start();
		if (is_user_logged_in()){
			tutor_load_template( 'dashboard.instructor.logged-in' );
		}else{
			tutor_load_template( 'dashboard.instructor.registration' );
		}
		return apply_filters( 'tutor_dashboard/student/index', ob_get_clean() );
	}

}