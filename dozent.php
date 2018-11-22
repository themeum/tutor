<?php
/*
Plugin Name: Dozent
Plugin URI: http://https://themeum.com/dozent
Description: Dozent is a complete solution for creating a Learning Management System in WordPress way. It can help you to create
 courses, lessons and quizzes.
Author: Themeum
Version: 1.0.0
Author URI: http://themeum.com
Requires at least: 4.5
Tested up to: 4.9
Text Domain: dozent
Domain Path: /languages/
*/
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Defined the dozent main file
 */
define('DOZENT_VERSION', '1.0.0');
define('DOZENT_FILE', __FILE__);

if ( ! function_exists('dozent')) {
	function dozent() {
		$info = array(
			'path'              => plugin_dir_path( DOZENT_FILE ),
			'url'               => plugin_dir_url( DOZENT_FILE ),
			'basename'          => plugin_basename( DOZENT_FILE ),
			'version'           => DOZENT_VERSION,
			'nonce_action'      => 'dozent_nonce_action',
			'nonce'             => '_wpnonce',
			'course_post_type'  => apply_filters( 'dozent_course_post_type', 'course' ),
			'lesson_post_type'  => apply_filters( 'dozent_lesson_post_type', 'lesson' ),
			'teacher_role'      => apply_filters( 'dozent_teacher_role', 'dozent_teacher' ),
			'teacher_role_name' => apply_filters( 'dozent_teacher_role_name', __( 'Dozent Teacher', 'dozent' ) ),
			'template_path'     => apply_filters( 'dozent_template_path', 'dozent/' ),
		);

		return (object) $info;
	}
}
include 'classes/init.php';

function dozent_utils(){
	return new \DOZENT\Utils();
}

$dozent = new \DOZENT\init();
$dozent->run(); //Boom