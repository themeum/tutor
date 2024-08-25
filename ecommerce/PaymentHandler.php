<?php
/**
 * Handle payment success/cancelled redirection & webhook events
 *
 * @package Tutor\Ecommerce
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\Ecommerce;

/**
 * Payment handler class.
 */
class PaymentHandler {

	/**
	 * Register hooks
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_webhook_route' ) );
	}

	/**
	 * Register route for handle webhook event.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function register_webhook_route() {
		register_rest_route(
			'tutor/v1',
			'/ecommerce-webhook',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'handle_ecommerce_webhook' ),
				'permission_callback' => '__return_true', // Allows public access to the route.
			)
		);
	}

	/**
	 * Webhook request handler
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function handle_ecommerce_webhook() {
		// @TODO implementation of webhook handler.
	}
}
