<?php
/**
 * Plugin Name: Tutor LMS
 * Plugin URI: https://tutorlms.com
 * Description: Tutor is a complete solution for creating a Learning Management System in WordPress way. It can help you to create small to large scale online education site very conveniently. Power features like report, certificate, course preview, private file sharing make Tutor a robust plugin for any educational institutes.
 * Author: Themeum
 * Version: 3.6.1
 * Author URI: https://themeum.com
 * Requires PHP: 7.4
 * Requires at least: 5.3
 * Tested up to: 6.8
 * License: GPLv2 or later
 * Text Domain: tutor
 *
 * @package Tutor
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Constants for tutor plugin.
 *
 * @since 1.0.0
 */
define( 'TUTOR_VERSION', '3.6.1' );
define( 'TUTOR_FILE', __FILE__ );

/**
 * Load text domain for translations.
 *
 * @since 1.0.0
 */
add_action( 'init', fn () => load_plugin_textdomain( 'tutor', false, basename( __DIR__ ) . '/languages' ) );

/**
 * Load core classes.
 *
 * @since 1.0.0
 */
! class_exists( 'Tutor' ) && require_once 'classes/Tutor.php';
! class_exists( '\TUTOR\Utils' ) && require_once 'classes/Utils.php';

/**
 * Do some task during activation
 *
 * @since 1.5.2
 * @since 2.6.2 Uninstall hook registered
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

$GLOBALS['tutor'] = tutor_lms();
