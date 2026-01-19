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
		$badge_class = '';
		switch ( $status ) {
			case OrderModel::PAYMENT_REFUNDED:
			case OrderModel::PAYMENT_PARTIALLY_REFUNDED:
				$badge_class = 'warning';
				break;
			case OrderModel::PAYMENT_UNPAID:
				$badge_class = 'danger';
				break;
			case OrderModel::PAYMENT_PAID:
				$badge_class = 'success';
				break;
			case OrderModel::PAYMENT_FAILED:
				$badge_class = 'error';
				break;

		}

		$label = tutor_utils()->translate_dynamic_text( $status );

		Badge::make()->attr( 'class', 'tutor-badge-' . $badge_class )->rounded()->label( $label )->render();
	}

	/**
	 * Render payment gateway badge.
	 *
	 * @since 4.0.0
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
