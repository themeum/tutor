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
		add_shortcode('tutor_dashboard', array($this, 'tutor_dashboard'));
		add_shortcode('tutor_teacher_registration_form', array($this, 'tutor_teacher_registration_form'));
	}

	public function tutor_dashboard(){
		ob_start();
		if (is_user_logged_in()){
			tutor_load_template( 'dashboard.student.index' );
		}else{
			tutor_load_template( 'global.login' );
		}
		return apply_filters( 'tutor_dashboard/student/index', ob_get_clean() );
	}


	public function tutor_teacher_registration_form(){


		ob_start();
		if (is_user_logged_in()){
			tutor_load_template( 'global.logged-in' );
		}else{
			tutor_load_template( 'dashboard.teacher.registration' );
		}
		return apply_filters( 'tutor_dashboard/student/index', ob_get_clean() );

	}

}