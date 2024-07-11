<?php
/**
 * Manage Order
 *
 * @package Tutor\Ecommerce
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\Ecommerce;

use TUTOR\Backend_Page_Trait;
use Tutor\Helpers\HttpHelper;
use Tutor\Helpers\QueryHelper;
use Tutor\Helpers\ValidationHelper;
use TUTOR\Input;
use Tutor\Models\CourseModel;
use Tutor\Models\OrderActivitiesModel;
use Tutor\Models\OrderModel;
use Tutor\Traits\JsonResponse;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * OrderController class
 *
 * @since 3.0.0
 */
class OrderController {

	/**
	 * Order page slug
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const PAGE_SLUG = 'tutor_orders';

	/**
	 * Order model
	 *
	 * @since 3.0.0
	 *
	 * @var OrderModel
	 */
	private $model;

	/**
	 * Trait for utilities
	 *
	 * @var $page_title
	 */
	use Backend_Page_Trait;

	/**
	 * Trait for sending JSON response
	 */
	use JsonResponse;

	/**
	 * Page Title
	 *
	 * @var $page_title
	 */
	public $page_title;

	/**
	 * Constructor.
	 *
	 * Initializes the Orders class, sets the page title, and optionally registers
	 * hooks for handling AJAX requests related to order data, bulk actions, order status updates,
	 * and order deletions.
	 *
	 * @param bool $register_hooks Whether to register hooks for handling requests. Default is true.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function __construct( $register_hooks = true ) {
		$this->page_title = __( 'Orders', 'tutor' );
		$this->model      = new OrderModel();

		if ( $register_hooks ) {
			/**
			 * Handle AJAX request for getting order related data by order ID.
			 *
			 * @since 3.0.0
			 */
			add_action( 'wp_ajax_tutor_order_details', array( $this, 'get_order_by_id' ) );

			/**
			 * Handle AJAX request for marking an order as paid by order ID.
			 *
			 * @since 3.0.0
			 */
			add_action( 'wp_ajax_tutor_order_paid', array( $this, 'order_mark_as_paid' ) );

			/**
			 * Handle AJAX request for marking an order's refund action.
			 *
			 * @since 3.0.0
			 */
			add_action( 'wp_ajax_tutor_order_refund', array( $this, 'make_refund' ) );

			/**
			 * Handle AJAX request for adding an order comment.
			 *
			 * @since 3.0.0
			 */
			add_action( 'wp_ajax_tutor_order_comment', array( $this, 'add_comment' ) );

			/**
			 * Handle bulk action
			 *
			 * @since 3.0.0
			 */
			add_action( 'wp_ajax_tutor_order_bulk_action', array( $this, 'bulk_action_handler' ) );
			/**
			 * Handle ajax request for updating order status
			 *
			 * @since 3.0.0
			 */
			add_action( 'wp_ajax_tutor_change_order_status', array( $this, 'tutor_change_order_status' ) );
			/**
			 * Handle ajax request for delete order
			 *
			 * @since 3.0.0
			 */
			add_action( 'wp_ajax_tutor_order_delete', array( $this, 'tutor_order_delete' ) );
		}
	}

	/**
	 * Get order page url
	 *
	 * @since 3.0.0
	 *
	 * @param boolean $is_admin Whether to get admin or frontend url.
	 *
	 * @return string
	 */
	public static function get_order_page_url( bool $is_admin = true ) {
		if ( $is_admin ) {
			return admin_url( 'admin.php?page=' . self::PAGE_SLUG );
		} else {
			return tutor_utils()->get_tutor_dashboard_url() . '/orders';
		}
	}

	/**
	 * Retrieve order data by order ID and respond with JSON.
	 *
	 * This function retrieves the order ID from the POST request, validates it,
	 * fetches the corresponding order data using the OrderModel class, and returns
	 * a JSON response with the order data or an error message.
	 *
	 * If the order ID is not provided, it responds with a "Bad Request" status.
	 * If the order is not found, it responds with a "Not Found" status.
	 * Otherwise, it responds with the order data and a success message.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function get_order_by_id() {
		if ( ! tutor_utils()->is_nonce_verified() ) {
			$this->json_response( tutor_utils()->error_message( 'nonce' ), null, HttpHelper::STATUS_BAD_REQUEST );
		}

		$order_id = Input::post( 'order_id' );

		if ( empty( $order_id ) ) {
			$this->json_response(
				__( 'Order ID is required', 'tutor' ),
				null,
				HttpHelper::STATUS_BAD_REQUEST
			);
		}

		$order_data = $this->model->get_order_by_id( $order_id );

		if ( ! $order_data ) {
			$this->json_response(
				__( 'Order not found', 'tutor' ),
				null,
				HttpHelper::STATUS_NOT_FOUND
			);
		}

		$this->json_response(
			__( 'Order retrieved successfully', 'tutor' ),
			$order_data
		);
	}

	/**
	 * Mark an order as paid.
	 *
	 * This function verifies a nonce for security, constructs a payload object with
	 * the order ID, note, and payment status, and updates the payment status of the order
	 * to 'paid'. It sends a JSON response indicating the success or failure of the operation.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function order_mark_as_paid() {
		if ( ! tutor_utils()->is_nonce_verified() ) {
			$this->json_response( tutor_utils()->error_message( 'nonce' ), null, HttpHelper::STATUS_BAD_REQUEST );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			$this->json_response( tutor_utils()->error_message( HttpHelper::STATUS_UNAUTHORIZED ), null, HttpHelper::STATUS_UNAUTHORIZED );
		}

		$params = array(
			'order_id' => Input::post( 'order_id' ),
			'note'     => Input::post( 'note' ),
		);

		// Validate request.
		$validation = $this->validate( $params );
		if ( ! $validation->success ) {
			$this->json_response(
				tutor_utils()->error_message( HttpHelper::STATUS_BAD_REQUEST ),
				$validation->errors,
				HttpHelper::STATUS_BAD_REQUEST
			);
		}

		do_action( 'tutor_before_order_mark_as_paid', $params );

		$payload                 = new \stdClass();
		$payload->order_id       = $params['order_id'];
		$payload->note           = $params['note'];
		$payload->payment_status = $this->model::PAYMENT_PAID;

		if ( empty( $payload->order_id ) ) {
			$this->json_response(
				__( 'Order ID is required', 'tutor' ),
				null,
				HttpHelper::STATUS_BAD_REQUEST
			);
		}

		$response = $this->model->payment_status_update( $payload );

		do_action( 'tutor_after_order_mark_as_paid', $params );

		if ( ! $response ) {
			$this->json_response(
				__( 'Failed to update order payment status', 'tutor' ),
				null,
				HttpHelper::STATUS_INTERNAL_SERVER_ERROR
			);
		}

		$this->json_response( __( 'Order payment status successfully updated', 'tutor' ) );
	}

	/**
	 * Handle order refund process.
	 *
	 * This method processes the refund for an order. It verifies the nonce and user capabilities,
	 * triggers necessary actions before and after the refund process, validates input data, and
	 * interacts with the OrderActivitiesModel to record the refund metadata. If any validation
	 * fails or the refund process encounters an error, it returns an appropriate JSON response.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function make_refund() {
		if ( ! tutor_utils()->is_nonce_verified() ) {
			$this->json_response( tutor_utils()->error_message( 'nonce' ), null, HttpHelper::STATUS_BAD_REQUEST );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			$this->json_response( tutor_utils()->error_message( HttpHelper::STATUS_UNAUTHORIZED ), null, HttpHelper::STATUS_UNAUTHORIZED );
		}

		$params = array(
			'order_id'   => Input::post( 'order_id' ),
			'meta_key'   => OrderActivitiesModel::META_KEY_REFUND,
			'meta_value' => Input::post( 'meta_value' ),
		);

		do_action( 'tutor_before_order_refund', $params );

		// Validate request.
		$validation = $this->validate( $params );
		if ( ! $validation->success ) {
			$this->json_response(
				tutor_utils()->error_message( HttpHelper::STATUS_BAD_REQUEST ),
				$validation->errors,
				HttpHelper::STATUS_BAD_REQUEST
			);
		}

		$payload             = new \stdClass();
		$payload->order_id   = $params['order_id'];
		$payload->meta_key   = $params['meta_key'];
		$payload->meta_value = $params['meta_value'];

		$activity_model = new OrderActivitiesModel();
		$response       = $activity_model->add_order_meta( $payload );

		do_action( 'tutor_after_order_refund', $params );

		if ( ! $response ) {
			$this->json_response(
				__( 'Failed to make refund', 'tutor' ),
				null,
				HttpHelper::STATUS_INTERNAL_SERVER_ERROR
			);
		}

		$this->json_response( __( 'Order refund successful', 'tutor' ) );
	}

	/**
	 * Handle adding a comment to an order.
	 *
	 * This method processes the addition of a comment to an order. It verifies the nonce and user capabilities,
	 * triggers necessary actions before and after the comment addition, validates input data, and
	 * interacts with the OrderActivitiesModel to record the comment metadata. If any validation
	 * fails or the comment addition process encounters an error, it returns an appropriate JSON response.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function add_comment() {
		if ( ! tutor_utils()->is_nonce_verified() ) {
			$this->json_response( tutor_utils()->error_message( 'nonce' ), null, HttpHelper::STATUS_BAD_REQUEST );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			$this->json_response( tutor_utils()->error_message( HttpHelper::STATUS_UNAUTHORIZED ), null, HttpHelper::STATUS_UNAUTHORIZED );
		}

		$params = array(
			'order_id'   => Input::post( 'order_id' ),
			'meta_key'   => OrderActivitiesModel::META_KEY_COMMENT,
			'meta_value' => Input::post( 'comment' ),
		);

		do_action( 'tutor_before_order_comment', $params );

		// Validate request.
		$validation = $this->validate( $params );
		if ( ! $validation->success ) {
			$this->json_response(
				tutor_utils()->error_message( HttpHelper::STATUS_BAD_REQUEST ),
				$validation->errors,
				HttpHelper::STATUS_BAD_REQUEST
			);
		}

		$payload             = new \stdClass();
		$payload->order_id   = $params['order_id'];
		$payload->meta_key   = $params['meta_key'];
		$payload->meta_value = wp_json_encode( (object) array( 'message' => $params['meta_value'] ) );

		$activity_model = new OrderActivitiesModel();
		$response       = $activity_model->add_order_meta( $payload );

		do_action( 'tutor_after_order_comment', $params );

		if ( ! $response ) {
			$this->json_response(
				__( 'Failed to make a comment', 'tutor' ),
				null,
				HttpHelper::STATUS_INTERNAL_SERVER_ERROR
			);
		}

		$this->json_response( __( 'Order comment successful added', 'tutor' ) );
	}

	/**
	 * Prepare bulk actions that will show on dropdown options
	 *
	 * @return array
	 * @since 3.0.0
	 */
	public function prepare_bulk_actions(): array {
		$actions = array(
			$this->bulk_action_default(),
		);

		$active_tab = Input::get( 'data', '' );

		if ( $this->model::ORDER_TRASH !== $active_tab ) {
			$actions[] = $this->bulk_action_mark_order_trash();
		}

		if ( ! empty( $active_tab ) ) {
			switch ( $active_tab ) {
				case $this->model::ORDER_INCOMPLETE:
					$actions[] = $this->bulk_action_mark_order_paid();
					break;
				case $this->model::ORDER_COMPLETED:
					$actions[] = $this->bulk_action_mark_order_unpaid();
					break;
				case $this->model::ORDER_TRASH:
					$actions[] = $this->bulk_action_delete();
					break;
				default:
					// code...
					break;
			}
		}

		return apply_filters( 'tutor_order_bulk_actions', $actions );
	}

	/**
	 * Available tabs that will visible on the right side of page navbar
	 *
	 * @return array
	 *
	 * @since 3.0.0
	 */
	public function tabs_key_value(): array {
		$url = get_pagenum_link();

		$date           = Input::get( 'date', '' );
		$payment_status = Input::get( 'payment-status', '' );
		$search         = Input::get( 'search', '' );

		$where = array();

		if ( ! empty( $date ) ) {
			$where['created_at_gmt'] = tutor_get_formated_date( 'Y-m-d', $date );
		}

		if ( ! empty( $payment_status ) ) {
			$where['payment_status'] = $payment_status;
		}

		$order_status = $this->model->get_order_status();

		$tabs = array();

		$tabs [] = array(
			'key'   => 'all',
			'title' => __( 'All', 'tutor' ),
			'value' => $this->model->get_order_count( $where, $search ),
			'url'   => $url . '&data=all',
		);

		foreach ( $order_status as $key => $value ) {
			$where['order_status'] = $key;

			$tabs[] = array(
				'key'   => $key,
				'title' => $value,
				'value' => $this->model->get_order_count( $where, $search ),
				'url'   => $url . '&data=' . $key,
			);
		}

		return apply_filters( 'tutor_order_tabs', $tabs );
	}

	/**
	 * Count orders by status & filters
	 * Count all | min | published | pending | draft
	 *
	 * @param string $status | required.
	 * @param string $order_id selected order id | optional.
	 * @param string $date selected date | optional.
	 * @param string $search_term search by user name or email | optional.
	 *
	 * @return int
	 *
	 * @since 3.0.0
	 */
	protected static function count_order( string $status, $order_id = '', $date = '', $search_term = '' ): int {
		$user_id     = get_current_user_id();
		$status      = sanitize_text_field( $status );
		$order_id    = sanitize_text_field( $order_id );
		$date        = sanitize_text_field( $date );
		$search_term = sanitize_text_field( $search_term );

		$args = array(
			'post_type' => tutor()->order_post_type,
		);

		if ( 'all' === $status || 'mine' === $status ) {
			$args['post_status'] = array( 'publish', 'pending', 'draft', 'private', 'future' );
		} else {
			$args['post_status'] = array( $status );
		}

		// Author query.
		if ( 'mine' === $status || ! current_user_can( 'administrator' ) ) {
			$args['author'] = $user_id;
		}

		$date_filter = sanitize_text_field( $date );

		$year  = date( 'Y', strtotime( $date_filter ) );
		$month = date( 'm', strtotime( $date_filter ) );
		$day   = date( 'd', strtotime( $date_filter ) );

		// Add date query.
		if ( '' !== $date_filter ) {
			$args['date_query'] = array(
				array(
					'year'  => $year,
					'month' => $month,
					'day'   => $day,
				),
			);
		}

		if ( '' !== $order_id ) {
			$args['p'] = $order_id;
		}

		// Search filter.
		if ( '' !== $search_term ) {
			$args['s'] = $search_term;
		}

		$the_query = new \WP_Query( $args );

		return ! is_null( $the_query ) && isset( $the_query->found_posts ) ? $the_query->found_posts : $the_query;
	}

	/**
	 * Handle order bulk action
	 *
	 * @since 3.0.0
	 *
	 * @return void send wp_json response
	 */
	public function bulk_action_handler() {

		tutor_utils()->checking_nonce();

		// Check if user is privileged.
		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$request     = Input::sanitize_array( $_POST );
		$bulk_action = $request['bulk-action'];

		$bulk_ids = isset( $request['bulk-ids'] ) ? array_map( 'intval', explode( ',', $request['bulk-ids'] ) ) : array();

		$allowed_bulk_actions = array(
			$this->model::PAYMENT_PAID,
			$this->model::PAYMENT_UNPAID,
			$this->model::ORDER_TRASH,
			'delete',
		);

		if ( ! in_array( $bulk_action, $allowed_bulk_actions, true ) ) {
			wp_send_json_error( __( 'Please select appropriate action', 'tutor' ) );
		}

		if ( empty( $bulk_ids ) ) {
			wp_send_json_error( __( 'No items selected for the bulk action.', 'tutor' ) );
		}

		do_action( 'tutor_before_order_bulk_action', $bulk_action, $bulk_ids );

		$response = false;
		if ( 'delete' === $bulk_action ) {
			$response = $this->model->delete_order( $bulk_ids );
		} else {
			$data = null;

			switch ( $bulk_action ) {
				case $this->model::PAYMENT_PAID:
					$data = array(
						'order_status' => $this->model::ORDER_COMPLETED,
					);
					break;
				case $this->model::PAYMENT_UNPAID:
					$data = array(
						'order_status' => $this->model::ORDER_INCOMPLETE,
					);
					break;
				case $this->model::ORDER_TRASH:
					$data = array(
						'order_status' => $this->model::ORDER_TRASH,
					);
					break;
				default:
					// code...
					break;
			}

			if ( ! empty( $data ) ) {
				$response = $this->model->update_order( $bulk_ids, $data );
			}
		}

		if ( $response ) {
			do_action( 'tutor_after_order_bulk_action', $bulk_action, $bulk_ids );
			wp_send_json_success( __( 'Order updated successfully.', 'tutor' ) );
		} else {
			wp_send_json_error( __( 'Failed to update order.', 'tutor' ) );
		}
	}

	/**
	 * Handle ajax request for updating order status
	 *
	 * @return void
	 * @since 3.0.0
	 */
	public static function tutor_change_order_status() {
		tutor_utils()->checking_nonce();

		// Check if user is privileged.
		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$status = Input::post( 'status' );
		$id     = Input::post( 'id' );
		$order  = get_post( $id );

		if ( OrderModel::POST_TYPE !== $order->post_type ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$args = array(
			'ID'          => $id,
			'post_status' => $status,
		);

		if ( OrderModel::STATUS_FUTURE === $order->post_status && OrderModel::STATUS_PUBLISH === $status ) {
			$args['post_status']   = OrderModel::STATUS_PUBLISH;
			$args['post_date']     = current_time( 'mysql' );
			$args['post_date_gmt'] = current_time( 'mysql', 1 );
		}

		wp_update_post( $args );
		wp_send_json_success();
		exit;
	}

	/**
	 * Handle ajax request for deleting order
	 *
	 * @since 3.0.0
	 *
	 * @return void JSON response
	 */
	public static function tutor_order_delete() {
		tutor_utils()->checking_nonce();

		$user_id  = get_current_user_id();
		$order_id = Input::post( 'id', 0, Input::TYPE_INT );

		// Check if user is privileged.
		if ( ! tutor_utils()->has_user_role( array( 'administrator', 'editor' ) ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$delete = CourseModel::delete_course( $order_id );

		if ( $delete ) {
			wp_send_json_success( __( 'Order has been deleted ', 'tutor' ) );
		} else {
			wp_send_json_error( __( 'Order delete failed ', 'tutor' ) );
		}

		exit;
	}

	/**
	 * Execute bulk delete action
	 *
	 * @param string $bulk_ids ids that need to update.
	 * @return bool
	 * @since 3.0.0
	 */
	public function bulk_delete_order( $bulk_ids ): bool {
		$bulk_ids = explode( ',', sanitize_text_field( $bulk_ids ) );

		$response = false;
		try {
			$response = QueryHelper::bulk_delete_by_ids( $this->model->get_table_name(), $bulk_ids );
		} catch ( \Throwable $th ) {
			error_log( $th->getMessage() . ' Line: ' . $th->getLine() . ' File: ' . $th->getFile() );
		}

		return $response;
	}

	/**
	 * Update order status
	 *
	 * @param string $status for updating order status.
	 * @param string $bulk_ids comma separated ids.
	 *
	 * @return bool
	 *
	 * @since 3.0.0
	 */
	public static function update_order_status( string $status, $bulk_ids ): bool {
		global $wpdb;
		$post_table = $wpdb->posts;
		$status     = sanitize_text_field( $status );
		$bulk_ids   = sanitize_text_field( $bulk_ids );

		$ids       = array_map( 'intval', explode( ',', $bulk_ids ) );
		$in_clause = QueryHelper::prepare_in_clause( $ids );

		$update = $wpdb->query(
			$wpdb->prepare(
				"UPDATE {$post_table} SET post_status = %s WHERE ID IN ($in_clause)", //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$status
			)
		);

		return true;
	}

	/**
	 * Get orders
	 *
	 * @since 3.0.0
	 *
	 * @param integer $limit List limit.
	 * @param integer $offset List offset.
	 *
	 * @return array
	 */
	public function get_orders( $limit = 10, $offset = 0 ) {

		$active_tab = Input::get( 'data', 'all' );

		$date           = Input::get( 'date', '' );
		$search_term    = Input::get( 'search', '' );
		$payment_status = Input::get( 'payment-status', '' );

		$where_clause = array();

		if ( $date ) {
			$where_clause['date(o.created_at_gmt)'] = tutor_get_formated_date( '', $date );
		}

		if ( $payment_status ) {
			$where_clause['o.payment_status'] = $payment_status;
		}

		if ( 'all' !== $active_tab ) {
			$where_clause['o.order_status'] = $active_tab;
		}

		$list_order    = Input::get( 'order', 'DESC' );
		$list_order_by = 'id';

		return $this->model->get_orders( $where_clause, $search_term, $limit, $offset, $list_order_by, $list_order );
	}

	/**
	 * Validate input data based on predefined rules.
	 *
	 * This protected method validates the provided data array against a set of
	 * predefined validation rules. The rules specify that 'order_id' is required
	 * and must be numeric. The method will skip validation rules for fields that
	 * are not present in the data array.
	 *
	 * @since 3.0.0
	 *
	 * @param array $data The data array to validate.
	 *
	 * @return object The validation result. It returns validation object.
	 */
	protected function validate( array $data ) {

		$validation_rules = array(
			'order_id'   => 'required|numeric',
			'meta_key'   => 'required',
			'meta_value' => 'required',
		);

		// Skip validation rules for not available fields in data.
		foreach ( $validation_rules as $key => $value ) {
			if ( ! array_key_exists( $key, $data ) ) {
				unset( $validation_rules[ $key ] );
			}
		}

		return ValidationHelper::validate( $validation_rules, $data );
	}
}
