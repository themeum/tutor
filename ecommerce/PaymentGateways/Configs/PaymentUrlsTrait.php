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
		if ( tutor_is_local_env() ) {
			$url = 'https://7be4-119-148-4-217.ngrok-free.app/wp-json/tutor/v1/ecommerce-webhook';
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

		return add_query_arg( $args, home_url() );
	}
}
