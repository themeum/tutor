<?php
/**
 * Order Model
 *
 * @package Tutor\Models
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\Models;

use Tutor\Helpers\DateTimeHelper;
use Tutor\Helpers\QueryHelper;

/**
 * OrderActivitiesModel Class
 *
 * @since 3.0.0
 */
class OrderActivitiesModel {
	/**
	 * Order Meta keys for history, refund & comment
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const META_KEY_HISTORY          = 'history';
	const META_KEY_REFUND           = 'refund';
	const META_KEY_PARTIALLY_REFUND = 'partially-refund';
	const META_KEY_COMMENT          = 'comment';
	const META_KEY_CANCEL_REASON    = 'cancel-reason';


	/**
	 * Order meta table name
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	private $table_name = 'tutor_ordermeta';

	/**
	 * Resolve props & dependencies
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . $this->table_name;
	}

	/**
	 * Get table name with wp prefix
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_table_name() {
		return $this->table_name;
	}


	/**
	 * Get all Meta keys
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public static function get_meta_keys() {
		return array(
			self::META_KEY_HISTORY => __( 'History', 'tutor' ),
			self::META_KEY_REFUND  => __( 'Refund', 'tutor' ),
			self::META_KEY_COMMENT => __( 'Comment', 'tutor' ),
		);
	}

	/**
	 * Retrieve order activities by order ID.
	 *
	 * This function fetches all order activities from the 'tutor_ordermeta' table
	 * based on the given order ID and the 'history' meta key. It uses a helper
	 * function from the QueryHelper class to perform the database query.
	 *
	 * If no order activities are found, the function returns an empty array.
	 * Otherwise, it decodes the JSON-encoded meta values and returns them as an array.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param int $order_id The ID of the order to retrieve activities for.
	 *
	 * @since 3.0.0
	 *
	 * @return array An array of order activities, each decoded from its JSON representation.
	 */
	public function get_order_activities( $order_id ) {
		global $wpdb;

		// Retrieve order activities for the given order ID from the 'tutor_ordermeta' table.
		$meta_keys = array(
			self::META_KEY_COMMENT,
			self::META_KEY_HISTORY,
			self::META_KEY_REFUND,
			self::META_KEY_PARTIALLY_REFUND,
		);

		$order_activities = QueryHelper::get_all(
			$this->table_name,
			array(
				'order_id' => $order_id,
				'meta_key' => $meta_keys,
			),
			'id'
		);

		if ( empty( $order_activities ) ) {
			return array();
		}

		$response = array();

		try {
			foreach ( $order_activities as &$activity ) {
				$values = new \stdClass();
				if ( tutor_is_json( $activity->meta_value ) ) {
					$values = json_decode( $activity->meta_value );
				} else {
					$values->message = $activity->meta_value;
				}
				$values->id                  = (int) $activity->id;
				$values->created_at_readable = DateTimeHelper::get_gmt_to_user_timezone_date( $activity->created_at_gmt );
				$values->type                = $activity->meta_key;
				$response[]                  = $values;
			}
		} catch ( \Throwable $th ) {
			// Log error message with file info.
			error_log( $th->getMessage() . ' in ' . $th->getFile() . ' at line ' . $th->getLine() );
		}

		unset( $activity );

		return $response;
	}

	/**
	 * Add order metadata.
	 *
	 * Inserts metadata into the 'tutor_ordermeta' table based on the provided data object.
	 *
	 * @since 3.0.0
	 *
	 * @param object $data An object containing order metadata:
	 *                    - $data->order_id   (int)     The ID of the order.
	 *                    - $data->meta_key   (string)  The meta key for the metadata.
	 *                    - $data->meta_value (string)  The meta value(JSON) for the metadata.
	 *
	 * @return int The ID of the inserted row on success, or 0 on failure.
	 */
	public function add_order_meta( object $data ) {
		$current_time    = current_time( 'mysql', true );
		$current_user_id = get_current_user_id();

		return QueryHelper::insert(
			$this->table_name,
			array(
				'order_id'       => $data->order_id,
				'meta_key'       => $data->meta_key,
				'meta_value'     => $data->meta_value,
				'created_at_gmt' => $current_time,
				'created_by'     => $current_user_id,
				'updated_at_gmt' => $current_time,
				'updated_by'     => $current_user_id,
			)
		);
	}

	/**
	 * Update order metadata.
	 *
	 * Updates metadata in the 'tutor_ordermeta' table based on the provided data object.
	 *
	 * @since 3.0.0
	 *
	 * @param object $data An object containing order metadata:
	 *                    - $data->order_id   (int)     The ID of the order.
	 *                    - $data->meta_key   (string)  The meta key for the metadata.
	 *                    - $data->meta_value (string)  The meta value(JSON) for the metadata.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function update_order_meta( object $data ) {
		return QueryHelper::update(
			$this->table_name,
			array(
				'meta_key'       => $data->meta_key,
				'meta_value'     => $data->meta_value,
				'updated_at_gmt' => current_time( 'mysql', true ),
				'updated_by'     => get_current_user_id(),
			),
			array(
				'id'       => $data->id,
				'order_id' => $data->order_id,
			)
		);
	}
}
