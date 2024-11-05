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
use TutorPro\CourseBundle\Models\BundleModel;

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

		if ( is_array( $items ) && count( $items ) ) {
			$courses = array();
			foreach ( $items as $key => $item ) {
				// Handle bundle courses.
				if ( tutor()->bundle_post_type === $item->type ) {

					$courses_list = BundleModel::get_bundle_courses( $item->id );
					$temp_courses = array();
					foreach ( $courses_list as $course ) {
						$course_price = tutor_utils()->get_raw_course_price( $course->ID );
						$course_item  = array(
							'course_id' => $course->ID,
							'price'     => $course_price->regular_price,
						);
						if ( floatval( $item->sale_price ) >= 0 || floatval( $item->discount_price ) >= 0 ) {
							$temp_courses[] = $course_item;
						} else {
							$courses[] = $course_item;
						}
					}

					// Handle bundle course sale.
					if ( floatval( $item->sale_price ) >= 0 || floatval( $item->discount_price ) >= 0 ) {
						$final_course_price = 0;

						if ( floatval( $item->sale_price ) >= 0 ) {
							$final_course_price = $item->sale_price / count( $courses_list );
						}

						if ( floatval( $item->discount_price ) >= 0 ) {
							$final_course_price = $item->discount_price / count( $courses_list );
						}

						$remaining_amount = 0;

						usort(
							$temp_courses,
							function ( $a, $b ) {
								return $a['price'] - $b['price'];
							}
						);

						foreach ( $temp_courses as $course ) {
							if ( $final_course_price > $course['price'] ) {
								$remaining_amount = $final_course_price - $course['price'];
							} else {
								$course['price']  = $final_course_price + $remaining_amount;
								$remaining_amount = 0;
							}

							$courses[] = $course;
						}
					}

					unset( $items[ $key ] );
				}
			}

			if ( count( $courses ) ) {
				foreach ( $courses as $course ) {
					$this->earning_data[] = $this->prepare_earning_data( $course['price'], $course['course_id'], $order_id, $order_details->order_status );
				}
			}

			foreach ( $items as $item ) {
				$total_price = $item->sale_price ? $item->sale_price : $item->regular_price;
				$total_price = floatval( $item->discount_price ) >= 0 ? floatval( $item->discount_price ) : $total_price;
				$course_id   = $item->id;

				if ( OrderModel::TYPE_SINGLE_ORDER !== $order_details->order_type ) {
					$course_id = apply_filters( 'tutor_subscription_course_by_plan', $item->id, $order_details );
				}

				$this->earning_data[] = $this->prepare_earning_data( $total_price, $course_id, $order_id, $order_details->order_status );
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
	 *
	 * @return array
	 */
	public function prepare_earning_data( $total_price, $course_id, $order_id, $order_status ) {
		$fees_deduct_data      = array();
		$tutor_earning_fees    = tutor_utils()->get_option( 'fee_amount_type' );
		$enable_fees_deducting = tutor_utils()->get_option( 'enable_fees_deducting' );

		$course_price_grand_total = $total_price;

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

		// Distribute amount between admin and instructor.
		$sharing_enabled   = tutor_utils()->get_option( 'enable_revenue_sharing' );
		$instructor_rate   = $sharing_enabled ? tutor_utils()->get_option( 'earning_instructor_commission' ) : 0;
		$admin_rate        = $sharing_enabled ? tutor_utils()->get_option( 'earning_admin_commission' ) : 100;
		$commission_type   = 'percent';
		$instructor_amount = $instructor_rate > 0 ? ( ( $course_price_grand_total * $instructor_rate ) / 100 ) : 0;
		$admin_amount      = $admin_rate > 0 ? ( ( $course_price_grand_total * $admin_rate ) / 100 ) : 0;

		// Course author id.
		$user_id = get_post_field( 'post_author', $course_id );

		// (Use Pro Filter - Start)
		// The response must be same array structure.
		// Do not change used variable names here, or change in both of here and pro plugin
		$pro_arg         = array(
			'user_id'                  => $user_id,
			'instructor_rate'          => $instructor_rate,
			'admin_rate'               => $admin_rate,
			'instructor_amount'        => $instructor_amount,
			'admin_amount'             => $admin_amount,
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

		try {
			foreach ( $this->earning_data as $earning_data ) {
				$inserted_id = QueryHelper::insert( $this->earning_table, $earning_data );
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
		global $wpdb;
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$this->earning_table}
				WHERE order_id = %d",
				$order_id
			)
		);

		return $rows;
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

		return QueryHelper::update(
			$this->earning_table,
			$this->earning_data,
			array( 'id' => $earning_id )
		);
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

		$earnings = $this->is_exist_order_earning( $this->order_id );
		if ( count( $earnings ) && is_array( $earnings ) ) {

			foreach ( $earnings as $earning ) {
				$this->delete_earning( $earning->earning_id );
			}
		}

		return $this->store_earnings();
	}
}
