<?php
/**
 * GDPR compliance logs controller.
 *
 * @package Tutor\GDPR\Controllers
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\GDPR\Controllers;

use Tutor\GDPR\Models\LegalConsentLogs as Model;
use TUTOR\Input;

defined( 'ABSPATH' ) || exit;

/**
 * GDPR AJAX controller for compliance logs.
 *
 * @since 4.0.0
 */
class LegalConsentLogs {

	/**
	 * Logs model.
	 *
	 * @since 4.0.0
	 *
	 * @var LegalConsentLogs
	 */
	private $model;

	/**
	 * Constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->model = new Model();
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
		add_action( 'wp_ajax_tutor_gdpr_compliance_log_ajax', array( $this, 'handle_log_ajax' ) );
	}

	/**
	 * Handle compliance log AJAX requests.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function handle_log_ajax() {
		$this->validate_ajax_request();

		$action = Input::post( 'crud_action', '' );
		$data   = Input::sanitize_array( $_POST ); //phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce is validated.

		switch ( $action ) {
			case 'read':
				$this->get_log( Input::post( 'id', 0, Input::TYPE_INT ) );
				break;

			case 'list':
				$this->list_logs( $data );
				break;

			case 'delete':
				$this->delete_log( Input::post( 'id', 0, Input::TYPE_INT ) );
				break;

			default:
				wp_send_json_error( __( 'Invalid compliance log action.', 'tutor' ) );
		}
	}

	/**
	 * Create a compliance log row.
	 *
	 * @since 4.0.0
	 *
	 * @param int        $compliance_id Compliance ID.
	 * @param string     $action        Action name.
	 * @param array|null $old_data      Old payload.
	 * @param array|null $new_data      New payload.
	 *
	 * @return void
	 */
	public function create_log( int $compliance_id, string $action, ?array $old_data, ?array $new_data ) {
		$this->model->create(
			array(
				'compliance_id'  => $compliance_id,
				'action'         => $action,
				'old_data'       => is_null( $old_data ) ? null : wp_json_encode( $old_data ),
				'new_data'       => is_null( $new_data ) ? null : wp_json_encode( $new_data ),
				'created_at_utc' => current_time( 'mysql', true ),
			)
		);
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
	 * Get single compliance log.
	 *
	 * @since 4.0.0
	 *
	 * @param int $id Log ID.
	 *
	 * @return void
	 */
	private function get_log( int $id ) {
		if ( ! $id ) {
			wp_send_json_error( __( 'Invalid log id.', 'tutor' ) );
		}

		$item = $this->model->get_row( array( 'id' => $id ) );
		if ( ! $item ) {
			wp_send_json_error( __( 'Log not found.', 'tutor' ) );
		}

		wp_send_json_success( $item );
	}

	/**
	 * Get compliance logs list.
	 *
	 * @since 4.0.0
	 *
	 * @param array $request Request data.
	 *
	 * @return void
	 */
	private function list_logs( array $request ) {
		$where = array();
		if ( isset( $request['compliance_id'] ) && '' !== $request['compliance_id'] ) {
			$where['compliance_id'] = (int) $request['compliance_id'];
		}

		if ( isset( $request['action'] ) && '' !== $request['action'] ) {
			$where['action'] = sanitize_text_field( $request['action'] );
		}

		$items = $this->model->get_all( $where );
		wp_send_json_success( $items );
	}

	/**
	 * Delete compliance log entry.
	 *
	 * @since 4.0.0
	 *
	 * @param int $id Log ID.
	 *
	 * @return void
	 */
	private function delete_log( int $id ) {
		if ( ! $id ) {
			wp_send_json_error( __( 'Invalid log id.', 'tutor' ) );
		}

		$deleted = $this->model->delete( $id );
		if ( ! $deleted ) {
			wp_send_json_error( __( 'Failed to delete log.', 'tutor' ) );
		}

		wp_send_json_success( __( 'Log deleted successfully.', 'tutor' ) );
	}
}
