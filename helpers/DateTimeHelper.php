<?php
/**
 * Helper class to work with datetime.
 *
 * @package Tutor\Helper
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\Helpers;

use Carbon\Carbon;
use TUTOR\User;

/**
 * DateTimeHelper class
 *
 * @since 3.0.0
 *
 * @see https://carbon.nesbot.com/docs/
 */
final class DateTimeHelper extends Carbon {
	/**
	 * Get GMT date to user timezone date.
	 *
	 * @since 3.0.0
	 *
	 * @param string $gmt_date gmt date time string.
	 * @param string $format format string.
	 *
	 * @return string
	 */
	public static function get_gmt_to_user_timezone_date( string $gmt_date, string $format = null ): string {
		$default_format  = get_option( 'date_format' ) . ', ' . get_option( 'time_format' );
		$date            = new \DateTime( $gmt_date );
		$timezone_string = User::get_user_timezone_string();

		$timezone = new \DateTimeZone( $timezone_string );
		$date->setTimezone( $timezone );
		return $date->format( is_null( $format ) ? $default_format : $format );
	}
}
