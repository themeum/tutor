<?php
/**
 * Handle native cart logics
 *
 * @package Tutor\Ecommerce
 * @author Themeum
 * @link https://themeum.com
 * @since 3.5.0
 */

namespace Tutor\Ecommerce\Cart;

use Tutor\Ecommerce\Cart\Contracts\CartInterface;
use Tutor\Ecommerce\CartController;
use Tutor\Models\CartModel;
use TutorPro\Ecommerce\GuestCheckout\GuestCart;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Class for managing native cart functions
 *
 * @since 3.5.0
 */
class NativeCart extends BaseCart implements CartInterface {

	/**
	 * Cart model
	 *
	 * @var CartModel
	 */
	private $cart_model;

	/**
	 * Initialize vars
	 *
	 * @since 3.5.0
	 */
	public function __construct() {
		parent::__construct();
		$this->cart_model = new CartModel();
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
		if ( $this->is_item_exists( $item_id ) ) {
			$this->cart_error = __( 'Item already exists in cart', 'tutor' );
			return false;
		}
		if ( ! $this->user_id ) {
			try {
				GuestCart::add_cart_item( $item_id );
				return true;
			} catch ( \Throwable $th ) {
				return false;
			}
		}

		return (bool) $this->cart_model->add_course_to_cart( $this->user_id, $item_id );
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
		if ( $this->user_id ) {
			return $this->cart_model->delete_cart_item( $item_id );
		} else {
			try {
				GuestCart::delete_cart_item( $item_id );
				return true;
			} catch ( \Throwable $th ) {
				return false;
			}
		}
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
		$items      = array();
		$cart_items = $this->user_id ? $this->cart_model->get_cart_items( $this->user_id ) : GuestCart::get_cart_items();
		if ( $this->user_id ) {
			$cart_items = $this->cart_model->get_cart_items( $this->user_id );
			if ( is_array( $cart_items ) && ! empty( $cart_items['courses']['results'] ) ) {
				foreach ( $cart_items['courses']['results'] as $cart_item ) {
					$item = (object) array(
						'id'    => $cart_item->ID,
						'title' => $cart_item->post_title,
					);

					$items[] = $item;
				}
			}
		} else {
			$cart_items = GuestCart::get_cart_items();
			$items      = array_map(
				function( $item ) {
					return (object) array(
						'id'    => $item,
						'title' => get_the_title( $item ),
					);
				},
				$cart_items
			);
		}

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
		return CartController::get_page_url();
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
		if ( $this->user_id ) {
			return $this->cart_model->is_course_in_user_cart( $this->user_id, $item_id );
		}
		return GuestCart::is_item_exists( $item_id );
	}
}
