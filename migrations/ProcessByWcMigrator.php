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

/**
 * Class ProcessByWcMigrator
 *
 * @since 3.8.2
 */
class ProcessByWcMigrator extends BatchProcessor {
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
		global $wpdb;

		$total_items = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}tutor_earnings AS te
			INNER JOIN {$wpdb->prefix}wc_orders_meta AS wcom ON te.order_id = wcom.order_id
			WHERE te.process_by = %s
			AND wcom.meta_key = '_is_tutor_order_for_course'",
				Earnings::PROCESS_BY_TUTOR
			)
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
		global $wpdb;

		$items = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT te.* FROM {$wpdb->prefix}tutor_earnings AS te
				INNER JOIN {$wpdb->prefix}wc_orders_meta AS wcom ON te.order_id = wcom.order_id
				WHERE te.process_by = %s
				AND wcom.meta_key = '_is_tutor_order_for_course'
				ORDER BY earning_id DESC
				LIMIT %d, %d",
				Earnings::PROCESS_BY_TUTOR,
				$offset,
				$limit
			)
		);

		return $items;
	}

	/**
	 * Process a single item
	 *
	 * @since 3.8.2
	 *
	 * @param object $item item.
	 *
	 * @return void
	 */
	protected function process_item( $item ):void {
		$data  = array( 'process_by' => Earnings::PROCESS_BY_WOOCOMMERCE );
		$where = array( 'earning_id' => $item->earning_id );
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
