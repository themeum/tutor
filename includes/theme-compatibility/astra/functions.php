<?php
/**
 * Astra theme compatibility.
 *
 * @package Tutor\ThemeCompatibility\Astra
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', 'tutor_astra_scripts' );

if ( ! function_exists( 'tutor_astra_scripts' ) ) {
	/**
	 * Enqueue Astra theme compatibility scripts.
	 *
	 * @since 1.0.0
	 */
	function tutor_astra_scripts() {
		$dir_url        = plugin_dir_url( __FILE__ );
		$common_dir_url = plugin_dir_url( __DIR__ );
		wp_enqueue_style( 'tutor_theme_compat_common', $common_dir_url . 'common/assets/css/style.css', array(), tutor()->version );
		wp_enqueue_style( 'tutor_astra', $dir_url . 'assets/css/style.css', array( 'tutor_theme_compat_common' ), tutor()->version );
	}
}
