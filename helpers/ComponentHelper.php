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
use Tutor\Components\Constants\Variant;
use Tutor\Ecommerce\Ecommerce;
use Tutor\Ecommerce\Settings;
use Tutor\Models\OrderModel;

/**
 * Class ComponentHelper
 *
 * @since 4.0.0
 */
class ComponentHelper {

	/**
	 * Render order status badge
	 *
	 * @since 4.0.0
	 *
	 * @param string $status order status. It can be tutor or other monetization order status.
	 *
	 * @return void
	 */
	public static function render_order_status_badge( $status ) : void {
		$badge_class = 'secondary';
		switch ( $status ) {
			case 'processing':
			case 'pending':
			case 'on-hold':
				$badge_class = 'pending';
				break;
			case 'refunded':
			case 'cancelled':
				$badge_class = 'cancelled';
				break;
			case 'incomplete':
				$badge_class = 'secondary';
				break;
			case 'completed':
				$badge_class = 'completed';
				break;
		}

		$label = tutor_utils()->translate_dynamic_text( $status );

		Badge::make()->attr( 'class', 'tutor-badge-' . $badge_class )->circle()->label( $label )->render();
	}

	/**
	 * Render payment status badge
	 *
	 * @since 4.0.0
	 *
	 * @param string $status order status.
	 *
	 * @return void
	 */
	public static function render_payment_status_badge( $status ) : void {
		$badge_class = 'secondary';
		switch ( $status ) {
			case OrderModel::PAYMENT_REFUNDED:
			case OrderModel::PAYMENT_PARTIALLY_REFUNDED:
				$badge_class = 'cancelled';
				break;
			case OrderModel::PAYMENT_UNPAID:
				$badge_class = 'pending';
				break;
			case OrderModel::PAYMENT_PAID:
				$badge_class = 'completed';
				break;
			case OrderModel::PAYMENT_FAILED:
				$badge_class = 'secondary';
				break;

		}

		$label = tutor_utils()->translate_dynamic_text( $status );

		Badge::make()->attr( 'class', 'tutor-badge-' . $badge_class )->circle()->label( $label )->render();
	}

	/**
	 * Render payment gateway badge.
	 *
	 * @param string $payment_method payment method name.
	 *
	 * @return void
	 */
	public static function render_payment_method_badge( $payment_method ) {
		$gateway_config = Settings::get_payment_gateway_settings( $payment_method ?? '' );
		$icon_url       = $gateway_config['icon'] ?? '';
		$label          = Ecommerce::get_payment_method_label( $payment_method ?? '' );

		Badge::make()
			->variant( Variant::SECONDARY )
			->icon( $icon_url, 12, 12 )
			->label( $label )
			->render();
	}
}
