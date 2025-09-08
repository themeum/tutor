<?php
/**
 * Order Item Meta Model
 * Handles CRUD operations for order item meta data.
 *
 * @package Tutor\Models
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.8.0
 */

namespace Tutor\Models;

use Tutor\Helpers\QueryHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class OrderItemModel
 */
class OrderItemMetaModel {

	/**
	 * Table name.
	 *
	 * @var string
	 */
	private $table;

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'tutor_order_itemmeta';
	}

	/**
	 * Add meta for an order item.
	 *
	 * @since 3.8.0
	 *
	 * @param int    $item_id Item ID.
	 * @param string $meta_key Meta key.
	 * @param mixed  $meta_value Meta value.
	 *
	 * @return int Inserted row ID on success, 0 on failure.
	 */
	public function add_meta( $item_id, $meta_key, $meta_value ):int {
		$meta_id = QueryHelper::insert(
			$this->table,
			array(
				'item_id'    => $item_id,
				'meta_key'   => $meta_key,
				'meta_value' => maybe_serialize( $meta_value ),
			),
		);

		return (int) $meta_id;
	}

	/**
	 * Get meta by item_id and optional meta_key.
	 *
	 * @since 3.8.0
	 *
	 * @param int         $item_id Item ID.
	 * @param string|null $meta_key Meta key (optional).
	 * @param bool        $single Get a single value or all.
	 *
	 * @return array|false Meta results or false if none.
	 */
	public function get_meta( int $item_id, $meta_key = null, $single = true ) {
		$where = array(
			'item_id' => $item_id,
		);

		if ( $meta_key ) {
			$where['meta_key'] = sanitize_key( $meta_key );
		}

		if ( $single ) {
			$meta = QueryHelper::get_row(
				$this->table,
				$where,
				'item_id'
			);
			if ( $meta ) {
				$meta->meta_value = maybe_unserialize( $meta->meta_value );
			}
		} else {
			$meta = QueryHelper::get_all(
				$this->table,
				$where,
				'item_id'
			);

			if ( tutor_utils()->count( $meta ) ) {
				foreach ( $meta as $row ) {
					$row->meta_value = maybe_unserialize( $row->meta_value );
				}
			}
		}

		return $meta;
	}

	/**
	 * Update meta for an order item.
	 *
	 * @since 3.8.0
	 *
	 * @param int    $item_id Item ID.
	 * @param string $meta_key Meta key.
	 * @param mixed  $meta_value Meta value.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function update_meta( int $item_id, string $meta_key, $meta_value ): bool {
		$is_meta_exists = $this->get_meta( $item_id, $meta_key );
		if ( ! $is_meta_exists ) {
			return $this->add_meta( $item_id, $meta_key, $meta_value );
		}

		return QueryHelper::update(
			$this->table,
			array(
				'meta_value' => maybe_serialize( $meta_value ),
			),
			array(
				'item_id'  => $item_id,
				'meta_key' => $meta_key,
			),
		);
	}

	/**
	 * Delete meta by item_id and optional meta_key.
	 *
	 * @since 3.8.0
	 *
	 * @param int         $item_id Item ID.
	 * @param string|null $meta_key Meta key (optional).
	 *
	 * @return bool True on success, false on failure.
	 */
	public function delete_meta( $item_id, $meta_key = null ): bool {
		$where = array(
			'item_id' => $item_id,
		);

		if ( $meta_key ) {
			$where['meta_key'] = $meta_key;
		}

		return QueryHelper::delete( $this->table, $where );
	}
}
