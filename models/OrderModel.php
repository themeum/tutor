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

use Exception;
use Tutor\Ecommerce\Ecommerce;
use Tutor\Ecommerce\OrderActivitiesController;
use Tutor\Helpers\DateTimeHelper;
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
	 * Order Meta keys for history & refunds
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const META_KEY_HISTORY = 'history';
	const META_KEY_REFUND  = 'refund';

	/**
	 * Tax type constants
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const TAX_TYPE_EXCLUSIVE = 'exclusive';
	const TAX_TYPE_INCLUSIVE = 'inclusive';


	/**
	 * Order type
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const TYPE_SINGLE_ORDER = 'single_order';
	const TYPE_SUBSCRIPTION = 'subscription';
	const TYPE_RENEWAL      = 'renewal';

	/**
	 * Transient constants
	 *
	 * @since 3.0.0
	 */
	const TRANSIENT_ORDER_BADGE_COUNT = 'tutor_order_badge_count';

	/**
	 * Order placement success
	 *
	 * @since 3.0.0
	 */
	const ORDER_PLACEMENT_SUCCESS = 'success';

	/**
	 * Order placement failed
	 *
	 * @since 3.0.0
	 */
	const ORDER_PLACEMENT_FAILED = 'failed';

	/**
	 * Order table name
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	private $table_name = 'tutor_orders';

	/**
	 * Order item table name
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	private $order_item_table = 'tutor_order_items';

	/**
	 * Order item fillable fields
	 *
	 * @since 3.0.0
	 *
	 * @var array
	 */
	private $order_items_fillable_fields = array(
		'order_id',
		'item_id',
		'regular_price',
		'sale_price',
		'discount_price',
		'coupon_code',
	);

	/**
	 * Resolve props & dependencies
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		global $wpdb;
		$this->table_name       = $wpdb->prefix . $this->table_name;
		$this->order_item_table = $wpdb->prefix . $this->order_item_table;
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
	 * Get order items fillable fields
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public function get_order_items_fillable_fields() {
		return $this->order_items_fillable_fields;
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
	 * Create order
	 *
	 * Note: validate data before using this method
	 *
	 * This method will also insert items if
	 * item is set.
	 *
	 * Ex: data['order_items] = [
	 *  user_id => 1,
	 *  course_id => 1,
	 *  regular_price => 100,
	 *  sale_price => 90
	 * ]
	 *
	 * @since 3.0.0
	 *
	 * @param array $data Order data based on db table.
	 *
	 * @throws \Exception Database error if occur.
	 *
	 * @return int Order id on success
	 */
	public function create_order( array $data ) {
		$order_items = $data['items'] ?? null;
		unset( $data['items'] );

		global $wpdb;

		// Start transaction.
		$wpdb->query( 'START TRANSACTION' );

		try {
			$order_id = QueryHelper::insert( $this->table_name, $data );
			if ( $order_id ) {
				if ( $order_items ) {
					$insert = $this->insert_order_items( $order_id, $order_items );
					if ( $insert ) {
						$wpdb->query( 'COMMIT' );
						return $order_id;
					} else {
						$wpdb->query( 'ROLLBACK' );
						throw new \Exception( __( 'Failed to insert order items', 'tutor' ) );
					}
				} else {
					$wpdb->query( 'COMMIT' );
					return $order_id;
				}
			}
		} catch ( \Throwable $th ) {
			throw new \Exception( $th->getMessage() );
		}
	}

	/**
	 * Insert order items
	 *
	 * Note: validate data before using this method
	 *
	 * @since 3.0.0
	 *
	 * @param int   $order_id Order ID.
	 * @param array $items    Order items.
	 *
	 * @throws Exception Database error if occur.
	 *
	 * @return bool
	 */
	public function insert_order_items( int $order_id, array $items ): bool {
		// Check if item is multi dimensional.
		if ( ! isset( $items[0] ) ) {
			$items = array( $items );
		}

		// Set order id on each item.
		foreach ( $items as $key => $item ) {
			$items[ $key ]['order_id'] = $order_id;
		}

		try {
			$insert = QueryHelper::insert_multiple_rows(
				$this->order_item_table,
				$items
			);
			return $insert ? true : false;
		} catch ( \Throwable $th ) {
			throw new Exception( $th->getMessage() );
		}
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
		if ( ! is_a( $user_info, 'WP_User' ) ) {
			return false;
		}

		$student                  = new \stdClass();
		$student->id              = (int) $user_info->ID;
		$student->name            = $user_info->data->display_name;
		$student->email           = $user_info->data->user_email;
		$student->phone           = get_user_meta( $order_data->user_id, 'phone_number', true );
		$student->billing_address = $this->get_tutor_customer_data( $order_data->user_id );
		$student->image           = get_avatar_url( $order_data->user_id );

		$order_data->student = $student;
		$order_data->items   = $this->get_order_items_by_id( $order_id );

		$order_data->subtotal_price  = (float) $order_data->subtotal_price;
		$order_data->total_price     = (float) $order_data->total_price;
		$order_data->net_payment     = (float) $order_data->net_payment;
		$order_data->discount_amount = (float) $order_data->discount_amount;
		$order_data->coupon_amount   = (float) $order_data->coupon_amount;
		$order_data->tax_rate        = (float) $order_data->tax_rate;
		$order_data->tax_amount      = (float) $order_data->tax_amount;

		$order_data->payment_method_readable = Ecommerce::get_payment_method_label( $order_data->payment_method );
		$order_data->created_at_readable     = DateTimeHelper::get_gmt_to_user_timezone_date( $order_data->created_at_gmt );
		$order_data->updated_at_readable     = empty( $order_data->updated_at_gmt ) ? '' : DateTimeHelper::get_gmt_to_user_timezone_date( $order_data->updated_at_gmt );

		$order_data->created_by = get_userdata( $order_data->created_by )->display_name ?? '';
		$order_data->updated_by = get_userdata( $order_data->updated_by )->display_name ?? '';

		$order_activities_model = new OrderActivitiesModel();
		$order_data->activities = $order_activities_model->get_order_activities( $order_id );
		$order_data->refunds    = $this->get_order_refunds( $order_id );

		unset( $student->billing_address->id );
		unset( $student->billing_address->user_id );

		return apply_filters( 'tutor_order_details', $order_data );
	}

	/**
	 * Get order data
	 *
	 * @since 3.1.0
	 *
	 * @param int|object $order order id or object.
	 *
	 * @return object
	 */
	public static function get_order( $order ) {
		if ( is_numeric( $order ) ) {
			$order = ( new self() )->get_order_by_id( $order );
		}

		return $order;
	}

	/**
	 * Check order is subscription order
	 *
	 * @since 3.1.0
	 *
	 * @param int|object $order order id or object.
	 *
	 * @return boolean
	 */
	public static function is_subscription_order( $order ) {
		$order = self::get_order( $order );
		return $order && self::TYPE_SUBSCRIPTION === $order->order_type;
	}

	/**
	 * Mark order Unpaid to Paid.
	 *
	 * @since 3.0.0
	 *
	 * @param int    $order_id order id.
	 * @param string $note note.
	 * @param bool   $trigger_hooks trigger hooks or not.
	 *
	 * @return bool
	 */
	public function mark_as_paid( $order_id, $note = '', $trigger_hooks = true ) {
		if ( $trigger_hooks ) {
			do_action( 'tutor_before_order_mark_as_paid', $order_id );
		}

		$data = array(
			'payment_status' => self::PAYMENT_PAID,
			'order_status'   => self::ORDER_COMPLETED,
			'note'           => $note,
		);

		$response = $this->update_order( $order_id, $data );
		if ( ! $response ) {
			return false;
		}

		if ( $trigger_hooks ) {
			do_action( 'tutor_order_payment_status_changed', $order_id, self::PAYMENT_UNPAID, self::PAYMENT_PAID );

			$order           = $this->get_order_by_id( $order_id );
			$discount_amount = $this->calculate_discount_amount( $order->discount_type, $order->discount_amount, $order->subtotal_price );
			do_action( 'tutor_after_order_mark_as_paid', $order, $discount_amount );
		}

		return true;
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
				'on'    => 'p.ID = oi.item_id',
			),
		);

		$where = array( 'order_id' => $order_id );

		$select_columns = array( 'oi.item_id AS id', 'oi.regular_price', 'oi.sale_price', 'oi.discount_price', 'oi.coupon_code', 'p.post_title AS title', 'p.post_type AS type' );

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
			'name'     => $customer_data->billing_first_name . ' ' . $customer_data->billing_last_name,
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

			$values->date = $refund->created_at_gmt;

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
	 * Get enrollment ids by order id.
	 *
	 * @since 3.0.0
	 *
	 * @param int $order_id order id.
	 *
	 * @return array
	 */
	public function get_enrollment_ids( $order_id ) {
		global $wpdb;
		$enrollment_ids = array();

		$enrollments = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->postmeta} 
				WHERE meta_key=%s
				AND meta_value LIKE %d",
				'_tutor_enrolled_by_order_id',
				$order_id
			)
		);

		if ( $enrollments ) {
			$enrollment_ids = array_column( $enrollments, 'post_id' );
		}

		return $enrollment_ids;
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
	 * @return bool
	 */
	public function delete_order( $order_id ) {
		global $wpdb;
		$order_ids = is_array( $order_id ) ? $order_id : array( intval( $order_id ) );

		try {
			$wpdb->query( 'START TRANSACTION' );

			foreach ( $order_ids as $order_id ) {
				// Delete enrollments if exist.
				$enrollment_ids = $this->get_enrollment_ids( $order_id );
				if ( $enrollment_ids ) {
					QueryHelper::bulk_delete_by_ids( $wpdb->posts, $enrollment_ids );
					// After enrollment delete, delete the course progress.
					foreach ( $enrollment_ids as $enrollment_id ) {
						$course_id  = get_post_field( 'post_parent', $enrollment_id );
						$student_id = get_post_field( 'post_author', $enrollment_id );

						if ( $course_id && $student_id ) {
							tutor_utils()->delete_course_progress( $course_id, $student_id );
						}
					}
				}

				// Delete earnings.
				QueryHelper::delete(
					$wpdb->prefix . 'tutor_earnings',
					array(
						'order_id'   => $order_id,
						'process_by' => 'Tutor',
					)
				);

				// Now delete order.
				QueryHelper::delete( $this->table_name, array( 'id' => $order_id ) );
			}

			$wpdb->query( 'COMMIT' );
			return true;

		} catch ( \Throwable $th ) {
			$wpdb->query( 'ROLLBACK' );
			return false;
		}
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
	 * Get order of a user
	 *
	 * @since 3.0.0
	 *
	 * @param string $time_period $time_period Sorting time period,
	 * supported time periods are: today, monthly & yearly.
	 * @param string $start_date $start_date For date range sorting.
	 * @param string $end_date $end_date For date range sorting.
	 * @param int    $user_id  User id for fetching order list.
	 * @param int    $limit  Limit to fetch record.
	 * @param int    $offset  Offset to fetch record.
	 *
	 * @throws \Exception Throw exception if database error occur.
	 *
	 * @return array
	 */
	public function get_user_orders( $time_period = null, $start_date = null, $end_date = null, int $user_id = 0, $limit = 10, int $offset = 0 ) {
		$user_id = $user_id ? $user_id : get_current_user_id();

		$response = array(
			'results'     => array(),
			'total_count' => 0,
		);

		global $wpdb;

		$time_period_clause = '';
		$date_range_clause  = '';

		if ( $start_date && $end_date ) {
			$date_range_clause = $wpdb->prepare( 'AND DATE(created_at_gmt) BETWEEN %s AND %s', $start_date, $end_date );
		} else {
			if ( $time_period ) {
				if ( 'today' === $time_period ) {
					$time_period_clause = 'AND  DATE(o.created_at_gmt) = CURDATE()';
				} elseif ( 'monthly' === $time_period ) {
					$time_period_clause = 'AND  MONTH(o.created_at_gmt) = MONTH(CURDATE()) ';
				} else {
					$time_period_clause = 'AND  YEAR(o.created_at_gmt) = YEAR(CURDATE()) ';
				}
			}
		}

		$query = $wpdb->prepare(
			"SELECT
				SQL_CALC_FOUND_ROWS
				o.* 
				FROM $this->table_name AS o
				WHERE o.user_id = %d
				{$time_period_clause}
				{$date_range_clause}
				ORDER BY o.id DESC
				LIMIT %d OFFSET %d
			",
			$user_id,
			$limit,
			$offset
		);

		$results = $wpdb->get_results( $query );

		if ( $wpdb->last_error ) {
			throw new \Exception( $wpdb->last_error );
		} else {
			$response['results']     = $results;
			$response['total_count'] = is_array( $results ) && count( $results ) ? (int) $wpdb->get_var( 'SELECT FOUND_ROWS()' ) : 0;
		}

		return $response;
	}

	/**
	 * Get total discounts by user_id (instructor), optionally can set period ( today | monthly| yearly )
	 *
	 * Optionally can set start date & end date to get enrollment list from date range
	 *
	 * If period or date range not pass then it will return all time enrollment list
	 *
	 * @since 3.0.0
	 *
	 * @param int    $user_id User id, if user not have admin access
	 * then only this user's refund amount will fetched.
	 * @param string $period Time period.
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @param int    $course_id Course id.
	 *
	 * @return array
	 */
	public function get_discounts_by_user( int $user_id, string $period = '', $start_date = '', string $end_date = '', int $course_id = 0 ): array {
		$response = array(
			'discounts'       => array(),
			'total_discounts' => 0,
		);

		global $wpdb;

		$user_clause       = '';
		$date_range_clause = '';
		$period_clause     = '';
		$course_clause     = '';
		$group_clause      = ' GROUP BY DATE(date_format) ';
		$discount_clause   = 'o.coupon_amount as total';

		if ( $start_date && $end_date ) {
			$date_range_clause = $wpdb->prepare(
				'AND o.created_at_gmt BETWEEN %s AND %s',
				$start_date,
				$end_date
			);
		} else {
			$period_clause = QueryHelper::get_period_clause( 'o.created_at_gmt', $period );
		}

		if ( 'today' !== $period ) {
			$group_clause = ' GROUP BY MONTH(date_format) ';
		}

		if ( $course_id ) {
			$course_clause   = $wpdb->prepare( 'AND i.item_id = %d', $course_id );
			$discount_clause = 'i.regular_price - i.discount_price AS total';
		}

		$item_table = $wpdb->prefix . 'tutor_order_items';

		if ( $course_id ) {
			if ( $user_id ) {
				$user_clause = $wpdb->prepare( 'AND c.post_author = %d', $user_id );
			}

			$discounts = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT 
						i.item_id AS course_id,
						SUM(
							COALESCE(o.coupon_amount, 0) +
							COALESCE(
								IF(
									o.discount_type = 'percentage', 
									COALESCE(o.subtotal_price * (o.discount_amount / 100), 0), 
									COALESCE(o.discount_amount, 0)
								), 
								0
							)
						) AS total,
						o.created_at_gmt AS date_format
					FROM 
						{$this->table_name} o
					JOIN 
						{$item_table} i ON o.id = i.order_id
					JOIN 
						{$wpdb->posts} c
						ON c.ID = i.item_id
						AND c.post_type = %s
					WHERE 
						1 = 1
						AND i.item_id = %d
						{$user_clause}
						{$period_clause}
						{$date_range_clause}
					{$group_clause}
					",
					tutor()->course_post_type,
					$course_id
				)
			);
		} else {
			if ( $user_id ) {
				$user_clause = $wpdb->prepare( "AND %d = (SELECT user_id FROM {$wpdb->tutor_earnings} WHERE order_status = 'completed' LIMIT 1) ", $user_id );
			}

			$discounts = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT 
					SUM(
						COALESCE(o.coupon_amount, 0) +
						COALESCE(
							IF(
								o.discount_type = 'percentage', 
								COALESCE(o.subtotal_price * (o.discount_amount / 100), 0), 
								COALESCE(o.discount_amount, 0)
							), 
							0
						)
					) AS total,
					o.created_at_gmt AS date_format
					FROM {$this->table_name} AS o
					WHERE 1 = %d
					AND o.order_status = 'completed'
					{$user_clause}
					{$period_clause}
					{$date_range_clause}
					{$course_clause}
					{$group_clause}
					HAVING total > 0
					",
					1
				)
			);
		}

		$total_discount = 0;
		$discount_items = array();

		$response = array(
			'discounts'       => array(),
			'total_discounts' => 0,
		);

		if ( $discounts ) {
			foreach ( $discounts as $discount ) {
				$total_discount  += $discount->total;
				$discount_items[] = $discount;

				// Split each discount.
				list( $admin_discount, $instructor_discount ) = array_values( tutor_split_amounts( $discount->total ) );

				$discount->total = is_admin() ? $admin_discount : $instructor_discount;
			}

			list( $admin_total, $instructor_total ) = array_values( tutor_split_amounts( $total_discount ) );

			$response['discounts']       = $discount_items;
			$response['total_discounts'] = is_admin() ? $admin_total : $instructor_total;
		}

		return $response;
	}

	/**
	 * Get total refunds by user_id (instructor), optionally can set period ( today | monthly| yearly )
	 *
	 * Optionally can set start date & end date to get enrollment list from date range
	 *
	 * If period or date range not pass then it will return all time enrollment list
	 *
	 * @since 3.0.0
	 *
	 * @param int    $user_id User id, if user not have admin access
	 * then only this user's refund amount will fetched.
	 * @param string $period Time period.
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @param int    $course_id Course id.
	 *
	 * @return array
	 */
	public function get_refunds_by_user( int $user_id, string $period = '', $start_date = '', string $end_date = '', int $course_id = 0 ): array {
		$response = array(
			'refunds'       => array(),
			'total_refunds' => 0,
		);

		global $wpdb;

		$user_clause       = '';
		$date_range_clause = '';
		$period_clause     = '';
		$course_clause     = '';
		$commission_clause = '';
		$group_clause      = ' GROUP BY DATE(o.created_at_gmt) ';

		if ( $start_date && $end_date ) {
			$date_range_clause = $wpdb->prepare(
				'AND o.created_at_gmt BETWEEN %s AND %s',
				$start_date,
				$end_date
			);
			$group_clause      = ' GROUP BY DATE(o.created_at_gmt) ';

		} else {
			$period_clause = QueryHelper::get_period_clause( 'o.created_at_gmt', $period );
		}

		if ( 'today' !== $period ) {
			$group_clause = ' GROUP BY MONTH(o.created_at_gmt) ';
		}

		if ( $course_id ) {
			if ( $user_id ) {
				$user_clause = $wpdb->prepare( 'AND c.post_author = %d', $user_id );
			}
		} else {
			if ( $user_id ) {
				$user_clause = $wpdb->prepare( 'AND c.post_author = %d', $user_id );
			}
		}

		// Refund query logic remains the same.
		$item_table = $wpdb->prefix . 'tutor_order_items';

		if ( $course_id ) {
			$refunds = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT 
						i.item_id AS course_id,
						ROUND(
							SUM(
								o.refund_amount * 
								(
									CASE 
										WHEN i.discount_price THEN i.discount_price
										WHEN i.sale_price > 0 THEN i.sale_price
										ELSE i.regular_price
									END / o.total_price
								)
							), 2
						) AS total
					FROM 
						{$this->table_name} o
					JOIN 
						{$item_table} i ON o.id = i.order_id
					JOIN 
						{$wpdb->posts} c
						ON c.ID = i.item_id
						AND c.post_type = %s
					WHERE 
						o.refund_amount > 0
						AND i.item_id = %d
						{$user_clause}
						{$period_clause}
						{$date_range_clause}
					{$group_clause},
					i.item_id
					",
					tutor()->course_post_type,
					$course_id
				)
			);
		} else {
			$earning_table = $wpdb->tutor_earnings;
			if ( $user_id ) {
				$user_clause = "AND {$user_id} = (SELECT user_id FROM {$earning_table} LIMIT 1)";
			}

			$refunds = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT 
					COALESCE(SUM(o.refund_amount), 0) AS total,
					created_at_gmt AS date_format
					FROM {$this->table_name} AS o
					-- LEFT JOIN {$item_table} AS i ON i.order_id = o.id
					-- LEFT JOIN {$wpdb->posts} AS c ON c.id = i.item_id
					WHERE 1 = %d
					AND o.refund_amount > %d
					{$user_clause}
					{$period_clause}
					{$date_range_clause}
					{$group_clause},
					o.id",
					1,
					0
				)
			);
		}

		$total_refund = 0;

		foreach ( $refunds as $refund ) {
			$total_refund += $refund->total;

			// Update total amount from list.
			$split_refund  = (object) tutor_split_amounts( $refund->total );
			$refund->total = is_admin() ? $split_refund->admin : $split_refund->instructor;
		}

		$split_total_refund = (object) tutor_split_amounts( $total_refund );

		$response = array(
			'refunds'       => $refunds,
			'total_refunds' => is_admin() ? $split_total_refund->admin : $split_total_refund->instructor,
		);

		return $response;
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
	 * Add a discount to an order.
	 *
	 * This function updates the order in the database with the provided discount details.
	 * It updates the discount type, discount amount, and discount reason for the given order ID.
	 *
	 * @since 3.0.0
	 *
	 * @param object $data An object containing the discount details:
	 *                     - $data->order_id       (int)    The ID of the order.
	 *                     - $data->discount_type  (string) The type of the discount.
	 *                     - $data->discount_amount(float)  The amount of the discount.
	 *                     - $data->discount_reason(string) The reason for the discount.
	 *
	 * @return bool True on successful update, false on failure.
	 */
	public function add_order_discount( object $data ) {
		$response = QueryHelper::update(
			$this->table_name,
			array(
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
	 * Calculate discount amount.
	 *
	 * @since 3.0.0
	 *
	 * @param string $discount_type The type of discount ('percent' or 'flat').
	 * @param float  $discount_amount The amount of discount to apply.
	 * @param float  $sub_total The subtotal amount before applying the discount.
	 *
	 * @return float discount amount.
	 */
	public function calculate_discount_amount( $discount_type, $discount_amount, $sub_total ) {
		if ( 'percentage' === $discount_type ) {
			$discounted_price = (float) $sub_total * ( ( (float) $discount_amount / 100 ) );
		} else {
			$discounted_price = (float) $discount_amount;
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

		$table     = $wpdb->prefix . 'tutor_ordermeta';
		$meta_keys = array( OrderActivitiesModel::META_KEY_REFUND, OrderActivitiesModel::META_KEY_PARTIALLY_REFUND );

		$where          = array(
			'meta_key' => $meta_keys,
			'order_id' => $order_id,
		);
		$refund_records = QueryHelper::get_all( $table, $where, 'created_at_gmt' );

		$refund_amount = 0;

		foreach ( $refund_records as $refund ) {
			$refund_data = json_decode( $refund->meta_value );

			if ( ! empty( $refund_data->amount ) ) {
				$refund_amount += (float) $refund_data->amount;
			}
		}

		return $refund_amount;
	}

	/**
	 * Get order status based on the payment status
	 *
	 * @since 3.0.0
	 *
	 * @param string $payment_status Order payment status.
	 *
	 * @return string
	 */
	public function get_order_status_by_payment_status( $payment_status ) {
		$status = '';

		switch ( $payment_status ) {
			case self::PAYMENT_PAID:
				$status = self::ORDER_COMPLETED;
				break;
			case self::PAYMENT_UNPAID:
				$status = self::ORDER_INCOMPLETE;
				break;
			case self::PAYMENT_PARTIALLY_REFUNDED:
				$status = self::ORDER_COMPLETED;
				break;
			case self::PAYMENT_REFUNDED:
				$status = self::ORDER_CANCELLED;
				break;
			case self::PAYMENT_FAILED:
				$status = self::ORDER_CANCELLED;
				break;
			case self::ORDER_TRASH:
				$status = self::ORDER_TRASH;
				break;
			case 'delete':
				$status = self::ORDER_CANCELLED;
				break;
			case self::ORDER_CANCELLED:
				$status = self::ORDER_CANCELLED;
				break;
		}

		return $status;
	}

	/**
	 * Calculate order price
	 *
	 * @since 3.0.0
	 *
	 * @param array $items Order items, multi or single dimensional arr.
	 *
	 * @return object {subtotal => 10, total => 10}
	 */
	public static function calculate_order_price( array $items ) {
		$subtotal = 0;
		$total    = 0;

		if ( isset( $items[0] ) ) {
			foreach ( $items as $item ) {
				$regular_price  = tutor_get_locale_price( $item['regular_price'] );
				$sale_price     = is_null( $item['sale_price'] ) || '' === $item['sale_price'] ? null : tutor_get_locale_price( $item['sale_price'] );
				$discount_price = is_null( $item['discount_price'] ) || '' === $item['discount_price'] ? null : tutor_get_locale_price( $item['discount_price'] );

				// Subtotal is the original price (regular price).
				$item_subtotal = $regular_price;
				$item_total    = $regular_price;

				// Determine the total based on sale price and discount.
				if ( ! is_null( $sale_price ) && $sale_price < $regular_price ) {
					$item_subtotal = $sale_price;
					$item_total    = $sale_price;
				} else {
					// If there's a discount, apply it to the total price.
					if ( ! is_null( $discount_price ) && $discount_price >= 0 ) {
						$item_total = max( 0, $discount_price ); // Ensure total doesn't go below 0.
					}
				}

				// $subtotal += $item_subtotal;
				$subtotal += $regular_price;
				$total    += $item_total;
			}
		} else {
			// for single dimensional array.
			$regular_price  = tutor_get_locale_price( $items['regular_price'] );
			$sale_price     = is_null( $items['sale_price'] ) || '' === $items['sale_price'] ? null : tutor_get_locale_price( $items['sale_price'] );
			$discount_price = is_null( $items['discount_price'] ) || '' === $items['discount_price'] ? null : tutor_get_locale_price( $items['discount_price'] );

			// Subtotal is the original price (regular price).
			$item_subtotal = $regular_price;
			$item_total    = $regular_price;

			// Determine the total based on sale price and discount.
			if ( ! is_null( $sale_price ) && $sale_price < $regular_price ) {
				$item_subtotal = $sale_price;
				$item_total    = $sale_price;
			} else {
				// If there's a discount, apply it to the total price.
				if ( ! is_null( $discount_price ) && $discount_price >= 0 ) {
					$item_total = max( 0, $discount_price ); // Ensure total doesn't go below 0.
				}
			}

			// $subtotal = $item_subtotal;
			$subtotal = $regular_price;
			$total    = $item_total;
		}

		return (object) array(
			'subtotal' => tutor_get_locale_price( $subtotal ),
			'total'    => tutor_get_locale_price( $total ),
		);
	}

	/**
	 * Check has exclusive type tax.
	 *
	 * @since 3.0.0
	 *
	 * @param object $order order object.
	 *
	 * @return boolean
	 */
	public static function has_exclusive_tax( $order ) {
		return self::TAX_TYPE_EXCLUSIVE === $order->tax_type && $order->tax_rate > 0 && $order->tax_amount > 0;
	}

	/**
	 * Check has inclusive type tax.
	 *
	 * @since 3.0.0
	 *
	 * @param object $order order object.
	 *
	 * @return boolean
	 */
	public static function has_inclusive_tax( $order ) {
		return self::TAX_TYPE_INCLUSIVE === $order->tax_type && $order->tax_rate > 0 && $order->tax_amount > 0;
	}

	/**
	 * Get an item
	 *
	 * @since 3.0.0
	 *
	 * @param integer $item_id Item id.
	 *
	 * @return mixed
	 */
	public function get_item( int $item_id ) {
		return QueryHelper::get_row(
			$this->order_item_table,
			array(
				'item_id' => $item_id,
			),
			'id'
		);
	}

	/**
	 * Get sellable price
	 *
	 * @since 3.0.0
	 *
	 * @param mixed $regular_price Regular price.
	 * @param mixed $sale_price Sale price.
	 * @param mixed $discount_price Discount price.
	 *
	 * @return float item sellable price
	 */
	public static function get_item_sellable_price( $regular_price, $sale_price = null, $discount_price = null ) {
		// Ensure prices are numeric and properly formatted.
		$sellable_price = (
			! empty( $sale_price )
			? $sale_price
			: (
				( ! is_null( $discount_price ) && '' !== $discount_price ) && $discount_price >= 0
				? $discount_price
				: $regular_price
			)
		);

		return $sellable_price;
	}

	/**
	 * Get item sold price
	 *
	 * @since 3.0.0
	 *
	 * @param mixed $item_id Item id.
	 * @param bool  $format Item id.
	 *
	 * @return mixed item sellable price
	 */
	public static function get_item_sold_price( $item_id, $format = true ) {
		$item = ( new self() )->get_item( $item_id );

		if ( $item ) {
			$sold_price = self::get_item_sellable_price( $item->regular_price, $item->sale_price, $item->discount_price );

			return $format ? tutor_get_formatted_price( $sold_price ) : $sold_price;
		}

		return 0;
	}

	/**
	 * Should show pay btn to the user
	 *
	 * @since 3.0.0
	 *
	 * @param object $order Order object.
	 *
	 * @return boolean
	 */
	public static function should_show_pay_btn( object $order ) {
		$order_items            = ( new self() )->get_order_items_by_id( $order->id );
		$is_enrolled_any_course = false;
		$is_incomplete_payment  = ! empty( $order->payment_method ) && self::ORDER_INCOMPLETE === $order->order_status;
		$is_manual_payment      = $order->payment_method ? self::is_manual_payment( $order->payment_method ) : true;

		if ( $is_incomplete_payment && ! $is_manual_payment && $order_items ) {
			if ( self::TYPE_SINGLE_ORDER === $order->order_type ) {
				foreach ( $order_items as $item ) {
					$course_id = $item->id;
					if ( $course_id ) {
						$is_enrolled = tutor_utils()->is_enrolled( $course_id );
						if ( $is_enrolled ) {
							$is_enrolled_any_course = true;
							break;
						}
					}
				}
			} else {
				if ( tutor_utils()->count( $order_items ) ) {
					$course_id = apply_filters( 'tutor_subscription_course_by_plan', $order_items[0]->id );
					if ( tutor_utils()->is_enrolled( $course_id ) ) {
						$is_enrolled_any_course = true;
					}
				}
			}
		}

		return apply_filters( 'tutor_should_show_pay_btn', $is_incomplete_payment && ! $is_manual_payment && ! $is_enrolled_any_course );
	}

	/**
	 * Check is manual payment
	 *
	 * @since 3.0.0
	 *
	 * @param string $method_name Payment method name.
	 *
	 * @return boolean
	 */
	public static function is_manual_payment( $method_name ) {
		$payment_methods = tutor_get_manual_payment_gateways();

		$is_manual_payment = false;
		foreach ( $payment_methods as $payment_method ) {
			$is_manual_payment = $payment_method->name === $method_name;
		}

		return $is_manual_payment;
	}

	/**
	 * Render pay button
	 *
	 * @since 3.0.1
	 *
	 * @param int|object $order Order id or object.
	 *
	 * @return void
	 */
	public static function render_pay_button( $order ) {
		if ( is_numeric( $order ) ) {
			$order = ( new self() )->get_order_by_id( $order );
		}

		if ( self::should_show_pay_btn( $order ) ) {
			?>
			<form method="post">
				<?php tutor_nonce_field(); ?>
				<input type="hidden" name="tutor_action" value="tutor_pay_incomplete_order">
				<input type="hidden" name="order_id" value="<?php echo esc_attr( $order->id ); ?>">
				<button type="submit" class="tutor-btn tutor-btn-sm tutor-btn-outline-primary">
					<?php esc_html_e( 'Pay', 'tutor' ); ?>
				</button>
			</form>
			<?php
		}
	}
}
