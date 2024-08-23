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

use Tutor\Helpers\HttpHelper;
use TUTOR\Input;
use Tutor\Traits\JsonResponse;
use Tutor\Models\CartModel;
use Tutor\Models\CouponModel;
use Tutor\PaymentGateways\StripeGateway;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Checkout Controller class
 *
 * @since 3.0.0
 */
class CheckoutController {

	use JsonResponse;

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
			add_action( 'tutor_action_tutor_pay_now', array( $this, 'pay_now' ) );
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
	 * Pay now ajax handler
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function pay_now() {
		tutor_utils()->check_nonce();

		$request = Input::sanitize_array( $_POST );

		$object_ids  = $request['object_ids'] ?? '';
		$coupon_code = $request['coupon_code'] ?? '';

		if ( empty( $object_ids ) ) {
			$this->json_response(
				__( 'Invalid cart items' ),
				'',
				HttpHelper::STATUS_BAD_REQUEST
			);
		}

		$coupon_model  = new CouponModel();
		$object_ids    = array_filter( explode( ',', $object_ids ), 'is_numeric' );
		$price_details = $coupon_model->apply_coupon_discount( $object_ids, $coupon_code );

		$payment_data = $this->prepare_payment_data( $object_ids, $price_details );

		$stripe = Ecommerce::get_payment_gateway_object( StripeGateway::class );
		$stripe->setup_payment_and_redirect( $payment_data );
	}

	/**
	 * Prepare payment data
	 *
	 * @since 3.0.0
	 *
	 * @param int|array $object_ids Course/bundle id or array of ids.
	 * @param array     $price_details Detail price.
	 *
	 * @return mixed
	 */
	public function prepare_payment_data( $object_ids, $price_details ) {
		return (object) array(

			'items'                                   => (object) array(
				array(
					'item_id'                           => 1,
					'item_name'                         => 'Sample Product 1',
					'regular_price'                     => 100.00,
					'regular_price_in_smallest_unit'    => 10000,
					'quantity'                          => 3,
					'discounted_price'                  => 90.00,
					'discounted_price_in_smallest_unit' => 9000,
					'image'                             => 'https://example.com/image.jpg',
				),
				array(
					'item_id'                           => 2,
					'item_name'                         => 'Sample Product 2',
					'regular_price'                     => 300.00,
					'regular_price_in_smallest_unit'    => 30000,
					'quantity'                          => 1,
					'discounted_price'                  => 0,
					'discounted_price_in_smallest_unit' => 0,
					'image'                             => 'https://example.com/image.jpg',
				),
			),
			'subtotal'                                => 570.00,
			'subtotal_in_smallest_unit'               => 57000,
			'total_price'                             => 550.00,
			'total_price_in_smallest_unit'            => 55000,
			'order_id'                                => '67530756',
			'store_name'                              => 'Sample Store Name',
			'order_description'                       => 'Sample Order Description',
			'tax'                                     => 230.00,
			'tax_in_smallest_unit'                    => 23000,
			'currency'                                => (object) array(
				'code'         => 'USD',
				'symbol'       => '$',
				'name'         => 'US Dollar',
				'locale'       => 'en-us',
				'numeric_code' => 840,
			),
			'country'                                 => (object) array(
				'name'         => 'United States',
				'numeric_code' => '840',
				'alpha_2'      => 'US',
				'alpha_3'      => 'USA',
				'phone_code'   => '1',
			),
			'shipping_charge'                         => 150.00,
			'shipping_charge_in_smallest_unit'        => 15000,
			'coupon_discount'                         => 400.00,
			'coupon_discount_amount_in_smallest_unit' => 40000,
			'shipping_address'                        => (object) array(
				'name'         => 'John Doe',
				'address1'     => '123 Main St',
				'address2'     => 'Apt 4B',
				'city'         => 'New York',
				'state'        => 'Manhattan',
				'region'       => 'NY',
				'postal_code'  => '10001',
				'country'      => (object) array(
					'name'         => 'United States',
					'numeric_code' => '840',
					'alpha_2'      => 'US',
					'alpha_3'      => 'USA',
				),
				'phone_number' => '123-456-7890',
				'email'        => 'john-doe@example.com',
			),
			'billing_address'                         => (object) array(
				'name'         => 'John Doe',
				'address1'     => '123 Main St',
				'address2'     => 'Apt 4B',
				'city'         => 'New York',
				'state'        => 'Manhattan',
				'region'       => 'NY',
				'postal_code'  => '10001',
				'country'      => (object) array(
					'name'         => 'United States',
					'numeric_code' => '840',
					'alpha_2'      => 'US',
					'alpha_3'      => 'USA',
				),
				'phone_number' => '123-456-7890',
				'email'        => 'john-doe@example.com',
			),
			'decimal_separator'                       => '.',
			'thousand_separator'                      => ',',
			'customer'                                => (object) array(
				'name'         => 'John Doe',
				'address1'     => '123 Main St',
				'address2'     => 'Apt 4B',
				'city'         => 'New York',
				'state'        => 'Manhattan',
				'region'       => 'NY',
				'postal_code'  => '10001',
				'country'      => (object) array(
					'name'         => 'United States',
					'numeric_code' => '840',
					'alpha_2'      => 'US',
					'alpha_3'      => 'USA',
				),
				'email'        => 'john-doe@example.com',
				'phone_number' => '123-456-7890',
			),
		);
	}

	/**
	 * Restrict checkout page
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
