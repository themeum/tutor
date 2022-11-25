<?php
/**
 * Integration class
 *
 * @package Tutor\ThemeCompatibility
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Template compatibility
 *
 * @since 1.0.0
 */
class Theme_Compatibility {

	/**
	 * Prepare dependencies
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$template   = trailingslashit( get_template() );
		$tutor_path = tutor()->path;

		$compatibility_theme_path = $tutor_path . 'includes/theme-compatibility/' . $template . 'functions.php';

		if ( file_exists( $compatibility_theme_path ) ) {
			include $compatibility_theme_path;
		}

	}
}
