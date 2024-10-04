<?php
/**
 * Manage the coupon related functionalities.
 *
 * @package Tutor\Ecommerce
 * @author Themeum
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\Ecommerce\Manager;

use Carbon\Carbon;
use Tutor\Ecommerce\Supports\Arr;
use Tutor\Ecommerce\Supports\Shop;
use Tutor\Models\CouponModel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * The coupon manager class
 *
 * @since 3.0.0
 */
class Coupon {

	/**
	 * The coupon code
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	private $code;

	/**
	 * The coupon model
	 *
	 * @since 3.0.0
	 *
	 * @var CouponModel
	 */
	private $model;

	/**
	 * The customer user ID.
	 *
	 * @since 3.0.0
	 *
	 * @var integer|null
	 */
	private $user_id = null;

	/**
	 * The coupon object
	 *
	 * @since 3.0.0
	 *
	 * @var object|null
	 */
	private $coupon = null;

	private function __construct( $code, $user_id ) {
		$this->code    = $code;
		$this->model   = new CouponModel();
		$this->user_id = $user_id;
	}

	/**
	 * Make the coupon class instance.
	 *
	 * @param integer $code
	 * @param integer $user_id
	 *
	 * @since 3.0.0
	 *
	 * @return self
	 */
	public static function make_with( $code, $user_id ) {
		return new static( $code, $user_id );
	}

	/**
	 * Get the coupon object by coupon ID.
	 *
	 * @since 3.0.0
	 *
	 * @return object
	 */
	private function get_coupon() {
		$this->coupon = $this->model->get_coupon_by_code( $this->code );
		return $this->coupon;
	}

	/**
	 * Traverse over the cart items and check if an item is applicable for the coupon or not.
	 *
	 * @param array $items
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public function check_if_coupon_is_applicable( array $items ) {
		if ( ! $this->is_valid_coupon() ) {
			return $items;
		}

		$coupon_data = $this->get_coupon();

		$applied_coupon = (object) array(
			'code'            => $coupon_data->coupon_code,
			'discount_type'   => $coupon_data->discount_type,
			'discount_amount' => $coupon_data->discount_amount,
		);

		// If the overall coupon usage limit exceeds or user coupon limit exceeds them return early.
		if ( $this->is_overall_coupon_usage_limit_exceeded( $coupon_data ) || $this->is_user_coupon_usage_limit_exceeded( $coupon_data ) ) {
			return $items;
		}

		return $this->apply_coupon_to_cart_items( $items, $coupon_data, $applied_coupon );
	}

	public function is_valid_coupon() {
		$coupon_data = $this->get_coupon();

		return ! empty( $this->code ) && ! empty( $coupon_data ) && ! $this->is_coupon_expired();
	}

	public function update_cart_items_price_after_applying_coupon( $items, $location = null ) {
		if ( empty( $this->code ) ) {
			return $items;
		}

		$coupon_data = $this->get_coupon();

		if ( empty( $coupon_data ) ) {
			return $items;
		}

		return $this->apply_coupon_discount( $items, $coupon_data );
	}

	/**
	 * Apply the non-automatic coupon code discount
	 *
	 * @param array  $items
	 * @param object $coupon_data
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	private function apply_coupon_discount( array $items, $coupon_data ) {
		$total_quantity = Arr::make( $items )->reduce(
			function ( $result, $item ) {
				return $result + $item->quantity;
			},
			0
		);

		$total_amount = Arr::make( $items )->reduce(
			function ( $result, $item ) {
				return $result + ( $item->final_price->item_price * $item->quantity );
			},
			0
		);

		if ( CouponModel::REQUIREMENT_MINIMUM_PURCHASE === $coupon_data->purchase_requirement ) {
			// If the total amount in the cart less than the minimum purchase requirement then
			// no need to apply the coupon, rollback the is_coupon_applicable status and return early.
			if ( (float) $coupon_data->purchase_requirements_value > $total_amount ) {
				return $this->rollback_is_coupon_applicable_status( $items );
			}
		}

		if ( CouponModel::REQUIREMENT_MINIMUM_QUANTITY === $coupon_data->purchase_requirement ) {
			// If the total amount in the cart less than the minimum purchase quantity then
			// no need to apply the coupon, rollback the is_coupon_applicable status and return early.
			if ( (int) $coupon_data->purchase_requirements_value > $total_quantity ) {
				return $this->rollback_is_coupon_applicable_status( $items );
			}
		}

		return CouponModel::DISCOUNT_TYPE_PERCENTAGE === $coupon_data->discount_type
			? $this->calculate_percentage_discount( $items )
			: $this->calculate_flat_discount( $items );
	}

	/**
	 * Make the final price object from the item price and the discount amount.
	 *
	 * @param object $item
	 * @param float  $discount_amount
	 *
	 * @since 3.0.0
	 *
	 * @return object
	 */
	private function make_final_price( $item, $discount_amount ) {
		return (object) array(
			'item_price'       => $item->final_price->item_price,
			'discount_value'   => $discount_amount,
			'discounted_price' => $item->final_price->item_price - $discount_amount,
		);
	}

	/**
	 * Process the coupon and calculate the discount if the discount type is percentage
	 *
	 * @param array $items
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	private function calculate_percentage_discount( array $items ) {
		return Arr::make( $items )->map(
			function ( $item ) {
				if ( ! $item->is_coupon_applicable ) {
					return $item;
				}

				$discount_amount   = Shop::calculate_percentage( $item->final_price->item_price, $item->applied_coupon->discount_amount );
				$item->final_price = $this->make_final_price( $item, $discount_amount );

				return $item;
			}
		);
	}

	/**
	 * Process the coupon and calculate the discount if the discount type is flat
	 *
	 * @param array $items
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	private function calculate_flat_discount( array $items ) {
		$total_price = Arr::make( $items )->reduce(
			function ( $result, $item ) {
				return $item->is_coupon_applicable ? $result + ( $item->final_price->item_price * $item->quantity ) : $result;
			},
			0
		);

		if ( 0 === $total_price ) {
			return $items;
		}

		return Arr::make( $items )->map(
			function ( $item ) use ( $total_price ) {
				if ( ! $item->is_coupon_applicable ) {
					return $item;
				}

				$discount_amount   = ( $item->final_price->item_price / $total_price ) * $item->applied_coupon->discount_amount;
				$item->final_price = $this->make_final_price( $item, $discount_amount );

				return $item;
			}
		);
	}

	/**
	 * Rollback the `is_coupon_applicable` status to false.
	 *
	 * @param array $items
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	private function rollback_is_coupon_applicable_status( array $items ) {
		return Arr::make( $items )->map(
			function ( $item ) {
				$item->is_coupon_applicable = false;

				return $item;
			}
		);
	}

	/**
	 * Check if the overall coupon usage limit exceeded
	 *
	 * @param object $coupon_data
	 *
	 * @since 3.0.0
	 *
	 * @return boolean
	 */
	private function is_overall_coupon_usage_limit_exceeded( $coupon_data ) {
		if ( empty( $coupon_data->total_usage_limit ) ) {
			return false;
		}

		$total_used        = $this->model->get_coupon_usage_count( $coupon_data->coupon_code );
		$total_usage_limit = intval( $coupon_data->total_usage_limit ?? 0 );

		return $total_used >= $total_usage_limit;
	}

	/**
	 * Check if the a specific user's coupon usage limit exceeded or not.
	 *
	 * @param object $coupon_data
	 *
	 * @since 3.0.0
	 *
	 * @return boolean
	 */
	private function is_user_coupon_usage_limit_exceeded( $coupon_data ) {
		if ( empty( $coupon_data->per_user_usage_limit ) ) {
			return false;
		}

		// If the user id is not provided then mark the coupon limit is exceeded.
		if ( empty( $this->user_id ) ) {
			return true;
		}

		$user_used            = $this->model->get_user_usage_count( $coupon_data->coupon_code, $this->user_id );
		$per_user_usage_limit = intval( $coupon_data->per_user_usage_limit ?? 0 );

		return $user_used >= $per_user_usage_limit;
	}

	private function is_coupon_expired() {
		$coupon_data     = $this->get_coupon();
		$start_date      = $coupon_data->start_date_gmt ?? null;
		$expiration_date = $coupon_data->expire_date_gmt ?? null;

		// If no expiration date set that means the coupon will never be expired.
		if ( is_null( $expiration_date ) ) {
			return false;
		}

		// If start date not set that means there is some problem in that coupon. So mark the coupon as expired.
		if ( is_null( $start_date ) ) {
			return true;
		}

		$start_date      = Carbon::parse( $start_date );
		$expiration_date = Carbon::parse( $expiration_date );
		$today           = Carbon::now();

		return ! $today->between( $start_date, $expiration_date );
	}

	/**
	 * Apply coupon to the cart items
	 *
	 * @param array  $items
	 * @param object $coupon_data
	 * @param object $applied_coupon
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	private function apply_coupon_to_cart_items( array $items, $coupon_data, $applied_coupon ) {
		switch ( $coupon_data->applies_to ) {
			case CouponModel::APPLIES_TO_ALL_COURSES:
				return $this->apply_coupon_to_all_courses( $items, $applied_coupon );
			case CouponModel::APPLIES_TO_SPECIFIC_COURSES:
				$course_ids = Arr::make( $coupon_data->courses )->map(
					function ( $course ) {
						return $course->id;
					}
				);
				return $this->apply_coupon_to_specific_courses( $items, $course_ids, $applied_coupon );
			case CouponModel::APPLIES_TO_SPECIFIC_CATEGORY:
				$category_ids = Arr::make( $coupon_data->categories )->map(
					function ( $category ) {
						return $category->id;
					}
				);
				return $this->apply_coupon_to_specific_categories( $items, $category_ids, $applied_coupon );
		}
	}

	/**
	 * Apply coupon to the all courses. If the coupon applies to value is all courses then this method will handle this.
	 *
	 * @param array  $items
	 * @param object $applied_coupon
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	private function apply_coupon_to_all_courses( $items, $applied_coupon ) {
		return Arr::make( $items )->map(
			function ( $item ) use ( $applied_coupon ) {
				$item->is_coupon_applicable = true;
				$item->applied_coupon       = $applied_coupon;
				return $item;
			}
		);
	}

	/**
	 * Apply this coupon to the specific courses instead of all courses
	 *
	 * @param array  $items
	 * @param object $applicable_product_list
	 * @param object $applied_coupon
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	private function apply_coupon_to_specific_courses( $items, $course_ids, $applied_coupon ) {
		return Arr::make( $items )->map(
			function ( $item ) use ( $course_ids, $applied_coupon ) {
				$item->is_coupon_applicable = in_array( $item->id, $course_ids, true );
				$item->applied_coupon       = $applied_coupon;

				return $item;
			}
		);
	}

	/**
	 * Apply this coupon to the specific categories.
	 *
	 * @param array  $items
	 * @param array  $applicable_category_list
	 * @param object $applied_coupon
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	private function apply_coupon_to_specific_categories( $items, $category_ids, $applied_coupon ) {
		return Arr::make( $items )->map(
			function ( $item ) use ( $category_ids, $applied_coupon ) {
				$item->is_coupon_applicable = in_array( $item->catid, $category_ids, true );
				$item->applied_coupon       = $applied_coupon;
				return $item;
			}
		);
	}
}
