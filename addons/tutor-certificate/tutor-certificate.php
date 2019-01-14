<?php
/*
Plugin Name: Tutor Certificate
Plugin URI: https://www.themeum.com/product/tutor-certificate
Description: Student will able to download certificate of completed course
Author: Themeum
Version: 1.0.0
Author URI: http://themeum.com
Requires at least: 4.5
Tested up to: 4.9
Text Domain: tutor-certificate
Domain Path: /languages/
*/
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Defined the tutor main file
 */
define('TUTOR_CERT_VERSION', '1.0.0');
define('TUTOR_CERT_FILE', __FILE__);

if ( ! function_exists('TUTOR_CERT')) {
	function TUTOR_CERT() {
		$info = array(
			'path'              => plugin_dir_path( TUTOR_CERT_FILE ),
			'url'               => plugin_dir_url( TUTOR_CERT_FILE ),
			'basename'          => plugin_basename( TUTOR_CERT_FILE ),
			'version'           => TUTOR_CERT_VERSION,
			'nonce_action'      => 'tutor_nonce_action',
			'nonce'             => '_wpnonce',
		);

		return (object) $info;
	}
}

include 'classes/init.php';
$tutor = new TUTOR_CERT\init();
$tutor->run(); //Boom