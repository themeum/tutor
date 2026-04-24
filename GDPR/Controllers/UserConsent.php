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
		$user_data = get_userdata( $user_id );
		if ( $user_data ) {
			$consents = LegalConsent::get_consent_by_display_key( LegalConsent::DISPLAY_ON_LOGIN );

			if ( tutor_utils()->count( $consents ) ) {
				foreach ( $consents as $consent ) {
					$build_consent = LegalConsent::build_consent_snapshot( $consent );
					if ( ! empty( $build_consent ) ) {
						$build_consent['user_id']    = $user_data->ID;
						$build_consent['user_email'] = $user_data->user_email;
						$build_consent['source']     = LegalConsent::DISPLAY_ON_LOGIN;

						// Store consent.
						$this->create( $build_consent );
					}
				}
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
	 * Get all consents
	 *
	 * @since 4.0.0
	 *
	 * @param array $request Request data.
	 *
	 * @return void
	 */
	public static function get_all( array $request ) {
	}
}
