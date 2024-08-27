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

use TUTOR\Input;
use WP_REST_Server;

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
		add_filter( 'template_include', array( $this, 'load_order_status_template' ) );
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

	public function load_order_status_template( $template ) {
		$placement_status = Input::get( 'tutor_order_placement' );
		$order_id         = Input::get( 'order_id', 0, Input::TYPE_INT );

		if ( $placement_status && $order_id ) {
			if ( 'success' === $placement_status ) {
				tutor_load_template(
					'ecommerce.order-placement-success',
					array(
						'order_status' => $placement_status,
						'order_id'     => $order_id,
					)
				);
			} else {
				tutor_load_template(
					'ecommerce.order-placement-failed',
					array(
						'order_status' => $placement_status,
						'order_id'     => $order_id,
					)
				);
			}
			exit();
		}

		return $template;
	}
}
