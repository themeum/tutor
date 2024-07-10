<?php
/**
 * Manage Cart
 *
 * @package Tutor\Ecommerce
 * @author Themeum
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\Ecommerce;

use Tutor\Helpers\HttpHelper;
use TUTOR\Input;
use Tutor\Models\CartModel;
use Tutor\Traits\JsonResponse;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CartController class
 *
 * @since 3.0.0
 */
class CartController {


	/**
	 * Cart model
	 *
	 * @since 3.0.0
	 *
	 * @var CartModel
	 */
	private $model;

	/**
	 * Trait for sending JSON response
	 */
	use JsonResponse;

	/**
	 * Constructor.
	 *
	 * Initializes the Cart class, sets the page title, and optionally registers
	 * hooks for handling AJAX requests related to cart data, bulk actions, cart updates,
	 * and cart deletions.
	 *
	 * @param bool $register_hooks Whether to register hooks for handling requests. Default is true.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function __construct( $register_hooks = true ) {
		$this->model = new CartModel();

		if ( $register_hooks ) {
			/**
			 * Handle AJAX request for getting cart related data by cart ID.
			 *
			 * @since 3.0.0
			 */
			add_action( 'wp_ajax_tutor_cart_details', array( $this, 'get_cart_by_id' ) );

			/**
			 * Handle AJAX request for updating cart item quantity by cart ID.
			 *
			 * @since 3.0.0
			 */
			add_action( 'wp_ajax_tutor_cart_update_quantity', array( $this, 'update_cart_quantity' ) );

			/**
			 * Handle ajax request for delete cart
			 *
			 * @since 3.0.0
			 */
			add_action( 'wp_ajax_tutor_cart_delete', array( $this, 'tutor_cart_delete' ) );
		}
	}

	/**
	 * Get cart page url
	 *
	 * @since 3.0.0
	 *
	 * @param boolean $is_admin Whether to get admin or frontend url.
	 *
	 * @return string
	 */
	public static function get_cart_page_url( bool $is_admin = true ) {
		if ( $is_admin ) {
			return admin_url( 'admin.php?page=' . self::PAGE_SLUG );
		} else {
			return tutor_utils()->get_tutor_dashboard_url() . '/cart';
		}
	}

	/**
	 * Retrieve cart data by cart ID and respond with JSON.
	 *
	 * This function retrieves the cart ID from the POST request, validates it,
	 * fetches the corresponding cart data using the CartModel class, and returns
	 * a JSON response with the cart data or an error message.
	 *
	 * If the cart ID is not provided, it responds with a "Bad Request" status.
	 * If the cart is not found, it responds with a "Not Found" status.
	 * Otherwise, it responds with the cart data and a success message.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function get_cart_by_id() {
		if ( ! tutor_utils()->is_nonce_verified() ) {
			$this->json_response( tutor_utils()->error_message( 'nonce' ), null, HttpHelper::STATUS_BAD_REQUEST );
		}

		$cart_id = Input::post( 'cart_id' );

		if ( empty( $cart_id ) ) {
			$this->json_response(
				__( 'Cart ID is required', 'tutor' ),
				null,
				HttpHelper::STATUS_BAD_REQUEST
			);
		}

		$cart_data = $this->model->get_cart_by_id( $cart_id );

		if ( ! $cart_data ) {
			$this->json_response(
				__( 'Cart not found', 'tutor' ),
				null,
				HttpHelper::STATUS_NOT_FOUND
			);
		}

		$this->json_response(
			__( 'Cart retrieved successfully', 'tutor' ),
			$cart_data
		);
	}
}