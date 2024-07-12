<?php
/**
 * Option keys for ecommerce
 *
 * @package Tutor\Ecommerce
 * @author Themeum
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\Ecommerce;

/**
 * Option key names
 */
class OptionKeys {

	// Currency Settings.
	const CURRENCY_TYPE      = 'currency_type';
	const CURRENCY_POSITION  = 'currency_position';
	const THOUSAND_SEPARATOR = 'thousand_separator';
	const DECIMAL_SEPARATOR  = 'decimal_separator';
	const NUMBER_OF_DECIMALS = 'number_of_decimals';

	// Payment Methods.
	const PAYMENT_METHOD_PAYPAL = 'payment_method_paypal';
	const PAYMENT_METHOD_STRIPE = 'payment_method_stripe';

	// Tax and Coupon Settings.
	const IS_TAX_APPLICABLE    = 'is_tax_applicable';
	const IS_COUPON_APPLICABLE = 'is_coupon_applicable';

	// Billing Information Settings.
	const BILLING_ADDRESS      = 'billing_address';
	const BILLING_PHONE_NUMBER = 'billing_phone_number';
	const BILLING_EMAIL        = 'billing_email';

	// Ecommerce Policies.
	const REFUND_POLICY  = 'ecommerce_refund_policy';
	const PRIVACY_POLICY = 'ecommerce_privacy_policy';

	/**
	 * Get billing fiend options
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public static function get_billing_field_options() {
		$options = array(
			'optional' => __( 'Optional', 'tutor' ),
			'required' => __( 'Required', 'tutor' ),
			'omit'     => __( 'Don\'t Include', 'tutor' ),
		);

		return apply_filters( 'tutor_ecommerce_billing_field_options', $options );
	}

	/**
	 * Get tax configuration page url
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public static function get_tax_config_page_url() {
		$url = admin_url( 'admin.php?page=tutor_settings&tab=tax_configuration' );
		return apply_filters( 'tutor_tax_config_page_url', $url );
	}
}
