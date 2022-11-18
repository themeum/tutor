<?php
/**
 * Input class for sanitize GET and POST request
 *
 * @package Tutor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.2
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Input class
 *
 * @since 2.0.2
 */
class Input {

	const TYPE_STRING    = 'string';
	const TYPE_INT       = 'int';
	const TYPE_NUMERIC   = 'numeric';
	const TYPE_BOOL      = 'bool';
	const TYPE_ARRAY     = 'array';
	const TYPE_TEXTAREA  = 'textarea';
	const TYPE_KSES_POST = 'kses-post';

	private const GET_REQUEST  = 'get';
	private const POST_REQUEST = 'post';

	/**
	 * Common data sanitizer method
	 *
	 * @since 2.0.2
	 *
	 * @param string  $value            input value.
	 * @param string  $default          default value if input key is not exit.
	 * @param string  $type             Default is Input::TYPE_STRING.
	 * @param boolean $trim             remove blank splace from start and end.
	 * @param string  $request_method   request method get or post.
	 *
	 * @return mixed
	 */
	private static function data_sanitizer( $value, $default = null, $type = self::TYPE_STRING, $trim = true, $request_method = null ) {
		$is_input_request = in_array( $request_method, array( self::GET_REQUEST, self::POST_REQUEST ), true );
		$key              = null;

		//phpcs:disable WordPress.Security.NonceVerification
		if ( $is_input_request ) {
			$key = $value;
			if ( self::GET_REQUEST === $request_method && ! isset( $_GET[ $key ] ) ) {
				if ( self::TYPE_ARRAY === $type ) {
					return is_array( $default ) ? $default : array();
				} else {
					return $default;
				}
			}
			if ( self::POST_REQUEST === $request_method && ! isset( $_POST[ $key ] ) ) {
				if ( self::TYPE_ARRAY === $type ) {
					return is_array( $default ) ? $default : array();
				} else {
					return $default;
				}
			}
		}

		$sanitized_value = null;

		switch ( $type ) {
			case self::TYPE_INT:
				$sanitized_value = (int) sanitize_text_field( wp_unslash( self::GET_REQUEST === $request_method ? $_GET[ $key ] : ( self::POST_REQUEST === $request_method ? $_POST[ $key ] : $value ) ) );
				break;

			case self::TYPE_NUMERIC:
				$input           = sanitize_text_field( wp_unslash( self::GET_REQUEST === $request_method ? $_GET[ $key ] : ( self::POST_REQUEST === $request_method ? $_POST[ $key ] : $value ) ) );
				$sanitized_value = is_numeric( $input ) ? $input + 0 : 0;
				break;

			case self::TYPE_BOOL:
				$sanitized_value = in_array( strtolower( sanitize_text_field( wp_unslash( self::GET_REQUEST === $request_method ? $_GET[ $key ] : ( self::POST_REQUEST === $request_method ? $_POST[ $key ] : $value ) ) ) ), array( '1', 'true', 'on' ), true );
				break;

			case self::TYPE_STRING:
				$sanitized_value = sanitize_text_field( wp_unslash( self::GET_REQUEST === $request_method ? $_GET[ $key ] : ( self::POST_REQUEST === $request_method ? $_POST[ $key ] : $value ) ) );
				break;
			case self::TYPE_ARRAY:
				if ( ! is_array( $default ) ) {
					$sanitized_value = array();
				} else {
					$sanitized_value = array_map(
						'sanitize_text_field',
						wp_unslash(
							is_array( self::GET_REQUEST === $request_method ? $_GET[ $key ] : ( self::POST_REQUEST === $request_method ? $_POST[ $key ] : $value ) ) //phpcs:ignore
							? ( self::GET_REQUEST === $request_method ? $_GET[ $key ] : ( self::POST_REQUEST === $request_method ? $_POST[ $key ] : $value ) ) //phpcs:ignore
							: $default
						)
					);
				}

				break;

			case self::TYPE_TEXTAREA:
				$sanitized_value = sanitize_textarea_field( wp_unslash( self::GET_REQUEST === $request_method ? $_GET[ $key ] : ( self::POST_REQUEST === $request_method ? $_POST[ $key ] : $value ) ) );
				break;

			case self::TYPE_KSES_POST:
				$sanitized_value = wp_kses_post( wp_unslash( self::GET_REQUEST === $request_method ? $_GET[ $key ] : ( self::POST_REQUEST === $request_method ? $_POST[ $key ] : $value ) ) );
				break;

			default:
				$sanitized_value = sanitize_text_field( wp_unslash( self::GET_REQUEST === $request_method ? $_GET[ $key ] : ( self::POST_REQUEST === $request_method ? $_POST[ $key ] : $value ) ) );
				break;
		}

		//phpcs:enable WordPress.Security.NonceVerification

		if ( $trim ) {
			if ( self::TYPE_ARRAY === $type && is_array( $sanitized_value ) ) {
				$sanitized_value = array_map( 'trim', $sanitized_value );
			}
		}

		if ( self::TYPE_ARRAY === $type && is_array( $sanitized_value ) ) {
			$final_array = array();
			$is_assoc    = array_keys( $sanitized_value ) !== range( 0, count( $sanitized_value ) - 1 );

			foreach ( $sanitized_value as $input_key => $input_value ) {
				/**
				 * Sanitize array key if array is assoc.
				 * When from form submit like person['name'], person['age'] etc
				 */
				if ( $is_assoc ) {
					$input_key = sanitize_text_field( wp_unslash( $input_key ) );
				}

				if ( is_numeric( $input_value ) ) {
					$input_value = $input_value + 0;
				}

				$final_array[ $input_key ] = $input_value;
			}

			$sanitized_value = $final_array;

		}

		return $sanitized_value;

	}

	/**
	 * Sanitize value
	 *
	 * @since 2.0.2
	 *
	 * @param string  $value      input value.
	 * @param string  $default    default value if input key is not exit.
	 * @param string  $type       Default is Input::TYPE_STRING.
	 * @param boolean $trim       remove blank splace from start and end.
	 *
	 * @return mixed
	 */
	public static function sanitize( $value, $default = null, $type = self::TYPE_STRING, $trim = true ) {
		return self::data_sanitizer( $value, $default, $type, $trim );
	}

	/**
	 * Get input value from GET request
	 *
	 * @param string  $key      $_GET request key.
	 * @param mixed   $default  default value if input key is not exit.
	 * @param string  $type     input type. Default is Input::TYPE_STRING.
	 * @param boolean $trim     remove blank splace from start and end.
	 *
	 * @return mixed
	 */
	public static function get( $key, $default = null, $type = self::TYPE_STRING, $trim = true ) {
		return self::data_sanitizer( $key, $default, $type, $trim, self::GET_REQUEST );
	}

	/**
	 * Get input value from POST request
	 *
	 * @since 2.0.2
	 *
	 * @param string  $key      $_POST request key.
	 * @param mixed   $default  default value if input key is not exit.
	 * @param string  $type     input type. Default is Input::TYPE_STRING.
	 * @param boolean $trim     remove blank splace from start and end.
	 * @return mixed
	 */
	public static function post( $key, $default = null, $type = self::TYPE_STRING, $trim = true ) {
		return self::data_sanitizer( $key, $default, $type, $trim, self::POST_REQUEST );
	}

	/**
	 * Check input has key or not
	 *
	 * @since 2.0.2
	 *
	 * @param string $key input key name.
	 * @return boolean
	 */
	public static function has( $key ) {
		//phpcs:ignore WordPress.Security.NonceVerification
		return isset( $_REQUEST[ $key ] );
	}

	/**
	 * Sanitize & unslash a request data
	 *
	 * @since 2.1.3
	 *
	 * @param string $key a request key.
	 * @param mixed  $default_value a default value if key not exists.
	 *
	 * @return mixed
	 */
	public static function sanitize_request_data( string $key, $default_value = '' ) {
		if ( self::has( $key ) ) {
			return sanitize_text_field( wp_unslash( $_REQUEST[ $key ] ) ); //phpcs:ignore
		}
		return $default_value;
	}

}
