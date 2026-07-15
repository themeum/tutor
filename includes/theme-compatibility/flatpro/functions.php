<?php
/**
 * Flat Pro theme compatibility.
 *
 * @package Tutor\ThemeCompatibility\FlatPro
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', 'tutor_flatpro_scripts' );

if ( ! function_exists( 'tutor_flatpro_scripts' ) ) {
	/**
	 * Enqueue FlatPro theme compatibility scripts.
	 *
	 * @since 1.0.0
	 */
	function tutor_flatpro_scripts() {
		$dir_url = plugin_dir_url( __FILE__ );
		wp_enqueue_style( 'tutor_flatpro', $dir_url . 'assets/css/style.css', array(), tutor()->version );
	}
}
