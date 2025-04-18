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
		$user_id = tutor_utils()->get_user_id();
		return (bool) $this->cart_model->add_course_to_cart( $user_id, $item_id );
	}

	/**
	 * Get cart page url to view the cart
	 *
	 * @since 3.5.0
	 *
	 * @return string
	 */
	public function get_page_url(): string {
		return CartController::get_page_url();
	}
}
