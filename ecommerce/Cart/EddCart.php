<?php
/**
 * Handle EDD cart logics
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
class EddCart implements CartInterface {

	/**
	 * Add to cart
	 *
	 * @since 3.5.0
	 */
	public function add() {
		return 'item added';
	}
}
