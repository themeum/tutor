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
use Tutor\Models\BillingModel;
use Tutor\Traits\JsonResponse;
use Tutor\Models\CartModel;
use Tutor\Models\CouponModel;
use Tutor\Models\OrderModel;
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
			add_action( 'tutor_order_placed', array( $this, 'proceed_to_payment' ) );
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

		$coupon_model  = new CouponModel();
		$billing_model = new BillingModel();

		$current_user_id = get_current_user_id();

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

		$object_ids = array_filter( explode( ',', $object_ids ), 'is_numeric' );

		$price_details = $coupon_code ? $coupon_model->apply_coupon_discount( $object_ids, $coupon_code ) : $coupon_model->apply_automatic_coupon_discount( $object_ids );

		$billing_info = array_intersect_key( $request, array_flip( $billing_model->get_fillable_fields() ) );

		$billing_model->update( $billing_info, array( 'user_id' => $current_user_id ) );

		$items = array();

		foreach ( $price_details->items as $item ) {
			$items[] = array(
				'user_id'       => $current_user_id,
				'item_id'       => $item->item_id,
				'regular_price' => $item->regular_price,
				'sale_price'    => $item->discount_price,
			);
		}

		( new OrderController( false ) )->create_order( $current_user_id, $items, OrderModel::PAYMENT_UNPAID, $coupon_code );
	}

	/**
	 * Prepare payment data
	 *
	 * @since 3.0.0
	 *
	 * @param array $order Order object.
	 *
	 * @return mixed
	 */
	public function proceed_to_payment( array $order ) {
		$site_info     = get_bloginfo();
		$order_user_id = $order['user_id'];

		$items          = array();
		$subtotal_price = $order['subtotal_price'];
		$total_price    = $order['subtotal_price'];

		$currency_code   = tutor_utils()->get_option( OptionKeys::CURRENCY_SYMBOL );
		$currency_symbol = tutor_get_currency_symbol_by_code( $currency_code );
		$currency_info   = tutor_get_currencies_info_code( $currency_code );

		$billing_info = ( new BillingModel() )->get_info( $order_user_id );

		$billing  = array();
		$shipping = array();
		$country  = array();

		foreach ( $order['items'] as $item ) {
			$subtotal_price += $item->regular_price;

			$items[] = array(
				'item_id'                           => $item->item_id,
				'item_name'                         => get_the_title( $item->item_id ),
				'regular_price'                     => floatval( $item->regular_price ),
				'regular_price_in_smallest_unit'    => intval( $item->regular_price * 100 ),
				'quantity'                          => 1,
				'discounted_price'                  => floatval( $item->discount_price ),
				'discounted_price_in_smallest_unit' => intval( $item->discount_price * 100 ),
				'image'                             => get_the_post_thumbnail_url( $item->item_id ),
			);
		}

		return (object) array(

			'items'                                   => (object) $items,
			'subtotal'                                => floatval( $subtotal_price ),
			'subtotal_in_smallest_unit'               => floatval( $subtotal_price * 100 ),
			'total_price'                             => floatval( $total_price ),
			'total_price_in_smallest_unit'            => floatval( $total_price * 100 ),
			'order_id'                                => $order['id'],
			'store_name'                              => $site_info['name'],
			'order_description'                       => 'Tutor Order',
			'tax'                                     => 0,
			'tax_in_smallest_unit'                    => floatval( 0 * 100 ),
			'currency'                                => (object) array(
				'code'         => $currency_code,
				'symbol'       => $currency_symbol,
				'name'         => $currency_info['name'],
				'locale'       => $currency_info['locale'],
				'numeric_code' => $currency_info['numeric_code'],
			),
			'country'                                 => (object) array(
				'name'         => $billing_info['billing_country'] ?? '',
				'numeric_code' => '',
				'alpha_2'      => '',
				'alpha_3'      => '',
				'phone_code'   => '',
			),
			'shipping_charge'                         => 0,
			'shipping_charge_in_smallest_unit'        => 0,
			'coupon_discount'                         => floatval( $order['discount_amount'] ),
			'coupon_discount_amount_in_smallest_unit' => floatval( $order['discount_amount'] * 100 ),
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
