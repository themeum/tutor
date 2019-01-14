<?php
/*
Plugin Name: Tutor EDD
Plugin URI: https://www.themeum.com/product/tutor-edd
Description: Sell your course by EDD
Author: Themeum
Version: 1.0.0
Author URI: http://themeum.com
Requires at least: 4.5
Tested up to: 4.9
Text Domain: tutor-edd
Domain Path: /languages/
*/
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Defined the tutor main file
 */
define('TUTOR_EDD_VERSION', '1.0.0');
define('TUTOR_EDD_FILE', __FILE__);

if ( ! function_exists('TUTOR_EDD')) {
	function TUTOR_EDD() {
		$info = array(
			'path'              => plugin_dir_path( TUTOR_EDD_FILE ),
			'url'               => plugin_dir_url( TUTOR_EDD_FILE ),
			'basename'          => plugin_basename( TUTOR_EDD_FILE ),
			'version'           => TUTOR_EDD_VERSION,
			'nonce_action'      => 'tutor_nonce_action',
			'nonce'             => '_wpnonce',
		);

		return (object) $info;
	}
}

include 'classes/init.php';

function tutor_edd_utils(){
	return new \TUTOR_EDD\Utils();
}

$tutor = new TUTOR_EDD\init();
$tutor->run(); //Boom