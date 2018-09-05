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

include 'classes/init.php';

function lms_utils(){
	return new \LMS\Utils();
}
function lms(){
	return new \LMS\init();
}
lms()->run(); //Boom