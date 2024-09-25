<?php
/**
 * OrderMeta Model
 *
 * @package Tutor\Models
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\Models;

use Tutor\Helpers\QueryHelper;

/**
 * OrderMetaModel Class
 *
 * @since 3.0.0
 */
class OrderMetaModel {
	/**
	 * Table name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	private $table_name = 'tutor_ordermeta';

	/**
	 * Constructor
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . $this->table_name;
	}

	/**
	 * Add meta
	 *
	 * @since 3.0.0
	 *
	 * @param int    $order_id order id.
	 * @param string $meta_key meta key.
	 * @param mixed  $meta_value meta value.
	 *
	 * @return int added meta id.
	 */
	public static function add_meta( $order_id, $meta_key, $meta_value ) {
		$self = new self();

		$current_time    = current_time( 'mysql', true );
		$current_user_id = get_current_user_id();

		return QueryHelper::insert(
			$self->table_name,
			array(
				'order_id'       => $order_id,
				'meta_key'       => $meta_key,
				'meta_value'     => maybe_serialize( $meta_value ),
				'created_at_gmt' => $current_time,
				'created_by'     => $current_user_id,
				'updated_at_gmt' => $current_time,
				'updated_by'     => $current_user_id,
			)
		);
	}

	/**
	 * Update meta.
	 * If meta key does not exist it will add new meta otherwise update the meta value.
	 *
	 * @param int    $order_id order id.
	 * @param string $meta_key meta key.
	 * @param mixed  $meta_value meta value.
	 *
	 * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
	 */
	public static function update_meta( $order_id, $meta_key, $meta_value ) {
		$self       = new self();
		$meta_value = maybe_serialize( $meta_value );

		if ( self::get_meta( $order_id, $meta_key ) ) {
			$current_time    = current_time( 'mysql', true );
			$current_user_id = get_current_user_id();

			return QueryHelper::update(
				$self->table_name,
				array(
					'meta_value'     => $meta_value,
					'updated_at_gmt' => $current_time,
					'updated_by'     => $current_user_id,
				),
				array(
					'order_id' => $order_id,
					'meta_key' => $meta_key,
				)
			);
		} else {
			return self::add_meta( $order_id, $meta_key, $meta_value );
		}
	}

	/**
	 * Get a meta record by order id and meta key.
	 *
	 * @param int    $order_id order id.
	 * @param string $meta_key meta key.
	 *
	 * @return object|null Meta object if the key exists, null otherwise.
	 *
	 * @since 3.0.0
	 */
	public static function get_meta( $order_id, $meta_key ) {
		$self   = new self();
		$result = QueryHelper::get_row(
			$self->table_name,
			array(
				'order_id' => $order_id,
				'meta_key' => $meta_key,
			),
			'id'
		);

		return $result;
	}

	/**
	 * Get all meta record of an order.
	 *
	 * @since 3.0.0
	 *
	 * @param int $order_id order id.
	 *
	 * @return array
	 */
	public static function get_all_meta( $order_id ) {
		$self   = new self();
		$result = QueryHelper::get_all(
			$self->table_name,
			array(
				'order_id' => $order_id,
			),
			'*',
			-1
		);

		return $result;
	}

	/**
	 * Get a meta record.
	 *
	 * @since 3.0.0
	 *
	 * @param array $where where.
	 *
	 * @return object|null
	 */
	public static function get_row( $where = array() ) {
		$self   = new self();
		$result = QueryHelper::get_row(
			$self->table_name,
			$where,
			'id'
		);

		return $result;
	}

	/**
	 * Get all order meta based on clauses.
	 *
	 * @since 3.0.0
	 *
	 * @param array   $where where.
	 * @param string  $order_by order by.
	 * @param string  $order order type.
	 * @param integer $limit limit.
	 *
	 * @return array
	 */
	public static function get_all( $where = array(), $order_by = 'id', $order = 'ASC', $limit = -1 ) {
		$self   = new self();
		$result = QueryHelper::get_all(
			$self->table_name,
			$where,
			$order_by,
			$limit,
			$order
		);

		return $result;
	}

	/**
	 * Get order meta value.
	 *
	 * @param int    $order_id order id.
	 * @param string $meta_key meta key.
	 * @param bool   $single single meta value.
	 *
	 * @return mixed return array of values if $single is false otherwise single value.
	 *               Empty string for invalid order id or meta key.
	 */
	public static function get_meta_value( $order_id, $meta_key, $single = false ) {
		$where = array(
			'order_id' => $order_id,
			'meta_key' => $meta_key,
		);

		$result = $single ? self::get_row( $where ) : self::get_all( $where );
		if ( ! $result ) {
			return '';
		}

		return $single
				? maybe_unserialize( $result->meta_value )
				: array_map( 'maybe_unserialize', array_column( $result, 'meta_value' ) );

	}
}
