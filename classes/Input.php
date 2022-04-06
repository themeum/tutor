<?php
/**
 * Input class
 *
 * @author: themeum
 * @author_uri: https://themeum.com
 * @package Tutor
 * @since 2.0.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Input {

	/**
	 * Get input value
	 *
	 * @param string  $key request key.
     * @param mixed   $default default value if input key is not exit.
	 * @param boolean $is_raw request data sanitized or not.
	 * @param boolean $trim remove blank splace from start and end.
	 * @return mixed
	 */
	public static function get( $key, $default = null, $is_raw = false, $trim = true ) {
		$value = isset( $_REQUEST[ $key ] ) ? $_REQUEST[ $key ] : $default;
		if( $value === $default ) return $default;

		if ( $trim ) {
			$value = trim( $value );
		}

		return $is_raw ? $value : sanitize_text_field( wp_unslash( $value ) );
	}

	/**
	 * Check input has key or not
	 *
	 * @param string $key input key name.
	 * @return boolean
	 */
	public static function has( $key ) {
		return isset( $_REQUEST[ $key ] );
	}
}
