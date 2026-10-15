<?php
/**
 * GeneratePress theme compatibility.
 *
 * @package Tutor\ThemeCompatibility\GeneratePress
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', 'tutor_gemeratepress_scripts' );

if ( ! function_exists( 'tutor_gemeratepress_scripts' ) ) {
	/**
	 * Enqueue GeneratePress theme compatibility scripts.
	 *
	 * @since 1.0.0
	 */
	function tutor_gemeratepress_scripts() {
		$dir_url = plugin_dir_url( __FILE__ );
		wp_enqueue_style( 'tutor_gemeratepress', $dir_url . 'assets/css/style.css', array(), tutor()->version );
	}
}



