<?php
/**
 * Tutor ecommerce functions
 *
 * @package TutorFunctions
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.5.0
 */

use Tutor\Ecommerce\Cart\CartFactory;

if ( ! function_exists( 'tutor_add_to_cart' ) ) {
	/**
	 * Handle add to cart functionalities
	 *
	 * @since 3.5.0
	 *
	 * @param int $item_id Item id.
	 *
	 * @return object {success, message, body}
	 */
	function tutor_add_to_cart( int $item_id ) {
		$monetization      = tutor_utils()->get_option( 'monetize_by' );
		$response          = new stdClass();
		$response->success = true;
		$response->message = __( 'Course added to cart', 'tutor' );
		$response->body    = null;
		try {
			$cart = CartFactory::create_cart( $monetization );
			if ( $cart->add( $item_id ) ) {
				$response->body = $cart->get_cart_url();
			} else {
				$response->success = false;
				$response->message = $cart->get_error();
			}
		} catch ( \Throwable $th ) {
			$response->success = false;
			$response->message = $th->getMessage();
		}

		return $response;
	}
}

if ( ! function_exists( 'tutor_get_cart_url' ) ) {
	/**
	 * Get the cart page URL
	 *
	 * @since 3.5.0
	 *
	 * @return string
	 */
	function tutor_get_cart_url() {
		$monetization = tutor_utils()->get_option( 'monetize_by' );
		try {
			$cart = CartFactory::create_cart( $monetization );
			return $cart->get_cart_url();
		} catch ( \Throwable $th ) {
			return $th->getMessage();
		}
	}
}

if ( ! function_exists( 'tutor_get_cart_items' ) ) {
	/**
	 * Get cart items
	 *
	 * @since 3.5.0
	 *
	 * @return array
	 */
	function tutor_get_cart_items() {
		$items = array();
		try {
			$cart  = tutor_get_cart_object();
			$items = $cart->get_cart_items();
		} catch ( \Throwable $th ) {
			error_log( $th->getMessage() );
		}

		return $items;
	}
}

if ( ! function_exists( 'tutor_get_cart_object' ) ) {
	/**
	 * Get cart items
	 *
	 * @since 3.5.0
	 *
	 * @throws \Throwable If cart object creation failed.
	 *
	 * @return object CartInterface
	 */
	function tutor_get_cart_object() {
		$monetization = tutor_utils()->get_option( 'monetize_by' );
		try {
			return CartFactory::create_cart( $monetization );
		} catch ( \Throwable $th ) {
			throw $th;
		}
	}
}
