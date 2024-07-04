<?php
/**
 * Order Model
 *
 * @package Tutor\Models
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.6
 */

namespace Tutor\Models;

use Tutor\Helpers\QueryHelper;

/**
 * OrderModel Class
 *
 * @since 2.0.6
 */
class OrderModel {

	/**
	 * Order table name
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	private $table_name = 'tutor_orders';

	/**
	 * Order status
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ORDER_PENDING            = 'pending';
	const ORDER_COMPLETED          = 'completed';
	const ORDER_CANCELLED          = 'cancelled';
	const ORDER_REFUNDED           = 'refunded';
	const ORDER_PARTIALLY_REFUNDED = 'partially-refunded';

	/**
	 * Payment status
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const PAYMENT_STATUS_PENDING = 'pending';
	const PAYMENT_STATUS_PAID    = 'paid';
	const PAYMENT_STATUS_FAILED  = 'failed';

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
	 * Get orders list
	 *
	 * @since 3.0.0
	 *
	 * @param array  $where where clause conditions.
	 * @param int    $limit limit default 10.
	 * @param int    $offset default 0.
	 * @param string $order_by column default 'o.id'.
	 * @param string $order list order default 'desc'.
	 *
	 * @return array
	 */
	public function get_orders( array $where = array(), int $limit = 10, int $offset = 0, string $order_by = 'o.id', string $order = 'desc' ) {

		global $wpdb;

		$primary_table  = "{$wpdb->prefix}tutor_orders o";
		$joining_tables = array(
			array(
				'type'  => 'INNER',
				'table' => "{$wpdb->users} u",
				'on'    => 'o.user_id = u.ID',
			),
			array(
				'type'  => 'LEFT',
				'table' => "{$wpdb->usermeta} um1",
				'on'    => 'u.ID = um1.user_id AND um1.meta_key = "tutor_customer_billing_name"',
			),
			array(
				'type'  => 'LEFT',
				'table' => "{$wpdb->usermeta} um2",
				'on'    => 'u.ID = um2.user_id AND um2.meta_key = "tutor_customer_billing_name"',
			),
		);

		$select_columns = array( 'o.*', 'u.user_login', 'um1.meta_value as billing_name', 'um2.meta_value as billing_email' );

		return QueryHelper::get_joined_data( $primary_table, $joining_tables, $select_columns, $where, $order_by, $limit, $offset, $order );

	}

	/**
	 * Delete an order by ID
	 *
	 * @since 2.0.9
	 *
	 * @param int $order_id  order id that need to delete.
	 * @return bool
	 */
	public static function delete_course( $order_id ) {
		// if ( get_post_type( $post_id ) !== tutor()->course_post_type ) {
		// return false;
		// }

		// wp_delete_post( $post_id, true );
		return true;
	}
}
