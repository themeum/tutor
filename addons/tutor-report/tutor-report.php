<?php
/*
Plugin Name: Tutor Report
Plugin URI: https://www.themeum.com/product/tutor-report
Description: Check your tutor assets performance through tutor report
Author: Themeum
Version: 1.0.0
Author URI: http://themeum.com
Requires at least: 4.5
Tested up to: 4.9
Text Domain: tutor-report
Domain Path: /languages/
*/
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Defined the tutor main file
 */
define('TUTOR_REPORT_VERSION', '1.0.0');
define('TUTOR_REPORT_FILE', __FILE__);

if ( ! function_exists('TUTOR_REPORT')) {
	function TUTOR_REPORT() {
		$info = array(
			'path'              => plugin_dir_path( TUTOR_REPORT_FILE ),
			'url'               => plugin_dir_url( TUTOR_REPORT_FILE ),
			'basename'          => plugin_basename( TUTOR_REPORT_FILE ),
			'version'           => TUTOR_REPORT_VERSION,
			'nonce_action'      => 'tutor_nonce_action',
			'nonce'             => '_wpnonce',
		);

		return (object) $info;
	}
}

include 'classes/init.php';
$tutor = new TUTOR_REPORT\init();
$tutor->run(); //Boom