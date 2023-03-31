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
			case self::TYPE_STRING:
			case self::TYPE_INT:
			case self::TYPE_NUMERIC:
			case self::TYPE_BOOL:
			default:
				$sanitized_value = sanitize_text_field( wp_unslash( self::get_value( $request_method, $_GET, $_POST, $key, $value ) ) );
				if ( self::TYPE_INT === $type ) {
					$sanitized_value = (int) $sanitized_value;
				}
				if ( self::TYPE_NUMERIC === $type ) {
					$sanitized_value = is_numeric( $sanitized_value ) ? $sanitized_value + 0 : 0;
				}
				if ( self::TYPE_BOOL === $type ) {
					$sanitized_value = in_array( strtolower( $sanitized_value ), array( '1', 'true', 'on' ), true );
				}

				break;

			case self::TYPE_ARRAY:
				if ( ! is_array( $default ) ) {
					$sanitized_value = array();
				} else {
					$sanitized_value = array_map(
						'sanitize_text_field',
						wp_unslash(
							is_array( self::get_value( $request_method, $_GET, $_POST, $key, $value ) )
							? ( self::get_value( $request_method, $_GET, $_POST, $key, $value ) )
							: $default
						)
					);
				}

				break;

			case self::TYPE_TEXTAREA:
				$sanitized_value = sanitize_textarea_field( wp_unslash( self::get_value( $request_method, $_GET, $_POST, $key, $value ) ) );
				break;

			case self::TYPE_KSES_POST:
				$sanitized_value = wp_kses_post( wp_unslash( self::get_value( $request_method, $_GET, $_POST, $key, $value ) ) );
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
	 * Dynamically get value
	 *
	 * @since 2.2.0
	 *
	 * @param string $request_method   detect called from get or post method.
	 * @param array  $get              GET superglobal.
	 * @param array  $post             POST superglobal.
	 * @param string $key              GET or POST input key name.
	 * @param string $value            value of variable or DB value.
	 *
	 * @return mixed
	 */
	private static function get_value( $request_method, $get, $post, $key, $value ) {
		return self::GET_REQUEST === $request_method
				? $get[ $key ]
				: ( self::POST_REQUEST === $request_method ? $post[ $key ] : $value );
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

	/**
	 * Sanitize array, single or multi dimensional array
	 * Explicitly setup how should a value sanitize by the
	 * sanitize function.
	 *
	 * @since 2.1.3
	 *
	 * @see available sanitize func
	 * https://developer.wordpress.org/themes/theme-security/data-sanitization-escaping/
	 *
	 * @param array $input array to sanitize.
	 * @param array $sanitize_mapping single dimensional map key value
	 * pair to set up sanitization process. Key name should by inside
	 * input array and the value will be callable func.
	 * For ex: [key1 => sanitize_email, key2 => wp_kses_post ]
	 *
	 * If key not passed then default sanitize_text_field will be used.
	 *
	 * @param bool  $allow_iframe if set true then iframe tag will be allowed.
	 *
	 * @return array
	 */
	public static function sanitize_array( array $input, array $sanitize_mapping = array(), $allow_iframe = false ):array {
		$array = array();

		if ( $allow_iframe ) {
			add_filter( 'wp_kses_allowed_html', __CLASS__ . '::allow_iframe', 10, 2 );
		}

		if ( is_array( $input ) && count( $input ) ) {
			foreach ( $input as $key => $value ) {
				if ( is_array( $value ) ) {
					$array[ $key ] = self::sanitize_array( $value, $sanitize_mapping, $allow_iframe );
				} else {
					$key = sanitize_text_field( $key );

					// If mapping exists then use callback.
					if ( isset( $sanitize_mapping[ $key ] ) ) {
						$callback = $sanitize_mapping[ $key ];
						$value    = call_user_func( $callback, wp_unslash( $value ) );
					} else {
						$value = sanitize_text_field( wp_unslash( $value ) );
					}
					$array[ $key ] = $value;
				}
			}
		}
		return is_array( $array ) && count( $array ) ? $array : array();
	}

	/**
	 * This method is used with wp_kses_allowed_html filter
	 * to allow iframe
	 *
	 * @since 2.1.3
	 *
	 * @param array  $tags allowed HTML tags.
	 * @param string $context context name.
	 *
	 * @return array
	 */
	public static function allow_iframe( $tags, $context ) {
		$tags['iframe'] = array(
			'src'             => true,
			'title'           => true,
			'height'          => true,
			'width'           => true,
			'frameborder'     => true,
			'allowfullscreen' => true,
			'allow'           => true,
			'style'           => true,
		);
		return $tags;
	}
}
