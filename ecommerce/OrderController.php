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

use TUTOR\Input;
use TUTOR\Earnings;
use Tutor\Models\OrderModel;
use TUTOR\Backend_Page_Trait;
use Tutor\Helpers\HttpHelper;
use Tutor\Models\CouponModel;
use Tutor\Models\CourseModel;
use Tutor\Helpers\QueryHelper;
use Tutor\Traits\JsonResponse;
use Tutor\Helpers\ValidationHelper;
use Tutor\Models\OrderActivitiesModel;

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
			 * Handle AJAX request for canceling an order status.
			 *
			 * @since 3.0.0
			 */
			add_action( 'wp_ajax_tutor_order_cancel', array( $this, 'order_cancel' ) );

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
			 * Handle AJAX request for add/update an order's discount action.
			 *
			 * @since 3.0.0
			 */
			add_action( 'wp_ajax_tutor_order_discount', array( $this, 'add_discount' ) );

			/**
			 * Handle bulk action
			 *
			 * @since 3.0.0
			 */
			add_action( 'wp_ajax_tutor_order_bulk_action', array( $this, 'bulk_action_handler' ) );
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
	 * Create order based on the arguments
	 *
	 * Note: This method assumes nonce & user cap has been validated.
	 *
	 * Note: This method will validate data so it could be
	 * used without validation.
	 *
	 * @since 3.0.0
	 *
	 * @param int    $user_id Typically student.
	 * @param array  $items Key value pairs based on order_items table.
	 * @param string $payment_status Order payment status.
	 * @param string $order_type Type single_order/subscription.
	 * @param mixed  $coupon_code Optional, if not provided automatic coupon.
	 * @param array  $args Optional, Args to set data such as fees, tax, etc. Even to modify $order_data.
	 * @param bool   $return_id return id.
	 *
	 * @throws \Exception Throw exception if data not valid or
	 * any other exception occur.
	 *
	 * @return mixed order id or order data.
	 */
	public function create_order( int $user_id, array $items, string $payment_status, string $order_type, $coupon_code = null, array $args = array(), $return_id = true ) {
		$items          = Input::sanitize_array( $items );
		$payment_status = Input::sanitize( $payment_status );
		$coupon_code    = Input::sanitize( $coupon_code );

		$allowed_item_fields = $this->model->get_order_items_fillable_fields();
		unset( $allowed_item_fields['order_id'] );

		// Validate order items.
		if ( ! isset( $items[0] ) ) {
			$items = array( $items );
		}

		// Validate payment status.
		if ( ! in_array( $payment_status, array_keys( $this->model->get_payment_status() ) ) ) {
			throw new \Exception( __( 'Invalid payment status', 'tutor' ) );
		}

		$subtotal_price = 0;
		$total_price    = 0;

		// Add enrollment fee with total & subtotal price.
		if ( $this->model::TYPE_SINGLE_ORDER !== $order_type ) {
			$plan = apply_filters( 'tutor_checkout_plan_info', null, $items[0]['item_id'] );
			if ( $plan ) {
				$item_price     = $this->model::calculate_order_price( $items );
				$subtotal_price = $item_price->subtotal;
				$total_price    = $item_price->total;

				if ( $this->model::TYPE_SUBSCRIPTION === $order_type && $plan->enrollment_fee ) {
					$subtotal_price += $plan->enrollment_fee;
					$total_price    += $plan->enrollment_fee;
				}
			}
		} else {
			$item_price     = $this->model::calculate_order_price( $items );
			$subtotal_price = $item_price->subtotal;
			$total_price    = $item_price->total;
		}

		$order_data = array(
			'items'          => $items,
			'payment_status' => $payment_status,
			'order_type'     => $order_type,
			'coupon_code'    => $coupon_code,
			'coupon_amount'  => isset( $args['coupon_amount'] ) ? $args['coupon_amount'] : null,
			'subtotal_price' => $subtotal_price,
			'total_price'    => $total_price,
			'net_payment'    => $total_price,
			'user_id'        => $user_id,
			'payment_status' => $payment_status,
			'order_status'   => $this->model::PAYMENT_PAID === $payment_status ? $this->model::ORDER_COMPLETED : $this->model::ORDER_INCOMPLETE,
			'created_at_gmt' => current_time( 'mysql', true ),
			'created_by'     => get_current_user_id(),
			'updated_at_gmt' => current_time( 'mysql', true ),
			'updated_by'     => get_current_user_id(),
		);

		if ( isset( $args['discount_amount'] ) && $args['discount_amount'] > 0 ) {
			$order_data['discount_type']   = 'flat';
			$order_data['discount_amount'] = floatval( $args['discount_amount'] );
			$order_data['discount_reason'] = __( 'Sale discount', 'tutor' );
		}

		/**
		 * Tax calculation for order.
		 */
		$tax_rate = Tax::get_user_tax_rate( $user_id );
		if ( $tax_rate ) {
			$order_data['tax_type']   = Tax::get_tax_type();
			$order_data['tax_rate']   = $tax_rate;
			$order_data['tax_amount'] = Tax::calculate_tax( $total_price, $tax_rate );

			if ( ! Tax::is_tax_included_in_price() ) {
				$total_price              += $order_data['tax_amount'];
				$order_data['total_price'] = $total_price;
				$order_data['net_payment'] = $total_price;
			}
		}

		// Update data with arguments.
		$order_data = apply_filters( 'tutor_before_order_create', array_merge( $order_data, $args ) );

		try {
			do_action( 'tutor_before_order_create', $order_data );
			$order_id = $this->model->create_order( $order_data );
			if ( $order_id ) {
				$order_data['id'] = $order_id;
				do_action( 'tutor_order_placed', $order_data );
				return $return_id ? $order_id : $order_data;
			}
		} catch ( \Throwable $th ) {
			throw new \Exception( $th->getMessage() );
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
		tutor_utils()->check_nonce();

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

		$order_id = (int) $params['order_id'];

		$updated = $this->model->mark_as_paid( $order_id, $params['note'] );

		if ( ! $updated ) {
			$this->json_response(
				__( 'Failed to update order payment status', 'tutor' ),
				null,
				HttpHelper::STATUS_INTERNAL_SERVER_ERROR
			);
		}

		$this->json_response( __( 'Order payment status successfully updated', 'tutor' ) );
	}

	/**
	 * Cancels an order.
	 *
	 * This function cancels an order by updating its status to 'cancelled'. It performs nonce verification
	 * and checks the user's permissions before proceeding. It also validates the input parameters and
	 * triggers actions before and after the order cancellation.
	 *
	 * The function responds with an appropriate JSON message depending on the outcome of the cancellation process.
	 *
	 * @since 3.0.0
	 *
	 * @return void Responds with a JSON message indicating success or failure.
	 */
	public function order_cancel() {
		if ( ! tutor_utils()->is_nonce_verified() ) {
			$this->json_response( tutor_utils()->error_message( 'nonce' ), null, HttpHelper::STATUS_BAD_REQUEST );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			$this->json_response( tutor_utils()->error_message( HttpHelper::STATUS_UNAUTHORIZED ), null, HttpHelper::STATUS_UNAUTHORIZED );
		}

		$params = array(
			'id'           => Input::post( 'order_id' ),
			'order_status' => $this->model::ORDER_CANCELLED,
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

		do_action( 'tutor_before_order_cancel', $params );

		$response = $this->model->update_order( $params['id'], $params );
		if ( ! $response ) {
			$this->json_response(
				__( 'Failed to cancel order status', 'tutor' ),
				null,
				HttpHelper::STATUS_INTERNAL_SERVER_ERROR
			);
		}

		do_action( 'tutor_order_payment_status_changed', $params['id'], '', $this->model::ORDER_CANCELLED );

		$this->json_response( __( 'Order successfully canceled', 'tutor' ) );
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

		$order_id          = Input::post( 'order_id', 0, Input::TYPE_INT );
		$amount            = (float) Input::post( 'amount' );
		$reason            = Input::post( 'reason' );
		$cancel_enrollment = Input::post( 'is_remove_enrolment', false, Input::TYPE_BOOL );

		if ( $amount <= 0 ) {
			$this->json_response( __( 'Invalid refund amount provided', 'tutor' ), null, HttpHelper::STATUS_BAD_REQUEST );
		}

		$meta_value = array(
			'amount'  => $amount,
			'reason'  => $reason,
			'message' => __( 'Order refunded by ', 'tutor' ) . get_userdata( get_current_user_id() )->display_name,
		);

		$order_data        = $this->model->get_order_by_id( $order_id );
		$cancel_enrollment = apply_filters( 'tutor_cancel_enrollment_on_refund', $cancel_enrollment, $order_data );

		if ( $amount > (float) $order_data->net_payment ) {
			$this->json_response(
				__( 'Refund amount exceeded.', 'tutor' ),
				null,
				HttpHelper::STATUS_BAD_REQUEST
			);
		}

		$order_status   = $order_data->order_status;
		$payment_status = $order_data->payment_status;

		$meta_key = OrderActivitiesModel::META_KEY_REFUND;
		if ( $amount < (float) $order_data->net_payment ) {
			$meta_key = OrderActivitiesModel::META_KEY_PARTIALLY_REFUND;
		}

		if ( OrderActivitiesModel::META_KEY_PARTIALLY_REFUND === $meta_key ) {
			$meta_value['message'] = __( 'Partially refunded by ', 'tutor' ) . get_userdata( get_current_user_id() )->display_name;
		}

		$params = array(
			'order_id'   => $order_id,
			'meta_key'   => $meta_key,
			'meta_value' => wp_json_encode( $meta_value ),
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

		$payload             = new \stdClass();
		$payload->order_id   = $params['order_id'];
		$payload->meta_key   = $params['meta_key'];
		$payload->meta_value = $params['meta_value'];

		$activity_model = new OrderActivitiesModel();
		$response       = $activity_model->add_order_meta( $payload );

		if ( $response ) {
			// Update net payment.
			$refund_amount = $this->model->get_refund_amount( $order_id );

			$net_payment = floatval( $order_data->total_price ) - floatval( $refund_amount );
			if ( 'refund' === $meta_key ) {
				$payment_status = $this->model::PAYMENT_REFUNDED;
			} else {
				$payment_status = $this->model::PAYMENT_PARTIALLY_REFUNDED;
			}

			if ( $cancel_enrollment ) {
				$order_status = $this->model::ORDER_CANCELLED;
			}

			$update_data = array(
				'net_payment'    => $net_payment,
				'refund_amount'  => $refund_amount,
				'payment_status' => $payment_status,
				'order_status'   => $order_status,
			);

			$this->model->update_order( $order_id, $update_data );

			do_action( 'tutor_order_payment_status_changed', $order_data->id, $order_data->payment_status, $payment_status );

			$order_data->payment_status = $update_data['payment_status'];
			$order_data->order_status   = $update_data['order_status'];
			do_action( 'tutor_after_order_refund', $order_id, $amount, $reason );

			$res_msg = __( 'Order refund successful', 'tutor' );

			try {
				$this->refund_from_payment_gateway( $order_id, $amount, $reason );
				$this->json_response( $res_msg );
			} catch ( \Throwable $th ) {
				$res_msg = __( 'Order refunded successfully, but pending payment gateway issuance.', 'tutor' );
				$this->json_response( $res_msg );
			}
		} else {
			$this->json_response(
				__( 'Failed to make refund', 'tutor' ),
				null,
				HttpHelper::STATUS_INTERNAL_SERVER_ERROR
			);
		}

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
		$payload->meta_value = $params['meta_value'];

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
	 * Add a discount to an order.
	 *
	 * This function handles the request to add a discount to an order. It verifies the nonce,
	 * checks user permissions, validates the input, and then updates the order with the discount details.
	 *
	 * @since 3.0.0
	 *
	 * @return void This function outputs a JSON response and does not return a value.
	 */
	public function add_discount() {
		if ( ! tutor_utils()->is_nonce_verified() ) {
			$this->json_response( tutor_utils()->error_message( 'nonce' ), null, HttpHelper::STATUS_BAD_REQUEST );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			$this->json_response( tutor_utils()->error_message( HttpHelper::STATUS_UNAUTHORIZED ), null, HttpHelper::STATUS_UNAUTHORIZED );
		}

		$request = Input::sanitize_array( $_POST ); //phpcs:ignore --already sanitized.

		// Validate request.
		$validation = $this->validate( $request );
		if ( ! $validation->success ) {
			$this->json_response(
				tutor_utils()->error_message( HttpHelper::STATUS_BAD_REQUEST ),
				$validation->errors,
				HttpHelper::STATUS_BAD_REQUEST
			);
		}

		$request = (object) $request;

		try {
			$order    = $this->model->get_order_by_id( $request->order_id );
			$subtotal = $order->subtotal_price;

			$discount_amount = $this->model->calculate_discount_amount( $request->discount_type, $request->discount_amount, $subtotal );

			$deducted_amount = $discount_amount;
			if ( ! empty( $order->coupon_code ) && $order->coupon_amount > 0 ) {
				$deducted_amount += $order->coupon_amount;
			}

			$total_price = $subtotal - $deducted_amount;

			$tax_rate   = Tax::get_user_tax_rate( $order->user_id );
			$tax_amount = Tax::calculate_tax( $total_price, $tax_rate );
			if ( ! Tax::is_tax_included_in_price() ) {
				$total_price += $tax_amount;
			}

			$order_data = array(
				'discount_amount' => $request->discount_amount,
				'discount_reason' => $request->discount_reason,
				'discount_type'   => $request->discount_type,
				'subtotal_price'  => $subtotal,
				'tax_rate'        => $tax_rate,
				'tax_amount'      => $tax_amount,
				'net_payment'     => $total_price,
				'total_price'     => $total_price,
			);

			$update = $this->model->update_order( $request->order_id, $order_data );
			if ( ! $update ) {
				$this->json_response(
					__( 'Failed to add a discount', 'tutor' ),
					null,
					HttpHelper::STATUS_INTERNAL_SERVER_ERROR
				);
			}

			do_action( 'tutor_after_add_order_discount', $order, $discount_amount );

			$this->json_response( __( 'Order discount successful added', 'tutor' ) );
		} catch ( \Throwable $th ) {
			$this->json_response(
				__( 'Failed to add a discount', 'tutor' ),
				$th->getMessage(),
				HttpHelper::STATUS_INTERNAL_SERVER_ERROR
			);
		}

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
		$url = apply_filters( 'tutor_data_tab_base_url', get_pagenum_link() );

		$date           = Input::get( 'date', '' );
		$payment_status = Input::get( 'payment-status', '' );
		$search         = Input::get( 'search', '' );

		$where = array(
			'order_type' => OrderModel::TYPE_SINGLE_ORDER,
		);

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

		$year  = gmdate( 'Y', strtotime( $date_filter ) );
		$month = gmdate( 'm', strtotime( $date_filter ) );
		$day   = gmdate( 'd', strtotime( $date_filter ) );

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

		$request     = Input::sanitize_array( $_POST ); //phpcs:ignore  -- already sanitized.
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
						'payment_status' => $this->model::PAYMENT_PAID,
						'order_status'   => $this->model::ORDER_COMPLETED,
					);
					break;
				case $this->model::PAYMENT_UNPAID:
					$data = array(
						'payment_status' => $this->model::PAYMENT_UNPAID,
						'order_status'   => $this->model::ORDER_INCOMPLETE,
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
			if ( 'delete' !== $bulk_action ) {
				foreach ( $bulk_ids as $id ) {
					do_action( 'tutor_order_payment_status_changed', $id, '', $bulk_action );
				}
			}
			wp_send_json_success( __( 'Order updated successfully.', 'tutor' ) );
		} else {
			wp_send_json_error( __( 'Failed to update order.', 'tutor' ) );
		}
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

		$where_clause = array(
			'order_type' => OrderModel::TYPE_SINGLE_ORDER,
		);

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
	 * Filter discount data if monetization is Tutor
	 *
	 * @since 3.0.0
	 *
	 * @param int    $user_id Current user id.
	 * @param string $period  Period for filter refund data.
	 * @param string $start_date Filter start date.
	 * @param string $end_date Filter end date.
	 * @param int    $course_id Course id.
	 *
	 * @return array
	 */
	public function get_discount_data( $user_id = 0, $period = '', $start_date = '', $end_date = '', $course_id = 0 ) {
		// Sanitize params.
		$user_id    = is_admin() ? 0 : $user_id;
		$period     = Input::sanitize( $period );
		$start_date = Input::sanitize( $start_date );
		$end_date   = Input::sanitize( $end_date );
		$course_id  = (int) $course_id;

		return $this->model->get_discounts_by_user( $user_id, $period, $start_date, $end_date, $course_id );
	}

	/**
	 * Filter refund data if monetization is Tutor
	 *
	 * @since 3.0.0
	 *
	 * @param int    $user_id Current user id.
	 * @param string $period  Period for filter refund data.
	 * @param string $start_date Filter start date.
	 * @param string $end_date Filter end date.
	 * @param int    $course_id Course id.
	 *
	 * @return array
	 */
	public function get_refund_data( $user_id = 0, $period = '', $start_date = '', $end_date = '', $course_id = 0 ) {
		// Sanitize params.
		$user_id    = is_admin() ? 0 : $user_id;
		$period     = Input::sanitize( $period );
		$start_date = Input::sanitize( $start_date );
		$end_date   = Input::sanitize( $end_date );
		$course_id  = (int) $course_id;

		return $this->model->get_refunds_by_user( $user_id, $period, $start_date, $end_date, $course_id );
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
			'order_id'        => 'required|numeric',
			'meta_key'        => 'required',
			'meta_value'      => 'required',
			'discount_type'   => 'required',
			'discount_amount' => 'required',
		);

		// Skip validation rules for not available fields in data.
		foreach ( $validation_rules as $key => $value ) {
			if ( ! array_key_exists( $key, $data ) ) {
				unset( $validation_rules[ $key ] );
			}
		}

		return ValidationHelper::validate( $validation_rules, $data );
	}

	/**
	 * Process refund from payment gateway
	 *
	 * @since 3.1.0
	 *
	 * @param int    $order_id Order id.
	 * @param string $amount Refund amount.
	 * @param string $reason Refund reason.
	 *
	 * @throws \Throwable If an error occurs during the refund process.
	 *
	 * @return void
	 */
	public function refund_from_payment_gateway( $order_id, $amount, $reason ) {
		$order = $this->model->get_order_by_id( $order_id );
		if ( $order && ! $this->model->is_manual_payment( $order->payment_method ) ) {
			$refund_data = $this->prepare_refund_data( $order, $amount, $reason );
			try {
				$payment_gateway_ref = Ecommerce::payment_gateways_with_ref( $order->payment_method );
				if ( $payment_gateway_ref ) {
					$gateway_obj = Ecommerce::get_payment_gateway_object( $payment_gateway_ref['gateway_class'] );
					$gateway_obj->make_refund( $refund_data );
				}
			} catch ( \Throwable $th ) {
				throw $th;
			}
		}
	}

	/**
	 * Prepare refund data
	 *
	 * @since 3.1.0
	 *
	 * @param object $order Order object.
	 * @param string $amount Raw amount.
	 * @param string $reason Refund reason.
	 *
	 * @return object
	 */
	public function prepare_refund_data( $order, $amount, $reason ) {
		$currency = tutor_get_currencies_info_by_code( tutor_utils()->get_option( OptionKeys::CURRENCY_CODE ) );

		$refund_data = array(
			'type'            => 'refund',
			'amount'          => $amount,
			'payment_payload' => $order->payment_payloads, // JSON string representing the  payment payload.
			'order_id'        => $order->id,
			'reason'          => $reason,
			'refund_type'     => $order->net_payment == $amount ? 'full' : 'partial',
			'currency'        => (object) array(
				'code'         => $currency['code'],
				'symbol'       => $currency['symbol'],
				'name'         => $currency['name'],
				'locale'       => $currency['locale'],
				'numeric_code' => $currency['numeric_code'],
			),
		);

		return (object) $refund_data;
	}

}
