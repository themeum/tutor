<?php
/**
 * Tutor LMS Timer Utility
 *
 * Clean, reusable countdown formatter using PHP built-in DateTime APIs.
 *
 * Features:
 * - Uses DateInterval / DateTimeImmutable
 * - Multiple formats
 * - Token generation
 * - Render helper
 * - No mixed business/render logic
 * - Stable/reusable API
 *
 * @package Tutor\Helper
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Helpers;

defined( 'ABSPATH' ) || exit;

use DateTimeImmutable;

/**
 * TimerHelper class
 *
 * @since 4.0.0
 */
final class TimerHelper {

	/**
	 * Convert seconds into normalized time parts.
	 *
	 * @param int $seconds Total seconds.
	 *
	 * @return array{
	 *     days:int,
	 *     hours:int,
	 *     minutes:int,
	 *     seconds:int,
	 *     total_hours:int
	 * }
	 */
	public static function decompose( int $seconds ): array {

		$seconds = max( 0, $seconds );

		$start = new DateTimeImmutable( '@0' );
		$end   = new DateTimeImmutable( '@' . $seconds );

		$diff = $start->diff( $end );

		$total_hours = (int) floor( $seconds / HOUR_IN_SECONDS );

		return array(
			'days'        => (int) $diff->days,
			'hours'       => (int) $diff->h,
			'minutes'     => (int) $diff->i,
			'seconds'     => (int) $diff->s,
			'total_hours' => $total_hours,
		);
	}

	/**
	 * Format as digital timer.
	 *
	 * Examples:
	 * - 05:12
	 * - 01:05:12
	 * - 2d 01:05:12
	 *
	 * @param int  $seconds Total seconds.
	 * @param bool $show_days Whether to show day segment.
	 *
	 * @return string
	 */
	public static function format_digital(
		int $seconds,
		bool $show_days = true
	): string {

		$time = self::decompose( $seconds );

		$days    = $time['days'];
		$hours   = $time['hours'];
		$minutes = $time['minutes'];
		$secs    = $time['seconds'];

		// Show days.
		if ( $show_days && $days > 0 ) {
			return sprintf(
				'%dd %02d:%02d:%02d',
				$days,
				$hours,
				$minutes,
				$secs
			);
		}

		// Show hours.
		if ( $time['total_hours'] > 0 ) {
			return sprintf(
				'%02d:%02d:%02d',
				$time['total_hours'],
				$minutes,
				$secs
			);
		}

		// Minutes only.
		return sprintf(
			'%02d:%02d',
			$minutes,
			$secs
		);
	}

	/**
	 * Format as verbose human readable string.
	 *
	 * Examples:
	 * - 2 days 3 hours 5 minutes
	 * - 12 minutes 10 seconds
	 *
	 * @param int $seconds Total seconds.
	 *
	 * @return string
	 */
	public static function format_human( int $seconds ): string {

		$time = self::decompose( $seconds );

		$parts = array();

		if ( $time['days'] > 0 ) {
			$parts[] = sprintf(
				/* translators: %d: number of days */
				_n( '%d day', '%d days', $time['days'], 'tutor' ),
				$time['days']
			);
		}

		if ( $time['hours'] > 0 ) {
			$parts[] = sprintf(
				/* translators: %d: number of hours */
				_n( '%d hour', '%d hours', $time['hours'], 'tutor' ),
				$time['hours']
			);
		}

		if ( $time['minutes'] > 0 ) {
			$parts[] = sprintf(
				/* translators: %d: number of minutes */
				_n( '%d minute', '%d minutes', $time['minutes'], 'tutor' ),
				$time['minutes']
			);
		}

		if ( $time['seconds'] > 0 || empty( $parts ) ) {
			$parts[] = sprintf(
				/* translators: %d: number of seconds */
				_n( '%d second', '%d seconds', $time['seconds'], 'tutor' ),
				$time['seconds']
			);
		}

		return implode( ' ', $parts );
	}

	/**
	 * Build render able timer tokens.
	 *
	 * Example output:
	 * [
	 *   ['type' => 'digit', 'value' => '0'],
	 *   ['type' => 'digit', 'value' => '2'],
	 *   ['type' => 'separator', 'value' => ':'],
	 * ]
	 *
	 * @param int $seconds Total seconds.
	 *
	 * @return array
	 */
	public static function build_tokens( int $seconds ): array {

		$formatted = self::format_digital( $seconds );

		$tokens = array();

		$chars = preg_split( '//u', $formatted, -1, PREG_SPLIT_NO_EMPTY );

		foreach ( $chars as $char ) {

			if ( ':' === $char ) {

				$tokens[] = array(
					'type'  => 'separator',
					'value' => $char,
				);

			} elseif ( ctype_digit( $char ) ) {

				$tokens[] = array(
					'type'  => 'digit',
					'value' => $char,
				);

			} elseif ( 'd' === $char ) {

				$tokens[] = array(
					'type'  => 'suffix',
					'value' => $char,
				);

			} elseif ( ' ' === $char ) {

				$tokens[] = array(
					'type'  => 'spacer',
					'value' => $char,
				);
			}
		}

		return $tokens;
	}

	/**
	 * Render timer HTML.
	 *
	 * @param int $seconds Total seconds.
	 *
	 * @return void
	 */
	public static function render( int $seconds ): void {

		$tokens = self::build_tokens( $seconds );

		echo '<div class="tutor-timer">';

		foreach ( $tokens as $token ) {

			$type  = $token['type'];
			$value = $token['value'];

			$class = 'tutor-timer-' . sanitize_html_class( $type );

			printf(
				'<span class="%1$s">%2$s</span>',
				esc_attr( $class ),
				esc_html( $value )
			);
		}

		echo '</div>';
	}

	/**
	 * Get remaining seconds between two timestamps.
	 *
	 * @param int $future_timestamp Future UNIX timestamp.
	 *
	 * @return int
	 */
	public static function remaining_seconds( int $future_timestamp ): int {

		return max(
			0,
			$future_timestamp - time()
		);
	}
}
