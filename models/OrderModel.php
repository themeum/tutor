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
 * OrderModel Class
 *
 * @since 3.0.0
 */
class OrderModel {
	/**
	 * WordPress order type name
	 *
	 * @var string
	 */
	const POST_TYPE = 'orders';

	const STATUS_PUBLISH     = 'publish';
	const STATUS_DRAFT       = 'draft';
	const STATUS_AUTO_DRAFT  = 'auto-draft';
	const STATUS_PENDING     = 'pending';
	const STATUS_PRIVATE     = 'private';
	const STATUS_FUTURE      = 'future';
	const TUTOR_ORDERS_TABLE = 'tutor_orders';

	/**
	 * Retrieve order details by order ID.
	 *
	 * This function fetches order information from the database based on the given
	 * order ID. It queries the 'tutor_orders' table for the order data, retrieves
	 * the corresponding user information and metadata, and constructs a detailed
	 * student object with placeholder values for billing address and phone.
	 *
	 * The function then assigns this student object to the order data, removes
	 * the user ID from the order data, and returns the modified order data.
	 *
	 * @since 3.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param int $order_id The ID of the order to retrieve.
	 *
	 * @return object The order data with the student's information included.
	 */
	public function get_order_by_id( $order_id ) {
		global $wpdb;

		$order_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}" . self::TUTOR_ORDERS_TABLE . ' WHERE id = %d', $order_id ) );

		$user_info = get_userdata( $order_data->user_id );
		$user_meta = get_user_meta( $order_data->user_id );

		$student = new \stdClass();

		$student->id              = $user_info->ID;
		$student->name            = $user_info->data->display_name;
		$student->email           = $user_info->data->user_email;
		$student->phone           = '(094) 294893249';
		$student->image           = '';
		$student->billing_address = (object) array(
			'address'  => 'Santiago Nuetro',
			'city'     => 'Caro, Caroa 2',
			'state'    => 'California',
			'country'  => 'United States',
			'zip_code' => '23434',
			'phone'    => '(094) 13294884938',
		);

		$order_data->student = $student;

		$order_data->courses = $this->get_order_items_by_id( $order_id );

		unset( $order_data->user_id );

		return $order_data;
	}

	/**
	 * Retrieve order items by order ID.
	 *
	 * This function fetches order item details from the database based on the given
	 * order ID. It queries the 'tutor_order_items' table and joins it with the 'posts'
	 * table to get the course titles associated with each order item.
	 *
	 * The function then returns the retrieved order items, or an empty array if no
	 * items are found.
	 *
	 * @since 3.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param int $order_id The ID of the order to retrieve items for.
	 *
	 * @return array The order items, each containing details and course titles, or an empty array if no items are found.
	 */
	public function get_order_items_by_id( $order_id ) {
		global $wpdb;

		$courses = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT 
				oi.* ,
				p.post_title AS title
			FROM {$wpdb->prefix}tutor_order_items AS oi
			LEFT JOIN {$wpdb->prefix}posts AS p ON p.ID = oi.course_id
			WHERE order_id = %d",
				$order_id
			)
		);

		return ! empty( $courses ) ? $courses : array();
	}

	/**
	 * Delete an order by ID
	 *
	 * @since 3.0.0
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
