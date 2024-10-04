<?php
/**
 * Manage all the cart items for the checkout
 *
 * @package Tutor\Ecommerce
 * @author Themeum
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\Ecommerce\Manager;

use Exception;
use Tutor\Ecommerce\Concerns\Taxable;
use Tutor\Ecommerce\Supports\Arr;
use Tutor\Ecommerce\Supports\Shop;
use Tutor\Models\CouponModel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Checkout manager class
 *
 * @since 3.0.0
 */
class Checkout {

	use Taxable;

	/**
	 * the product items added to the cart.
	 *
	 * @var array
	 * @since 3.0.0
	 */
	private $items = array();

	/**
	 * the country numeric code
	 *
	 * @var string|null
	 * @since 3.0.0
	 */
	private $country = null;

	/**
	 * the country state id
	 *
	 * @var string|null
	 * @since 3.0.0
	 */
	private $state = null;

	/**
	 * the coupon code ID applied to the cart/checkout
	 *
	 * @var string|null
	 * @since 3.0.0
	 */
	private $coupon_code = null;

	/**
	 * the primary key of the #__easystore__users table.
	 * note that: this is not the joomla user id.
	 *
	 * @var int|null
	 * @since 3.0.0
	 */
	private $customer_id = null;

	/**
	 * check if the cart initiated successfully and ready for calculation.
	 *
	 * @var boolean
	 * @since 3.0.0
	 */
	private $is_ready = false;


	/**
	 * the cart manager constructor method.
	 * this is a local constructor and could only be instantiated via create_with() method
	 *
	 * @param array       $items      the product items.
	 * @param string|null $coupon_id the coupon code if applied.
	 * @param int|null    $customer_id the customer id.
	 * @param string|null $country    the shipping country numeric code
	 * @param string|null $state      the shipping state id.
	 * @param string|null $shipping_id the shipping method's uuid.
	 *
	 * @since 3.0.0
	 */
	private function __construct( array $items, $coupon_code = null, $customer_id = null, $country = null, $state = null ) {
		$this->items       = $items;
		$this->coupon_code = $coupon_code;
		$this->country     = $country;
		$this->state       = $state;
		$this->customer_id = $customer_id;

		$this->prepare_cart_items();
	}

	/**
	 * create the instance of the cart manager with the required data.
	 *
	 * @param array       $items      the product items.
	 * @param string|null $coupon_id the coupon code if applied.
	 * @param int|null    $customer_id the customer id.
	 * @param string|null $country    the shipping country numeric code
	 * @param string|null $state      the shipping state id.
	 * @param string|null $shipping_id the shipping method's uuid.
	 *
	 * @return self
	 * @since 3.0.0
	 */
	public static function create_with( array $items, $coupon_id = null, $customer_id = null, $country = null, $state = null ) {
		return new self( $items, $coupon_id, $customer_id, $country, $state );
	}

	public function get_calculated_data() {
		$data = (object) array(
			'subtotal'            => $this->calculate_subtotal(),
			'discounted_subtotal' => $this->calculate_discounted_subtotal(),
			'taxable_amount'      => $this->calculate_tax(),
			'total'               => $this->calculate_total(),
			'coupon_discount'     => $this->calculate_coupon_discount(),
		);

		return Shop::format_with_currency( $data );
	}

	private function apply_automatic_coupon_discounts( array $items ) {
		$coupon_model      = new CouponModel();
		$automatic_coupons = $coupon_model->get_automatic_coupons();

		if ( ! empty( $automatic_coupons ) ) {
			foreach ( $automatic_coupons as $coupon ) {
				$coupon_manager = Coupon::make_with( $coupon->coupon_code, $this->customer_id );
				$items          = $this->swap_item_and_discounted_price(
					$coupon_manager->update_cart_items_price_after_applying_coupon(
						$coupon_manager->check_if_coupon_is_applicable( $items )
					)
				);
			}
		}
		return $items;
	}

	/**
	 * Swap the item price and the discounted price into the `final_price` object.
	 * This is required for applying multiple coupons.
	 *
	 * @param array $items
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	private function swap_item_and_discounted_price( array $items ) {
		return Arr::make( $items )->map(
			function ( $item ) {
				if ( $item->is_coupon_applicable ) {
						$item->final_price->item_price = $item->final_price->discounted_price;
				}
				return $item;
			}
		);
	}

	/**
	 * prepare the cart items and make ready for calculation.
	 *
	 * @return self
	 * @since 3.0.0
	 */
	public function prepare_cart_items() {
		$this->items = $this->make_ready_cart_items( $this->items );

		if ( Shop::is_coupon_enabled() ) {
			$this->items    = $this->apply_automatic_coupon_discounts( $this->items );
			$coupon_manager = Coupon::make_with( $this->coupon_code, $this->customer_id );
			$this->items    = $coupon_manager->update_cart_items_price_after_applying_coupon(
				$coupon_manager->check_if_coupon_is_applicable( $this->items )
			);
		}

		$this->items = $this->format_final_prices_after_coupon_discount( $this->items );
		$this->apply_tax( $this->items, $this->country, $this->state );
		$this->items = $this->post_processing_items( $this->items );

		// Set the is_ready flag true, that is the cart manager is ready for calculations.
		$this->is_ready = true;

		return $this;
	}

	/**
	 * get the cart product items.
	 *
	 * @return array
	 * @since 3.0.0
	 */
	public function get_items() {
		return $this->items;
	}

	/**
	 * calculate the subtotal of the product items without applying any coupon discount.
	 *
	 * @since 3.0.0
	 *
	 * @return float
	 *
	 * @throws Exception if the system is not ready yet.
	 */
	public function calculate_subtotal() {
		$this->check_ready_status();

		return array_reduce(
			$this->items,
			function ( $result, $item ) {
				return $result + $item->final_price->item_price;
			},
			0
		);
	}

	/**
	 * calculate the discounted subtotal after applying any coupon.
	 *
	 * @since 3.0.0
	 *
	 * @return float
	 *
	 * @throws Exception if the system is not ready yet.
	 */
	public function calculate_discounted_subtotal() {
		$this->check_ready_status();

		return array_reduce(
			$this->items,
			function ( $result, $item ) {
				return $result + $item->final_price->discounted_price;
			},
			0
		);
	}

	/**
	 * calculate the total coupon discount amount.
	 * this is the total discount value.
	 *
	 * @return float
	 * @since 3.0.0
	 */
	public function calculate_coupon_discount() {
		if ( empty( $this->items ) ) {
			return 0;
		}

		return array_reduce(
			$this->items,
			function ( $result, $item ) {
				return $result + ( $item->final_price->discount_value ?: 0 );
			},
			0
		);
	}

	/**
	 * calculate the total tax applied to the cart.
	 * the total tax is including the product tax and the shipping tax.
	 *
	 * @return float
	 *
	 * @throws exception if it is not ready for calculation.
	 * @since 3.0.0
	 */
	public function calculate_tax() {
		$this->check_ready_status();

		return Shop::is_tax_enabled() ? $this->calculate_total_product_tax() : 0;
	}

	/**
	 * calculate the payable total amount including the shipping cost, tax and deducting the coupon discount.
	 *
	 * @return float
	 * @since 3.0.0
	 */
	public function calculate_total() {
		$subtotal = $this->is_coupon_applied()
			? $this->calculate_discounted_subtotal()
			: $this->calculate_subtotal();
		$tax      = $this->calculate_tax();

		return $subtotal + $tax;
	}

	/**
	 * check if the coupon discount is applied or not.
	 *
	 * @since 3.0.0
	 *
	 * @return boolean
	 */
	public function is_coupon_applied() {
		if ( ! Shop::is_coupon_enabled() ) {
			return false;
		}

		$coupon = Coupon::make_with( $this->coupon_code, $this->customer_id );

		return $coupon->is_valid_coupon();
	}

	/**
	 * Get the applied coupon code
	 *
	 * @since 3.0.0
	 *
	 * @return string|null
	 */
	public function get_coupon_code() {
		return $this->coupon_code;
	}

	/**
	 * calculate the total product taxable amount.
	 *
	 * @return float
	 */
	private function calculate_total_product_tax() {
		return array_reduce(
			$this->items,
			function ( $result, $item ) {
				return $result + $item->taxable_amount;
			},
			0
		);
	}

	/**
	 * check for the ready status.
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since 3.0.0
	 */
	private function check_ready_status() {
		if ( ! $this->is_ready ) {
			throw new Exception( sprintf( 'the cart is not yet ready for calculation. please run prepare_cart_items function first.' ) );
		}
	}

	/**
	 * make the cart items ready for calculations.
	 *
	 * @param array $items
	 *
	 * @return array
	 * @since 3.0.0
	 */
	private function make_ready_cart_items( $items ) {
		if ( empty( $items ) ) {
			return array();
		}

		return array_map(
			function ( $item ) {
				$item->price                    = $this->get_item_price( $item );
				$item->discount                 = $this->get_discount_object( $item );
				$item->quantity                 = intval( $item->quantity ?? 1 );
				$item->item_price               = $item->price;
				$item->item_price_with_currency = tutor_get_formatted_price( $item->item_price );
				$item->is_coupon_applicable     = false;
				$item->is_item_taxable          = $item->is_taxable ?? true;
				$item->final_price              = Shop::default_price( $item );
				$item->applied_coupon           = (object) array(
					'code'            => null,
					'discount_type'   => null,
					'discount_amount' => null,
				);

				return $item;
			},
			$items
		);
	}

	/**
	 * traverse all the cart item's final price object and add a suffix `_with_currency` with all the price property.
	 *
	 * @param array $items
	 *
	 * @return array
	 * @since 3.0.0
	 */
	private function format_final_prices_after_coupon_discount( $items ) {
		if ( empty( $items ) ) {
			return array();
		}

		return array_map(
			function ( $item ) {
				$item->final_price = Shop::format_with_currency( $item->final_price );
				return $item;
			},
			$items
		);
	}

	/**
	 * apply tax to the cart items.
	 *
	 * @return self
	 * @since 3.0.0
	 */
	private function apply_tax() {
		$this->items = array_map(
			function ( $item ) {
				$item->tax_rate                     = $this->get_tax_rate( $this->country, $this->state, $item );
				$item_price                         = $this->is_coupon_applied()
					? $item->final_price->discounted_price
					: $item->final_price->item_price;
				$item->taxable_amount               = Shop::calculate_taxable_amount( $item_price, $item->tax_rate );
				$item->taxable_amount_with_currency = tutor_get_formatted_price( $item->taxable_amount );
				return $item;
			},
			$this->items
		);

		return $this;
	}

	/**
	 * get the cart item price.
	 * if the cart item has variant then the item price would be the variant's price
	 * otherwise the regular price.
	 *
	 * @param object $item
	 *
	 * @return float
	 * @since 3.0.0
	 */
	private function get_item_price( $item ) {
		$price_type = tutor_utils()->price_type( $item->ID );
		$price      = tutor_utils()->get_raw_course_price( $item->ID );

		if ( empty( $price ) ) {
			return 0;
		}

		if ( 'free' === $price_type ) {
			return 0;
		}

		$course_price = floatval( $price->regular_price ?? 0 );
		$sale_price   = floatval( $price->sale_price ?? 0 );

		return $sale_price > 0 ? $sale_price : $course_price;
	}

	/**
	 * apply the product sale discount at the beginning that is applied while creating a product.
	 * other discount like, coupon discount, admin discount will be applied later upon this discounted price.
	 *
	 * @param object $item the cart product item.
	 *
	 * @return object
	 * @since 3.0.0
	 */
	private function get_sale_price( $item ) {
		if ( empty( $item->course_pricing ) ) {
			return 0;
		}

		$pricing = $item->course_pricing;

		return 'free' === $pricing->type ? 0 : (float) $pricing->sale_price;
	}

	/**
	 * get the discount object.
	 *
	 * @param object $item the cart product item.
	 *
	 * @return object
	 * @since 3.0.0
	 */
	private function get_discount_object( $item ) {
		return (object) array(
			'type'   => $item->discount_type ?? 'percent',
			'amount' => $item->discount_value ?? 0,
		);
	}

	/**
	 * post processing the cart items after all the calculation done.
	 *
	 * @param array $items
	 *
	 * @return array
	 * @since 3.0.0
	 */
	private function post_processing_items( $items ) {
		if ( empty( $items ) ) {
			return array();
		}

		return array_map(
			function ( $item ) {
				$tax_rate          = $item->tax_rate ?? 0;
				$item->final_price = shop::add_taxable_prices( $item->final_price, $tax_rate );

				return $item;
			},
			$items
		);
	}
}
