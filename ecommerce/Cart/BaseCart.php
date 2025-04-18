<?php
/**
 * Base cart for handling common functionalities
 *
 * @package Tutor\Ecommerce
 * @author Themeum
 * @link https://themeum.com
 * @since 3.5.0
 */

namespace Tutor\Ecommerce\Cart;

/**
 * BaseCart class
 */
class BaseCart {

	/**
	 * Cart error
	 *
	 * @var string
	 */
	private $cart_error;

	/**
	 * Initialize member variables
	 *
	 * @return void
	 */
	public function __construct() {
		$this->cart_error = __( 'Failed to add item to the cart', 'tutor' );
	}

	/**
	 * Get cart error
	 *
	 * @return string
	 */
	public function get_error(): string {
		return $this->cart_error;
	}

}
