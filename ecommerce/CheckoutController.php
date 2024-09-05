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

use Tutor\Helpers\SessionHelper;
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
	 * Create pending order, prepare payment data & proceed
	 * to payment gateway
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function pay_now() {
		tutor_utils()->check_nonce();

		$errors     = array();
		$order_data = null;

		$coupon_model  = new CouponModel();
		$billing_model = new BillingModel();

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
		$coupon_code    = $request['coupon_code'];
		$payment_method = $request['payment_method'];
		$payment_type   = $request['payment_type'];
		$order_type     = $request['order_type'];

		if ( empty( $object_ids ) ) {
			array_push( $errors, __( 'Invalid cart items', 'tutor' ) );
		}

		$price_details = $coupon_code ? $coupon_model->apply_coupon_discount( $object_ids, $coupon_code ) : $coupon_model->apply_automatic_coupon_discount( $object_ids );

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

		$items = array();

		foreach ( $price_details->items as $item ) {
			$items[] = array(
				'user_id'       => $current_user_id,
				'item_id'       => $item->item_id,
				'regular_price' => $item->regular_price,
				'sale_price'    => $item->discount_price,
			);
		}

		$args = array(
			'payment_method' => $payment_method,
		);

		if ( empty( $errors ) ) {
			$order_data = ( new OrderController( false ) )->create_order( $current_user_id, $items, OrderModel::PAYMENT_UNPAID, $order_type, $coupon_code, $args, false );
			if ( ! empty( $order_data ) ) {
				if ( 'automate' === $payment_type ) {
					try {
						$payment_data = self::prepare_payment_data( $order_data );
						$this->proceed_to_payment( $payment_data, $payment_method );
					} catch ( \Throwable $th ) {
						wp_safe_redirect( home_url( '?tutor_order_placement=failed&order_id=' . $order_data['id'] ) );
						exit();
					}
				} else {
					// Set alert message session.
					$this->set_pay_now_alert_msg( $order_data );

					wp_safe_redirect(
						add_query_arg(
							array(
								'tutor_order_placement' => 'success',
								'order_id'              => $order_data['id'],
							),
							home_url()
						)
					);
					exit();
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

		$currency_code   = tutor_utils()->get_option( OptionKeys::CURRENCY_SYMBOL, 'USD' );
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
			$item = (object) $item;

			$items[] = array(
				'item_id'                           => $item->item_id,
				'item_name'                         => get_the_title( $item->item_id ),
				'regular_price'                     => floatval( $item->regular_price ),
				'regular_price_in_smallest_unit'    => intval( floatval( $item->regular_price ) * 100 ),
				'quantity'                          => 1,
				'discounted_price'                  => floatval( $item->sale_price ),
				'discounted_price_in_smallest_unit' => intval( floatval( $item->sale_price ) * 100 ),
			);
		}

		return (object) array(
			'items'                                   => (object) $items,
			'subtotal'                                => floatval( $subtotal_price ),
			'subtotal_in_smallest_unit'               => intval( floatval( $subtotal_price ) * 100 ),
			'total_price'                             => floatval( $total_price ),
			'total_price_in_smallest_unit'            => intval( floatval( $total_price ) * 100 ),
			'order_id'                                => $order['id'],
			'store_name'                              => $site_name,
			'order_description'                       => 'Tutor Order',
			'tax'                                     => 0,
			'tax_in_smallest_unit'                    => floatval( 0 * 100 ),
			'currency'                                => (object) array(
				'code'         => $currency_code,
				'symbol'       => $currency_symbol,
				'name'         => $currency_info['name'] ?? '',
				'locale'       => $currency_info['locale'] ?? '',
				'numeric_code' => $currency_info['numeric_code'] ?? '',
			),
			'country'                                 => $country,
			'shipping_charge'                         => 0,
			'shipping_charge_in_smallest_unit'        => 0,
			'coupon_discount'                         => 0,
			'coupon_discount_amount_in_smallest_unit' => 0,
			'shipping_address'                        => (object) $shipping_and_billing,
			'billing_address'                         => (object) $shipping_and_billing,
			'decimal_separator'                       => tutor_utils()->get_option( OptionKeys::DECIMAL_SEPARATOR, '.' ),
			'thousand_separator'                      => tutor_utils()->get_option( OptionKeys::THOUSAND_SEPARATOR, '.' ),
			'customer'                                => (object) $customer_info,
		);
	}

	/**
	 * Prepare payment data
	 *
	 * @since 3.0.0
	 *
	 * @param int   $order_id Order id.
	 * @param float $amount Order amount.
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

		$currency_code   = tutor_utils()->get_option( OptionKeys::CURRENCY_SYMBOL, 'USD' );
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
			'type'                              => 'recurring',
			'previous_payload'                  => $order_data->payment_payloads,
			'total_amount'                      => floatval( $amount ),
			'total_amount_in_smallest_unit'     => floatval( $amount ) * 100,
			'sub_total_amount'                  => floatval( $amount ),
			'sub_total_amount_in_smallest_unit' => floatval( $amount ) * 100,
			'currency'                          => (object) array(
				'code'         => $currency_code,
				'symbol'       => $currency_symbol,
				'name'         => $currency_info['name'] ?? '',
				'locale'       => $currency_info['locale'] ?? '',
				'numeric_code' => $currency_info['numeric_code'] ?? '',
			),
			'order_id'                          => $order_id,
			'customer'                          => (object) $customer_info,
			'shipping_address'                  => (object) $shipping_and_billing,
		);
	}

	/**
	 * Proceed to payment
	 *
	 * @since 3.0.0
	 *
	 * @param mixed  $payment_data Payment data for making order.
	 * @param string $payment_method Payment method name.
	 *
	 * @throws \Throwable Throw throwable if error occur.
	 * @throws \Exception Throw exception if payment gateway is invalid.
	 *
	 * @return void
	 */
	public function proceed_to_payment( $payment_data, $payment_method ) {
		$payment_gateways = apply_filters( 'tutor_gateways_with_class', Ecommerce::payment_gateways_with_ref(), $payment_method );

		$payment_gateway_class = isset( $payment_gateways[ $payment_method ] )
								? $payment_gateways[ $payment_method ]['gateway_class']
								: null;

		if ( $payment_gateway_class ) {
			try {
				add_filter(
					'tutor_ecommerce_webhook_url',
					function( $url ) use ( $payment_method ) {
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
