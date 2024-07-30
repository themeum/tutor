<?php
/**
 * Manage earnings
 *
 * @package Tutor\Ecommerce
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\Ecommerce;

use Tutor\Helpers\QueryHelper;
use Tutor\Models\OrderModel;
use TUTOR\Singleton;
use Tutor\Traits\EarningData;

/**
 * Manage earnings
 */
class EarningController extends Singleton {

	/**
	 * Trait for preparing earning data
	 *
	 * @since 3.0.0
	 */
	use EarningData;

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
		$courses       = is_object( $order_details ) && property_exists( $order_details, 'courses' ) ? $order_details->courses : array();

		if ( is_array( $courses ) && count( $courses ) ) {
			foreach ( $courses as $course ) {
				$total_price = $course->sale_price ? $course->sale_price : $course->regular_price;

				$this->earning_data = $this->prepare_earning_data( $total_price, $course->id, $order_id, $order_details->order_status );
			}
		}
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
			$inserted_id = QueryHelper::insert( $this->earning_table, $this->earning_data );
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

		$earning = $this->is_exist_order_earning( $this->order_id );
		if ( $earning ) {
			$this->delete_earning( $earning->earning_id );
		}

		return $this->store_earnings();
	}

}
