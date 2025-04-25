<?php
/**
 * Woo cart logics handler
 *
 * @package Tutor\Ecommerce
 * @author Themeum
 * @link https://themeum.com
 * @since 3.5.0
 */

namespace Tutor\Ecommerce\Cart;

use Tutor\Ecommerce\Cart\Contracts\CartInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Class for managing woocommere cart functions
 *
 * @since 3.5.0
 */
class WooCart extends BaseCart implements CartInterface {

	/**
	 * WC_Cart object
	 *
	 * @var WC_Cart
	 */
	private $cart;

	/**
	 * Constructor
	 *
	 * @since 3.5.0
	 */
	public function __construct() {
		parent::__construct();
		$this->cart = WC()->cart;
	}

	/**
	 * Add to cart
	 *
	 * @since 3.5.0
	 *
	 * @param int $item_id Item id to add to cart.
	 *
	 * @return bool
	 */
	public function add( int $item_id ): bool {
		$wc_product_id = tutor_utils()->get_course_product_id( $item_id );
		if ( ! $wc_product_id ) {
			$this->cart_error = __( 'Woocommerce Product not found', 'tutor' );
			return false;
		}

		if ( $this->is_item_exists( $item_id ) ) {
			$this->cart_error = __( 'Item already exists in cart', 'tutor' );
			return false;
		} else {
			return $this->cart->add_to_cart( $wc_product_id ) ? true : false;
		}
	}

	/**
	 * Remove an item from cart
	 *
	 * @since 3.5.0
	 *
	 * @param integer $item_id Item id to add to cart.
	 *
	 * @return boolean
	 */
	public function remove( int $item_id ): bool {
		// @TODO
		return false;
	}

	/**
	 * Clear the cart entirely
	 *
	 * @since 3.5.0
	 *
	 * @return boolean
	 */
	public function clear_cart(): bool {
		// @TODO
		return false;
	}


	/**
	 * Get cart items
	 *
	 * @since 3.5.0
	 *
	 * @return array Array of objects
	 */
	public function get_cart_items(): array {
		$items = array();
		// @TODO need to implement this.
		return $items;
	}

	/**
	 * Get cart page url to view the cart
	 *
	 * @since 3.5.0
	 *
	 * @return string
	 */
	public function get_cart_url(): string {
		return wc_get_cart_url();
	}

	/**
	 * Check if item exists in cart
	 *
	 * @since 3.5.0
	 *
	 * @param int $item_id Item id.
	 *
	 * @return bool
	 */
	public function is_item_exists( int $item_id ): bool {
		$product_id = tutor_utils()->get_course_product_id( $item_id );
		return (bool) $this->cart->find_product_in_cart( $this->cart->generate_cart_id( $product_id ) );
	}
}
