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
use Tutor\Helpers\HttpHelper;
use Tutor\Helpers\ValidationHelper;
use TUTOR\Input;
use Tutor\Models\CouponModel;
use Tutor\Models\CourseModel;
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
	 * @var Object
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

		$data = $this->get_allowed_fields( Input::sanitize_array( $_POST ), true );

		$validation = $this->validate( $data );
		if ( ! $validation->success ) {
			$this->json_response(
				tutor_utils()->error_message( 'validation_error' ),
				$validation->errors,
				HttpHelper::STATUS_UNPROCESSABLE_ENTITY
			);
		}

		// Convert start & expire date time into gmt.
		$data['start_date_gmt'] = get_gmt_from_date( $data['start_date_gmt'] );
		$data['created_by']     = get_current_user_id();
		$data['created_at_gmt'] = current_time( 'mysql', true );
		$data['updated_at_gmt'] = current_time( 'mysql', true );

		// Set expire date if isset.
		if ( isset( $data['expire_date_gmt'] ) ) {
			$data['expire_date_gmt'] = get_gmt_from_date( $data['expire_date_gmt'] );
		}

		try {
			$coupon_id = $this->model->create_coupon( $data );
			if ( $coupon_id ) {
				if ( isset( $data['applies_to_items'] ) && is_array( $data['applies_to_items'] ) && count( $data['applies_to_items'] ) ) {
					$applies_to_ids = array_column( $data['applies_to_items'], 'id' );
					$this->model->insert_applies_to( $data['applies_to'], $applies_to_ids, $data['coupon_code'] );
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

		$data = $this->get_allowed_fields( Input::sanitize_array( $_POST ), false );

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

		// Convert start & expire date time into gmt.
		if ( isset( $data['start_date_gmt'] ) ) {
			get_gmt_from_date( $data['start_date_gmt'] );
		}

		if ( isset( $data['expire_date_gmt'] ) ) {
			get_gmt_from_date( $data['expire_date_gmt'] );
		}

		// Set updated by.
		$data['updated_by'] = get_current_user_id();

		try {
			$update = $this->model->update_coupon( $coupon_id, $data );
			if ( $update ) {
				if ( isset( $data['applies_to_items'] ) && is_array( $data['applies_to_items'] ) && count( $data['applies_to_items'] ) ) {
					$applies_to_ids = array_column( $data['applies_to_items'], 'id' );
					$this->model->delete_applies_to( $data['coupon_code'] );
					$this->model->insert_applies_to( $data['applies_to'], $applies_to_ids, $data['coupon_code'] );
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

		$applies_to = Input::post( 'applies_to' );

		if ( $this->model->is_specific_applies_to( $applies_to ) ) {
			try {
				$list = $this->get_application_list( $applies_to );
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
		$url = get_pagenum_link();

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
		$request     = Input::sanitize_array( $_POST );
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

		$this->json_response(
			__( 'Coupon retrieved successfully', 'tutor' ),
			$coupon_data
		);
	}

	/**
	 * Get application if applies to is specific category or bundle
	 *
	 * @since 3.0.0
	 *
	 * @param string $applies_to Applies to.
	 *
	 * @return array
	 */
	public function get_application_list( string $applies_to ) {
		$response = array();

		if ( $this->model::APPLIES_TO_SPECIFIC_BUNDLES === $applies_to && class_exists( 'TutorPro\CourseBundle\Models\BundleModel' ) ) {
			$args = array(
				'post_type'      => 'course-bundle',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			);

			$bundles = new \WP_Query( $args );
			if ( $bundles->have_posts() ) {
				$bundles = $bundles->get_posts();
				foreach ( $bundles as $bundle ) {
					$response[] = array(
						'id'            => $bundle->ID,
						'title'         => $bundle->post_title,
						'image'         => get_the_post_thumbnail_url( $bundle->ID ),
						'course_count'  => count( BundleModel::get_bundle_course_ids( $bundle->ID ) ),
						'regular_price' => get_post_meta( $bundle->ID, Course::COURSE_PRICE_META, true ),
						'sale_price'    => get_post_meta( $bundle->ID, Course::COURSE_SALE_PRICE_META, true ),
					);
				}
			}
		} elseif ( $this->model::APPLIES_TO_SPECIFIC_CATEGORY === $applies_to ) {
			$terms = get_terms(
				array(
					'taxonomy'   => 'course-category',
					'hide_empty' => true,
				)
			);

			if ( ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$thumb_id = get_term_meta( $term->term_id, 'thumbnail_id', true );

					$response[] = array(
						'id'            => $term->term_id,
						'title'         => $term->name,
						'image'         => $thumb_id ? wp_get_attachment_thumb_url( $thumb_id ) : '',
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

		$course_ids  = Input::post( 'course_ids' );
		$course_ids  = array_filter( explode( ',', $course_ids ), 'is_numeric' );
		$coupon_code = Input::post( 'coupon_code' );

		if ( empty( $course_ids ) ) {
			$this->json_response(
				tutor_utils()->error_message( 'invalid_req' ),
				null,
				HttpHelper::STATUS_BAD_REQUEST
			);
		}

		$discount_price = $coupon_code ? $this->model->apply_coupon_discount( $course_ids, $coupon_code ) : $this->model->apply_automatic_coupon_discount( $course_ids );

		$this->json_response(
			__( 'Coupon applied successfully', 'tutor' ),
			$discount_price
		);
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
