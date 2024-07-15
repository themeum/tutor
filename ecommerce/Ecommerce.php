<?php
/**
 * Main class to handle tutor native e-commerce.
 *
 * @package Tutor\Ecommerce
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\Ecommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Ecommerce
 *
 * @since 3.0.0
 */
class Ecommerce {

	/**
	 * Native ecommerce engin
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const MONETIZE_BY = 'tutor';

	/**
	 * Construct function to initialize e-commerce classes
	 *
	 * @since 3.0.0
	 *
	 * @param bool $register_hooks register hooks.
	 */
	public function __construct( $register_hooks = true ) {

		if ( ! $register_hooks ) {
			return;
		}

		add_filter( 'tutor_monetization_options', array( $this, 'add_monetization_option' ) );

		new OrderController();
		new OrderActivitiesController();
		new CouponController();
		new HooksHandler();
	}

	/**
	 * Add monetization option.
	 *
	 * @since 3.0.0
	 *
	 * @param array $arr options.
	 *
	 * @return array
	 */
	public function add_monetization_option( $arr ) {
		$arr[ self::MONETIZE_BY ] = __( 'Tutor', 'tutor' );

		return $arr;
	}

	/**
	 * Get default automate payment gateways
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public static function get_automate_payment_gateways(): array {
		$gateways = array(
			'paypal' => array(
				'label'       => 'PayPal',
				'icon'        => tutor()->url . 'assets/images/payment-gateways/paypal.svg',
				'package_url' => '',
				'is_active'   => true,
			),
			'stripe' => array(
				'label'       => 'Stripe',
				'icon'        => tutor()->url . 'assets/images/payment-gateways/stripe.svg',
				'package_url' => '',
				'is_active'   => true,
			),
		);

		return apply_filters( 'tutor_automate_payment_gateways', $gateways );
	}

	/**
	 * Get default automate payment gateways
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public static function get_manual_payment_gateways(): array {
		$gateways = array(
			array(
				'label'       => 'Bank Transfer',
				'icon'        => tutor()->url . 'assets/images/payment-gateways/bank-transfer.svg',
				'package_url' => '',
				'is_active'   => true,
			),
			array(
				'label'       => 'Cash on Delivery',
				'icon'        => tutor()->url . 'assets/images/payment-gateways/cash-on-delivery.svg',
				'package_url' => '',
				'is_active'   => true,
			),
		);

		return apply_filters( 'tutor_manual_payment_gateways', $gateways );
	}
}
