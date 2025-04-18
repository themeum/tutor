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
 * Class for managing native cart functions
 *
 * @since 3.5.0
 */
class WooCart extends BaseCart implements CartInterface {

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
		return true;
	}

	/**
	 * Get cart page url to view the cart
	 *
	 * @since 3.5.0
	 *
	 * @return string
	 */
	public function get_page_url(): string {
		return '';
	}
}
