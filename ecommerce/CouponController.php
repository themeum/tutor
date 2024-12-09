<?php
/**
 * Manage Coupon
 *
 * @package Tutor\Ecommerce
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\Ecommerce;

use TUTOR\Backend_Page_Trait;
use TUTOR\BaseController;
use TUTOR\Course;
use Tutor\Helpers\DateTimeHelper;
use Tutor\Helpers\HttpHelper;
use Tutor\Helpers\ValidationHelper;
use TUTOR\Input;
use Tutor\Models\CouponModel;
use Tutor\Models\CourseModel;
use Tutor\Models\OrderModel;
use Tutor\Traits\JsonResponse;
use TutorPro\CourseBundle\Models\BundleModel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * CouponController class
 *
 * @since 3.0.0
 */
class CouponController extends BaseController {

	/**
	 * Page slug
	 *
	 * @var string
	 */
	const PAGE_SLUG = 'tutor_coupons';

	/**
	 * Coupon model
	 *
	 * @since 3.0.0
	 *
	 * @var CouponModel
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
	 * Bulk Action
	 *
	 * @var $bulk_action
	 */
	public $bulk_action = true;

	/**
	 * Constructor.
	 *
	 * Initializes the Coupons class, sets the page title, and optionally registers
	 * hooks for handling AJAX requests related to coupon data, bulk actions, coupon status updates,
	 * and coupon deletions.
	 *
	 * @param bool $register_hooks Whether to register hooks for handling requests. Default is true.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function __construct( $register_hooks = true ) {
		$this->page_title = __( 'Coupons', 'tutor' );
		$this->model      = new CouponModel();

		if ( $register_hooks ) {
			// Register hooks here.
			add_action( 'wp_ajax_tutor_coupon_bulk_action', array( $this, 'bulk_action_handler' ) );
			add_action( 'wp_ajax_tutor_coupon_permanent_delete', array( $this, 'coupon_permanent_delete' ) );
			/**
			 * Handle AJAX request for getting coupon related data by coupon ID.
			 *
			 * @since 3.0.0
			 */
			add_action( 'wp_ajax_tutor_coupon_details', array( $this, 'ajax_coupon_details' ) );
			/**
			 * Handle AJAX request for getting courses for coupon.
			 *
			 * @since 3.0.0
			 */
			add_action( 'wp_ajax_tutor_get_coupon_applies_to', array( $this, 'get_coupon_applies_to' ) );

			add_action( 'wp_ajax_tutor_coupon_create', array( $this, 'ajax_create_coupon' ) );
			add_action( 'wp_ajax_tutor_coupon_update', array( $this, 'ajax_update_coupon' ) );
			add_action( 'wp_ajax_tutor_coupon_applies_to_list', array( $this, 'ajax_coupon_applies_to_list' ) );
			add_action( 'wp_ajax_tutor_apply_coupon', array( $this, 'ajax_apply_coupon' ) );
		}
	}

	/**
	 * Get coupon model object
	 *
	 * @since 3.0.0
	 *
	 * @return CouponModel
	 */
	public function get_model() {
		return $this->model;
	}

	/**
	 * Handle ajax request for creating coupon
	 *
	 * @since 3.0.0
	 *
	 * @return void send wp_json response
	 */
	public function ajax_create_coupon() {
		tutor_utils()->check_nonce();
		tutor_utils()->check_current_user_capability();

		$data = $this->get_allowed_fields( Input::sanitize_array( $_POST ), true );//phpcs:ignore --sanitized already

		if ( $this->model::TYPE_AUTOMATIC === $data['coupon_type'] ) {
			$data['coupon_code'] = time();
		}

		$validation = $this->validate( $data );
		if ( ! $validation->success ) {
			$this->json_response(
				tutor_utils()->error_message( 'validation_error' ),
				$validation->errors,
				HttpHelper::STATUS_UNPROCESSABLE_ENTITY
			);
		}

		if ( $this->model->get_coupon( array( 'coupon_code' => $data['coupon_code'] ) ) ) {
			$this->json_response(
				__( 'Coupon code already exists!', 'tutor' ),
				null,
				HttpHelper::STATUS_UNPROCESSABLE_ENTITY
			);
		}

		// Convert start & expire date time into gmt.
		$data['start_date_gmt'] = $data['start_date_gmt'];
		$data['created_by']     = get_current_user_id();
		$data['created_at_gmt'] = current_time( 'mysql', true );
		$data['updated_at_gmt'] = current_time( 'mysql', true );
		$applies_to_items       = isset( $data['applies_to_items'] ) ? $data['applies_to_items'] : array();
		unset( $data['applies_to_items'] );

		// Set expire date if isset.
		if ( isset( $data['expire_date_gmt'] ) ) {
			$data['expire_date_gmt'] = $data['expire_date_gmt'];
		}

		try {
			$coupon_id = $this->model->create_coupon( $data );
			if ( $coupon_id ) {
				if ( is_array( $applies_to_items ) && count( $applies_to_items ) ) {
					$this->model->insert_applies_to( $data['applies_to'], $applies_to_items, $data['coupon_code'] );
				}

				$this->json_response( __( 'Coupon created successfully!', 'tutor' ) );
			} else {
				$this->json_response(
					__( 'Failed to create!', 'tutor' ),
					null,
					HttpHelper::STATUS_INTERNAL_SERVER_ERROR
				);
			}
		} catch ( \Throwable $th ) {
			$this->json_response(
				tutor_utils()->error_message( 'server_error' ),
				$th->getMessage(),
				HttpHelper::STATUS_INTERNAL_SERVER_ERROR
			);
		}
	}

	/**
	 * Handle ajax request for updating coupon
	 *
	 * @since 3.0.0
	 *
	 * @return void send wp_json response
	 */
	public function ajax_update_coupon() {
		tutor_utils()->check_nonce();
		tutor_utils()->check_current_user_capability();

		$data = $this->get_allowed_fields( Input::sanitize_array( $_POST ), false );//phpcs:ignore --sanitized already

		$coupon_id              = Input::post( 'id', null, Input::TYPE_INT );
		$data['coupon_id']      = $coupon_id;
		$data['updated_at_gmt'] = current_time( 'mysql', true );

		$validation = $this->validate( $data );
		if ( ! $validation->success ) {
			$this->json_response(
				tutor_utils()->error_message( 'validation_error' ),
				$validation->errors,
				HttpHelper::STATUS_UNPROCESSABLE_ENTITY
			);
		}

		unset( $data['coupon_id'] );

		if ( ! isset( $data['expire_date_gmt'] ) ) {
			$data['expire_date_gmt'] = null;
		}

		// Set updated by.
		$data['updated_by'] = get_current_user_id();

		try {
			$update = $this->model->update_coupon( $coupon_id, $data );
			if ( $update ) {
				$coupon_data = $this->model->get_coupon( array( 'id' => $coupon_id ) );
				$this->model->delete_applies_to( $coupon_data->coupon_code );
				if ( isset( $data['applies_to_items'] ) && is_array( $data['applies_to_items'] ) && count( $data['applies_to_items'] ) ) {
					$this->model->insert_applies_to( $data['applies_to'], $data['applies_to_items'], $coupon_data->coupon_code );
				}

				$this->json_response( __( 'Coupon updated successfully!', 'tutor' ) );
			} else {
				$this->json_response(
					__( 'Failed to update!', 'tutor' ),
					null,
					HttpHelper::STATUS_INTERNAL_SERVER_ERROR
				);
			}
		} catch ( \Throwable $th ) {
			$this->json_response(
				tutor_utils()->error_message( 'server_error' ),
				$th->getMessage(),
				HttpHelper::STATUS_INTERNAL_SERVER_ERROR
			);
		}
	}

	/**
	 * Get list of coupon applies to on which coupon
	 * will be applicable
	 *
	 * @since 3.0.0
	 *
	 * @return void send wp_json response
	 */
	public function ajax_coupon_applies_to_list() {
		tutor_utils()->check_nonce();
		tutor_utils()->check_current_user_capability();

		$applies_to  = Input::post( 'applies_to' );
		$limit       = Input::post( 'limit', 10, Input::TYPE_INT );
		$offset      = Input::post( 'offset', 0, Input::TYPE_INT );
		$search_term = '';

		$filter = json_decode( wp_unslash( $_POST['filter'] ) ); //phpcs:ignore --sanitized already
		if ( ! empty( $filter ) && property_exists( $filter, 'search' ) ) {
			$search_term = Input::sanitize( $filter->search );
		}

		if ( $this->model->is_specific_applies_to( $applies_to ) ) {
			try {
				$list = $this->get_application_list( $applies_to, $limit, $offset, $search_term );
				if ( $list ) {
					$this->json_response(
						__( 'Coupon application list retrieved successfully!' ),
						$list
					);
				} else {
					$this->json_response(
						tutor_utils()->error_message( 'not_found' ),
						null,
						HttpHelper::STATUS_NOT_FOUND
					);
				}
			} catch ( \Throwable $th ) {
				$this->json_response(
					tutor_utils()->error_message( 'server_error' ),
					$th->getMessage(),
					HttpHelper::STATUS_INTERNAL_SERVER_ERROR
				);
			}
		} else {
			$this->json_response(
				tutor_utils()->error_message( 'invalid_req' ),
				null,
				HttpHelper::STATUS_UNPROCESSABLE_ENTITY
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
			$this->bulk_action_active(),
			$this->bulk_action_inactive(),
		);

		$active_tab = Input::get( 'data', '' );

		if ( 'trash' === $active_tab ) {
			array_push( $actions, $this->bulk_action_delete() );
		} else {
			array_push( $actions, $this->bulk_action_trash() );
		}

		return apply_filters( 'tutor_coupon_bulk_actions', $actions );
	}

	/**
	 * Get coupon page url
	 *
	 * @since 3.0.0
	 *
	 * @param boolean $is_admin Whether to get admin or frontend url.
	 *
	 * @return string
	 */
	public static function get_coupon_page_url( bool $is_admin = true ) {
		if ( $is_admin ) {
			return admin_url( 'admin.php?page=' . self::PAGE_SLUG );
		} else {
			return tutor_utils()->get_tutor_dashboard_url() . '/coupons';
		}
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

		$date          = Input::get( 'date', '' );
		$coupon_status = Input::get( 'coupon-status', '' );
		$search        = Input::get( 'search', '' );

		$where = array();

		if ( ! empty( $date ) ) {
			$where['created_at_gmt'] = tutor_get_formated_date( 'Y-m-d', $date );
		}

		if ( ! empty( $coupon_status ) ) {
			$where['coupon_status'] = $coupon_status;
		}

		$coupon_status = $this->model->get_coupon_status();

		$tabs = array();

		$tabs [] = array(
			'key'   => 'all',
			'title' => __( 'All', 'tutor' ),
			'value' => $this->model->get_coupon_count( $where, $search ),
			'url'   => $url . '&data=all',
		);

		foreach ( $coupon_status as $key => $value ) {
			$where['coupon_status'] = $key;

			$tabs[] = array(
				'key'   => $key,
				'title' => $value,
				'value' => $this->model->get_coupon_count( $where, $search ),
				'url'   => $url . '&data=' . $key,
			);
		}

		return apply_filters( 'tutor_coupon_tabs', $tabs );
	}

	/**
	 * Get coupons
	 *
	 * @since 3.0.0
	 *
	 * @param integer $limit List limit.
	 * @param integer $offset List offset.
	 *
	 * @return array
	 */
	public function get_coupons( $limit = 10, $offset = 0 ) {

		$active_tab = Input::get( 'data', 'all' );

		$date          = Input::get( 'date', '' );
		$search_term   = Input::get( 'search', '' );
		$coupon_status = Input::get( 'coupon-status' );

		$where_clause = array();

		if ( $date ) {
			$where_clause['created_at_gmt'] = tutor_get_formated_date( '', $date );
		}

		if ( ! is_null( $coupon_status ) ) {
			$where_clause['coupon_status'] = $coupon_status;
		}

		if ( 'all' !== $active_tab && in_array( $active_tab, array_keys( $this->model->get_coupon_status() ), true ) ) {
			$where_clause['coupon_status'] = $active_tab;
		}

		$list_order    = Input::get( 'order', 'DESC' );
		$list_order_by = 'id';

		return $this->model->get_coupons( $where_clause, $search_term, $limit, $offset, $list_order_by, $list_order );
	}

	/**
	 * Handle bulk action AJAX request.
	 *
	 * Bulk actions: active, inactive, trash, delete
	 *
	 * @since 3.0.0
	 *
	 * @return void send wp_json response
	 */
	public function bulk_action_handler() {
		tutor_utils()->checking_nonce();

		if ( ! current_user_can( 'manage_options' ) ) {
			tutor_utils()->error_message();
		}

		// Get and sanitize input data.
		$request     = Input::sanitize_array( $_POST ); //phpcs:ignore --sanitized already
		$bulk_action = $request['bulk-action'];

		$bulk_ids = isset( $request['bulk-ids'] ) ? array_map( 'intval', explode( ',', $request['bulk-ids'] ) ) : array();

		if ( empty( $bulk_ids ) ) {
			wp_send_json_error( __( 'No items selected for the bulk action.', 'tutor' ) );
		}

		$allowed_bulk_actions = array_keys( $this->model->get_coupon_status() );
		array_push( $allowed_bulk_actions, 'delete' );

		if ( ! in_array( $bulk_action, $allowed_bulk_actions, true ) ) {
			wp_send_json_error( __( 'Invalid bulk action.', 'tutor' ) );
		}

		do_action( 'tutor_before_coupon_bulk_action', $bulk_action, $bulk_ids );

		$response = false;
		if ( 'delete' === $bulk_action ) {
			$response = $this->model->delete_coupon( $bulk_ids );
		} else {
			$data     = array(
				'coupon_status' => $bulk_action,
			);
			$response = $this->model->update_coupon( $bulk_ids, $data );
		}

		do_action( 'tutor_after_coupon_bulk_action', $bulk_action, $bulk_ids );

		if ( $response ) {
			wp_send_json_success( __( 'Coupon updated successfully.', 'tutor' ) );
		} else {
			wp_send_json_error( __( 'Failed to update coupon.', 'tutor' ) );
		}
	}

	/**
	 * Handle coupon permanent delete
	 *
	 * @since 3.0.0
	 *
	 * @return void send wp_json response
	 */
	public function coupon_permanent_delete() {
		tutor_utils()->checking_nonce();

		if ( ! current_user_can( 'manage_options' ) ) {
			tutor_utils()->error_message();
		}

		// Get and sanitize input data.
		$id = Input::post( 'id', 0, Input::TYPE_INT );
		if ( ! $id ) {
			wp_send_json_error( __( 'Invalid coupon ID', 'tutor' ) );
		}

		do_action( 'tutor_before_coupon_permanent_delete', $id );

		$response = $this->model->delete_coupon( $id );
		if ( $response ) {
			do_action( 'tutor_after_coupon_permanent_delete', $id );

			wp_send_json_success( __( 'Coupon delete successfully.', 'tutor' ) );
		} else {
			wp_send_json_error( __( 'Failed to delete coupon.', 'tutor' ) );
		}
	}

	/**
	 * Ajax handler to retrieve coupon details.
	 *
	 * @since 3.0.0
	 *
	 * @return void Sends a JSON response with the coupon data or an error message.
	 */
	public function ajax_coupon_details() {
		if ( ! tutor_utils()->is_nonce_verified() ) {
			$this->json_response( tutor_utils()->error_message( 'nonce' ), null, HttpHelper::STATUS_BAD_REQUEST );
		}

		$coupon_id = Input::post( 'id' );

		if ( empty( $coupon_id ) ) {
			$this->json_response(
				__( 'Coupon code is required', 'tutor' ),
				null,
				HttpHelper::STATUS_BAD_REQUEST
			);
		}

		$coupon_data = $this->model->get_coupon( array( 'id' => $coupon_id ) );

		if ( ! $coupon_data ) {
			$this->json_response(
				__( 'Coupon not found', 'tutor' ),
				null,
				HttpHelper::STATUS_NOT_FOUND
			);
		}

		$applications = $this->model->get_formatted_coupon_applications( $coupon_data );

		// Set applies to items.
		$coupon_data->applies_to_items = $applications;

		// Set coupon usage.
		$coupon_data->coupon_usage = $this->model->get_coupon_usage_count( $coupon_data->coupon_code );

		// Set created & updated by.
		$coupon_data->coupon_created_by = tutor_utils()->display_name( $coupon_data->created_by );
		$coupon_data->coupon_update_by  = tutor_utils()->display_name( $coupon_data->updated_by );

		$coupon_data->start_date_readable  = empty( $coupon_data->start_date_gmt ) ? '' : DateTimeHelper::get_gmt_to_user_timezone_date( $coupon_data->start_date_gmt );
		$coupon_data->expire_date_readable = empty( $coupon_data->expire_date_gmt ) ? '' : DateTimeHelper::get_gmt_to_user_timezone_date( $coupon_data->expire_date_gmt );
		$coupon_data->created_at_readable  = DateTimeHelper::get_gmt_to_user_timezone_date( $coupon_data->created_at_gmt );
		$coupon_data->updated_at_readable  = empty( $coupon_data->updated_at_gmt ) ? '' : DateTimeHelper::get_gmt_to_user_timezone_date( $coupon_data->updated_at_gmt );

		$this->json_response(
			__( 'Coupon retrieved successfully', 'tutor' ),
			$coupon_data
		);
	}

	/**
	 * Get application if applies to a specific category or bundle.
	 *
	 * @since 3.0.0
	 *
	 * @param string $applies_to Applies to.
	 * @param int    $limit      Number of items to fetch.
	 * @param int    $offset     Offset for fetching items.
	 * @param int    $search_term Search term.
	 *
	 * @return array
	 */
	public function get_application_list( string $applies_to, int $limit = 10, int $offset = 0, $search_term = '' ) {

		$response = array(
			'total_items' => 0,
			'results'     => array(),
		);

		if ( $this->model::APPLIES_TO_SPECIFIC_COURSES === $applies_to ) {
			$args = array(
				'post_type'      => tutor()->course_post_type,
				'posts_per_page' => $limit,
				'offset'         => $offset,
			);

			// Add search.
			if ( $search_term ) {
				$args['s'] = $search_term;
			}

			$courses = ( new CourseModel() )->get_paid_courses( $args );

			$response['total_items'] = is_a( $courses, 'WP_Query' ) ? $courses->found_posts : 0;

			if ( is_a( $courses, 'WP_Query' ) && $courses->have_posts() ) {
				$courses = $courses->get_posts();
				foreach ( $courses as $course ) {
					$response['results'][] = Course::get_mini_info( $course );
				}
			}
		} elseif ( $this->model::APPLIES_TO_SPECIFIC_BUNDLES === $applies_to && tutor_utils()->is_addon_enabled( 'tutor-pro/addons/course-bundle/course-bundle.php' ) ) {
			$args = array(
				'post_type'      => 'course-bundle',
				'posts_per_page' => $limit,
				'offset'         => $offset,
			);

			// Add search.
			if ( $search_term ) {
				$args['s'] = $search_term;
			}

			$bundles = ( new CourseModel() )->get_paid_courses( $args );

			$response['total_items'] = is_a( $bundles, 'WP_Query' ) ? $bundles->found_posts : 0;

			if ( is_a( $bundles, 'WP_Query' ) && $bundles->have_posts() ) {
				$bundles = $bundles->get_posts();
				foreach ( $bundles as $bundle ) {
					$response['results'][] = Course::get_mini_info( $bundle );
				}
			}
		} elseif ( $this->model::APPLIES_TO_SPECIFIC_CATEGORY === $applies_to ) {
			$args = array(
				'number'     => $limit,
				'offset'     => $offset,
				'hide_empty' => true,
			);

			$total_arg = array(
				'fields'     => 'ids',
				'taxonomy'   => 'course-category',
				'hide_empty' => true,
			);

			// Add search.
			if ( $search_term ) {
				$args['search']      = $search_term;
				$total_arg['search'] = $search_term;
			}

			$terms = tutor_utils()->get_course_categories( 0, $args );
			$total = get_terms( $total_arg );

			$response['total_items'] = is_array( $total ) ? count( $total ) : 0;

			if ( ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$thumb_id = get_term_meta( $term->term_id, 'thumbnail_id', true );

					$response['results'][] = array(
						'id'            => $term->term_id,
						'title'         => $term->name,
						'image'         => $thumb_id ? wp_get_attachment_thumb_url( $thumb_id ) : tutor()->url . 'assets/images/placeholder.svg',
						'total_courses' => (int) $term->count,
					);
				}
			}
		}

		return $response;
	}

	/**
	 * Ajax handler for applying coupon
	 *
	 * @since 3.0.0
	 *
	 * @return void send wp_json response
	 */
	public function ajax_apply_coupon() {
		tutor_utils()->check_nonce();

		if ( ! Settings::is_coupon_usage_enabled() ) {
			$this->json_response(
				__( 'Coupon usage is disabled', 'tutor' ),
				null,
				HttpHelper::STATUS_BAD_REQUEST
			);
		}

		$object_ids = Input::post( 'object_ids' ); // Course/bundle ids.
		$object_ids = array_filter( explode( ',', $object_ids ), 'is_numeric' );

		if ( empty( $object_ids ) ) {
			$this->json_response(
				tutor_utils()->error_message( 'invalid_req' ),
				null,
				HttpHelper::STATUS_BAD_REQUEST
			);
		}

		try {
			$coupon_code = Input::post( 'coupon_code' );
			$plan        = Input::post( 'plan', 0, Input::TYPE_INT );
			$order_type  = $plan ? OrderModel::TYPE_SUBSCRIPTION : OrderModel::TYPE_SINGLE_ORDER;

			$checkout_data = ( new CheckoutController( false ) )->prepare_checkout_items( $object_ids, $order_type, $coupon_code );

			if ( $checkout_data->is_coupon_applied ) {
				$this->json_response(
					__( 'Coupon applied successfully', 'tutor' ),
					$checkout_data
				);
			} else {
				$this->json_response(
					__( 'Coupon code is not applicable!', 'tutor' ),
					null,
					HttpHelper::STATUS_BAD_REQUEST
				);
			}
		} catch ( \Throwable $th ) {
			$this->json_response(
				$th->getMessage(),
				null,
				HttpHelper::STATUS_INTERNAL_SERVER_ERROR
			);
		}
	}

	/**
	 * Manage coupon usage
	 *
	 * Store usage upon order completion
	 *
	 * @since 3.0.0
	 *
	 * @param int $order_id Order id.
	 *
	 * @return void
	 */
	public function store_coupon_usage( $order_id ) {
		$order_model = ( new OrderModel() );

		$order = $order_model->get_order_by_id( $order_id );
		if ( $order ) {
			if ( $order->coupon_amount > 0 && $order_model::ORDER_COMPLETED === $order->order_status ) {
				// Store coupon usage.
				$data = array(
					'coupon_code' => $order->coupon_code,
					'user_id'     => $order->user_id,
				);

				try {
					$this->model->store_coupon_usage( $data );
				} catch ( \Throwable $th ) {
					tutor_log( $th );
				}
			}
		}
	}

	/**
	 * Validate input data based on predefined rules.
	 *
	 * @since 3.0.0
	 *
	 * @param array $data The data array to validate.
	 *
	 * @return object The validation result. It returns validation object.
	 */
	protected function validate( array $data ) {

		$validation_rules = array(
			'coupon_id'            => 'numeric',
			'coupon_status'        => 'required',
			'coupon_type'          => 'required',
			'coupon_code'          => 'required',
			'coupon_title'         => 'required',
			'discount_type'        => 'required',
			'discount_amount'      => 'required',
			'applies_to'           => 'required',
			'total_usage_limit'    => 'numeric',
			'per_user_usage_limit' => 'numeric',
			'start_date_gmt'       => 'required|date_format:Y-m-d H:i:s',
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
