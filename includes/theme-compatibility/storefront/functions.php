<?php
/**
 * Storefront theme compatibility.
 *
 * @package Tutor\ThemeCompatibility\Storefront
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', 'tutor_storefront_scripts' );

if ( ! function_exists( 'tutor_storefront_scripts' ) ) {
	/**
	 * Enqueue Storefront theme compatibility scripts.
	 *
	 * @since 1.0.0
	 */
	function tutor_storefront_scripts() {
		$dir_url        = plugin_dir_url( __FILE__ );
		$common_dir_url = plugin_dir_url( __DIR__ );
		wp_enqueue_style( 'tutor_theme_compat_common', $common_dir_url . 'common/assets/css/style.css', array(), tutor()->version );
		wp_enqueue_style( 'tutor_storefront', $dir_url . 'assets/css/style.css', array( 'tutor_theme_compat_common' ), tutor()->version );
	}
}
