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
	 * Cart table name
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	private $table_name = 'tutor_carts';

	/**
	 * Resolve props
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		$this->table_name = $GLOBALS['wpdb']->prefix . $this->table_name;
	}

	/**
	 * Add a course to the user's cart.
	 *
	 * @param int $user_id User ID.
	 * @param int $course_id Course ID.
	 *
	 * @return array Array containing the result of the insert operation.
	 */
	public function add_course_to_cart( $user_id, $course_id ) {
		global $wpdb;

		$current_time = current_time( 'mysql', true );
		$user_cart_id = 0;

		$user_cart = QueryHelper::get_row(
			"{$wpdb->prefix}tutor_carts",
			array(
				'user_id' => $user_id,
			),
			'id'
		);

		if ( $user_cart ) {
			$user_cart_id = $user_cart->id;
		} else {
			$user_cart_id = QueryHelper::insert(
				"{$wpdb->prefix}tutor_carts",
				array(
					'user_id'        => $user_id,
					'created_at_gmt' => $current_time,
				)
			);
		}

		return QueryHelper::insert(
			"{$wpdb->prefix}tutor_cart_items",
			array(
				'cart_id'   => $user_cart_id,
				'course_id' => $course_id,
			)
		);
	}

	/**
	 * Get items from the user's cart.
	 *
	 * @param int $user_id User ID.
	 *
	 * @return array Array containing the cart items and their total count.
	 */
	public function get_cart_items( $user_id ) {
		global $wpdb;

		$cart_data = array(
			'cart'    => null,
			'courses' => array(
				'total_count' => 0,
				'results'     => array(),
			),
		);

		$user_cart = QueryHelper::get_row(
			"{$wpdb->prefix}tutor_carts",
			array(
				'user_id' => $user_id,
			),
			'id'
		);

		if ( $user_cart ) {
			$cart_data['cart'] = $user_cart;

			$primary_table        = "{$wpdb->prefix}tutor_cart_items AS item";
			$joining_tables       = array(
				array(
					'type'  => 'LEFT',
					'table' => "{$wpdb->prefix}posts AS post",
					'on'    => 'item.course_id = post.ID',
				),
			);
			$where                = array( 'item.cart_id' => $user_cart->id );
			$select_columns       = array( 'post.*' );
			$cart_data['courses'] = QueryHelper::get_joined_data(
				$primary_table,
				$joining_tables,
				$select_columns,
				$where,
				array(),
				'item.id'
			);
		}

		return $cart_data;
	}

	/**
	 * Check if the user has any items in their cart.
	 *
	 * @param int $user_id User ID.
	 * @return bool True if the user has items in the cart, false otherwise.
	 */
	public function has_item_in_cart( $user_id ) {
		$get_cart    = $this->get_cart_items( $user_id );
		$courses     = $get_cart['courses'];
		$total_count = $courses['total_count'];

		return (int) $total_count > 0;
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
	 * Delete a course from the user's cart.
	 *
	 * @since 3.0.0
	 *
	 * @param int $user_id User ID.
	 * @param int $course_id Course ID.
	 *
	 * @return boolean True if the course was successfully deleted, otherwise false.
	 */
	public function delete_course_from_cart( $user_id, $course_id ) {
		global $wpdb;

		$user_cart = QueryHelper::get_row(
			"{$wpdb->prefix}tutor_carts",
			array(
				'user_id' => $user_id,
			),
			'id'
		);

		return QueryHelper::delete(
			"{$wpdb->prefix}tutor_cart_items",
			array(
				'cart_id'   => $user_cart->id,
				'course_id' => $course_id,
			)
		);
	}

	/**
	 * Determine if a course is already added to the user's cart.
	 *
	 * @since 3.0.0
	 *
	 * @param int $user_id User ID.
	 * @param int $course_id Course ID.
	 *
	 * @return boolean True if the course is already in the cart, otherwise false.
	 */
	public static function is_course_in_user_cart( $user_id, $course_id ) {
		global $wpdb;

		$cart_table = "{$wpdb->prefix}tutor_carts AS cart";
		$item_table = "{$wpdb->prefix}tutor_cart_items AS item";

		$join_conditions = array(
			array(
				'type'  => 'LEFT',
				'table' => $item_table,
				'on'    => 'cart.id = item.cart_id',
			),
		);

		$conditions = array(
			'cart.user_id'   => $user_id,
			'item.course_id' => $course_id,
		);

		$select_columns = array( 'item.course_id' );

		$cart_data = QueryHelper::get_joined_data(
			$cart_table,
			$join_conditions,
			$select_columns,
			$conditions,
			array(),
			'item.id'
		);

		return (bool) $cart_data['total_count'];
	}

	/**
	 * Delete cart data using user id
	 *
	 * @since 3.0.0
	 *
	 * @param int $user_id User ID.
	 *
	 * @return boolean
	 */
	public function clear_user_cart( $user_id ) {
		return QueryHelper::delete(
			"{$this->table_name}",
			array(
				'user_id' => $user_id,
			)
		);
	}

}
