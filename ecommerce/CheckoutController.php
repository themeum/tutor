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

use Tutor\Helpers\ValidationHelper;
use TUTOR\Input;
use Tutor\Models\BillingModel;
use Tutor\Traits\JsonResponse;
use Tutor\Models\CartModel;
use Tutor\Models\CouponModel;
use Tutor\Models\OrderModel;

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
	 * Page slug for checkout page
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const PAGE_SLUG = 'checkout';

	/**
	 * Page slug for checkout page
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const PAGE_ID_OPTION_NAME = 'tutor_checkout_page_id';

	/**
	 * Pay now error transient key
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const PAY_NOW_ERROR_TRANSIENT_KEY = 'tutor_pay_now_errors_';

	/**
	 * Pay now alert transient key
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const PAY_NOW_ALERT_MSG_TRANSIENT_KEY = 'tutor_pay_now_alert_msg_';

	/**
	 * Coupon model instance.
	 *
	 * @since 3.0.0
	 *
	 * @var CouponModel
	 */
	public $coupon_model;

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
		$this->coupon_model = new CouponModel();

		if ( $register_hooks ) {
			add_action( 'tutor_action_tutor_pay_now', array( $this, 'pay_now' ) );
			add_action( 'tutor_action_tutor_pay_incomplete_order', array( $this, 'pay_incomplete_order' ) );
			add_action( 'template_redirect', array( $this, 'restrict_checkout_page' ) );
			add_action( 'wp_ajax_tutor_get_checkout_html', array( $this, 'ajax_get_checkout_html' ) );
		}
	}

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
	 * Get checkout HTML
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function ajax_get_checkout_html() {
		tutor_utils()->check_nonce();

		ob_start();
		tutor_load_template( 'ecommerce/checkout-details' );
		$content = ob_get_clean();

		$this->json_response(
			__( 'Success', 'tutor' ),
			$content
		);
	}

	/**
	 * Prepare items
	 *
	 * @since 3.0.0
	 *
	 * @param array       $item_ids items.
	 * @param string      $order_type order type.
	 * @param object|null $coupon coupon.
	 *
	 * @return array
	 */
	private function prepare_items( $item_ids, $order_type = OrderModel::TYPE_SINGLE_ORDER, $coupon = null ) {
		$items     = array();
		$plan_info = null;

		foreach ( $item_ids as $item_id ) {
			$item_name    = get_the_title( $item_id );
			$course_price = tutor_utils()->get_raw_course_price( $item_id );
			if ( OrderModel::TYPE_SINGLE_ORDER !== $order_type ) {
				$plan_info = apply_filters( 'tutor_checkout_plan_info', null, $item_id );
				if ( $plan_info ) {
					$item_name                   = $plan_info->plan_name;
					$course_price->regular_price = $plan_info->regular_price;
					$course_price->sale_price    = $plan_info->in_sale_price ? $plan_info->sale_price : 0;
				}
			}

			$regular_price = $course_price->regular_price;
			$sale_price    = $course_price->sale_price;

			$item = array(
				'item_id'           => (int) $item_id,
				'item_name'         => $item_name,
				'regular_price'     => $regular_price,
				'sale_price'        => $sale_price ? $sale_price : null,
				'is_coupon_applied' => false,
				'coupon_code'       => null,
			);

			$is_coupon_applicable = false;
			if ( Settings::is_coupon_usage_enabled() && is_object( $coupon ) ) {
				$is_coupon_applicable = ! $sale_price && $this->coupon_model->is_coupon_applicable( $coupon, $item_id );
				if ( $is_coupon_applicable ) {
					$item['is_coupon_applied'] = $is_coupon_applicable;
					$item['coupon_code']       = $coupon->coupon_code;
				}
			}

			$items[] = $item;
		}

		return array( $items, $plan_info );
	}

	/**
	 * Calculate discount.
	 *
	 * @since 3.0.0
	 *
	 * @param array  $items item array.
	 * @param string $discount_type discount type. like percentage or fixed.
	 * @param float  $discount_value value of discount.
	 *
	 * @return array
	 */
	public function calculate_discount( $items, $discount_type, $discount_value ) {
		// Filter products without a sale price.
		$items_without_sale = array_filter(
			$items,
			function( $item ) {
				return empty( $item['sale_price'] );
			}
		);

		// Calculate the total regular price of products without a sale price.
		$total_regular_price = array_sum( array_column( $items_without_sale, 'regular_price' ) );

		$final = array();
		foreach ( $items as $item ) {
			// If product already has a sale price, no discount is applied.
			if ( ! empty( $item['sale_price'] ) || ( isset( $item['is_coupon_applied'] ) && ! $item['is_coupon_applied'] ) ) {
				$item['discount_amount'] = 0;
				$final[]                 = $item;
			} else {
				if ( 'percentage' === $discount_type ) {
					// Apply percentage discount.
					$discount       = $item['regular_price'] * ( $discount_value / 100 );
					$discount_price = $item['regular_price'] - $discount;

					$item['discount_amount'] = round( $discount, 2 );
					$item['discount_price']  = $discount_price;

				} elseif ( 'flat' === $discount_type && $total_regular_price > 0 ) {
					// Apply a proportional fixed discount based on the regular price.
					$proportion     = $item['regular_price'] / $total_regular_price;
					$discount       = $discount_value * $proportion;
					$discount_price = $item['regular_price'] - $discount;

					$item['discount_amount'] = round( $discount, 2 );
					$item['discount_price']  = round( $discount_price, 2 );
				}
				$final[] = $item;
			}
		}

		return $final;
	}

	/**
	 * Prepare checkout item with applying coupon if required.
	 *
	 * @since 3.0.0
	 *
	 * @param int|array $item_ids Required, course ids or plan id.
	 * @param string    $order_type order type.
	 * @param string    $coupon_code coupon code.
	 *
	 * @return object
	 */
	public function prepare_checkout_items( $item_ids, $order_type = OrderModel::TYPE_SINGLE_ORDER, $coupon_code = null ) {
		$item_ids = is_array( $item_ids ) ? $item_ids : array( $item_ids );
		$response = array();

		$coupon_type       = empty( $coupon_code ) ? 'automatic' : 'manual';
		$is_coupon_applied = false;
		$coupon_title      = '';

		$total_price     = 0;
		$subtotal_price  = 0;
		$coupon_discount = 0;
		$sale_discount   = 0;

		$coupon                  = null;
		$is_coupon_applied       = false;
		$is_meet_min_requirement = false;
		$selected_coupon         = null;

		if ( Settings::is_coupon_usage_enabled() ) {
			$selected_coupon = $this->coupon_model->get_coupon_details_for_checkout( $coupon_code );
		}

		$is_valid = is_object( $selected_coupon ) && $this->coupon_model->is_coupon_valid( $selected_coupon );
		if ( $is_valid ) {
			$is_meet_min_requirement = $this->coupon_model->is_coupon_requirement_meet( $item_ids, $selected_coupon, $order_type );
			if ( $is_meet_min_requirement ) {
				$coupon            = $selected_coupon;
				$is_coupon_applied = true;
			}
		}

		list($items, $plan_info) = $this->prepare_items( $item_ids, $order_type, $coupon );

		if ( $is_coupon_applied ) {
			$items        = $this->calculate_discount( $items, $coupon->discount_type, $coupon->discount_amount );
			$coupon_title = $coupon->coupon_title;
		}

		// Keep calculated price for each item.
		foreach ( $items as $item ) {
			$discount_amount        = isset( $item['discount_amount'] ) ? $item['discount_amount'] : 0;
			$has_discount_amount    = $discount_amount > 0;
			$item['discount_price'] = $has_discount_amount ? max( 0, $item['discount_price'] ) : null;

			$display_price         = isset( $item['sale_price'] ) ? $item['sale_price'] : $item['regular_price'];
			$display_price         = $has_discount_amount ? $item['discount_price'] : $display_price;
			$item['display_price'] = $display_price;

			$sale_discount_amount         = isset( $item['sale_price'] ) ? $item['regular_price'] - $item['sale_price'] : 0;
			$item['sale_discount_amount'] = $sale_discount_amount;

			$response['items'][] = (object) $item;

			$subtotal_price  += $item['regular_price'];
			$coupon_discount += $discount_amount;
			$sale_discount   += $sale_discount_amount;
		}

		if ( $plan_info && $plan_info->enrollment_fee > 0 ) {
			$subtotal_price += $plan_info->enrollment_fee;
		}

		$total_price = $subtotal_price - ( $coupon_discount + $sale_discount );
		$tax_rate    = Tax::get_user_tax_rate();
		$tax_amount  = Tax::calculate_tax( $total_price, $tax_rate );

		$total_price_without_tax = $total_price;
		if ( ! Tax::is_tax_included_in_price() ) {
			$total_price += $tax_amount;
		}

		// Total price should not negative.
		$total_price = max( 0, $total_price );

		$response['coupon_type']       = $coupon_type;
		$response['coupon_title']      = $coupon_title;
		$response['is_coupon_applied'] = $is_coupon_applied;

		$response['subtotal_price']          = $subtotal_price;
		$response['coupon_discount']         = $coupon_discount;
		$response['sale_discount']           = $sale_discount;
		$response['tax_rate']                = $tax_rate;
		$response['total_price_without_tax'] = $total_price_without_tax;
		$response['tax_amount']              = $tax_amount;
		$response['total_price']             = $total_price;

		return (object) $response;
	}

	/**
	 * Pay now ajax handler
	 * Create pending order, prepare payment data & proceed to payment gateway
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function pay_now() {
		tutor_utils()->check_nonce();

		$errors     = array();
		$order_data = null;

		$billing_model   = new BillingModel();
		$current_user_id = get_current_user_id();
		$request = Input::sanitize_array( $_POST ); //phpcs:ignore --sanitized.

		$billing_fillable_fields = array_intersect_key( $request, array_flip( $billing_model->get_fillable_fields() ) );

		$order_payment_fields = array(
			'object_ids',
			'coupon_code',
			'payment_method',
			'payment_type',
			'order_type',
		);

		$request = array_intersect_key( $request, array_flip( $order_payment_fields ) );
		// Set required.
		foreach ( $order_payment_fields as $field ) {
			if ( ! isset( $request[ $field ] ) ) {
				$request[ $field ] = '';
			}
		}

		// Validate data.
		$validate = $this->validate_pay_now_req( $request );

		if ( ! $validate->success ) {
			foreach ( $validate->errors as $error ) {
				if ( is_array( $error ) ) {
					foreach ( $error as $err ) {
						array_push( $errors, $err );
					}
				} else {
					array_push( $errors, $error );
				}
			}
		}

		// Return if validation failed.
		if ( ! empty( $errors ) ) {
			set_transient( self::PAY_NOW_ERROR_TRANSIENT_KEY . $current_user_id, $errors );
			return;
		}

		$object_ids     = array_filter( explode( ',', $request['object_ids'] ), 'is_numeric' );
		$coupon_code    = isset( $request['coupon_code'] ) ? $request['coupon_code'] : '';
		$payment_method = $request['payment_method'];
		$payment_type   = $request['payment_type'];
		$order_type     = $request['order_type'];

		if ( empty( $object_ids ) ) {
			array_push( $errors, __( 'Invalid cart items', 'tutor' ) );
		}

		// if ( ! Ecommerce::is_payment_gateway_configured( $payment_method ) ) {
		// array_push( $errors, Ecommerce::get_incomplete_payment_setup_error_message( $payment_method ) );
		// }

		$billing_info = $billing_model->get_info( $current_user_id );
		if ( $billing_info ) {
			$update_billing = $billing_model->update( $billing_fillable_fields, array( 'user_id' => $current_user_id ) );
			if ( ! $update_billing ) {
				array_push( $errors, __( 'Billing information update failed!', 'tutor' ) );
			}
		} else {
			// Save billing info.
			$billing_fillable_fields['user_id'] = $current_user_id;

			$save = $billing_model->insert( $billing_fillable_fields );
			if ( ! $save ) {
				array_push( $errors, __( 'Billing info save failed!', 'tutor' ) );
			}
		}

		$checkout_data = $this->prepare_checkout_items( $object_ids, $order_type, $coupon_code );
		$items         = array();
		foreach ( $checkout_data->items as $item ) {
			$items[] = array(
				'item_id'        => $item->item_id,
				'regular_price'  => $item->regular_price,
				'sale_price'     => $item->sale_price,
				'discount_price' => $item->discount_price,
				'coupon_code'    => $item->is_coupon_applied ? $item->coupon_code : null,
			);
		}

		$args = array(
			'payment_method'  => $payment_method,
			'coupon_amount'   => $checkout_data->coupon_discount,
			'discount_amount' => $checkout_data->sale_discount,
		);

		if ( empty( $errors ) ) {
			$order_data = ( new OrderController( false ) )->create_order( $current_user_id, $items, OrderModel::PAYMENT_UNPAID, $order_type, $coupon_code, $args, false );
			if ( ! empty( $order_data ) ) {
				if ( 'automate' === $payment_type ) {
					try {
						$payment_data = self::prepare_payment_data( $order_data );
						$this->proceed_to_payment( $payment_data, $payment_method, $order_type );
					} catch ( \Throwable $th ) {
						tutor_log( $th );
						tutor_redirect_after_payment( OrderModel::ORDER_PLACEMENT_FAILED, $order_data['id'], $th->getMessage() );
					}
				} else {
					// Set alert message session.
					$this->set_pay_now_alert_msg( $order_data );
					tutor_redirect_after_payment( OrderModel::ORDER_PLACEMENT_SUCCESS, $order_data['id'] );
				}
			} else {
				array_push( $errors, __( 'Failed to place order!', 'tutor' ) );
				set_transient( self::PAY_NOW_ERROR_TRANSIENT_KEY . $current_user_id, $errors );
				$this->set_pay_now_alert_msg( $order_data );
			}
		} else {
			set_transient( self::PAY_NOW_ERROR_TRANSIENT_KEY . $current_user_id, $errors );
			$this->set_pay_now_alert_msg( $order_data );
		}
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
	public static function prepare_payment_data( array $order ) {
		$site_name     = get_bloginfo( 'name' );
		$order_user_id = $order['user_id'];
		$user_data     = get_userdata( $order_user_id );

		$items          = array();
		$subtotal_price = $order['subtotal_price'];
		$total_price    = $order['total_price'];
		$grand_total    = $total_price;
		$order_type     = $order['order_type'];

		$currency_code   = tutor_utils()->get_option( OptionKeys::CURRENCY_CODE, 'USD' );
		$currency_symbol = tutor_get_currency_symbol_by_code( $currency_code );
		$currency_info   = tutor_get_currencies_info_by_code( $currency_code );

		$billing_info = ( new BillingModel() )->get_info( $order_user_id );

		$country_info = tutor_get_country_info_by_name( $billing_info->billing_country );

		$country = (object) array(
			'name'         => $country_info['name'],
			'numeric_code' => $country_info['numeric_code'],
			'alpha_2'      => $country_info['alpha_2'],
			'alpha_3'      => $country_info['alpha_3'],
			'phone_code'   => $country_info['phone_code'],
		);

		$billing_name = $billing_info ? trim( $billing_info->billing_first_name . ' ' . $billing_info->billing_last_name ) : $user_data->display_name;

		$shipping_and_billing = array(
			'name'         => $billing_name,
			'address1'     => $billing_info->billing_address ?? '',
			'address2'     => $billing_info->billing_address ?? '',
			'city'         => $billing_info->billing_city ?? '',
			'state'        => $billing_info->billing_state ?? '',
			'region'       => '',
			'postal_code'  => $billing_info->billing_zip_code ?? '',
			'country'      => $country,
			'phone_number' => $billing_info->billing_phone ?? '',
			'email'        => $billing_info->billing_email ?? '',
		);

		$customer_info = $shipping_and_billing;

		foreach ( $order['items'] as $item ) {
			$item            = (object) $item;
			$item_name       = '';
			$enrollment_item = null;

			// Support for both item_id & id added.
			$item_id = $item->item_id ?? $item->id;

			if ( OrderModel::TYPE_SUBSCRIPTION === $order_type ) {
				$plan_id   = $item_id;
				$plan_info = apply_filters( 'tutor_checkout_plan_info', new \stdClass(), $plan_id );
				$item_name = $plan_info->plan_name ?? '';

				$items[] = array(
					'item_id'          => $item_id,
					'item_name'        => $item_name,
					'regular_price'    => $item->sale_price > 0 ? $item->sale_price : $item->regular_price,
					'quantity'         => 1,
					'discounted_price' => is_null( $item->discount_price ) || '' === $item->discount_price ? null : $item->discount_price,
				);

				if ( $plan_info && property_exists( $plan_info, 'enrollment_fee' ) && $plan_info->enrollment_fee > 0 ) {
					$enrollment_item = array(
						'item_id'          => 0,
						'item_name'        => 'Enrollment Fee',
						'regular_price'    => floatval( $plan_info->enrollment_fee ),
						'quantity'         => 1,
						'discounted_price' => null,
					);

					$items[] = $enrollment_item;
				}
			} else {
				// Single order item.
				$items[] = array(
					'item_id'          => $item_id,
					'item_name'        => get_the_title( $item_id ),
					'regular_price'    => tutor_get_locale_price( $item->sale_price > 0 ? $item->sale_price : $item->regular_price ),
					'quantity'         => 1,
					'discounted_price' => is_null( $item->discount_price ) || '' === $item->discount_price ? null : tutor_get_locale_price( $item->discount_price ),
				);
			}
		}

		if ( isset( $order['tax_amount'] ) && ! Tax::is_tax_included_in_price() ) {
			$grand_total += $order['tax_amount'];

			/* translators: %s: tax rate */
			$tax_item = sprintf( __( 'Tax (%s)', 'tutor' ), $order['tax_rate'] . '%' );
			$items[]  = array(
				'item_id'          => 'tax',
				'item_name'        => $tax_item,
				'regular_price'    => $order['tax_amount'],
				'quantity'         => 1,
				'discounted_price' => null,
			);
		}

		return (object) array(
			'items'              => (object) $items,
			'subtotal'           => floatval( $subtotal_price ),
			'total_price'        => floatval( $total_price ),
			'order_id'           => $order['id'],
			'store_name'         => $site_name,
			'order_description'  => 'Tutor Order',
			'tax'                => 0,
			'currency'           => (object) array(
				'code'         => $currency_code,
				'symbol'       => $currency_symbol,
				'name'         => $currency_info['name'] ?? '',
				'locale'       => $currency_info['locale'] ?? '',
				'numeric_code' => $currency_info['numeric_code'] ?? '',
			),
			'country'            => $country,
			'shipping_charge'    => 0,
			'coupon_discount'    => 0,
			'shipping_address'   => (object) $shipping_and_billing,
			'billing_address'    => (object) $shipping_and_billing,
			'decimal_separator'  => tutor_utils()->get_option( OptionKeys::DECIMAL_SEPARATOR, '.' ),
			'thousand_separator' => tutor_utils()->get_option( OptionKeys::THOUSAND_SEPARATOR, '.' ),
			'customer'           => (object) $customer_info,
		);
	}

	/**
	 * Prepare payment data
	 *
	 * @since 3.0.0
	 *
	 * @param int $order_id Order id.
	 *
	 * @throws \Exception Throw exception if order not found.
	 *
	 * @return mixed
	 */
	public static function prepare_recurring_payment_data( int $order_id ) {
		$order_data = ( new OrderModel() )->get_order_by_id( $order_id );
		if ( ! $order_data ) {
			throw new \Exception( __( 'Order not found!', 'tutor' ) );
		}

		$amount = $order_data->total_price;

		$order_user_id = $order_data->student->id;
		$user_data     = get_userdata( $order_user_id );

		$currency_code   = tutor_utils()->get_option( OptionKeys::CURRENCY_CODE, 'USD' );
		$currency_symbol = tutor_get_currency_symbol_by_code( $currency_code );
		$currency_info   = tutor_get_currencies_info_by_code( $currency_code );

		$billing_info = ( new BillingModel() )->get_info( $order_user_id );

		$country_info = tutor_get_country_info_by_name( $billing_info->billing_country );

		$country = (object) array(
			'name'         => $country_info['name'],
			'numeric_code' => $country_info['numeric_code'],
			'alpha_2'      => $country_info['alpha_2'],
			'alpha_3'      => $country_info['alpha_3'],
			'phone_code'   => $country_info['phone_code'],
		);

		$billing_name = $billing_info ? trim( $billing_info->billing_first_name . ' ' . $billing_info->billing_last_name ) : $user_data->display_name;

		$shipping_and_billing = array(
			'name'         => $billing_name,
			'address1'     => $billing_info->billing_address ?? '',
			'address2'     => $billing_info->billing_address ?? '',
			'city'         => $billing_info->billing_city ?? '',
			'state'        => $billing_info->billing_state ?? '',
			'region'       => '',
			'postal_code'  => $billing_info->billing_zip_code ?? '',
			'country'      => $country,
			'phone_number' => $billing_info->billing_phone ?? '',
			'email'        => $billing_info->billing_email ?? '',
		);

		$customer_info = $shipping_and_billing;

		return (object) array(
			'type'             => 'recurring',
			'previous_payload' => $order_data->payment_payloads,
			'total_amount'     => floatval( $amount ),
			'sub_total_amount' => floatval( $amount ),
			'currency'         => (object) array(
				'code'         => $currency_code,
				'symbol'       => $currency_symbol,
				'name'         => $currency_info['name'] ?? '',
				'locale'       => $currency_info['locale'] ?? '',
				'numeric_code' => $currency_info['numeric_code'] ?? '',
			),
			'order_id'         => $order_id,
			'customer'         => (object) $customer_info,
			'shipping_address' => (object) $shipping_and_billing,
		);
	}

	/**
	 * Proceed to payment
	 *
	 * @since 3.0.0
	 *
	 * @param mixed  $payment_data Payment data for making order.
	 * @param string $payment_method Payment method name.
	 * @param string $order_type Order type.
	 *
	 * @throws \Throwable Throw throwable if error occur.
	 * @throws \Exception Throw exception if payment gateway is invalid.
	 *
	 * @return void
	 */
	public function proceed_to_payment( $payment_data, $payment_method, $order_type ) {
		$payment_gateways = apply_filters( 'tutor_gateways_with_class', Ecommerce::payment_gateways_with_ref(), $payment_method );

		$payment_gateway_class = isset( $payment_gateways[ $payment_method ] )
								? $payment_gateways[ $payment_method ]['gateway_class']
								: null;

		if ( $payment_gateway_class ) {
			try {

				add_filter(
					'tutor_ecommerce_webhook_url',
					function ( $url ) use ( $payment_method ) {
						$url = add_query_arg( array( 'payment_method' => $payment_method ), $url );
						return $url;
					}
				);

				add_filter(
					'tutor_ecommerce_payment_success_url_args',
					function ( $args ) use ( $payment_data ) {
						$args['order_id'] = $payment_data->order_id;
						return $args;
					}
				);
				add_filter(
					'tutor_ecommerce_payment_cancelled_url_args',
					function ( $args ) use ( $payment_data ) {
						$args['order_id'] = $payment_data->order_id;
						return $args;
					}
				);

				$gateway_instance = Ecommerce::get_payment_gateway_object( $payment_gateway_class );
				$gateway_instance->setup_payment_and_redirect( $payment_data );
			} catch ( \Throwable $th ) {
				throw $th;
			}
		} else {
			throw new \Exception( 'Invalid payment gateway class' );
		}
	}

	/**
	 * Restrict checkout page
	 *
	 * @return void
	 */
	public function restrict_checkout_page() {
		$page_id = self::get_page_id();
		$plan_id = Input::get( 'plan' );

		if ( is_page( $page_id ) && ! $plan_id ) {
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

	/**
	 * Set alert message on the session based on
	 * order data
	 *
	 * @since 3.0.0
	 *
	 * @param mixed $order_data Order data or null. If order
	 * data is falsy then failed message will be set.
	 *
	 * @return void
	 */
	private function set_pay_now_alert_msg( $order_data ) {
		$user_id = $order_data ? $order_data['user_id'] : get_current_user_id();
		if ( empty( $order_data ) ) {
			set_transient(
				self::PAY_NOW_ALERT_MSG_TRANSIENT_KEY . $user_id,
				array(
					'alert'   => 'danger',
					'message' => __( 'Failed to place order!', 'tutor' ),
				),
			);
		} else {
			set_transient(
				self::PAY_NOW_ALERT_MSG_TRANSIENT_KEY . $user_id,
				array(
					'alert'   => 'success',
					'message' => __( 'Your order has been placed successfully!', 'tutor' ),
				),
			);
		}
	}

	/**
	 * Pay for the incomplete order
	 *
	 * Redirect to the payment gateway to complete the order
	 * After completing the process it will redirect user to
	 * order placement page
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function pay_incomplete_order() {
		$order_id = Input::post( 'order_id', 0, Input::TYPE_INT );
		if ( ! tutor_utils()->is_nonce_verified() ) {
			tutor_utils()->redirect_to( tutor_utils()->tutor_dashboard_url( 'purchase_history' ), tutor_utils()->error_message( 'nonce' ), 'error' );
			exit;
		}
		if ( $order_id ) {
			$order_data = ( new OrderModel() )->get_order_by_id( $order_id );
			if ( $order_data ) {
				try {
					$payment_data = $this->prepare_payment_data( (array) $order_data, $order_data->payment_method, $order_data->order_type );
					$this->proceed_to_payment( $payment_data, $order_data->payment_method, $order_data->order_type );
				} catch ( \Throwable $th ) {
					tutor_log( $th );
					tutor_redirect_after_payment( OrderModel::ORDER_PLACEMENT_FAILED, $order_data->id, $th->getMessage() );
				}
			} else {
				$error_msg = __( 'Order not found!', 'tutor' );
				tutor_redirect_after_payment( OrderModel::ORDER_PLACEMENT_FAILED, $order_id, $error_msg );
			}
		} else {
			$error_msg = __( 'Invalid order ID!', 'tutor' );
			tutor_redirect_after_payment( OrderModel::ORDER_PLACEMENT_FAILED, $order_id, $error_msg );
		}
	}

	/**
	 * Validate pay now request
	 *
	 * @since 3.0.0
	 *
	 * @param array $data The data array to validate.
	 *
	 * @return object The validation result. It returns validation object.
	 */
	protected function validate_pay_now_req( array $data ) {

		$order_types = array(
			OrderModel::TYPE_SINGLE_ORDER,
			OrderModel::TYPE_SUBSCRIPTION,
			OrderModel::TYPE_RENEWAL,
		);
		$order_types = implode( ',', $order_types );

		$validation_rules = array(
			'object_ids'     => 'required',
			'payment_method' => 'required',
			'order_type'     => "required|match_string:{$order_types}",
		);

		// Skip validation rules for not available fields in data.
		foreach ( $validation_rules as $key => $value ) {
			if ( ! array_key_exists( $key, $data ) ) {
				unset( $validation_rules[ $key ] );
			}
		}

		return ValidationHelper::validate( $validation_rules, $data );
	}
}
