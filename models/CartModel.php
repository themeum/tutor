<?php
/**
 * Cart Model
 *
 * @package Tutor\Models
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\Models;

use Tutor\Helpers\QueryHelper;

/**
 * Cart model class for performing cart functionalities
 */
class CartModel {

	/**
	 * Get cart items
	 *
	 * @return array
	 */
	public function add_cart_item() {
		$cart_items = QueryHelper::get_all();
		return $cart_items;
	}

	/**
	 * Get cart items
	 *
	 * @return array
	 */
	public function get_cart_items() {
		global $wpdb;

		$cart_data = array(
			'total_count' => 0,
			'results'     => array(),
		);

		$user_id   = tutils()->get_user_id();
		$user_cart = QueryHelper::get_row(
			"{$wpdb->prefix}tutor_carts",
			array(
				'user_id' => $user_id,
			),
			'id'
		);

		if ( $user_cart ) {
			$primary_table  = "{$wpdb->prefix}tutor_cart_items AS ci";
			$joining_tables = array(
				array(
					'type'  => 'LEFT',
					'table' => "{$wpdb->prefix}posts AS p",
					'on'    => 'ci.course_id = p.ID',
				),
			);
			$where          = array( 'ci.cart_id' => $user_cart->id );
			$select_columns = array( 'p.*' );
			$cart_data      = QueryHelper::get_joined_data( $primary_table, $joining_tables, $select_columns, $where, array(), 'p.ID', 0, 0 );
		}

		return $cart_data;
	}

	/**
	 * Delete cart item.
	 *
	 * @since 3.0.0
	 *
	 * @param int $id The ID of the cart.
	 *
	 * @return boolean
	 */
	public function delete_cart_item( $id ) {
		global $wpdb;

		$delete = QueryHelper::delete( "{$wpdb->prefix}tutor_carts", array( 'id' => $id ) );

		return $delete;
	}

	/**
	 * Delete course from cart.
	 *
	 * @since 3.0.0
	 *
	 * @param int $course_id Course id.
	 *
	 * @return boolean
	 */
	public function delete_course_from_cart( $course_id ) {
		global $wpdb;

		$user_id   = tutils()->get_user_id();
		$user_cart = QueryHelper::get_row(
			"{$wpdb->prefix}tutor_carts",
			array(
				'user_id' => $user_id,
			),
			'id'
		);

		$delete = QueryHelper::delete(
			"{$wpdb->prefix}tutor_cart_items",
			array(
				'cart_id'   => $user_cart->id,
				'course_id' => $course_id,
			)
		);

		return $delete;
	}
}
