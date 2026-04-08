<?php
/**
 * Twenty Seventeen theme compatibility.
 *
 * @package Tutor\ThemeCompatibility\TwentySeventeen
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', 'tutor_twentyseventeen_scripts' );

if ( ! function_exists( 'tutor_twentyseventeen_scripts' ) ) {
	/**
	 * Enqueue Twenty Seventeen theme compatibility scripts.
	 *
	 * @since 1.0.0
	 */
	function tutor_twentyseventeen_scripts() {
		$dir_url = plugin_dir_url( __FILE__ );
		wp_enqueue_style( 'tutor_twentyseventeen', $dir_url . 'assets/css/style.css', array(), tutor()->version );
	}
}



