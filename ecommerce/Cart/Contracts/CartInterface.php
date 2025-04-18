<?php
/**
 * Cart contracts
 *
 * @package Tutor\Ecommerce
 * @author Themeum
 * @link https://themeum.com
 * @since 3.5.0
 */

namespace Tutor\Ecommerce\Cart\Contracts;

interface CartInterface {

	/**
	 * Add item to cart
	 *
	 * @since 3.5.0
	 *
	 * @param integer $item_id Item id to add to cart.
	 *
	 * @return boolean
	 */
	public function add( int $item_id ): bool;

	/**
	 * Get cart page url to view the cart
	 *
	 * @since 3.5.0
	 *
	 * @return string
	 */
	public function get_page_url(): string;

	/**
	 * Get cart error message
	 *
	 * @since 3.5.0
	 *
	 * @return string
	 */
	public function get_error(): string;
}
