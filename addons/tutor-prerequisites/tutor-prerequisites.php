<?php
/*
Plugin Name: Tutor Prerequisites
Plugin URI: https://www.themeum.com/product/tutor-prerequisites
Description: Specific course you must complete before you can enroll new course by Tutor Prerequisites
Author: Themeum
Version: 1.0.0
Author URI: http://themeum.com
Requires at least: 4.5
Tested up to: 5.0
Text Domain: tutor-prerequisites
Domain Path: /languages/
*/
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Defined the tutor main file
 */
define('TUTOR_PREREQUISITES_VERSION', '1.0.0');
define('TUTOR_PREREQUISITES_FILE', __FILE__);

if ( ! function_exists('TUTOR_PREREQUISITES')) {
	function TUTOR_PREREQUISITES() {
		$info = array(
			'path'              => plugin_dir_path( TUTOR_PREREQUISITES_FILE ),
			'url'               => plugin_dir_url( TUTOR_PREREQUISITES_FILE ),
			'basename'          => plugin_basename( TUTOR_PREREQUISITES_FILE ),
			'version'           => TUTOR_PREREQUISITES_VERSION,
			'nonce_action'      => 'tutor_nonce_action',
			'nonce'             => '_wpnonce',
		);

		return (object) $info;
	}
}

include 'classes/init.php';
$tutor = new TUTOR_PREREQUISITES\init();
$tutor->run(); //Boom