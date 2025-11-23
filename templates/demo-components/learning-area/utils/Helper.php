<?php
/**
 * Learning Area functions.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\LearningArea;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Learning Area Helper class.
 */
class Helper {
	/**
	 * Check if current answer is correct
	 *
	 * @param array $answer Answer data.
	 *
	 * @return string Correct, Incorrect or Empty string.
	 */
	public static function is_correct( $answer ) {
		if ( ! array_key_exists( 'is_correct', $answer ) ) {
			return '';
		}

		$value = $answer['is_correct'];

		// values that should return an empty string.
		$empty_values = array( null, '' );

		if ( in_array( $value, $empty_values, true ) ) {
			return '';
		}

		// map boolean values to their labels.
		$map = array(
			true  => 'correct',
			false => 'incorrect',
		);

		return $map[ $value ] ?? '';
	}

	/**
	 * Check if current answer has a thumb
	 *
	 * @param array $answer Answer data.
	 *
	 * @return bool
	 */
	public static function has_thumb( $answer ) {
		return array_key_exists( 'thumb', $answer );
	}
}
