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

use Tutor\Helpers\HttpHelper;
use Tutor\Helpers\ValidationHelper;
use TUTOR\Input;
use Tutor\Models\OrderActivitiesModel;
use Tutor\Traits\JsonResponse;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * OrderActivitiesController class
 *
 * @since 3.0.0
 */
class OrderActivitiesController {

	/**
	 * Order model
	 *
	 * @since 3.0.0
	 *
	 * @var Object
	 */
	private $model;

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
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		$this->page_title = __( 'Order Activity', 'tutor' );
		$this->model      = new OrderActivitiesModel();
	}

	/**
	 * Store order activity.
	 *
	 * This function handles the process of storing order activity metadata in the database.
	 * It triggers actions before and after adding the order activity, sanitizes input data,
	 * validates the request, and constructs a payload object. The payload is then passed to
	 * the OrderActivitiesModel to be stored in the database.
	 *
	 * @since 3.0.0
	 *
	 * @param int    $order_id   The ID of the order.
	 * @param string $meta_key   The meta key for the order activity.
	 * @param string $meta_value The meta value for the order activity.
	 *
	 * @return void
	 */
	public static function store_order_activity( int $order_id, string $meta_key, string $meta_value ) {
		$params = array(
			'order_id'   => $order_id,
			'meta_key'   => $meta_key,
			'meta_value' => $meta_value,
		);

		do_action( 'tutor_before_adding_order_activity', $params );

		// Validate request.
		$validation = self::validate( $params );
		if ( ! $validation->success ) {
			self::json_response(
				tutor_utils()->error_message( HttpHelper::STATUS_BAD_REQUEST ),
				$validation->errors,
				HttpHelper::STATUS_BAD_REQUEST
			);
		}

		$payload             = new \stdClass();
		$payload->order_id   = $params['order_id'];
		$payload->meta_key   = $params['meta_key'];
		$payload->meta_value = $params['meta_value'];

		$model    = new OrderActivitiesModel();
		$response = $model->add_order_meta( $payload );

		do_action( 'tutor_after_adding_order_activity', $params );

		if ( ! $response ) {
			self::json_response(
				__( 'Failed to add order activity', 'tutor' ),
				null,
				HttpHelper::STATUS_INTERNAL_SERVER_ERROR
			);
		}

		self::json_response( __( 'Order activity successfully added', 'tutor' ) );
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
