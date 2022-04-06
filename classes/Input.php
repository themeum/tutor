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

	const TYPE_RAW = 'raw';
	const TYPE_STRING = 'string';
	const TYPE_INT = 'int';
	const TYPE_TEXTAREA = 'textarea';

	/**
	 * Get input value
	 *
	 * @param string  $key request key.
     * @param mixed   $default default value if input key is not exit.
	 * @param string  $type input type. Default is string.
	 * @param boolean $trim remove blank splace from start and end.
	 * @return mixed
	 */
	public static function get( $key, $default = null, $type = self::TYPE_STRING, $trim = true ) {
		$value = isset( $_REQUEST[ $key ] ) ? $_REQUEST[ $key ] : $default;
		if( $value === $default ) return $default;

		if ( $trim ) {
			$value = trim( $value );
		}

		if( self::TYPE_RAW === $type ) return $value;
		if( self::TYPE_INT === $type ) return (int) sanitize_text_field( wp_unslash( $value ) );
		if( self::TYPE_TEXTAREA === $type) return sanitize_textarea_field( wp_unslash( $value ) );

		return sanitize_text_field( wp_unslash( $value ) );
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
