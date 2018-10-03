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
	}

	public function tutor_dashboard(){
		ob_start();
		tutor_load_template( 'dashboard.student.index' );
		return apply_filters( 'tutor_dashboard/student/index', ob_get_clean() );
	}

}