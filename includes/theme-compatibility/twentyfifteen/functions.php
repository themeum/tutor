<?php
/**
 * Twenty Fifteen theme compatibility.
 *
 * @package Tutor\ThemeCompatibility\TwentyFifteen
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', 'tutor_twentyfifteen_scripts' );

if ( ! function_exists( 'tutor_twentyfifteen_scripts' ) ) {
	/**
	 * Enqueue Twenty Fifteen theme compatibility scripts.
	 *
	 * @since 1.0.0
	 */
	function tutor_twentyfifteen_scripts() {
		$dir_url = plugin_dir_url( __FILE__ );
		wp_enqueue_style( 'tutor_twentyfifteen', $dir_url . 'assets/css/style.css', array(), tutor()->version );
	}
}



