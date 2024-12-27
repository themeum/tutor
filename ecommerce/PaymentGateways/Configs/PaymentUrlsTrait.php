<?php
/**
 * Payment URLs Trait
 * Contains success, cancel & webhook url
 *
 * @package Tutor\Ecommerce
 * @author Themeum
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\PaymentGateways\Configs;

trait PaymentUrlsTrait {

	/**
	 * Retrieves the webhook URL for Stripe.
	 *
	 * @since 3.0.0
	 *
	 * @return string The webhook URL.
	 */
	public function getWebhookUrl(): string {
		$url = home_url( 'wp-json/tutor/v1/ecommerce-webhook' );
		if ( tutor_is_dev_mode() && defined( 'TUTOR_ECOMMERCE_WEBHOOK_URL' ) ) {
			$url = TUTOR_ECOMMERCE_WEBHOOK_URL;
		}

		return apply_filters( 'tutor_ecommerce_webhook_url', $url );
	}

	/**
	 * Retrieves the success URL for the payment process.
	 *
	 * @since 3.0.0
	 *
	 * @return string The success URL.
	 */
	public function getSuccessUrl(): string {
		$args = array(
			'tutor_order_placement' => 'success',
			'order_id'              => 0,
		);
		$args = apply_filters( 'tutor_ecommerce_payment_success_url_args', $args );
		return add_query_arg( $args, home_url() );
	}

	/**
	 * Retrieves the cancel URL for the payment process.
	 *
	 * @since 3.0.0
	 *
	 * @return string The cancel URL.
	 */
	public function getCancelUrl(): string {
		$args = array(
			'tutor_order_placement' => 'failed',
			'order_id'              => 0,
		);
		$args = apply_filters( 'tutor_ecommerce_payment_cancelled_url_args', $args );

		$url = defined( 'TUTOR_ECOMMERCE_CANCEL_URL' ) ? TUTOR_ECOMMERCE_CANCEL_URL : home_url();
		return add_query_arg( $args, $url );
	}

	/**
	 * Helper function to retrieve field values from config.
	 *
	 * @param array  $config Paypal config array.
	 * @param string $field_name Field name to get value.
	 *
	 * @return mixed
	 */
	public function get_field_value( array $config, string $field_name ) {
		$value = '';
		foreach ( $config['fields'] as $field ) {
			if ( isset( $field['name'] ) && $field['name'] === $field_name ) {
				$value = $field['value'] ?? '';
				break;
			}
		}
		return $value;
	}
}
