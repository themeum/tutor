<?php
/**
 * Plugin Name: Tutor LMS
 * Plugin URI: https://www.themeum.com/product/tutor-lms/
 * Description: Tutor is a complete solution for creating a Learning Management System in WordPress way. It can help you to create small to large scale online education site very conveniently. Power features like report, certificate, course preview, private file sharing make Tutor a robust plugin for any educational institutes.
 * Author: Themeum
 * Version: 2.6.2
 * Author URI: https://themeum.com
 * Requires PHP: 7.4
 * Requires at least: 5.3
 * Tested up to: 6.4
 * License: GPLv2 or later
 * Text Domain: tutor
 *
 * @package Tutor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'vendor/autoload.php';

/**
 * Defined the tutor main file
 */
define( 'TUTOR_VERSION', '2.6.2' );
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

if ( ! function_exists( 'tutor' ) ) {
	/**
	 * Tutor helper function.
	 *
	 * @since 1.0.0
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
		$home_url  = get_home_url();
		$parsed    = parse_url( $home_url );
		$base_path = ( is_array( $parsed ) && isset( $parsed['path'] ) ) ? $parsed['path'] : '/';
		$base_path = rtrim( $base_path, '/' ) . '/';
		// Get current URL.
		$current_url = trailingslashit( $home_url ) . substr( $_SERVER['REQUEST_URI'], strlen( $base_path ) );//phpcs:ignore

		$info = array(
			'path'                   => $path,
			'url'                    => plugin_dir_url( TUTOR_FILE ),
			'icon_dir'               => plugin_dir_url( TUTOR_FILE ) . 'assets/images/images-v2/icons/',
			'v2_img_dir'             => plugin_dir_url( TUTOR_FILE ) . 'assets/images/images-v2/',
			'current_url'            => $current_url,
			'basename'               => plugin_basename( TUTOR_FILE ),
			'basepath'               => $base_path,
			'version'                => TUTOR_VERSION,
			'nonce_action'           => 'tutor_nonce_action',
			'nonce'                  => '_tutor_nonce',
			'course_post_type'       => apply_filters( 'tutor_course_post_type', 'courses' ),
			'lesson_post_type'       => apply_filters( 'tutor_lesson_post_type', 'lesson' ),
			'instructor_role'        => apply_filters( 'tutor_instructor_role', 'tutor_instructor' ),
			'instructor_role_name'   => apply_filters( 'tutor_instructor_role_name', __( 'Tutor Instructor', 'tutor' ) ),
			'template_path'          => apply_filters( 'tutor_template_path', 'tutor/' ),
			'has_pro'                => apply_filters( 'tutor_has_pro', $has_pro ),
			// @since v2.0.6.
			'topics_post_type'       => apply_filters( 'tutor_topics_post_type', 'topics' ),
			'announcement_post_type' => apply_filters( 'tutor_announcement_post_type', 'tutor_announcements' ),
			'assignment_post_type'   => apply_filters( 'tutor_assignment_post_type', 'tutor_assignments' ),
			'enrollment_post_type'   => apply_filters( 'tutor_enrollment_post_type', 'tutor_enrolled' ),
			'quiz_post_type'         => apply_filters( 'tutor_quiz_post_type', 'tutor_quiz' ),
			'zoom_post_type'         => apply_filters( 'tutor_zoom_meeting_post_type', 'tutor_zoom_meeting' ),
			'meet_post_type'         => apply_filters( 'tutor_google_meeting_post_type', 'tutor-google-meet' ),
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
	 * @since 1.0.0
	 *
	 * @return \TUTOR\Utils
	 */
	function tutor_utils() {
		if ( ! isset( $GLOBALS['tutor_utils_object'] ) ) {
			// Use runtime cache.
			$GLOBALS['tutor_utils_object'] = new \TUTOR\Utils();
		}

		return $GLOBALS['tutor_utils_object'];
	}
}


if ( ! function_exists( 'tutils' ) ) {
	/**
	 * Alis of tutor_utils()
	 *
	 * @since 1.3.4
	 *
	 * @return \TUTOR\Utils
	 */
	function tutils() {
		return tutor_utils();
	}
}

/**
 * Do some task during activation
 *
 * @since 1.5.2
 *
 * @since 2.6.2
 *
 * Uninstall hook registered
 */
register_activation_hook( TUTOR_FILE, array( '\TUTOR\Tutor', 'tutor_activate' ) );
register_deactivation_hook( TUTOR_FILE, array( '\TUTOR\Tutor', 'tutor_deactivation' ) );
register_uninstall_hook( TUTOR_FILE, array( '\TUTOR\Tutor', 'tutor_uninstall' ) );

if ( ! function_exists( 'tutor_lms' ) ) {
	/**
	 * Run main instance of the Tutor
	 *
	 * @since 1.2.0
	 *
	 * @return null|\TUTOR\Tutor
	 */
	function tutor_lms() {
		return \TUTOR\Tutor::instance();
	}
}

if ( ! function_exists( 'str_contains' ) ) {
	/**
	 * String helper for str contains
	 *
	 * @since 1.0.0
	 *
	 * @param string $haystack haystack.
	 * @param string $needle needle.
	 *
	 * @return bool
	 */
	function str_contains( string $haystack, string $needle ) {
		return empty( $needle ) || strpos( $haystack, $needle ) !== false;
	}
}

$GLOBALS['tutor'] = tutor_lms();
