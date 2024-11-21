<?php
/**
 * Manage earnings
 *
 * @package Tutor\Ecommerce
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace TUTOR;

use Tutor\Helpers\QueryHelper;
use Tutor\Models\OrderModel;
use TUTOR\Singleton;
use Tutor\Traits\EarningData;

/**
 * Manage earnings
 */
class Earnings extends Singleton {

	/**
	 * Error message for the invalid earning data
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const INVALID_DATA_MSG = 'Invalid earning data';

	/**
	 * Earning table name
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	private $earning_table;

	/**
	 * Order id
	 *
	 * @since 3.0.0
	 *
	 * @var int
	 */
	private $order_id;

	/**
	 * Keep earning data here
	 *
	 * @since 3.0.0
	 *
	 * @var array
	 */
	public $earning_data = array();

	/**
	 * Set table name prop
	 *
	 * @since 3.0.0
	 */
	protected function __construct() {
		global $wpdb;
		$this->earning_table = $wpdb->prefix . 'tutor_earnings';
	}

	/**
	 * Prepare earnings from this order to store it as
	 * earning & commission data.
	 *
	 * @since 3.0.0
	 *
	 * @param int $order_id Order id.
	 *
	 * @return mixed
	 */
	public function prepare_order_earnings( int $order_id ) {
		$this->order_id = $order_id;

		$order_model   = new OrderModel();
		$order_details = $order_model->get_order_by_id( $order_id );
		$items         = is_object( $order_details ) && property_exists( $order_details, 'items' ) ? $order_details->items : array();

		$deducted_amount = $order_details->refund_amount + $order_details->coupon_amount;
		if ( $order_details->discount_amount ) {
			$discount_amount  = $order_model->calculate_discount_amount( $order_details->discount_type, $order_details->discount_amount, $order_details->subtotal_price );
			$deducted_amount += $discount_amount;
		}

		if ( is_array( $items ) && count( $items ) ) {

			foreach ( $items as $item ) {

				$subtotal_price  = $item->regular_price;
				$item_sold_price = $order_model->get_item_sold_price( $item->id, false );

				try {
					$per_earning_refund = ( $deducted_amount * $subtotal_price ) / $order_details->total_price;
				} catch ( \Throwable $th ) {
					tutor_log( $th );
					$per_earning_refund = 0;
				}

				// Split deduct amount fro admin & instructor.
				$split_deduction = tutor_split_amounts( $per_earning_refund );

				// Split earnings.
				$split_earnings = tutor_split_amounts( $subtotal_price );

				// Deduct earnings.
				$admin_amount      = $split_earnings['admin'] - $split_deduction['admin'];
				$instructor_amount = $split_earnings['instructor'] - $split_deduction['instructor'];

				$course_id = $item->id;

				if ( OrderModel::TYPE_SINGLE_ORDER !== $order_details->order_type ) {
					$course_id = apply_filters( 'tutor_subscription_course_by_plan', $item->id, $order_details );
				}

				$this->earning_data[] = $this->prepare_earning_data( $item_sold_price, $course_id, $order_id, $order_details->order_status, $admin_amount, $instructor_amount );
			}
		}
	}

	/**
	 * Prepare earning data
	 *
	 * @since 3.0.0
	 *
	 * @param mixed  $total_price Total price of an item.
	 * @param int    $course_id Connected course id.
	 * @param int    $order_id Order id.
	 * @param string $order_status Order status.
	 * @param string $admin_amount Admin amount.
	 * @param string $instructor_amount Instructor status.
	 *
	 * @return array
	 */
	public function prepare_earning_data( $total_price, $course_id, $order_id, $order_status, $admin_amount, $instructor_amount ) {
		$fees_deduct_data      = array();
		$tutor_earning_fees    = tutor_utils()->get_option( 'fee_amount_type' );
		$enable_fees_deducting = tutor_utils()->get_option( 'enable_fees_deducting' );

		$course_price_grand_total = $total_price;

		// Site maintenance fees.
		$fees_amount = 0;

		// Deduct predefined amount (percent or fixed).
		if ( $enable_fees_deducting ) {
			$fees_name   = tutor_utils()->get_option( 'fees_name', '' );
			$fees_amount = (int) tutor_utils()->avalue_dot( 'fees_amount', $tutor_earning_fees );
			$fees_type   = tutor_utils()->avalue_dot( 'fees_type', $tutor_earning_fees );

			if ( $fees_amount > 0 ) {
				if ( 'percent' === $fees_type ) {
					$fees_amount = ( $total_price * $fees_amount ) / 100;
				}

				$course_price_grand_total = $total_price - $fees_amount;
			}

			$fees_deduct_data = array(
				'deduct_fees_amount' => $fees_amount,
				'deduct_fees_name'   => $fees_name,
				'deduct_fees_type'   => $fees_type,
			);
		}

		if ( $fees_amount ) {
			list( $admin_fees, $instructor_fees ) = array_values( tutor_split_amounts( $fees_amount ) );

			// Deduct fees.
			$admin_amount      -= $admin_fees;
			$instructor_amount -= $instructor_fees;
		}

		// Distribute amount between admin and instructor.
		$sharing_enabled = tutor_utils()->get_option( 'enable_revenue_sharing' );
		$instructor_rate = $sharing_enabled ? tutor_utils()->get_option( 'earning_instructor_commission' ) : 0;
		$admin_rate      = $sharing_enabled ? tutor_utils()->get_option( 'earning_admin_commission' ) : 100;
		$commission_type = 'percent';

		// Course author id.
		$user_id = get_post_field( 'post_author', $course_id );

		// (Use Pro Filter - Start)
		// The response must be same array structure.
		// Do not change used variable names here, or change in both of here and pro plugin
		$pro_arg = array(
			'user_id'                  => $user_id,
			'instructor_rate'          => $instructor_rate,
			'admin_rate'               => $admin_rate,
			'instructor_amount'        => max( 0, $instructor_amount ),
			'admin_amount'             => max( 0, $admin_amount ),
			'course_price_grand_total' => $course_price_grand_total,
			'commission_type'          => $commission_type,
		);

		$pro_calculation = apply_filters( 'tutor_pro_earning_calculator', $pro_arg );
		extract( $pro_calculation ); //phpcs:ignore
		// (Use Pro Filter - End).

		// Prepare insertable earning data.
		$earning_data = array(
			'user_id'                  => $user_id,
			'course_id'                => $course_id,
			'order_id'                 => $order_id,
			'order_status'             => $order_status,
			'course_price_total'       => $total_price,
			'course_price_grand_total' => $course_price_grand_total,

			'instructor_amount'        => $instructor_amount,
			'instructor_rate'          => $instructor_rate,
			'admin_amount'             => $admin_amount,
			'admin_rate'               => $admin_rate,

			'commission_type'          => $commission_type,
			'process_by'               => 'Tutor',
			'created_at'               => current_time( 'mysql', true ),
		);
		$earning_data = apply_filters( 'tutor_new_earning_data', array_merge( $earning_data, $fees_deduct_data ) );

		return $earning_data;
	}

	/**
	 * Get order earnings
	 *
	 * @since 3.0.0
	 *
	 * @param int $order_id Order id.
	 *
	 * @return mixed Array of objects on success
	 */
	public function get_order_earnings( int $order_id ) {
		return QueryHelper::get_all(
			$this->earning_table,
			array( 'order_id' => $order_id ),
			'earning_id'
		);
	}

	/**
	 * Store earnings
	 *
	 * @since 3.0.0
	 *
	 * @throws \Exception If earning_data is empty.
	 *
	 * @return int On success inserted id will be returned
	 */
	public function store_earnings() {
		if ( empty( $this->earning_data ) ) {
			throw new \Exception( self::INVALID_DATA_MSG );
		}

		$inserted_id = 0;
		try {
			foreach ( $this->earning_data as $earning ) {
				$inserted_id = QueryHelper::insert( $this->earning_table, $earning );
			}
		} catch ( \Throwable $th ) {
			throw new \Exception( $th->getMessage() );
		}

		return $inserted_id;
	}

	/**
	 * Check if earning for a order already exists
	 *
	 * @since 3.0.0
	 *
	 * @param int $order_id Order id.
	 *
	 * @return mixed Earning row if exists, false|null otherwise.
	 */
	public function is_exist_order_earning( $order_id ) {
		$row = QueryHelper::get_row(
			$this->earning_table,
			array(
				'order_id' => $order_id,
			),
			'earning_id'
		);

		return $row;
	}

	/**
	 * Update earning data
	 *
	 * Use prepare_order_earnings before updating
	 *
	 * @since 3.0.0
	 *
	 * @param int $earning_id Earning id.
	 *
	 * @throws \Exception If earning_data is empty.
	 *
	 * @return bool true|false
	 */
	public function update_earning( $earning_id ) {
		if ( empty( $this->earning_data ) ) {
			throw new \Exception( self::INVALID_DATA_MSG );
		}

		$update = QueryHelper::update(
			$this->earning_table,
			$this->earning_data,
			array( 'earning_id' => $earning_id )
		);

		if ( $update ) {
			$this->earning_data = null;
		}

		return $update;
	}

	/**
	 * Delete earning
	 *
	 * @since 3.0.0
	 *
	 * @param int $earning_id Earning id.
	 *
	 * @return bool true|false
	 */
	public function delete_earning( $earning_id ) {
		return QueryHelper::delete(
			$this->earning_table,
			array( 'earning_id' => $earning_id )
		);
	}

	/**
	 * Delete earning by order id
	 *
	 * @since 3.0.0
	 *
	 * @param int $order_id Order id.
	 *
	 * @return bool true|false
	 */
	public function delete_earning_by_order( $order_id ) {
		return QueryHelper::delete(
			$this->earning_table,
			array( 'order_id' => $order_id )
		);
	}

	/**
	 * Before storing earning this method will check if
	 * earning exist for the given order id. If found it will
	 * remove then store.
	 *
	 * @since 3.0.0
	 *
	 * @throws \Exception If earning_data is empty.
	 *
	 * @return int On success inserted id will be returned
	 */
	public function remove_before_store_earnings() {
		if ( empty( $this->earning_data ) ) {
			throw new \Exception( self::INVALID_DATA_MSG );
		}

		if ( $this->is_exist_order_earning( $this->order_id ) ) {
			$this->delete_earning_by_order( $this->order_id );
		}

		try {
			return $this->store_earnings();
		} catch ( \Throwable $th ) {
			tutor_log( $th );
		}
	}

}
