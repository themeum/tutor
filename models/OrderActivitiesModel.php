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
	const META_KEY_HISTORY = 'history';
	const META_KEY_REFUND  = 'refund';
	const META_KEY_COMMENT = 'comment';


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
		$order_activities = QueryHelper::get_all(
			$this->table_name,
			array(
				'order_id' => $order_id,
				'meta_key' => self::META_KEY_HISTORY,
			),
			'id'
		);

		if ( empty( $order_activities ) ) {
			return array();
		}

		$response = array();

		foreach ( $order_activities as &$activity ) {
			$values     = new \stdClass();
			$values     = json_decode( $activity->meta_value );
			$values->id = (int) $activity->id;
			$response[] = $values;
		}

		unset( $activity );

		// Custom comparison function for sorting by date.
		usort(
			$response,
			function ( $a, $b ) {
				$date_a = strtotime( $a->date );
				$date_b = strtotime( $b->date );

				return $date_b - $date_a;
			}
		);

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
		return QueryHelper::insert(
			$this->table_name,
			array(
				'order_id'       => $data->order_id,
				'meta_key'       => $data->meta_key,
				'meta_value'     => $data->meta_value,
				'created_at_gmt' => current_time( 'mysql' ),
				'created_by'     => get_current_user_id(),
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
				'updated_at_gmt' => current_time( 'mysql' ),
				'updated_by'     => get_current_user_id(),
			),
			array(
				'id'       => $data->id,
				'order_id' => $data->order_id,
			)
		);
	}
}
