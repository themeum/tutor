<?php
/*
Plugin Name: LMS
Plugin URI: http://https://themeum.com/lms
Description: A WordPress complete solution for creating a Learning Management System (LMS). It can help you to create
 courses, lessons and quizzes.
Author: Themeum
Version: 1.0.0
Author URI: http://themeum.com
Requires at least: 3.8
Tested up to: 4.9

Text Domain: lms
Domain Path: /languages/
*/
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Defined the lms main file
 */
define('LMS_VERSION', '1.0.0');
define('LMS_FILE', __FILE__);

function lms(){
	$info = array(
		'path' => plugin_dir_path(LMS_FILE),
		'url' => plugin_dir_url(LMS_FILE),
		'basename' => plugin_basename(LMS_FILE),
		'version' => LMS_VERSION,
		'nonce_action' => 'lms_nonce_action',
		'nonce' => '_wpnonce',
	);

	return (object) $info;
}

include 'classes/init.php';

function lms_utils(){
	return new \LMS\Utils();
}

$lms = new \LMS\init();
$lms->run(); //Boom