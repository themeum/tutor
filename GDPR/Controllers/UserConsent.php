<?php
/**
 * GDPR user content controller.
 *
 * @package Tutor\GDPR\Controllers
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\GDPR\Controllers;

use Tutor\GDPR\Models\UserContents;
use TUTOR\Input;

defined( 'ABSPATH' ) || exit;

/**
 * GDPR AJAX controller for user contents CRUD.
 *
 * @since 4.0.0
 */
class UserConsent {

	/**
	 * User contents model.
	 *
	 * @since 4.0.0
	 *
	 * @var UserContents
	 */
	private $user_contents;

	/**
	 * Constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->user_contents = new UserContents();
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
		add_action( 'wp_ajax_tutor_gdpr_consent_ajax', array( $this, 'handle_user_content_ajax' ) );
	}

	/**
	 * Handle user content CRUD AJAX requests.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function handle_user_content_ajax() {
		$this->validate_ajax_request();

		$action = Input::post( 'crud_action', '' );
		$data   = Input::sanitize_array( $_POST ); //phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce is validated.

		switch ( $action ) {
			case 'create':
				$this->create_user_content( $data );
				break;

			case 'read':
				$this->get_user_content( Input::post( 'id', 0, Input::TYPE_INT ) );
				break;

			case 'list':
				$this->list_user_contents( $data );
				break;

			case 'update':
				$this->update_user_content( Input::post( 'id', 0, Input::TYPE_INT ), $data );
				break;

			case 'delete':
				$this->delete_user_content( Input::post( 'id', 0, Input::TYPE_INT ) );
				break;

			default:
				wp_send_json_error( __( 'Invalid user content action.', 'tutor' ) );
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
	 * Create user content entry.
	 *
	 * @since 4.0.0
	 *
	 * @param array $request Request data.
	 *
	 * @return void
	 */
	private function create_user_content( array $request ) {
		$data = $this->prepare_user_content_data( $request, true );
		if ( is_wp_error( $data ) ) {
			wp_send_json_error( $data->get_error_message() );
		}

		$user_content_id = $this->user_contents->create( $data );
		if ( ! $user_content_id ) {
			wp_send_json_error( __( 'Failed to create user content.', 'tutor' ) );
		}

		wp_send_json_success(
			array(
				'message' => __( 'User content created successfully.', 'tutor' ),
				'id'      => $user_content_id,
			)
		);
	}

	/**
	 * Get single user content.
	 *
	 * @since 4.0.0
	 *
	 * @param int $id User content ID.
	 *
	 * @return void
	 */
	private function get_user_content( int $id ) {
		if ( ! $id ) {
			wp_send_json_error( __( 'Invalid user content id.', 'tutor' ) );
		}

		$item = $this->user_contents->get_row( array( 'id' => $id ) );
		if ( ! $item ) {
			wp_send_json_error( __( 'User content not found.', 'tutor' ) );
		}

		wp_send_json_success( $item );
	}

	/**
	 * Get user content list.
	 *
	 * @since 4.0.0
	 *
	 * @param array $request Request data.
	 *
	 * @return void
	 */
	private function list_user_contents( array $request ) {
		$where = array();
		if ( isset( $request['user_id'] ) && '' !== $request['user_id'] ) {
			$where['user_id'] = (int) $request['user_id'];
		}

		if ( isset( $request['compliance_key'] ) && '' !== $request['compliance_key'] ) {
			$where['compliance_key'] = sanitize_text_field( $request['compliance_key'] );
		}

		if ( isset( $request['accepted'] ) && '' !== $request['accepted'] ) {
			$where['accepted'] = (int) $request['accepted'];
		}

		$items = $this->user_contents->get_all( $where );
		wp_send_json_success( $items );
	}

	/**
	 * Update user content entry.
	 *
	 * @since 4.0.0
	 *
	 * @param int   $id      User content ID.
	 * @param array $request Request data.
	 *
	 * @return void
	 */
	private function update_user_content( int $id, array $request ) {
		if ( ! $id ) {
			wp_send_json_error( __( 'Invalid user content id.', 'tutor' ) );
		}

		$existing = $this->user_contents->get_row( array( 'id' => $id ) );
		if ( ! $existing ) {
			wp_send_json_error( __( 'User content not found.', 'tutor' ) );
		}

		$data = $this->prepare_user_content_data( $request, false );
		if ( is_wp_error( $data ) ) {
			wp_send_json_error( $data->get_error_message() );
		}

		if ( empty( $data ) ) {
			wp_send_json_error( __( 'No update data found.', 'tutor' ) );
		}

		$updated = $this->user_contents->update( $id, $data );
		if ( ! $updated ) {
			wp_send_json_error( __( 'Failed to update user content.', 'tutor' ) );
		}

		wp_send_json_success( __( 'User content updated successfully.', 'tutor' ) );
	}

	/**
	 * Delete user content entry.
	 *
	 * @since 4.0.0
	 *
	 * @param int $id User content ID.
	 *
	 * @return void
	 */
	private function delete_user_content( int $id ) {
		if ( ! $id ) {
			wp_send_json_error( __( 'Invalid user content id.', 'tutor' ) );
		}

		$deleted = $this->user_contents->delete( $id );
		if ( ! $deleted ) {
			wp_send_json_error( __( 'Failed to delete user content.', 'tutor' ) );
		}

		wp_send_json_success( __( 'User content deleted successfully.', 'tutor' ) );
	}

	/**
	 * Prepare and validate user content payload.
	 *
	 * @since 4.0.0
	 *
	 * @param array $request   Request data.
	 * @param bool  $is_create True for create operation.
	 *
	 * @return array|\WP_Error
	 */
	private function prepare_user_content_data( array $request, bool $is_create ) {
		$data = array(
			'user_id'        => isset( $request['user_id'] ) ? (int) $request['user_id'] : 0,
			'user_email'     => isset( $request['user_email'] ) ? sanitize_email( $request['user_email'] ) : '',
			'compliance_key' => isset( $request['compliance_key'] ) ? sanitize_text_field( $request['compliance_key'] ) : '',
			'label_snapshot' => isset( $request['label_snapshot'] ) ? wp_kses_post( $request['label_snapshot'] ) : '',
			'policy_url'     => isset( $request['policy_url'] ) ? esc_url_raw( $request['policy_url'] ) : '',
			'version'        => isset( $request['version'] ) ? sanitize_text_field( $request['version'] ) : '',
			'accepted'       => isset( $request['accepted'] ) ? (int) (bool) $request['accepted'] : 0,
			'ip_address'     => isset( $request['ip_address'] ) ? sanitize_text_field( $request['ip_address'] ) : '',
			'user_agent'     => isset( $request['user_agent'] ) ? sanitize_text_field( $request['user_agent'] ) : '',
			'source'         => isset( $request['source'] ) ? sanitize_text_field( $request['source'] ) : '',
		);

		if ( $is_create ) {
			$required = array( 'compliance_key', 'label_snapshot', 'version' );
			foreach ( $required as $field ) {
				if ( empty( $data[ $field ] ) ) {
					return new \WP_Error( 'validation_error', __( 'Required user content fields are missing.', 'tutor' ) );
				}
			}

			$data['created_at_utc'] = current_time( 'mysql', true );
		} else {
			$data = array_filter(
				$data,
				function( $value ) {
					return '' !== $value && null !== $value;
				}
			);
		}

		return $data;
	}
}
