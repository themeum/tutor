<?php
/**
 * Form validation helper
 *
 * Provides static helper methods for form validation.
 *
 * @package Tutor\Helper
 * @author  Themum<support@themeum.com>
 * @link    https://themeum.com
 * @since   2.6.0
 */

namespace Tutor\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * Validation class contains static methods
 */
class ValidationHelper {

	/**
	 * Validate array elements
	 *
	 * @since 2.6.0
	 *
	 * @param array $validation_rules associative array for validation
	 * rules. For ex: [id => 'required|number', name => 'alpha_numeric|max:255'].
	 *
	 * @param array $data  key value pair of data. Note array index should
	 * exactly match with validation_rules array index.
	 *
	 * @return object validation response
	 */
	public static function validate( array $validation_rules, array $data ): object {
		$validation_pass   = true;
		$validation_errors = array();

		foreach ( $validation_rules as $key => $validation_rule ) {
			$rules = explode( '|', $validation_rule );

			$required_rule_failed = false;

			foreach ( $rules as $rule ) {
				if ( $required_rule_failed ) {
					break;
				}

				$nested_rules = explode( ':', $rule );

				/**
				 * Optional input validation.
				 */
				if ( isset( $nested_rules[0] ) && 'if_input' === $nested_rules[0] ) {
					if ( ! self::has_key( $key, $data ) ) {
						break;
					}
				}

				foreach ( $nested_rules as $nested_rule ) {
					switch ( $nested_rule ) {
						case 'required':
							if ( ! self::has_key( $key, $data ) || self::is_empty( $data[ $key ] ) ) {
								$validation_pass             = false;
								$validation_errors[ $key ][] = $key . __( ' is required', 'tutor' );
								$required_rule_failed        = true;
							}
							break;
						case 'numeric':
							if ( ! self::is_numeric( $data[ $key ] ) ) {
								$validation_pass             = false;
								$validation_errors[ $key ][] = $key . __( ' is not numeric', 'tutor' );
							}
							break;
						/* Greater than (gt) */
						case 'gt':
							if ( $data[ $key ] < $nested_rules[1] ) {
								$validation_pass = false;
								/* translators: %1$s: field name, %2$d: value */
								$validation_errors[ $key ][] = sprintf( __( '%1$s need to be greater than %2$d', 'tutor' ), $key, $nested_rules[1] );
							}
							break;
						/* Less than (lt) */
						case 'lt':
							if ( $data[ $key ] > $nested_rules[1] ) {
								$validation_pass = false;
								/* translators: %1$s: field name, %2$d: value */
								$validation_errors[ $key ][] = sprintf( __( '%1$s need to be less than %2$d', 'tutor' ), $key, $nested_rules[1] );
							}
							break;
						case 'email':
							if ( ! is_email( $data[ $key ] ) ) {
								$validation_pass = false;
								/* translators: %s: field name */
								$validation_errors[ $key ][] = sprintf( __( '%s is not valid email', 'tutor' ), $key );
							}
							break;
						case 'min_length':
							if ( strlen( $data[ $key ] ) < $nested_rules[1] ) {
								$validation_pass = false;
								/* translators: %1$s: field name, %2$d: value */
								$validation_errors[ $key ][] = sprintf( __( '%1$s minimum length is %2$d' ), $key, $nested_rule[1] );
							}
							break;
						case 'max_length':
							if ( strlen( $data[ $key ] ) > $nested_rules[1] ) {
								$validation_pass = false;
								/* translators: %1$s: field name, %2$d: value */
								$validation_errors[ $key ][] = sprintf( __( '%1$s maximum length is %2$d' ), $key, $nested_rule[1] );
							}
							break;
						case 'mimes':
							$extensions = explode( ',', $nested_rules[1] );
							if ( ! self::in_array( $data[ $key ], $extensions ) ) {
								$validation_pass             = false;
								$validation_errors[ $key ][] = $key . __( ' extension is not valid', 'tutor' );
							}
							break;
						case 'match_string':
							$strings = explode( ',', $nested_rules[1] );
							if ( ! self::in_array( $data[ $key ], $strings ) ) {
								$validation_pass             = false;
								$validation_errors[ $key ][] = $key . __( ' string is not valid', 'tutor' );
							}
							break;
						case 'boolean':
							if ( ! self::is_boolean( $data[ $key ] ) ) {
								$validation_pass             = false;
								$validation_errors[ $key ][] = $key . __( ' is not boolean', 'tutor' );
							}
							break;
						case 'is_array':
							if ( ! self::is_array( $data[ $key ] ) ) {
								$validation_pass             = false;
								$validation_errors[ $key ][] = $key . __( ' is not an array', 'tutor' );
							}
							break;
						case 'date_format':
							$format = explode( ':', $rule, 2 )[1];
							if ( ! self::is_valid_date( $data[ $key ], $format ) ) {
								$validation_pass             = false;
								$validation_errors[ $key ][] = $key . __( ' invalid date format', 'tutor' );
							}
							break;

						case 'has_record':
							list( $table, $column ) = explode( ',', $nested_rules[1], 2 );

							$value      = $data[ $key ];
							$has_record = self::has_record( $table, $column, $value );
							if ( ! $has_record ) {
								$validation_pass             = false;
								$validation_errors[ $key ][] = $key . __( ' record not found', 'tutor' );
							}
							break;

						case 'user_exists':
							$user_id   = (int) $data[ $key ];
							$is_exists = self::is_user_exists( $user_id );
							if ( ! $is_exists ) {
								$validation_pass             = false;
								$validation_errors[ $key ][] = $key . __( ' user does not exist', 'tutor' );
							}
							break;
						default:
							// code...
							break;
					}
				}
			}
		}

		$response = array(
			'success' => $validation_pass,
			'errors'  => $validation_errors,
		);

		return (object) $response;
	}

	/**
	 * Check if value is numeric
	 *
	 * Rules: numeric
	 *
	 * @param mixed $value  value to check.
	 *
	 * @return boolean
	 */
	public static function is_numeric( $value ): bool {
		return is_numeric( $value );
	}

	/**
	 * Check if value is empty
	 *
	 * Value will be considered empty if it is either null or empty string.
	 *
	 * Rules: required
	 *
	 * @param mixed $value  value to check.
	 *
	 * @return boolean
	 */
	public static function is_empty( $value ): bool {
		return '' === $value || is_null( $value ) ? true : false;
	}

	/**
	 * Check if array has key
	 *
	 * @param string $key  key to check.
	 * @param array  $array_assoc  array where to check.
	 *
	 * @return boolean
	 */
	public static function has_key( string $key, array $array_assoc ): bool {
		return isset( $array_assoc[ $key ] );
	}

	/**
	 * Check if element has in array
	 *
	 * Rules: match_string:{value1},{value2}",
	 *
	 * @param string $key  key to check.
	 * @param array  $array  array where to check.
	 *
	 * @return boolean
	 */
	public static function in_array( string $key, array $array ): bool {
		return in_array( $key, $array );
	}


	/**
	 * The function checks if a given value is a boolean.
	 *
	 * Considered values: array( 1, 0, 'true', 'false', true, false ), any value
	 * except these will be not counted as boolean
	 *
	 * Rules: boolean
	 *
	 * @param mixed $value  is the variable that will be checked if it is a boolean value or not.
	 *
	 * @return bool A boolean value is being returned, indicating whether the input value is a valid
	 * boolean or not.
	 */
	public static function is_boolean( $value ): bool {
		$allowed_booleans = array( 1, 0, '1', '0', 'true', 'false', true, false );
		return in_array( $value, $allowed_booleans, true );
	}

	/**
	 * The function checks if a given value is an array.
	 *
	 * Usage: is_array:{value}
	 *
	 * @param mixed $value  is the variable that will be checked if it is an array or not.
	 *
	 * @return bool A boolean value is being returned, indicating whether the input value is a valid
	 * boolean or not.
	 */
	public static function is_array( $value ): bool {
		return is_array( $value );
	}

	/**
	 * The function checks if a given date string is valid according to a specified format in PHP.
	 *
	 * Rules: date_format:Y-m-d
	 *
	 * @since 2.6.0
	 *
	 * @param string $date_string  is a string representing a date in a specific format. For example,
	 * "2022-01-31" or "31/01/2022".
	 * @param string $format The format parameter is a string that specifies the expected format of the date
	 * string. It uses the same format as the PHP date() function, with placeholders for different parts of
	 * the date (e.g. "Y" for the year, "m" for the month, "d" for the day.
	 *
	 * @return bool A boolean value (true or false) is being returned, depending on whether the given date
	 * string is valid according to the specified format.
	 */
	public static function is_valid_date( $date_string, $format ): bool {
		$date_object = \DateTime::createFromFormat( $format, $date_string );
		return $date_object && $date_object->format( $format ) === $date_string;
	}

	/**
	 * Check if user exists
	 *
	 * Rules: user_exists:{user_id}
	 *
	 * @param integer $user_id user id.
	 * @return boolean
	 */
	public static function is_user_exists( int $user_id ): bool {
		$user = get_user_by( 'id', $user_id );
		return $user ? true : false;
	}

	/**
	 * Check a table has record.
	 *
	 * @since 2.7.0
	 *
	 * @param string $table table name with prefix or without.
	 * @param string $column table column name.
	 * @param mixed  $value table column value.
	 *
	 * @return boolean
	 */
	public static function has_record( $table, $column, $value ) {
		global $wpdb;
		$table_prefix = $wpdb->prefix;
		if ( strpos( $table, $table_prefix ) !== 0 ) {
			$table = $table_prefix . $table;
		}

		$record = QueryHelper::get_row( $table, array( $column => $value ), $column );
		return $record ? true : false;
	}
}
