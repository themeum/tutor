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
use TutorPro\CourseBundle\Models\BundleModel;

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

	const STATUS_PUBLISH    = 'publish';
	const STATUS_DRAFT      = 'draft';
	const STATUS_AUTO_DRAFT = 'auto-draft';
	const STATUS_PENDING    = 'pending';
	const STATUS_PRIVATE    = 'private';
	const STATUS_FUTURE     = 'future';

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

		$order_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}tutor_orders WHERE id = %d", $order_id ) );

		$user_info = get_userdata( $order_data->user_id );

		$student                  = new \stdClass();
		$student->id              = $user_info->ID;
		$student->name            = $user_info->data->display_name;
		$student->email           = $user_info->data->user_email;
		$student->phone           = get_user_meta( $order_data->user_id, 'phone_number', true );
		$student->billing_address = $this->get_tutor_customer_data( $order_data->user_id );
		$student->image           = '';
		$profile_photo            = get_user_meta( $order_data->user_id, '_tutor_profile_photo', true );

		if ( $profile_photo ) {
			$student->image = wp_get_attachment_image_url( $profile_photo );
		}

		$order_data->student         = $student;
		$order_data->courses         = $this->get_order_items_by_id( $order_id );
		$order_data->sub_total_price = (float) $order_data->sub_total_price;
		$order_data->total_price     = (float) $order_data->total_price;
		$order_data->order_price     = (float) $order_data->order_price;
		$order_data->discount_amount = (float) $order_data->discount_amount;
		$order_data->tax             = (float) $order_data->tax;
		$order_data->tax_amount      = (float) $order_data->tax_amount;

		$order_data->created_by = get_userdata( $order_data->created_by )->display_name;
		$order_data->updated_by = get_userdata( $order_data->updated_by )->display_name;

		$order_data->activities = $this->get_order_activities( $order_id );
		$order_data->refunds    = $this->get_order_refunds( $order_id );

		unset( $order_data->user_id );
		unset( $student->billing_address->id );
		unset( $student->billing_address->user_id );

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
				oi.course_id AS id,
				oi.regular_price,
				oi.sale_price,
				p.post_title AS title,
				p.post_type AS type
			FROM {$wpdb->prefix}tutor_order_items AS oi
			LEFT JOIN {$wpdb->prefix}posts AS p ON p.ID = oi.course_id
			AND order_id = %d",
				$order_id
			)
		);

		$bundle_model = new BundleModel();
		foreach ( $courses as &$course ) {
			if ( 'course-bundle' === $course->type ) {
				$course->total_courses = count( $bundle_model->get_bundle_course_ids( $course->id ) );
			}

			$course->id            = (int) $course->id;
			$course->regular_price = (float) $course->regular_price;
			$course->sale_price    = (float) $course->sale_price;
			$course->image         = get_the_post_thumbnail_url( $course->id );
		}

		unset( $course );

		return ! empty( $courses ) ? $courses : array();
	}

	/**
	 * Retrieve tutor customer data by user ID.
	 *
	 * This function fetches customer data from the 'tutor_customers' table based on
	 * the given user ID. It utilizes a helper function from the QueryHelper class
	 * to perform the database query.
	 *
	 * The function returns the customer data as an object.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param int $user_id The ID of the user to retrieve customer data for.
	 *
	 * @since 3.0.0
	 *
	 * @return object The customer data retrieved from the database.
	 */
	public function get_tutor_customer_data( $user_id ) {
		global $wpdb;

		// Retrieve customer data for the given user ID from the 'tutor_customers' table.
		$customer_data = QueryHelper::get_row( "{$wpdb->prefix}tutor_customers", array( 'user_id' => $user_id ), 'id' );

		if ( empty( $customer_data ) ) {
			return array();
		}

		$return_data = (object) array(
			'id'       => $customer_data->id,
			'user_id'  => $customer_data->user_id,
			'name'     => $customer_data->billing_name,
			'email'    => $customer_data->billing_email,
			'phone'    => $customer_data->billing_phone,
			'address'  => $customer_data->billing_address,
			'city'     => $customer_data->billing_city,
			'state'    => $customer_data->billing_state,
			'country'  => $customer_data->billing_country,
			'zip_code' => $customer_data->billing_zip_code,
		);

		return $return_data;
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
			"{$wpdb->prefix}tutor_ordermeta",
			array(
				'order_id' => $order_id,
				'meta_key' => 'history',
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
	 * Retrieve order refunds by order ID.
	 *
	 * This function fetches all order refunds from the 'tutor_ordermeta' table
	 * based on the given order ID and the 'refund' meta key. It uses a helper
	 * function from the QueryHelper class to perform the database query.
	 *
	 * If no order refunds are found, the function returns an empty array.
	 * Otherwise, it decodes the JSON-encoded meta values and returns them as an array.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param int $order_id The ID of the order to retrieve refunds for.
	 *
	 * @since 3.0.0
	 *
	 * @return array An array of order refunds, each decoded from its JSON representation.
	 */
	public function get_order_refunds( $order_id ) {
		global $wpdb;

		// Retrieve order refunds for the given order ID from the 'tutor_ordermeta' table.
		$order_refunds = QueryHelper::get_all(
			"{$wpdb->prefix}tutor_ordermeta",
			array(
				'order_id' => $order_id,
				'meta_key' => 'refund',
			),
			'id'
		);

		if ( empty( $order_refunds ) ) {
			return array();
		}

		$response = array();

		foreach ( $order_refunds as &$refund ) {
			$values     = new \stdClass();
			$values     = json_decode( $refund->meta_value );
			$values->id = (int) $refund->id;
			$response[] = $values;
		}

		unset( $refund );

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
	 * Delete an order by ID
	 *
	 * @since 3.0.0
	 *
	 * @param int $order_id  order id that need to delete.
	 * @return bool
	 */
	public static function delete_course( $order_id ) {
		return true;
	}
}
