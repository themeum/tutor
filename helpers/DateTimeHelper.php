<?php
/**
 * Helper class to work with datetime.
 *
 * @package Tutor\Helper
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 *
 * @fileIgnore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
 */

namespace Tutor\Helpers;

use DateInterval;
use DateTime;
use DateTimeZone;

/**
 * DateTimeHelper class
 *
 * @since 3.0.0
 */
final class DateTimeHelper {
	/**
	 * Format constants
	 */
	const FORMAT_MYSQL     = 'Y-m-d H:i:s';
	const FORMAT_DATE_TIME = 'Y-m-d H:i:s';
	const FORMAT_DATE      = 'Y-m-d';
	const FORMAT_TIME      = 'H:i:s';
	const FORMAT_TIMESTAMP = 'U';

	/**
	 * Interval constants
	 */
	const INTERVAL_HOUR  = 'hour';
	const INTERVAL_DAY   = 'day';
	const INTERVAL_WEEK  = 'week';
	const INTERVAL_MONTH = 'month';
	const INTERVAL_YEAR  = 'year';

	/**
	 * Date
	 *
	 * @var DateTime
	 */
	private $datetime;

	/**
	 * Create an instance.
	 */
	private static function instance() {
		return new self();
	}

	/**
	 * Get current time.
	 *
	 * @return self
	 */
	public static function now() {
		$instance           = self::instance();
		$instance->datetime = new DateTime();
		return $instance;
	}

	/**
	 * Create time from date time string or timestamp.
	 *
	 * @param string|int $datetime datetime string or timestamp.
	 *
	 * @return self
	 */
	public static function create( $datetime ) {
		$instance           = self::instance();
		$instance->datetime = new DateTime( $datetime );
		return $instance;
	}

	/**
	 * Set timezone
	 *
	 * @param string|DateTimeZone $timezone timezone string or object.
	 *
	 * @return self
	 */
	public function set_timezone( $timezone ) {
		$tz = is_string( $timezone ) ? new DateTimeZone( $timezone ) : $timezone;
		$this->datetime->setTimezone( $tz );
		return $this;
	}

	/**
	 * Add
	 *
	 * @param int    $number number of interval.
	 * @param string $interval interval type (day, month, year, etc).
	 *
	 * @return self
	 */
	public function add( $number, $interval ) {
		$this->datetime->add( DateInterval::createFromDateString( "{$number} {$interval}" ) );
		return $this;
	}

	/**
	 * Sub
	 *
	 * @param int    $number number of interval.
	 * @param string $interval interval type (day, month, year, etc).
	 *
	 * @since 3.0.0
	 *
	 * @return self
	 */
	public function sub( $number, $interval ) {
		$this->datetime->sub( DateInterval::createFromDateString( "{$number} {$interval}" ) );
		return $this;
	}

	/**
	 * Format datetime ( WP i18 translation supported )
	 *
	 * @param string $format format for date. Default is mysql format.
	 * @param bool   $i18_translation i18 translation support.
	 *
	 * @return string
	 */
	public function format( $format = null, $i18_translation = true ) {
		if ( $i18_translation ) {
			$result = wp_date(
				$format ? $format : self::FORMAT_MYSQL,
				$this->to_timestamp(),
				$this->datetime->getTimezone()
			);
		} else {
			$result = $this->datetime->format( $format ? $format : self::FORMAT_MYSQL );
		}

		return $result;
	}

	/**
	 * Convert to timestamp.
	 *
	 * @return int
	 */
	public function to_timestamp() {
		return $this->datetime->getTimestamp();
	}

	/**
	 * Covert to date time string.
	 *
	 * @return string
	 */
	public function toDateTimeString() {
		return $this->format( self::FORMAT_DATE_TIME, false );
	}

	/**
	 * Covert to date string.
	 *
	 * @return string
	 */
	public function to_date_string() {
		return $this->format( self::FORMAT_DATE, false );
	}

	/**
	 * Covert to date string.
	 *
	 * @return string
	 */
	public function to_time_string() {
		return $this->format( self::FORMAT_TIME, false );
	}

	/**
	 * Get GMT date to user timezone date.
	 *
	 * @since 3.0.0
	 *
	 * @param string     $gmt_date gmt date time string.
	 * @param string     $format format string.
	 * @param int|object $user id or object. 0 for current user (optional).
	 *
	 * @return string
	 */
	public static function get_gmt_to_user_timezone_date( string $gmt_date, string $format = null, $user = 0 ): string {
		$default_format = get_option( 'date_format' ) . ', ' . get_option( 'time_format' );
		$format         = is_null( $format ) ? $default_format : $format;

		return self::create( $gmt_date )
				->set_timezone( \TUTOR\User::get_user_timezone_string( $user ) )
				->format( $format );
	}
}
