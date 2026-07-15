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
	 * @param bool $register_hooks When to trigger hook or not.
	 */
	public function __construct( bool $register_hooks = true ) {
		$this->model = new UserConsents();

		if ( $register_hooks ) {
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
		add_action( 'tutor_new_instructor_registered', array( $this, 'store_instructor_registration_consent' ), 10, 2 );
		add_action( 'tutor_after_login_success', array( $this, 'store_login_consent' ), 10, 2 );
		add_action( 'tutor_after_checkout_consent', array( $this, 'store_checkout_consent' ), 10, 2 );
		add_action( 'wp_ajax_tutor_user_consents', array( $this, 'handle_ajax_request' ) );
		add_action( 'tutor_render_consent_logs_button', array( $this, 'render_consent_logs_button' ) );
		add_action( 'tutor_render_consent_logs_modal', array( $this, 'render_consent_logs_modal' ) );
		add_filter( 'manage_users_columns', array( $this, 'add_consent_logs_column' ) );
		add_filter( 'manage_users_custom_column', array( $this, 'render_consent_logs_column' ), 10, 3 );
	}

	/**
	 * Add consent logs column to user list table.
	 *
	 * @since 4.0.0
	 *
	 * @param array $columns User list table columns.
	 *
	 * @return array
	 */
	public function add_consent_logs_column( $columns ) {
		$columns['consent_logs'] = __( 'Consent Logs', 'tutor' );

		ob_start();
		$this->render_consent_logs_modal();
		$columns['consent_logs_modal'] = ob_get_clean();

		return $columns;
	}

	/**
	 * Render consent logs column.
	 *
	 * @since 4.0.0
	 *
	 * @param string $value       Column value.
	 * @param string $column_name Column name.
	 * @param int    $user_id     User ID.
	 */
	public function render_consent_logs_column( $value, $column_name, $user_id ) {
		if ( 'consent_logs' !== $column_name ) {
			return $value;
		}

		$user = get_userdata( $user_id );

		if ( ! $user ) {
			return $value;
		}

		$value = '<button type="button" class="tutor-btn tutor-btn-outline-primary tutor-btn-sm" data-tutor-modal-target="tutor-consent-logs-modal" data-consent-logs-trigger data-user-id="' . esc_attr( $user_id ) . '" data-user-name="' . esc_attr( $user->display_name ) . '" data-user-joined="' . esc_attr( $user->user_registered ) . '" data-user-email="' . esc_attr( $user->user_email ) . '" data-user-login="' . esc_attr( $user->user_login ) . '" data-avatar-src="' . esc_url( tutor_utils()->get_user_avatar_url( $user_id ) ) . '"><i class="tutor-icon-eye-line tutor-mr-8" aria-hidden="true"></i>' . esc_html__( 'View Logs', 'tutor' ) . '</button>';

		return $value;
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

				$consents = $this->get_all_consents_given_by_user( $user_id );

				$this->json_response(
					__( 'Consent fetched successfully', 'tutor' ),
					$consents
				);

				break;
			default:
				$this->response_bad_request( __( 'Invalid action', 'tutor' ) );
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

		$records = array_map(
			function ( $record ) {
				if ( ! isset( $record->created_at_gmt ) ) {
					return $record;
				}

				$created_at = strtotime( $record->created_at_gmt . ' UTC' );
				if ( false === $created_at ) {
					return $record;
				}

				$record->time_ago = sprintf(
					/* translators: %s human-readable time difference. */
					__( '%s ago', 'tutor' ),
					human_time_diff( $created_at, time() )
				);

				return $record;
			},
			$records
		);

		return $records;
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

	/**
	 * Render consent logs button.
	 * Called via action hook.
	 *
	 * @since 4.0.0
	 *
	 * @param object $user_data User list item object.
	 */
	public function render_consent_logs_button( $user_data ): void {
		$user_id = $user_data->ID ?? 0;
		if ( ! $user_id ) {
			return;
		}

		$user_name   = $user_data->display_name ?? '';
		$user_joined = $user_data->user_registered ?? '';
		$user_email  = $user_data->user_email ?? '';
		$user_login  = $user_data->user_login ?? '';
		$avatar_src  = get_avatar_url( $user_id, array( 'size' => 40 ) );
		?>
		<div class="tutor-dropdown-parent">
			<button type="button" class="tutor-iconic-btn" action-tutor-dropdown="toggle">
				<span class="tutor-icon-kebab-menu" aria-hidden="true"></span>
			</button>
			<div id="user-actions-<?php echo esc_attr( $user_id ); ?>" class="tutor-dropdown tutor-dropdown-dark tutor-text-left">
				<button
					type="button"
					class="tutor-dropdown-item"
					data-tutor-modal-target="tutor-consent-logs-modal"
					data-consent-logs-trigger
					data-user-id="<?php echo esc_attr( $user_id ); ?>"
					data-user-name="<?php echo esc_attr( $user_name ); ?>"
					data-user-joined="<?php echo esc_attr( $user_joined ); ?>"
					data-user-email="<?php echo esc_attr( $user_email ); ?>"
					data-user-login="<?php echo esc_attr( $user_login ); ?>"
					data-avatar-src="<?php echo esc_url( $avatar_src ); ?>"
				>
					<i class="tutor-icon-file-text tutor-mr-8" aria-hidden="true"></i>
					<span><?php esc_html_e( 'Consent Logs', 'tutor' ); ?></span>
				</button>
			</div>
		</div>
		<?php
	}

	/**
	 * Render consent logs modal.
	 * Called via action hook.
	 *
	 * @since 4.0.0
	 */
	public function render_consent_logs_modal(): void {
		include tutor()->path . 'views/templates/consent-logs-modal.php';
	}
}
