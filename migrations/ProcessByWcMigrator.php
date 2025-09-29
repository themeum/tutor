<?php
/**
 * Since Tutor 3.0.0 all wc earnings store in tutor_earnings table with 'Tutor' in process_by column.
 * This migration will update all wc earnings process_by column to 'woocommerce'.
 *
 * @package Tutor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.8.2
 */

namespace Tutor\Migrations;

use TUTOR\Earnings;
use Tutor\Helpers\QueryHelper;
use Tutor\Migrations\Contracts\BulkProcessor;

/**
 * Class ProcessByWcMigrator
 *
 * @since 3.8.2
 */
class ProcessByWcMigrator extends BatchProcessor implements BulkProcessor {
	/**
	 * Name of the migration
	 *
	 * @since 3.8.2
	 *
	 * @var string
	 */
	protected $name = 'Process By WC Migration';

	/**
	 * Action
	 *
	 * @since 3.8.2
	 *
	 * @var string
	 */
	protected $action = 'process_by_wc_migrator';

	/**
	 * Batch size
	 *
	 * @since 3.8.2
	 *
	 * @var integer
	 */
	protected $batch_size = 1000;

	/**
	 * Schedule interval.
	 *
	 * @since 3.8.2
	 *
	 * @var integer
	 */
	protected $schedule_interval = 10;

	/**
	 * Get the total count of the data to be processed
	 *
	 * @since 3.8.2
	 *
	 * @return int
	 */
	protected function get_total_items() : int {
		$primary_table  = 'tutor_earnings te';
		$joining_tables = array(
			array(
				'table' => 'wc_orders_meta wcom',
				'on'    => 'te.order_id = wcom.order_id',
				'type'  => 'INNER',
			),
		);

		$where = array(
			'te.process_by' => Earnings::PROCESS_BY_TUTOR,
			'wcom.meta_key' => '_is_tutor_order_for_course',
		);

		$total_items = QueryHelper::get_joined_count(
			$primary_table,
			$joining_tables,
			$where,
			array(),
			'te.earning_id'
		);

		return $total_items;
	}

	/**
	 * Get items to batch process.
	 *
	 * @since 3.8.2
	 *
	 * @param int $offset offset.
	 * @param int $limit limit.
	 *
	 * @return array
	 */
	protected function get_items( $offset, $limit ) : array {
		$primary_table  = 'tutor_earnings te';
		$joining_tables = array(
			array(
				'table' => 'wc_orders_meta wcom',
				'on'    => 'te.order_id = wcom.order_id',
				'type'  => 'INNER',
			),
		);

		$where = array(
			'te.process_by' => Earnings::PROCESS_BY_TUTOR,
			'wcom.meta_key' => '_is_tutor_order_for_course',
		);

		$response = QueryHelper::get_joined_data(
			$primary_table,
			$joining_tables,
			array( 'te.*' ),
			$where,
			array(),
			'te.earning_id',
			$limit,
			$offset,
			'DESC'
		);

		$items = $response['total_count'] > 0 ? $response['results'] : array();

		return $items;
	}

	/**
	 * Process a batch of items
	 *
	 * @since 3.8.2
	 *
	 * @param array $items items.
	 *
	 * @return void
	 */
	public function process_items( $items ) : void {
		$ids   = wp_list_pluck( $items, 'earning_id' );
		$data  = array( 'process_by' => Earnings::PROCESS_BY_WOOCOMMERCE );
		$where = array( 'earning_id' => $ids );
		QueryHelper::update( 'tutor_earnings', $data, $where );
	}

	/**
	 * On migration complete event.
	 *
	 * @since 3.8.2
	 *
	 * @return void
	 */
	protected function on_complete() {
		error_log( 'Process by WC migration completed!' );
	}
}
