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
				'methods'             => WP_REST_Server::ALLMETHODS,
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
		$webhook_data = (object) array(
			'get'    => $_GET,
			'post'   => $_POST,
			'server' => $_SERVER,
			'stream' => file_get_contents( 'php://input' ),
		);

		$payment_method   = Input::get( 'payment_method', 'paypal' );
		$payment_gateways = apply_filters( 'tutor_gateways_with_class', Ecommerce::payment_gateways_with_ref(), $payment_method );

		$payment_gateway_class = isset( $payment_gateways[ $payment_method ] )
								? $payment_gateways[ $payment_method ]['gateway_class']
								: null;

		if ( $payment_gateway_class ) {
			$payment = Ecommerce::get_payment_gateway_object( $payment_gateway_class );
			$res     = $payment->verify_webhook_signature( $webhook_data );
			if ( is_object( $res ) && property_exists( $res, 'id' ) ) {
				do_action( 'tutor_order_payment_updated', $res );
				if ( property_exists( $res, 'redirectUrl' ) ) {
					wp_safe_redirect( $res->redirectUrl );
					exit();
				}
			}
		}
	}

	/**
	 * Load order status template
	 *
	 * It will load success or failed template based on order status
	 *
	 * @since 3.0.0
	 *
	 * @param string $template Default template.
	 *
	 * @return string
	 */
	public function load_order_status_template( $template ) {
		$user_id          = get_current_user_id();
		$placement_status = Input::get( 'tutor_order_placement' );
		$order_id         = Input::get( 'order_id', 0, Input::TYPE_INT );

		if ( $placement_status && $order_id ) {
			delete_transient( CheckoutController::PAY_NOW_ALERT_MSG_TRANSIENT_KEY . $user_id );
			delete_transient( CheckoutController::PAY_NOW_ERROR_TRANSIENT_KEY . $user_id );

			// Modify the page title.
			add_filter(
				'document_title_parts',
				function( $title ) use ( $placement_status ) {
					$site_title = get_bloginfo( 'name' );

					if ( 'success' === $placement_status ) {
						$title['title'] = __( 'Order Placement Success', 'tutor' ) . ' - ' . $site_title;
					} else {
						$title['title'] = __( 'Order Placement Failed', 'tutor' ) . ' - ' . $site_title;
					}

					return $title;
				}
			);

			if ( 'success' === $placement_status ) {
				do_action( 'tutor_order_placement_success', $order_id );
				tutor_load_template(
					'ecommerce.order-placement-success',
					array(
						'order_status' => $placement_status,
						'order_id'     => $order_id,
					)
				);
			} else {
				do_action( 'tutor_order_placement_failed', $order_id );
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
