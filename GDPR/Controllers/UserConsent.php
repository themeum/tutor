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
use WP_User;

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
		add_action( 'tutor_new_user_registered', array( $this, 'store_registration_consent' ), 10, 2 );
		add_action( 'tutor_after_instructor_signup', array( $this, 'store_instructor_registration_consent' ), 10, 2 );
		add_action( 'tutor_after_login_success', array( $this, 'store_login_consent' ), 10, 2 );
		add_action( 'tutor_after_checkout_consent', array( $this, 'store_checkout_consent' ), 10, 2 );
		add_action( 'wp_ajax_tutor_user_consents', array( $this, 'handle_ajax_request' ) );
	}

	/**
	 * Store registration consent
	 *
	 * @since 4.0.0
	 *
	 * @param WP_User $user User object.
	 * @param array   $checked_consents The provided consent fields.
	 *
	 * @return void
	 */
	public function store_registration_consent( WP_User $user, array $checked_consents ): void {
		$this->create_user_consent( $user->ID, LegalConsent::DISPLAY_ON_STD_REG, $checked_consents );
	}

	/**
	 * Store instructor registration consent.
	 *
	 * @since 4.0.0
	 *
	 * @param int   $user_id User id.
	 * @param array $checked_consents The provided consent fields.
	 *
	 * @return void
	 */
	public function store_instructor_registration_consent( int $user_id, array $checked_consents ): void {
		$this->create_user_consent( $user_id, LegalConsent::DISPLAY_ON_INS_REG, $checked_consents );
	}

	/**
	 * Store login consent
	 *
	 * @since 4.0.0
	 *
	 * @param int   $user_id User id.
	 * @param array $checked_consents The provided consent fields.
	 *
	 * @return void
	 */
	public function store_login_consent( int $user_id, array $checked_consents ): void {
		$this->create_user_consent( $user_id, LegalConsent::DISPLAY_ON_LOGIN, $checked_consents );
	}

	/**
	 * Store checkout consent.
	 *
	 * @since 4.0.0
	 *
	 * @param int   $user_id User id.
	 * @param array $checked_consents The provided consent fields.
	 *
	 * @return void
	 */
	public function store_checkout_consent( int $user_id, array $checked_consents ): void {
		$this->create_user_consent( $user_id, LegalConsent::DISPLAY_ON_CHECKOUT, $checked_consents );
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
	 * @param array  $checked_consents Checked consent fields.
	 *
	 * @return void
	 */
	private function create_user_consent( int $user_id, string $display_key, array $checked_consents ) {
		$user_data = get_userdata( $user_id );
		if ( $user_data ) {
			$consents = LegalConsent::get_consent_by_display_key( $display_key );

			if ( tutor_utils()->count( $consents ) ) {
				foreach ( $consents as $consent ) {
					$is_active = LegalConsent::is_active( $consent );

					$args = array(
						'source'        => $display_key,
						'version'       => $consent->version,
						'user_id'       => $user_data->ID,
						'consent_title' => $consent->consent_title,
					);

					$already_given = $this->model->get_row( $args );

					if ( ! $is_active || $already_given ) {
						continue;
					}

					$is_text_only = LegalConsent::is_text_only( $consent );
					if ( $is_text_only ) {
						// Store consent.
						$this->build_and_store( $consent, $user_data, $display_key );
					} else {
						$consent_field      = LegalConsent::get_field_name( $consent );
						$is_checked_consent = in_array( $consent_field, $checked_consents, true );

						if ( ! $is_checked_consent ) {
							continue;
						}

						$this->build_and_store( $consent, $user_data, $display_key );
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
		if ( ! is_array( $records ) ) {
			return array();
		}

		return array_map(
			function ( $record ) {
				if ( ! isset( $record->created_at_utc ) ) {
					return $record;
				}

				$created_at = strtotime( $record->created_at_utc . ' UTC' );
				if ( false === $created_at ) {
					return $record;
				}

				$record->time_ago = sprintf(
					/* translators: %s: human-readable time difference. */
					__( '%s ago', 'tutor' ),
					human_time_diff( $created_at, time() )
				);

				return $record;
			},
			$records
		);
	}

	/**
	 * Build and store give consent
	 *
	 * @since 4.0.0
	 *
	 * @param Object  $consent Consent object.
	 * @param WP_User $user_data User data object.
	 * @param string  $display_key Display key.
	 */
	private function build_and_store( $consent, $user_data, $display_key ) {
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
