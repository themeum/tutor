<?php
/**
 * Input class
 *
 * @author: themeum
 * @author_uri: https://themeum.com
 * @package Tutor
 * @since 2.0.2
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Input class for handling GET and POST request
 *
 * @since 2.0.2
 */
class Input {

	const TYPE_RAW      = 'raw';
	const TYPE_STRING   = 'string';
	const TYPE_INT      = 'int';
	const TYPE_NUMERIC	= 'numeric';
	const TYPE_TEXTAREA = 'textarea';

	/**
	 * Common data sanitize method
	 *
	 * @param string  $value      input value.
	 * @param string  $default    default value if input key is not exit.
	 * @param string  $type       Default is Input::TYPE_STRING.
	 * @param boolean $trim       remove blank splace from start and end.
	 * @return mixed
	 */
	private static function sanitize( $value, $default = null, $type = self::TYPE_STRING, $trim = true ) {
		if ( $value === $default ) {
			return $default;
		}

		if ( $trim ) {
			$value = trim( $value );
		}

		if ( self::TYPE_RAW === $type ) {
			return $value;
		}
		if ( self::TYPE_INT === $type ) {
			return (int) sanitize_text_field( wp_unslash( $value ) );
		}
		if ( self::TYPE_NUMERIC === $type ) {
			$val = sanitize_text_field( wp_unslash( $value ) );
			return is_numeric( $val ) ? $val + 0 : 0;
		}
		if ( self::TYPE_TEXTAREA === $type ) {
			return sanitize_textarea_field( wp_unslash( $value ) );
		}

		return sanitize_text_field( wp_unslash( $value ) );
	}

	/**
	 * Get input value from GET request
	 *
	 * @param string  $key      $_GET request key.
	 * @param mixed   $default  default value if input key is not exit.
	 * @param string  $type     input type. Default is Input::TYPE_STRING.
	 * @param boolean $trim     remove blank splace from start and end.
	 * @return mixed
	 */
	public static function get( $key, $default = null, $type = self::TYPE_STRING, $trim = true ) {
		//phpcs:ignore
		$value = isset( $_GET[ $key ] ) ? $_GET[ $key ] : $default;
		return self::sanitize( $value, $default, $type, $trim );
	}

	/**
	 * Get input value from POST request
	 *
	 * @param string  $key      $_POST request key.
	 * @param mixed   $default  default value if input key is not exit.
	 * @param string  $type     input type. Default is Input::TYPE_STRING.
	 * @param boolean $trim     remove blank splace from start and end.
	 * @return mixed
	 */
	public static function post( $key, $default = null, $type = self::TYPE_STRING, $trim = true ) {
		//phpcs:ignore
		$value = isset( $_POST[ $key ] ) ? $_POST[ $key ] : $default;
		return self::sanitize( $value, $default, $type, $trim );
	}

	/**
	 * Check input has key or not
	 *
	 * @param string $key input key name.
	 * @return boolean
	 */
	public static function has( $key ) {
		//phpcs:ignore
		return isset( $_REQUEST[ $key ] );
	}
}
