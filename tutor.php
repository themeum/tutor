<?php
/**
 * Plugin Name: Tutor LMS
 * Plugin URI: https://www.themeum.com/product/tutor-lms/
 * Description: Tutor is a complete solution for creating a Learning Management System in WordPress way. It can help you to create small to large scale online education site very conveniently. Power features like report, certificate, course preview, private file sharing make Tutor a robust plugin for any educational institutes.
 * Author: Themeum
 * Version: 2.1.10
 * Author URI: https://themeum.com
 * Requires at least: 5.3
 * Tested up to: 6.2
 * License: GPLv2 or later
 * Text Domain: tutor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once "vendor/autoload.php";

/**
 * Defined the tutor main file
 */
define( 'TUTOR_VERSION', '2.1.10' );
define( 'TUTOR_FILE', __FILE__ );

/**
 * Load tutor text domain for translation
 */
add_action(
	'init',
	function () {
		load_plugin_textdomain( 'tutor', false, basename( dirname( __FILE__ ) ) . '/languages' );
	}
);

/**
 * Tutor Helper function
 *
 * @since v.1.0.0
 */

if ( ! function_exists( 'tutor' ) ) {
	/**
	 * Tutor variable and declarations
	 *
	 * @return object
	 */
	function tutor() {
		if ( isset( $GLOBALS['tutor_plugin_info'] ) ) {
			return $GLOBALS['tutor_plugin_info'];
		}

		$path    = plugin_dir_path( TUTOR_FILE );
		$has_pro = defined( 'TUTOR_PRO_VERSION' );

		// Prepare the basepath.
		$home_url = get_home_url();
		$parsed = parse_url($home_url);
		$base_path = (is_array($parsed) && isset($parsed['path'])) ? $parsed['path'] : '/';
		$base_path = rtrim($base_path, '/') . '/';
		// Get current URL.
		$current_url = trailingslashit( $home_url ) . substr($_SERVER['REQUEST_URI'], strlen($base_path));

		$info = array(
			'path'                 => $path,
			'url'                  => plugin_dir_url( TUTOR_FILE ),
			'icon_dir'             => plugin_dir_url( TUTOR_FILE ) . 'assets/images/images-v2/icons/',
			'v2_img_dir'           => plugin_dir_url( TUTOR_FILE ) . 'assets/images/images-v2/',
			'current_url'          => $current_url,
			'basename'             => plugin_basename( TUTOR_FILE ),
			'basepath'             => $base_path,
			'version'              => TUTOR_VERSION,
			'nonce_action'         => 'tutor_nonce_action',
			'nonce'                => '_tutor_nonce',
			'course_post_type'     => apply_filters( 'tutor_course_post_type', 'courses' ),
			'lesson_post_type'     => apply_filters( 'tutor_lesson_post_type', 'lesson' ),
			'instructor_role'      => apply_filters( 'tutor_instructor_role', 'tutor_instructor' ),
			'instructor_role_name' => apply_filters( 'tutor_instructor_role_name', __( 'Tutor Instructor', 'tutor' ) ),
			'template_path'        => apply_filters( 'tutor_template_path', 'tutor/' ),
			'has_pro'              => apply_filters( 'tutor_has_pro', $has_pro ),
			// @since v2.0.6.
			'topics_post_type'	   	 => apply_filters( 'tutor_topics_post_type', 'topics' ),
			'announcement_post_type' => apply_filters( 'tutor_announcement_post_type', 'tutor_announcements' ),
			'assignment_post_type'	 => apply_filters( 'tutor_assignment_post_type', 'tutor_assignments' ),
			'enrollment_post_type'   => apply_filters( 'tutor_enrollment_post_type', 'tutor_enrolled' ),
			'quiz_post_type'   		 => apply_filters( 'tutor_quiz_post_type', 'tutor_quiz' ),
			'zoom_post_type'   		 => apply_filters( 'tutor_zoom_meeting_post_type', 'tutor_zoom_meeting' ),
		);

		$GLOBALS['tutor_plugin_info'] = (object) $info;
		return $GLOBALS['tutor_plugin_info'];
	}
}

if ( ! class_exists( 'Tutor' ) ) {
	include_once 'classes/Tutor.php';
}

/**
 * Get all helper functions/methods
 *
 * @return \TUTOR\Utils
 */

if ( ! class_exists( '\TUTOR\Utils' ) ) {
	include_once 'classes/Utils.php';
}

if ( ! function_exists( 'tutor_utils' ) ) {
	/**
	 * Access tutor utils functions
	 *
	 * @return \TUTOR\Utils
	 */
	function tutor_utils() {
		if ( ! isset( $GLOBALS['tutor_utils_object'] ) ) {
			// Use runtime cache
			$GLOBALS['tutor_utils_object'] = new \TUTOR\Utils();
		}

		return $GLOBALS['tutor_utils_object'];
	}
}


if ( ! function_exists( 'tutils' ) ) {
	/**
	 * Alis of tutor_utils()
	 *
	 * @return \TUTOR\Utils
	 *
	 * @since v.1.3.4
	 */
	function tutils() {
		return tutor_utils();
	}
}

/**
 * Do some task during activation
 *
 * @moved here from Tutor Class
 * @since v.1.5.2
 */
register_activation_hook( TUTOR_FILE, array( '\TUTOR\Tutor', 'tutor_activate' ) );
register_deactivation_hook( TUTOR_FILE, array( '\TUTOR\Tutor', 'tutor_deactivation' ) );

/**
 * Run main instance of the Tutor
 *
 * @return null|\TUTOR\Tutor
 *
 * @since v.1.2.0
 */
if ( ! function_exists( 'tutor_lms' ) ) {
	function tutor_lms() {
		return \TUTOR\Tutor::instance();
	}
}

if ( ! function_exists( 'str_contains' ) ) {
	function str_contains( string $haystack, string $needle ) {
		return empty( $needle ) || strpos( $haystack, $needle ) !== false;
	}
}
// add_action('plugins_loaded', 'tutor_lms');.
$GLOBALS['tutor'] = tutor_lms();
