<?php
/**
 * Component Helper.
 *
 * @package Tutor\Helper
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Helpers;

use Tutor\Components\Badge;

/**
 * Class ComponentHelper
 *
 * @since 4.0.0
 */
class ComponentHelper {

	/**
	 * Order status badge
	 *
	 * @since 4.0.0
	 *
	 * @param string $status order status. It can be tutor or other monetization order status.
	 *
	 * @return void
	 */
	public static function order_status_badge( $status ) : void {
		$badge_class = '';
		switch ( $status ) {
			case 'processing':
			case 'pending':
			case 'on-hold':
				$badge_class = 'warning';
				break;
			case 'refunded':
			case 'cancelled':
				$badge_class = 'error';
				break;
			case 'completed':
				$badge_class = 'success';
				break;
		}

		$label = tutor_utils()->translate_dynamic_text( $status );

		Badge::make()->attr( 'class', 'tutor-badge-' . $badge_class )->rounded()->label( $label )->render();

	}
}
