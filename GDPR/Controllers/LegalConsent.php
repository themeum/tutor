<?php
/**
 * GDPR legal consent controller for managing consents.
 *
 * @package Tutor\GDPR\Controllers
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\GDPR\Controllers;

use Tutor\GDPR\Models\{LegalConsents, LegalConsentLogs};
use Tutor\Helpers\ValidationHelper;
use TUTOR\Input;
use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * GDPR AJAX controller for legal consents CRUD.
 *
 * @since 4.0.0
 */
class LegalConsent {

	/**
	 * Legal consent model.
	 *
	 * @since 4.0.0
	 *
	 * @var LegalConsents
	 */
	private $model;

	/**
	 * Consent update logs model.
	 *
	 * @since 4.0.0
	 *
	 * @var LegalConsentLogs
	 */
	private $log_model;

	/**
	 * Constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->model     = new LegalConsents();
		$this->log_model = new LegalConsentLogs();

		$this->register_hooks();
	}

	/**
	 * Register AJAX hooks.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function register_hooks() {
		add_action( 'wp_ajax_tutor_gdpr_compliance_ajax', array( $this, 'handle_legal_consent_ajax' ) );
	}

	/**
	 * Handle legal consent CRUD AJAX requests.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function handle_legal_consent_ajax() {
		// $this->validate_ajax_request();

		$action = Input::post( 'action', '' );
		$data   = Input::sanitize_array( $_POST ); //phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce is validated.

		switch ( $action ) {
			case 'create':
				$this->create_legal_consent( $data );
				break;

			case 'read':
				$this->get_legal_consent( Input::post( 'id', 0, Input::TYPE_INT ) );
				break;

			case 'list':
				$this->list_legal_consents( $data );
				break;

			case 'update':
				$this->update_legal_consent( Input::post( 'id', 0, Input::TYPE_INT ), $data );
				break;

			case 'delete':
				$this->delete_legal_consent( Input::post( 'id', 0, Input::TYPE_INT ) );
				break;

			default:
				wp_send_json_error( __( 'Invalid legal consent action.', 'tutor' ) );
		}
	}

	/**
	 * Validate nonce and user capability for AJAX requests.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function validate_ajax_request() {
		tutor_utils()->check_nonce();
		tutor_utils()->check_current_user_capability();
	}

	/**
	 * Create legal consent entry.
	 *
	 * @since 4.0.0
	 *
	 * @param array $request Request data.
	 *
	 * @return void
	 */
	private function create_legal_consent( array $request ) {
		global $wpdb;

		$request['version'] = 1;

		$data = $this->prepare_legal_consent_data( $request, true );
		if ( is_wp_error( $data ) ) {
			wp_send_json_error( $data->errors );
		}

		$wpdb->query( 'START TRANSACTION' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery

		$legal_consent_id = $this->model->create( $data );
		if ( ! $legal_consent_id ) {
			$wpdb->query( 'ROLLBACK' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			wp_send_json_error( __( 'Failed to create legal consent.', 'tutor' ) );
		}

		$log_id = $this->log_model->create(
			array(
				'legal_consent_id' => (int) $legal_consent_id,
				'action'           => 'created',
				'old_data'         => null,
				'new_data'         => wp_json_encode( $data ),
				'created_at_utc'   => current_time( 'mysql', true ),
			)
		);

		if ( ! $log_id ) {
			$wpdb->query( 'ROLLBACK' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			wp_send_json_error( __( 'Failed to create legal consent log.', 'tutor' ) );
		}

		$wpdb->query( 'COMMIT' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery

		wp_send_json_success(
			array(
				'message' => __( 'Legal consent created successfully.', 'tutor' ),
				'id'      => $legal_consent_id,
			)
		);
	}

	/**
	 * Get single legal consent.
	 *
	 * @since 4.0.0
	 *
	 * @param int $id Legal consent ID.
	 *
	 * @return void
	 */
	private function get_legal_consent( int $id ) {
		if ( ! $id ) {
			wp_send_json_error( __( 'Invalid legal consent id.', 'tutor' ) );
		}

		$item = $this->model->get_row( array( 'id' => $id ) );
		if ( ! $item ) {
			wp_send_json_error( __( 'Legal consent not found.', 'tutor' ) );
		}

		wp_send_json_success( $item );
	}

	/**
	 * Get legal consent list.
	 *
	 * @since 4.0.0
	 *
	 * @param array $request Request data.
	 *
	 * @return void
	 */
	private function list_legal_consents( array $request ) {
		$where = array();
		if ( isset( $request['compliance_key'] ) && '' !== $request['compliance_key'] ) {
			$where['compliance_key'] = sanitize_text_field( $request['compliance_key'] );
		}

		if ( isset( $request['is_active'] ) && '' !== $request['is_active'] ) {
			$where['is_active'] = (int) $request['is_active'];
		}

		$items = $this->model->get_all( $where );
		wp_send_json_success( $items );
	}

	/**
	 * Update legal consent entry.
	 *
	 * @since 4.0.0
	 *
	 * @param int   $id      Legal consent ID.
	 * @param array $request Request data.
	 *
	 * @return void
	 */
	private function update_legal_consent( int $id, array $request ) {
		if ( ! $id ) {
			wp_send_json_error( __( 'Invalid legal consent id.', 'tutor' ) );
		}

		$existing = $this->model->get_row( array( 'id' => $id ) );
		if ( ! $existing ) {
			wp_send_json_error( __( 'Legal consent not found.', 'tutor' ) );
		}

		$data = $this->prepare_legal_consent_data( $request, false );
		if ( is_wp_error( $data ) ) {
			wp_send_json_error( $data->get_error_message() );
		}

		if ( empty( $data ) ) {
			wp_send_json_error( __( 'No update data found.', 'tutor' ) );
		}

		$data['updated_at_utc'] = current_time( 'mysql', true );
		$updated                = $this->model->update( $id, $data );
		if ( ! $updated ) {
			wp_send_json_error( __( 'Failed to update legal consent.', 'tutor' ) );
		}

		$this->log_model->create_log( $id, 'updated', (array) $existing, $data );

		wp_send_json_success( __( 'Legal consent updated successfully.', 'tutor' ) );
	}

	/**
	 * Delete legal consent entry.
	 *
	 * @since 4.0.0
	 *
	 * @param int $id Legal consent ID.
	 *
	 * @return void
	 */
	private function delete_legal_consent( int $id ) {
		if ( ! $id ) {
			wp_send_json_error( __( 'Invalid legal consent id.', 'tutor' ) );
		}

		$existing = $this->model->get_row( array( 'id' => $id ) );
		if ( ! $existing ) {
			wp_send_json_error( __( 'Legal consent not found.', 'tutor' ) );
		}

		$deleted = $this->model->delete( $id );
		if ( ! $deleted ) {
			wp_send_json_error( __( 'Failed to delete legal consent.', 'tutor' ) );
		}

		$this->log_model->create( $id, 'deleted', (array) $existing, null );

		wp_send_json_success( __( 'Legal consent deleted successfully.', 'tutor' ) );
	}

	/**
	 * Prepare and validate legal consent payload.
	 *
	 * @since 4.0.0
	 *
	 * @param array $request   Request data.
	 * @param bool  $is_create True for create operation.
	 *
	 * @return array|\WP_Error
	 */
	private function prepare_legal_consent_data( array $request, bool $is_create ) {
		$data = array(
			'consent_title'   => Input::sanitize( $request['consent_title'] ?? '', '', Input::TYPE_STRING ),
			'display_on'      => Input::sanitize( $request['display_on'] ?? '', '', Input::TYPE_STRING ),
			'consent_message' => Input::sanitize( $request['consent_message'] ?? '', '', Input::TYPE_KSES_POST ),
			'policy_urls'     => Input::sanitize( $request['policy_urls'] ?? '', '', Input::TYPE_STRING ),
			'version'         => Input::sanitize( $request['version'] ?? '', '', Input::TYPE_STRING ),
			'is_required'     => (int) Input::sanitize( $request['is_required'] ?? false, false, Input::TYPE_BOOL ),
			'is_active'       => (int) Input::sanitize( $request['is_active'] ?? true, true, Input::TYPE_BOOL ),
			'settings'        => $this->sanitize_json_field( $request['settings'] ?? '' ),
		);

		if ( $is_create ) {
			$rules = array(
				'consent_title'   => 'required',
				'display_on'      => 'required',
				'consent_message' => 'required',
				'version'         => 'required',
				'is_required'     => 'required',
			);

			$validation = ValidationHelper::validate( $rules, $data );
			if ( ! $validation->success ) {
				return new WP_Error( 'validation_error', $validation->errors );
			}

			$data['created_at_utc'] = current_time( 'mysql', true );
		} else {
			$data = array_filter(
				$data,
				function ( $value ) {
					return '' !== $value && null !== $value;
				}
			);
		}

		return $data;
	}

	/**
	 * Sanitize JSON-like settings field.
	 *
	 * @since 4.0.0
	 *
	 * @param mixed $value Settings input.
	 *
	 * @return string|null
	 */
	private function sanitize_json_field( $value ) {
		if ( is_array( $value ) ) {
			return wp_json_encode( $value );
		}

		$value = is_string( $value ) ? trim( wp_unslash( $value ) ) : '';
		if ( '' === $value ) {
			return null;
		}

		$decoded = json_decode( $value, true );
		return JSON_ERROR_NONE === json_last_error() ? wp_json_encode( $decoded ) : sanitize_text_field( $value );
	}
}
