<?php
/**
 * Cart Model
 *
 * @package Tutor\Models
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\Models;

use Tutor\Helpers\QueryHelper;

/**
 * Cart model class for performing cart functionalities
 */
class CartModel {

	/**
	 * Get cart items
	 *
	 * @return array
	 */
	public function add_cart_item() {
		$cart_items = QueryHelper::get_all();
		return $cart_items;
	}

	/**
	 * Get cart items
	 *
	 * @return array
	 */
	public function get_cart_items() {
		$cart_items = QueryHelper::get_all();
		return $cart_items;
	}

	/**
	 * Get cart items
	 *
	 * @return array
	 */
	public function delete_cart_item() {
		$cart_items = QueryHelper::get_all();
		return $cart_items;
	}
}
