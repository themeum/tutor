<?php
/**
 * Plugin Name: Tutor LMS
 * Plugin URI: https://tutorlms.com
 * Description: Build and manage professional online courses with unlimited lessons, a flexible quiz engine, and a complete student learning experience. No coding needed.
 * Author: Themeum
 * Version: 4.0.0
 * Author URI: https://themeum.com
 * Requires PHP: 7.4
 * Requires at least: 5.3
 * Tested up to: 7.0
 * License: GPLv2 or later
 * Text Domain: tutor
 *
 * @package Tutor
 */

use TUTOR\Tutor;

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Constants for tutor plugin.
 *
 * @since 1.0.0
 */
define( 'TUTOR_VERSION', '4.0.0' );
define( 'TUTOR_FILE', __FILE__ );
define( 'TUTOR_ENV', 'PROD' ); // DEV || PROD.

/**
 * Load text domain for translations.
 *
 * @since 1.0.0
 */
add_action( 'init', fn () => load_plugin_textdomain( 'tutor', false, basename( __DIR__ ) . '/languages' ) );

/**
 * Do some task during activation
 *
 * @since 1.5.2
 * @since 2.6.2 Uninstall hook registered
 */
register_activation_hook( TUTOR_FILE, array( Tutor::class, 'tutor_activate' ) );
register_deactivation_hook( TUTOR_FILE, array( Tutor::class, 'tutor_deactivation' ) );
register_uninstall_hook( TUTOR_FILE, array( Tutor::class, 'tutor_uninstall' ) );

if ( ! function_exists( 'tutor_lms' ) ) {
	/**
	 * Run main instance of the Tutor
	 *
	 * @since 1.2.0
	 *
	 * @return Tutor
	 */
	function tutor_lms() {
		return Tutor::get_instance();
	}
}

$GLOBALS['tutor'] = tutor_lms();
