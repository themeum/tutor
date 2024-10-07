<?php

/**
 * Settings support class.
 *
 * @package Tutor\Ecommerce
 * @author Themeum
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\Ecommerce\Supports;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Settings {
	/**
	 * Get the tax settings from tutor options.
	 *
	 * @since 3.0.0
	 *
	 * @return object
	 */
	public static function get_tax_settings() {
		$tax_settings = tutor_utils()->get_option( 'ecommerce_tax' );

		if ( ! empty( $tax_settings ) && is_string( $tax_settings ) ) {
			$tax_settings = json_decode( $tax_settings );
		}

		return $tax_settings;
	}
}
