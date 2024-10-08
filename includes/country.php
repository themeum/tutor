<?php
/**
 * Country List
 *
 * @package Tutor\Includes
 * @author Themeum <support@themeum.com>
 * @link https=>//themeum.com
 * @since 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'tutor_get_country_list' ) ) {
	/**
	 * Get country list.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	function tutor_get_country_list() {
		$file = trailingslashit( tutor()->path ) . 'assets/json/countries.json';
		if ( ! file_exists( $file ) ) {
			return array();
		}

		$data = file_get_contents( $file );
		return json_decode( $data, true );
	}
}

if ( ! function_exists( 'tutor_get_country_info_by_name' ) ) {
	/**
	 * Get country info by country name
	 *
	 * @since 3.0.0
	 *
	 * @param string $country_name country name.
	 *
	 * @return array|null
	 */
	function tutor_get_country_info_by_name( $country_name ) {
		$countries = tutor_get_country_list();
		foreach ( $countries as $country ) {
			if ( strtolower( $country['name'] ) === strtolower( $country_name ) ) {
				return $country;
			}
		}
	}
}
