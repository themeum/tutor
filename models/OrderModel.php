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

use Tutor\Ecommerce\OrderActivitiesController;
use Tutor\Helpers\QueryHelper;

/**
 * OrderModel Class
 *
 * @since 3.0.0
 */
class OrderModel {

	/**
	 * Order status
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ORDER_INCOMPLETE = 'incomplete';
	const ORDER_COMPLETED  = 'completed';
	const ORDER_CANCELLED  = 'cancelled';
	const ORDER_TRASH      = 'trash';

	/**
	 * Payment status
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const PAYMENT_PAID               = 'paid';
	const PAYMENT_FAILED             = 'failed';
	const PAYMENT_UNPAID             = 'unpaid';
	const PAYMENT_REFUNDED           = 'refunded';
	const PAYMENT_PARTIALLY_REFUNDED = 'partially-refunded';

	/**
	 * Order table name
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	private $table_name = 'tutor_orders';

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
	 * Get all order statuses
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public static function get_order_status() {
		return array(
			self::ORDER_INCOMPLETE => __( 'Incomplete', 'tutor' ),
			self::ORDER_COMPLETED  => __( 'Completed', 'tutor' ),
			self::ORDER_CANCELLED  => __( 'Cancelled', 'tutor' ),
			self::ORDER_TRASH      => __( 'Trash', 'tutor' ),
		);
	}

	/**
	 * Get all payment statuses
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public static function get_payment_status() {
		return array(
			self::PAYMENT_PAID               => __( 'Paid', 'tutor' ),
			self::PAYMENT_UNPAID             => __( 'Unpaid', 'tutor' ),
			self::PAYMENT_FAILED             => __( 'Failed', 'tutor' ),
			self::PAYMENT_REFUNDED           => __( 'Refunded', 'tutor' ),
			self::PAYMENT_PARTIALLY_REFUNDED => __( 'Partially Refunded', 'tutor' ),
		);
	}

	/**
	 * Get searchable fields
	 *
	 * This method is intendant to use with get order list
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	private function get_searchable_fields() {
		return array(
			'o.id',
			'o.transaction_id',
			'o.coupon_code',
			'o.payment_method',
			'o.order_status',
			'o.payment_status',
			'u.display_name',
			'u.user_login',
			'u.user_email',
		);
	}

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
	 * @return object|false The order data with the student's information included, or false if no order is found.
	 */
	public function get_order_by_id( $order_id ) {
		$order_data = QueryHelper::get_row(
			$this->table_name,
			array( 'id' => $order_id ),
			'id'
		);

		if ( ! $order_data ) {
			return false;
		}

		$user_info = get_userdata( $order_data->user_id );

		$student                  = new \stdClass();
		$student->id              = (int) $user_info->ID;
		$student->name            = $user_info->data->display_name;
		$student->email           = $user_info->data->user_email;
		$student->phone           = get_user_meta( $order_data->user_id, 'phone_number', true );
		$student->billing_address = $this->get_tutor_customer_data( $order_data->user_id );
		$student->image           = get_avatar_url( $order_data->user_id );

		$order_data->student         = $student;
		$order_data->courses         = $this->get_order_items_by_id( $order_id );
		$order_data->subtotal_price  = (float) $order_data->subtotal_price;
		$order_data->total_price     = (float) $order_data->total_price;
		$order_data->net_payment     = (float) $order_data->net_payment;
		$order_data->discount_amount = (float) $order_data->discount_amount;
		$order_data->tax_rate        = (float) $order_data->tax_rate;
		$order_data->tax_amount      = (float) $order_data->tax_amount;

		$order_data->created_by = get_userdata( $order_data->created_by )->display_name;
		$order_data->updated_by = get_userdata( $order_data->updated_by )->display_name;

		$order_activities_model = new OrderActivitiesModel();
		$order_data->activities = $order_activities_model->get_order_activities( $order_id );
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

		$primary_table  = "{$wpdb->prefix}tutor_order_items AS oi";
		$joining_tables = array(
			array(
				'type'  => 'LEFT',
				'table' => "{$wpdb->prefix}posts AS p",
				'on'    => 'p.ID = oi.course_id',
			),
		);

		$where = array( 'order_id' => $order_id );

		$select_columns = array( 'oi.course_id AS id', 'oi.regular_price', 'oi.sale_price', 'p.post_title AS title', 'p.post_type AS type' );

		$courses_data = QueryHelper::get_joined_data( $primary_table, $joining_tables, $select_columns, $where, array(), 'id', 0, 0 );
		$courses      = $courses_data['results'];

		if ( tutor()->has_pro ) {
			$bundle_model = new \TutorPro\CourseBundle\Models\BundleModel();
		}

		if ( ! empty( $courses_data['total_count'] ) ) {
			foreach ( $courses as &$course ) {
				if ( tutor()->has_pro && 'course-bundle' === $course->type ) {
					$course->total_courses = count( $bundle_model->get_bundle_course_ids( $course->id ) );
				}

				$course->id            = (int) $course->id;
				$course->regular_price = (float) $course->regular_price;
				$course->sale_price    = (float) $course->sale_price;
				$course->image         = get_the_post_thumbnail_url( $course->id );
			}
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
	 * @return object|null The customer data retrieved from the database.
	 */
	public function get_tutor_customer_data( $user_id ) {
		global $wpdb;

		// Retrieve customer data for the given user ID from the 'tutor_customers' table.
		$customer_data = QueryHelper::get_row( "{$wpdb->prefix}tutor_customers", array( 'user_id' => $user_id ), 'id' );

		if ( empty( $customer_data ) ) {
			return null;
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

		$meta_keys = array(
			OrderActivitiesModel::META_KEY_REFUND,
			OrderActivitiesModel::META_KEY_PARTIALLY_REFUND,
		);

		// Retrieve order refunds for the given order ID from the 'tutor_ordermeta' table.
		$order_refunds = QueryHelper::get_all(
			"{$wpdb->prefix}tutor_ordermeta",
			array(
				'order_id' => $order_id,
				'meta_key' => $meta_keys,
			),
			'created_at_gmt',
			1000,
			'ASC'
		);

		if ( empty( $order_refunds ) ) {
			return array();
		}

		$response = array();

		foreach ( $order_refunds as $refund ) {
			$parsed_meta_value = json_decode( $refund->meta_value );
			$values            = new \stdClass();
			$values->id        = (int) $refund->id;

			foreach ( $parsed_meta_value as $key => $value ) {
				$values->$key = $value;
			}

			$values->data = $refund->created_at_gmt;

			$response[] = $values;
		}

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
	 * Update an order
	 *
	 * @since 3.0.0
	 *
	 * @param int|array $order_id Integer or array of ids sql escaped.
	 * @param array     $data Data to update, escape data.
	 *
	 * @return bool
	 */
	public function update_order( $order_id, array $data ) {
		$order_id = is_array( $order_id ) ? $order_id : array( $order_id );
		$order_id = QueryHelper::prepare_in_clause( $order_id );
		try {
			QueryHelper::update_where_in(
				$this->table_name,
				$data,
				$order_id
			);
			return true;
		} catch ( \Throwable $th ) {
			error_log( $th->getMessage() . ' in ' . $th->getFile() . ' at line ' . $th->getLine() );
			return false;
		}
	}

	/**
	 * Delete an order by order ID.
	 *
	 * This function deletes an order from the 'tutor_orders' table based on the given
	 * order ID. It uses the QueryHelper class to perform the database delete operation.
	 *
	 * @since 3.0.0
	 *
	 * @param int|array $order_id The ID of the order to delete.
	 *
	 * @return bool False on failure, or the number of rows affected if successful.
	 */
	public function delete_order( $order_id ) {
		$order_id = is_array( $order_id ) ? $order_id : array( intval( $order_id ) );
		return QueryHelper::bulk_delete_by_ids( $this->table_name, $order_id ) ? true : false;
	}

	/**
	 * Get orders list
	 *
	 * @since 3.0.0
	 *
	 * @param array  $where where clause conditions.
	 * @param string $search_term search clause conditions.
	 * @param int    $limit limit default 10.
	 * @param int    $offset default 0.
	 * @param string $order_by column default 'o.id'.
	 * @param string $order list order default 'desc'.
	 *
	 * @return array
	 */
	public function get_orders( array $where = array(), $search_term = '', int $limit = 10, int $offset = 0, string $order_by = 'o.id', string $order = 'desc' ) {
		global $wpdb;

		$primary_table  = "{$this->table_name} o";
		$joining_tables = array(
			array(
				'type'  => 'LEFT',
				'table' => "{$wpdb->users} u",
				'on'    => 'o.user_id = u.ID',
			),
		);

		$select_columns = array( 'o.*', 'u.user_login' );

		$search_clause = array();
		if ( '' !== $search_term ) {
			foreach ( $this->get_searchable_fields() as $column ) {
				$search_clause[ $column ] = $search_term;
			}
		}

		$response = array(
			'results'     => array(),
			'total_count' => 0,
		);

		try {
			return QueryHelper::get_joined_data( $primary_table, $joining_tables, $select_columns, $where, $search_clause, $order_by, $limit, $offset, $order );
		} catch ( \Throwable $th ) {
			// Log with error, line & file name.
			error_log( $th->getMessage() . ' in ' . $th->getFile() . ' at line ' . $th->getLine() );
			return $response;
		}
	}

	/**
	 * Get order count
	 *
	 * @since 3.0.0
	 *
	 * @param array  $where Where conditions, sql esc data.
	 * @param string $search_term Search terms, sql esc data.
	 *
	 * @return int
	 */
	public function get_order_count( $where = array(), string $search_term = '' ) {
		global $wpdb;

		$search_clause = array();
		if ( '' !== $search_term ) {
			foreach ( $this->get_searchable_fields() as $column ) {
				$search_clause[ $column ] = $search_term;
			}
		}

		$join_table    = array(
			array(
				'type'  => 'INNER',
				'table' => "{$wpdb->users} u",
				'on'    => 'o.user_id = u.ID',
			),
		);
		$primary_table = "{$this->table_name} o";
		return QueryHelper::get_joined_count( $primary_table, $join_table, $where, $search_clause );
	}

	/**
	 * Update the payment status of an order.
	 *
	 * This function updates the payment status and note of an order in the database.
	 * It uses the QueryHelper class to perform the update operation.
	 *
	 * @since 3.0.0
	 *
	 * @param object $data An object containing the payment status, note, and order ID.
	 *                     - 'payment_status' (string): The new payment status.
	 *                     - 'note' (string): A note regarding the payment status update.
	 *                     - 'order_id' (int): The ID of the order to update.
	 *
	 * @return bool True on successful update, false on failure.
	 */
	public function payment_status_update( object $data ) {
		$response = QueryHelper::update(
			$this->table_name,
			array(
				'payment_status' => $data->payment_status,
				'note'           => $data->note,
			),
			array( 'id' => $data->order_id )
		);

		if ( $response ) {
			$activity_controller = new OrderActivitiesController();
			$activity_controller->store_order_activity_for_marked_as_paid( $data->order_id );
		}

		return $response;
	}

	/**
	 * Adds a discount to an order by updating the order's discount details and recalculating order prices.
	 *
	 * This method recalculates the subtotal, total price, and net payment for an order
	 * based on the provided discount type and amount. It then updates these prices along with the
	 * discount details in the database.
	 *
	 * @since 3.0.0
	 *
	 * @param object $data An object containing the following properties:
	 *                     - order_id (int): The ID of the order to be updated.
	 *                     - discount_type (string): The type of discount applied.
	 *                     - discount_amount (float): The amount of discount applied.
	 *                     - discount_reason (string): The reason for the discount.
	 *
	 * @return bool True on successful update, false on failure.
	 */
	public function add_order_discount( object $data ) {
		$order_prices = $this->recalculate_order_prices( $data->order_id, $data->discount_type, $data->discount_amount );

		$response = QueryHelper::update(
			$this->table_name,
			array(
				'subtotal_price'  => $order_prices->sub_total,
				'total_price'     => $order_prices->total_price,
				'net_payment'     => $order_prices->net_price,
				'discount_type'   => $data->discount_type,
				'discount_amount' => $data->discount_amount,
				'discount_reason' => $data->discount_reason,
			),
			array( 'id' => $data->order_id )
		);

		return $response;
	}

	/**
	 * Updates the status of an order and logs the activity.
	 *
	 * This function updates the status of an order in the database and, if successful, logs the activity
	 * with a message indicating the status change. The message includes the current user's display name,
	 * if available.
	 *
	 * The possible order statuses include:
	 * - ORDER_CANCELLED
	 * - ORDER_COMPLETED
	 * - ORDER_INCOMPLETE
	 * - ORDER_TRASH
	 *
	 * If the update is successful, an order activity log entry is created with the current date, time,
	 * and status change message.
	 *
	 * @since 3.0.0
	 *
	 * @param object $data An object containing:
	 *                     - int    $order_id       The ID of the order to update.
	 *                     - string $order_status   The new status of the order.
	 *                     - string $cancel_reason  The reason for the order cancellation (optional).
	 *
	 * @return bool True on successful update, false on failure.
	 */
	public function order_status_update( object $data ) {
		$response = QueryHelper::update(
			$this->table_name,
			array(
				'order_status' => $data->order_status,
			),
			array( 'id' => $data->order_id )
		);

		if ( $response ) {
			$user_name    = '';
			$current_user = wp_get_current_user();

			if ( $current_user->exists() ) {
				$user_name = $current_user->display_name;
			}

			$message = '';

			if ( self::ORDER_CANCELLED === $data->order_status ) {
				$message = empty( $user_name ) ? __( 'Order marked as cancelled', 'tutor' ) : sprintf( __( 'Order marked as cancelled by %s', 'tutor' ), $user_name );
			} elseif ( self::ORDER_COMPLETED === $data->order_status ) {
				$message = empty( $user_name ) ? __( 'Order marked as completed', 'tutor' ) : sprintf( __( 'Order marked as completed by %s', 'tutor' ), $user_name );
			} elseif ( self::ORDER_INCOMPLETE === $data->order_status ) {
				$message = empty( $user_name ) ? __( 'Order marked as incomplete', 'tutor' ) : sprintf( __( 'Order marked as incomplete by %s', 'tutor' ), $user_name );
			} elseif ( self::ORDER_TRASH === $data->order_status ) {
				$message = empty( $user_name ) ? __( 'Order marked as trash', 'tutor' ) : sprintf( __( 'Order marked as trash by %s', 'tutor' ), $user_name );
			}

			// insert cancel reason in tutor_ordermeta table.
			if ( self::ORDER_CANCELLED === $data->order_status && ! empty( $data->cancel_reason ) ) {
				$meta_payload             = new \stdClass();
				$meta_payload->order_id   = $data->order_id;
				$meta_payload->meta_key   = OrderActivitiesModel::META_KEY_CANCEL_REASON;
				$meta_payload->meta_value = $data->cancel_reason;

				$order_activities_model = new OrderActivitiesModel();
				$order_activities_model->add_order_meta( $meta_payload );
			}

			if ( $message ) {
				$value = wp_json_encode(
					array(
						'message' => $message,
					)
				);
				OrderActivitiesController::store_order_activity( $data->order_id, OrderActivitiesModel::META_KEY_HISTORY, $value );
			}
		}

		return $response;
	}

	/**
	 * Recalculates the order prices including discounts, taxes, and refunds.
	 *
	 * This method retrieves the order data and item prices, applies any given discounts or those already
	 * associated with the order, calculates the tax amount, and adjusts for any refunds to determine
	 * the final net price.
	 *
	 * @since 3.0.0
	 *
	 * @param int    $order_id      The ID of the order to recalculate prices for.
	 * @param string $discount_type Optional. The type of discount to apply.
	 * @param float  $discount_value Optional. The value of the discount to apply.
	 *
	 * @return object An object containing the recalculated prices:
	 *                  - sub_total (float): The sum of order item prices.
	 *                  - discount_amount (float): The total discount applied.
	 *                  - tax_amount (float): The tax amount.
	 *                  - total_price (float): The total price after applying discount and tax.
	 *                  - net_price (float): The final price after accounting for refunds.
	 */
	public function recalculate_order_prices( $order_id, $discount_type = null, $discount_value = null ) {
		$order_data = QueryHelper::get_row( $this->table_name, array( 'id' => $order_id ), 'id' );

		// get the order item prices/sub_total.
		$order_items_price = $this->get_order_items_price( $order_id );
		$sub_total         = $order_items_price->sub_total;

		// get discount/coupon amount.
		$discount_amount = 0;

		$discount_type  = ! empty( $discount_type ) ? $discount_type : $order_data->discount_type;
		$discount_value = ! empty( $discount_value ) ? $discount_value : $order_data->discount_amount;

		if ( ! empty( $discount_type ) && ! empty( $discount_value ) ) {
			$discount_amount = $this->calculate_discount_amount( $discount_type, $discount_value, $sub_total );
		}

		// get tax amount.
		$tax_amount = (float) $order_data->tax_amount ?? 0;

		// calculate total (sub_total - discount + tax).
		$total = $sub_total - $discount_amount + $tax_amount;

		// get refund values if refunded.
		$refund_amount = 0;
		if ( self::PAYMENT_REFUNDED === $order_data->payment_status || self::PAYMENT_PARTIALLY_REFUNDED === $order_data->payment_status ) {
			// refund amount code here.
			$refund_amount = $this->get_refund_amount( $order_id );
		}

		// update net_price (total - refund_amount).
		$net_price = $total - $refund_amount;

		$response                  = new \stdClass();
		$response->sub_total       = $sub_total;
		$response->discount_amount = $discount_amount;
		$response->tax_amount      = $tax_amount;
		$response->total_price     = $total;
		$response->net_price       = $net_price;

		return $response;
	}

	/**
	 * Retrieves and calculates the total price of order items.
	 *
	 * This method fetches all order items associated with the given order ID,
	 * calculates the subtotal by summing up the item prices, and returns an object
	 * containing the order items and the subtotal.
	 *
	 * @since 3.0.0
	 *
	 * @param int $order_id The ID of the order to retrieve items for.
	 *
	 * @return object An object containing the order items and their total price:
	 *                  - items (array): An array of order item objects.
	 *                  - sub_total (float): The sum of order item prices.
	 */
	public function get_order_items_price( $order_id ) {
		global $wpdb;

		$where       = array( 'order_id' => $order_id );
		$order_items = QueryHelper::get_all( $wpdb->prefix . 'tutor_order_items', $where, 'id', 0 );

		$response            = new \stdClass();
		$response->items     = $order_items;
		$response->sub_total = 0;

		foreach ( $order_items as $item ) {
			$response->sub_total += empty( $item->sale_price ) ? (float) $item->regular_price : (float) $item->sale_price;
		}

		return $response;
	}

	/**
	 * Calculates the discounted price based on the discount type and amount.
	 *
	 * This method calculates the discounted price of a given subtotal based on the provided
	 * discount type (either 'percent' or a fixed amount) and the discount amount.
	 *
	 * @since 3.0.0
	 *
	 * @param string $discount_type The type of discount ('percent' or 'fixed').
	 * @param float  $discount_amount The amount of discount to apply.
	 * @param float  $sub_total The subtotal amount before applying the discount.
	 *
	 * @return float The discounted price after applying the discount.
	 */
	public function calculate_discount_amount( $discount_type, $discount_amount, $sub_total ) {
		if ( 'percent' === $discount_type ) {
			$discounted_price = (float) $sub_total - ( (float) $sub_total * (float) $discount_amount ) / 100;
		} else {
			$discounted_price = (float) $sub_total - (float) $discount_amount;
		}
		return $discounted_price;
	}

	/**
	 * Retrieves the total refund amount for a given order.
	 *
	 * This method fetches all refund records for the specified order ID from the database,
	 * calculates the total refund amount, and returns it. The refund records are retrieved
	 * from the `tutor_ordermeta` table where the `meta_key` matches the refund meta keys.
	 *
	 * @since 3.0.0
	 *
	 * @param int $order_id The ID of the order for which the refund amount is to be calculated.
	 *
	 * @return float The total refund amount for the order.
	 */
	public function get_refund_amount( $order_id ) {
		global $wpdb;

		$table    = $wpdb->prefix . 'tutor_ordermeta';
		$where_id = OrderActivitiesModel::META_KEY_REFUND . ',' . OrderActivitiesModel::META_KEY_PARTIALLY_REFUND;

		$refund_records = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT *
					FROM %s
					WHERE order_id = %d
					AND meta_key IN ( %s )
					ORDER BY created_at_gmt DESC
				',
				$table,
				$order_id,
				$where_id
			)
		);

		$refund_amount = 0;

		foreach ( $refund_records as $refund ) {
			$refund_data = json_decode( $refund->meta_value );

			if ( ! empty( $refund_data->amount ) ) {
				$refund_amount += (float) $refund_data->amount;
			}
		}

		return $refund_amount;
	}
}
