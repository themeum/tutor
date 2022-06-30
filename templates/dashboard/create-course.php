<?php
/**
 * Frontend course create template
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use TUTOR\Input;

get_tutor_header( true );

do_action( 'tutor_load_template_before', 'dashboard.create-course', null );

$course_id = Input::get( 'course_ID', 0, Input::TYPE_INT );
$post      = get_post( $course_id );

setup_postdata( $post );

do_action( 'tutor_frontend_course_builder' );

do_action( 'tutor_load_template_after', 'dashboard.create-course', null );

get_tutor_footer( true );
