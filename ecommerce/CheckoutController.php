<?php
/**
 * Manage Checkout
 *
 * @package Tutor\Ecommerce
 * @author Themeum
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\Ecommerce;

use Tutor\Models\CartModel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Checkout Controller class
 *
 * @since 3.0.0
 */
class CheckoutController {

	/**
	 * Constructor.
	 *
	 * Initializes the Checkout class, sets the page title, and optionally registers
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
		if ( $register_hooks ) {
			add_action( 'template_redirect', array( $this, 'restrict_checkout_page' ) );
		}
	}

	/**
	 * Page slug for checkout page
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	public const PAGE_SLUG = 'checkout';

	/**
	 * Page slug for checkout page
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	public const PAGE_ID_OPTION_NAME = 'tutor_checkout_page_id';

	/**
	 * Get cart page url
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public static function get_page_url() {
		return get_post_permalink( self::get_page_id() );
	}

	/**
	 * Get cart page ID
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public static function get_page_id() {
		return (int) tutor_utils()->get_option( self::PAGE_ID_OPTION_NAME );
	}

	/**
	 * Create checkout page
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public static function create_checkout_page() {
		$page_id = self::get_page_id();
		if ( ! $page_id ) {
			$args = array(
				'post_title'   => ucfirst( self::PAGE_SLUG ),
				'post_content' => '',
				'post_type'    => 'page',
				'post_status'  => 'publish',
			);

			$page_id = wp_insert_post( $args );
			tutor_utils()->update_option( self::PAGE_ID_OPTION_NAME, $page_id );
		}
	}

	/**
	 * Restrict checkout page
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function restrict_checkout_page() {
		$page_id = self::get_page_id();

		if ( is_page( $page_id ) ) {
			$cart_controller = new CartController();
			$cart_model      = new CartModel();

			$user_id       = tutils()->get_user_id();
			$has_cart_item = $cart_model->has_item_in_cart( $user_id );

			if ( ! $has_cart_item ) {
				wp_safe_redirect( $cart_controller::get_page_url() );
				exit;
			}
		}
	}
}
