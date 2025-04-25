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
	 * Remove an item from cart
	 *
	 * @since 3.5.0
	 *
	 * @param integer $item_id Item id to add to cart.
	 *
	 * @return boolean
	 */
	public function remove( int $item_id ): bool;

	/**
	 * Clear the cart entirely
	 *
	 * @since 3.5.0
	 *
	 * @return boolean
	 */
	public function clear_cart(): bool;

	/**
	 * Get cart items
	 *
	 * @since 3.5.0
	 *
	 * @return array Array of object
	 */
	public function get_cart_items(): array;

	/**
	 * Get cart url to view the cart
	 *
	 * @since 3.5.0
	 *
	 * @return string
	 */
	public function get_cart_url(): string;

	/**
	 * Get cart error message
	 *
	 * @since 3.5.0
	 *
	 * @return string
	 */
	public function get_error(): string;

	/**
	 * Check if item already exists in cart
	 *
	 * @since 3.5.0
	 *
	 * @param integer $item_id Item id.
	 *
	 * @return boolean
	 */
	public function is_item_exists( int $item_id ): bool;
}
