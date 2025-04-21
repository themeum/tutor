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
	protected $cart_error;

	/**
	 * Current user id
	 *
	 * @var int
	 */
	protected $user_id;

	/**
	 * Initialize member variables
	 *
	 * @return void
	 */
	public function __construct() {
		$this->cart_error = __( 'Failed to add item to the cart', 'tutor' );
		$this->user_id    = get_current_user_id();
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
