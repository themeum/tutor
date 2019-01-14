<?php
/*
Plugin Name: Tutor Course Preview
Plugin URI: https://www.themeum.com/product/tutor-course-preview
Description: Add unlimited preview/ private files to any Tutor course
Author: Themeum
Version: 1.0.0
Author URI: http://themeum.com
Requires at least: 4.5
Tested up to: 4.9
Text Domain: tutor-course-preview
Domain Path: /languages/
*/
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Defined the tutor main file
 */
define('TUTOR_CP_VERSION', '1.0.0');
define('TUTOR_CP_FILE', __FILE__);

if ( ! function_exists('TUTOR_CP')) {
	function TUTOR_CP() {
		$info = array(
			'path'              => plugin_dir_path( TUTOR_CP_FILE ),
			'url'               => plugin_dir_url( TUTOR_CP_FILE ),
			'basename'          => plugin_basename( TUTOR_CP_FILE ),
			'version'           => TUTOR_CP_VERSION,
			'nonce_action'      => 'tutor_nonce_action',
			'nonce'             => '_wpnonce',
		);

		return (object) $info;
	}
}

include 'classes/init.php';
$tutor = new TUTOR_CP\init();
$tutor->run(); //Boom