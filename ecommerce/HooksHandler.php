<?php
/**
 * Handle ecommerce hooks
 *
 * @package Tutor\Ecommerce
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\Ecommerce;

use Tutor\Models\OrderActivitiesModel;

/**
 * Handle custom hooks
 */
class HooksHandler {

	/**
	 * OrderActivitiesModel
	 *
	 * @since 3.0.0
	 *
	 * @var OrderActivitiesModel
	 */
	private $order_activities_model;

	/**
	 * Register hooks & resolve props
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		$this->order_activities_model = new OrderActivitiesModel();

		// Register hooks.
		add_action( 'tutor_after_order_bulk_action', array( $this, 'store_order_activity_after' ), 10, 2 );
	}

	/**
	 * Store order activity before bulk action.
	 *
	 * @since 3.0.0
	 *
	 * @param string $bulk_action The bulk action being performed.
	 * @param array  $bulk_ids The IDs of the orders being acted upon.
	 *
	 * @return void
	 */
	public function store_order_activity_after( $bulk_action, $bulk_ids ) {
		foreach ( $bulk_ids as $order_id ) {
			$data = (object) array(
				'order_id'   => $order_id,
				'meta_key'   => $this->order_activities_model::META_KEY_HISTORY,
				'meta_value' => "Order mark as {$bulk_action}",
			);

			try {
				$this->order_activities_model->add_order_meta( $data );
			} catch ( \Throwable $th ) {
				// Log message with line & file.
				error_log( $th->getMessage() . ' in ' . $th->getFile() . ' at line ' . $th->getLine() );
			}
		}
	}
}

