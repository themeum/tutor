<?php
/**
 * Manage Billing
 *
 * @package Tutor\Ecommerce
 * @author Themeum
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\Ecommerce;

use TUTOR\BaseController;
use Tutor\Helpers\HttpHelper;
use Tutor\Helpers\ValidationHelper;
use Tutor\Models\BillingModel;
use Tutor\Traits\JsonResponse;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BillingController class
 *
 * @since 3.0.0
 */
class BillingController extends BaseController {


	/**
	 * Billing model
	 *
	 * @since 3.0.0
	 *
	 * @var BillingModel
	 */
	private $model;

	/**
	 * Trait for sending JSON response
	 */
	use JsonResponse;

	/**
	 * Constructor.
	 *
	 * Initializes the Billing class, sets the page title, and optionally registers
	 * hooks for handling AJAX requests related to billing data.
	 *
	 * @param bool $register_hooks Whether to register hooks for handling requests. Default is true.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function __construct( $register_hooks = true ) {
		$this->model = new BillingModel();

		if ( $register_hooks ) {
			/**
			 * Handle AJAX request for saving billing info if current user.
			 *
			 * @since 3.0.0
			 */
			add_action( 'wp_ajax_tutor_save_billing_info', array( $this, 'save_billing_info' ) );

			/**
			 * Handle AJAX request for getting billing info if current user.
			 *
			 * @since 3.0.0
			 */
			add_action( 'wp_ajax_tutor_get_billing_info', array( $this, 'get_billing_info' ) );
		}
	}

	/**
	 * Get the customer model object
	 *
	 * @since 3.0.0
	 *
	 * @return object
	 */
	public function get_model() {
		return $this->model;
	}

	/**
	 * Save billing information for the current user.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function save_billing_info() {
		if ( ! tutor_utils()->is_nonce_verified() ) {
			$this->json_response(
				tutor_utils()->error_message( 'nonce' ),
				null,
				HttpHelper::STATUS_BAD_REQUEST
			);
		}

		$data = $this->get_allowed_fields( $_POST );

		$user_id         = get_current_user_id();
		$data['user_id'] = $user_id;

		$validation = $this->validate( $data );
		if ( ! $validation->success ) {
			$this->json_response(
				__( 'Invalid inputs', 'tutor' ),
				$validation->errors,
				HttpHelper::STATUS_UNPROCESSABLE_ENTITY
			);
		}

		$billing_info = $this->get_billing_info();

		if ( $billing_info ) {
			$response = $this->model->update( $data, array( 'user_id' => $user_id ) );
		} else {
			$response = $this->model->insert( $data );
		}

		if ( ! $response ) {
			$this->json_response(
				__( 'Failed to save billing info', 'tutor' ),
				null,
				HttpHelper::STATUS_INTERNAL_SERVER_ERROR
			);
		}

		$this->json_response( __( 'Billing info saved successfully', 'tutor' ) );
	}

	/**
	 * Get billing information for the current user.
	 *
	 * @since 3.0.0
	 *
	 * @param int $user_id User id.
	 *
	 * @return mixed The user's billing information.
	 */
	public function get_billing_info( $user_id = 0 ) {
		$user_id = tutor_utils()->get_user_id( $user_id );
		return $this->model->get_info( $user_id );
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
			'user_id'    => 'required|numeric',
			'first_name' => 'required',
			'last_name'  => 'required',
			'email'      => 'required|email',
			'phone'      => 'required',
			'zip_code'   => 'required',
			'address'    => 'required',
			'country'    => 'required',
			'state'      => 'required',
			'city'       => 'required',
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
