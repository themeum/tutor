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

use Exception;
use Tutor\GDPR\Models\UserConsents;
use Tutor\Helpers\ValidationHelper;
use TUTOR\Input;
use Tutor\Traits\JsonResponse;

defined( 'ABSPATH' ) || exit;

/**
 * GDPR AJAX controller for user contents CRUD.
 *
 * @since 4.0.0
 */
class UserConsent extends BaseController {

	use JsonResponse;

	/**
	 * User contents model.
	 *
	 * @since 4.0.0
	 *
	 * @var UserContents
	 */
	private $model;

	/**
	 * Constructor.
	 *
	 * @since 4.0.0
	 *
	 * @param bool $trigger_hooks When to trigger hook or not.
	 */
	public function __construct( bool $trigger_hooks = true ) {
		$this->model = new UserConsents();

		if ( $trigger_hooks ) {
			$this->register_hooks();
		}
	}

	/**
	 * Register AJAX hooks.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function register_hooks() {
		add_action( 'tutor_after_login_success', array( $this, 'store_login_consent' ) );
		add_action( 'wp_ajax_tutor_user_consents', array( $this, 'handle_ajax_request' ) );
	}

	/**
	 * Store login consent
	 *
	 * @since 4.0.0
	 *
	 * @param int $user_id User id.
	 *
	 * @return void
	 */
	public function store_login_consent( int $user_id ): void {
		$this->create_user_consent( $user_id, LegalConsent::DISPLAY_ON_LOGIN );
	}

	/**
	 * Handle ajax request
	 *
	 * @since 4.0.0
	 *
	 * @return void Send json response
	 */
	public function handle_ajax_request(): void {
		$this->validate_ajax_request();

		$user_action = Input::post( 'user_action' );

		switch ( $user_action ) {
			case 'all_consents_given_by_user':
				$user_id = Input::post( 'user_id', 0, Input::TYPE_INT );

				$validate_user = ValidationHelper::validate(
					array( 'user_id' => 'required|is_exists' ),
					array( 'user_id' => $user_id )
				);

				if ( ! $validate_user->success ) {
					$this->response_bad_request( __( 'Invalid user ID', 'tutor' ) );
				}

				$this->json_response(
					__( 'Consent fetched successfully', 'tutor' ),
					$this->get_all_consents_given_by_user( $user_id )
				);

				break;
			default:
				// code...
				break;
		}
	}


	/**
	 * Check if the user has already given consent for a specific display key and version.
	 *
	 * @since 4.0.0
	 *
	 * @param int    $user_id     ID of the user.
	 * @param string $display_key Consent display key (e.g., registration, login).
	 *
	 * @return void
	 */
	private function create_user_consent( $user_id, $display_key ) {
		$user_data = get_userdata( $user_id );
		if ( $user_data ) {
			$consents = LegalConsent::get_consent_by_display_key( $display_key );

			if ( tutor_utils()->count( $consents ) ) {
				foreach ( $consents as $consent ) {
					$is_consent_given = self::is_consent_given_by_user( $display_key, $consent->version, $user_data->ID );

					if ( ! $is_consent_given ) {
						$build_consent = LegalConsent::build_consent_snapshot( $consent );
						if ( ! empty( $build_consent ) ) {
							$build_consent['user_id']    = $user_data->ID;
							$build_consent['user_email'] = $user_data->user_email;
							$build_consent['source']     = $display_key;

							try {
								$this->create( $build_consent );
							} catch ( \Throwable $th ) {
								tutor_log( $th );
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Retrieve all consents given by a specific user.
	 *
	 * @since 4.0.0
	 *
	 * @param int $user_id ID of the user.
	 *
	 * @return array Array of user consent records.
	 */
	private function get_all_consents_given_by_user( int $user_id ): array {
		$where = array(
			'user_id' => $user_id,
		);

		$records = $this->model->get_all( $where );

		return is_array( $records ) ? $records : array();
	}

	/**
	 * Create user content entry.
	 *
	 * @since 4.0.0
	 *
	 * @throws Exception If failed to store consent.
	 *
	 * @param array $data Request data.
	 *
	 * @return int On success consent id
	 */
	private function create( array $data ) {
		$user_consent_id = $this->model->create( $data );
		if ( ! $user_consent_id ) {
			throw new Exception( esc_html__( 'Failed to store consent', 'tutor' ) );
		}

		return $user_consent_id;
	}

	/**
	 * Check if a user already gave consent for a display key and version.
	 *
	 * @since 4.0.0
	 *
	 * @param string $display_key Consent display key.
	 * @param string $version     Consent version.
	 * @param int    $user_id     User ID. Defaults to current user.
	 *
	 * @return bool
	 */
	private function is_consent_given_by_user( string $display_key, string $version, int $user_id ): bool {
		$user_data = get_userdata( $user_id );
		if ( ! $user_data ) {
			return false;
		}

		$given_consent = $this->model->is_consent_given_by_user( $user_id, $display_key, $version );

		return $given_consent;
	}
}
