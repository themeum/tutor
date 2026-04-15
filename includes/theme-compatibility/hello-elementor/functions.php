<?php
/**
 * Hello Elementor theme compatibility.
 *
 * @package Tutor\ThemeCompatibility\HelloElementor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', 'tutor_hello_elementor_scripts' );

if ( ! function_exists( 'tutor_hello_elementor_scripts' ) ) {
	/**
	 * Enqueue Hello Elementor theme compatibility scripts.
	 *
	 * @since 1.0.0
	 */
	function tutor_hello_elementor_scripts() {
		$dir_url = plugin_dir_url( __FILE__ );
		wp_enqueue_style( 'tutor_hello_elementor', $dir_url . 'assets/css/style.css', array(), tutor()->version );
	}
}
