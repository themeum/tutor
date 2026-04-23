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

use Exception;
use Tutor\GDPR\Models\{LegalConsents, LegalConsentLogs};
use Tutor\Helpers\ValidationHelper;
use TUTOR\Input;
use Tutor\Traits\JsonResponse;
use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * GDPR AJAX controller for legal consents CRUD.
 *
 * @since 4.0.0
 */
class LegalConsent {

	use JsonResponse;

	/**
	 * Consent display places
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	const DISPLAY_ON_SIGNUP       = 'signup';
	const DISPLAY_ON_SIGNIN       = 'signin';
	const DISPLAY_ON_CHECKOUT     = 'checkout';
	const DISPLAY_ON_SUBSCRIPTION = 'subscription';
	const DISPLAY_ON_ENROLLMENT   = 'enrollment';

	/**
	 * Consent method
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	const METHOD_MANDATORY_CHECK = 'mandatory_checkbox';
	const METHOD_OPTIONAL_CHECK  = 'optional_checkbox';
	const METHOD_TEXT_ONLY       = 'text_only';

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
	 *
	 * @param bool $trigger_hooks Trigger hooks or not.
	 */
	public function __construct( $trigger_hooks = true ) {
		$this->model     = new LegalConsents();
		$this->log_model = new LegalConsentLogs();

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
		add_action( 'wp_ajax_tutor_gdpr_legal_consents', array( $this, 'handle_legal_consent_ajax' ) );
		add_filter( 'tutor_localize_data', array( $this, 'extend_localize_data' ) );
	}

	/**
	 * Add legal consent display places to localized data.
	 *
	 * @since 4.0.0
	 *
	 * @param array $localize_data Localized data array.
	 *
	 * @return array
	 */
	public function extend_localize_data( $localize_data ) {
		$localize_data['legal_consent_display_places'] = self::get_consent_places();

		return $localize_data;
	}

	/**
	 * Get the list of display places for legal consent.
	 *
	 * The list is filterable with the 'tutor_legal_consent_display_places' filter hook.
	 *
	 * @since 4.0.0
	 *
	 * @return array List of display place keys.
	 */
	public static function get_consent_places() {
		$places = array(
			self::DISPLAY_ON_SIGNUP,
			self::DISPLAY_ON_SIGNIN,
			self::DISPLAY_ON_CHECKOUT,
		);

		return apply_filters( 'tutor_legal_consent_display_places', $places );
	}

	/**
	 * Get admin display place options for the legal consent settings screen.
	 *
	 * @since 4.0.0
	 *
	 * @return array<string, string>
	 */
	public static function get_display_place_options(): array {
		$labels  = array(
			self::DISPLAY_ON_SIGNUP       => __( 'Sign up page', 'tutor' ),
			self::DISPLAY_ON_SIGNIN       => __( 'Login Page', 'tutor' ),
			self::DISPLAY_ON_CHECKOUT     => __( 'Checkout', 'tutor' ),
			self::DISPLAY_ON_SUBSCRIPTION => __( 'Subscription', 'tutor' ),
			self::DISPLAY_ON_ENROLLMENT   => __( 'Enrollment', 'tutor' ),
		);
		$options = array();

		foreach ( self::get_consent_places() as $place ) {
			$options[ $place ] = $labels[ $place ] ?? ucwords( str_replace( '_', ' ', $place ) );
		}

		return $options;
	}

	/**
	 * Get admin consent method options for the legal consent settings screen.
	 *
	 * @since 4.0.0
	 *
	 * @return array<string, string>
	 */
	public static function get_consent_method_options(): array {
		return array(
			self::METHOD_MANDATORY_CHECK => __( 'Mandatory Checkbox', 'tutor' ),
			self::METHOD_OPTIONAL_CHECK  => __( 'Optional Checkbox', 'tutor' ),
			self::METHOD_TEXT_ONLY       => __( 'Display Text Only', 'tutor' ),
		);
	}

	/**
	 * Get normalized legal consent items for admin rendering.
	 *
	 * @since 4.0.0
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_consents(): array {
		$items = ( new self( false ) )->model->get_all( array() );

		if ( ! is_array( $items ) ) {
			return array();
		}

		return array_map(
			function ( $item ) {
				$item = (array) $item;

				return array(
					'id'          => isset( $item['id'] ) ? (int) $item['id'] : 0,
					'enabled'     => ! empty( $item['is_active'] ) ? 'on' : 'off',
					'title'       => $item['consent_title'] ?? '',
					'display_on'  => $item['display_on'] ?? '',
					'message'     => $item['consent_message'] ?? '',
					'method'      => $item['consent_method'] ?? '',
					'collapsed'   => 'off',
					'content_map' => $item['consent_map'] ?? array(),
				);
			},
			$items
		);
	}

	/**
	 * Handle legal consent CRUD AJAX requests.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function handle_legal_consent_ajax() {
		$this->validate_ajax_request();

		$action = Input::post( 'crud_action', '' );
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
				$this->response_fail( __( 'Invalid legal consent action.', 'tutor' ), 400 );
		}
	}

	/**
	 * Get legal consents by scope
	 *
	 * @since 4.0.0
	 *
	 * @throws Exception If place_key is invalid.
	 *
	 * @param string $place_key Place key like signup, signin, etc.
	 *
	 * @return array Consent places.
	 */
	public static function get_consent_by_display_key( string $place_key ): array {
		if ( ! in_array( $place_key, self::get_consent_places(), true ) ) {
			throw new Exception( esc_html__( 'Invalid place key', 'tutor' ) );
		}

		$where = array(
			array( 'display_on', 'IN', $place_key ),
		);

		$res = ( new self( false ) )->model->get_all( $where );

		return $res ? $res : array();
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

		$consent_map = tutor_is_json( $data['consent_map'] ) ? $data['consent_map'] : null;
		if ( is_null( $consent_map ) ) {
			$this->json_response( __( 'Invalid consent map', 'tutor' ), '', 400 );
		}

		if ( is_wp_error( $data ) ) {
			$this->json_response( '', $data->errors, 400 );
		}

		$wpdb->query( 'START TRANSACTION' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery

		$legal_consent_id = $this->model->create( $data );
		if ( ! $legal_consent_id ) {
			$wpdb->query( 'ROLLBACK' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$this->response_fail( __( 'Failed to create legal consent.', 'tutor' ), 500 );
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
			$this->response_fail( __( 'Failed to create legal consent log.', 'tutor' ), 500 );
		}

		$wpdb->query( 'COMMIT' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery

		$this->json_response(
			__( 'Legal consent created successfully.', 'tutor' ),
			array(
				'id' => $legal_consent_id,
			),
			200
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
			$this->response_fail( __( 'Invalid legal consent id.', 'tutor' ), 400 );
		}

		$item = $this->model->get_row( array( 'id' => $id ) );
		if ( ! $item ) {
			$this->response_fail( __( 'Legal consent not found.', 'tutor' ), 404 );
		}

		$this->response_data( $item );
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

		$consent_title = $request['consent_title'] ?? '';
		if ( ! empty( $consent_title ) ) {
			$where['consent_title'] = Input::sanitize( $consent_title );
		}

		if ( Input::has( 'is_active' ) ) {
			$where['is_active'] = (int) $request['is_active'];
		}

		$items = $this->model->get_all( $where );
		$this->response_data( $items );
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
		global $wpdb;

		if ( ! $id ) {
			$this->response_fail( __( 'Invalid legal consent id.', 'tutor' ), 400 );
		}

		$existing = $this->model->get_row( array( 'id' => $id ) );
		if ( ! $existing ) {
			$this->response_fail( __( 'Legal consent not found.', 'tutor' ), 404 );
		}

		$data = $this->prepare_legal_consent_data( $request, false );
		if ( is_wp_error( $data ) ) {
			$this->json_response( '', $data->errors, 400 );
		}

		if ( isset( $data['consent_map'] ) && ! tutor_is_json( $data['consent_map'] ) ) {
			$this->response_fail( __( 'Invalid consent map.', 'tutor' ), 400 );
		}

		if ( empty( $data ) ) {
			$this->response_fail( __( 'No update data found.', 'tutor' ), 400 );
		}

		$wpdb->query( 'START TRANSACTION' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery

		$data['updated_at_utc'] = current_time( 'mysql', true );
		$updated                = $this->model->update( $id, $data );
		if ( ! $updated ) {
			$wpdb->query( 'ROLLBACK' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$this->response_fail( __( 'Failed to update legal consent.', 'tutor' ), 500 );
		}

		$new_data = array_merge( (array) $existing, $data );

		$log_id = $this->log_model->create(
			array(
				'legal_consent_id' => $id,
				'action'           => 'updated',
				'old_data'         => wp_json_encode( (array) $existing ),
				'new_data'         => wp_json_encode( $new_data ),
				'created_at_utc'   => current_time( 'mysql', true ),
			)
		);
		if ( ! $log_id ) {
			$wpdb->query( 'ROLLBACK' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$this->response_fail( __( 'Failed to create legal consent log.', 'tutor' ), 500 );
		}

		$wpdb->query( 'COMMIT' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery

		$this->response_success( __( 'Legal consent updated successfully.', 'tutor' ) );
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
		global $wpdb;

		if ( ! $id ) {
			$this->response_fail( __( 'Invalid legal consent id.', 'tutor' ), 400 );
		}

		$existing = $this->model->get_row( array( 'id' => $id ) );
		if ( ! $existing ) {
			$this->response_fail( __( 'Legal consent not found.', 'tutor' ), 404 );
		}

		$wpdb->query( 'START TRANSACTION' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery

		$deleted = $this->model->delete( $id );
		if ( ! $deleted ) {
			$wpdb->query( 'ROLLBACK' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$this->response_fail( __( 'Failed to delete legal consent.', 'tutor' ), 500 );
		}

		$log_id = $this->log_model->create(
			array(
				'legal_consent_id' => $id,
				'action'           => 'deleted',
				'old_data'         => wp_json_encode( (array) $existing ),
				'new_data'         => null,
				'created_at_utc'   => current_time( 'mysql', true ),
			)
		);
		if ( ! $log_id ) {
			$wpdb->query( 'ROLLBACK' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$this->response_fail( __( 'Failed to create legal consent log.', 'tutor' ), 500 );
		}

		$wpdb->query( 'COMMIT' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery

		$this->response_success( __( 'Legal consent deleted successfully.', 'tutor' ) );
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
		$request = array_intersect_key( $request, array_flip( $this->model->get_fillable_fields() ) );

		$data = array(
			'consent_title'   => Input::sanitize( $request['consent_title'] ?? '', '', Input::TYPE_STRING ),
			'display_on'      => Input::sanitize( $request['display_on'] ?? '', '', Input::TYPE_STRING ),
			'consent_message' => Input::sanitize( $request['consent_message'] ?? '', '', Input::TYPE_KSES_POST ),
			'consent_map'     => Input::sanitize( $request['consent_map'] ?? '', '', Input::TYPE_STRING ),
			'version'         => Input::sanitize( $request['version'] ?? '', '', Input::TYPE_STRING ),
			'consent_method'  => Input::sanitize( $request['consent_method'] ?? '' ),
			'is_active'       => (int) Input::sanitize( $request['is_active'] ?? true, true, Input::TYPE_BOOL ),
			'settings'        => $this->sanitize_json_field( $request['settings'] ?? '' ),
		);

		if ( $is_create ) {
			$validation = ValidationHelper::validate( $this->get_legal_consent_validation_rules(), $data );
			if ( ! $validation->success ) {
				return new WP_Error( 'validation_error', $validation->errors );
			}

			$data['created_at_utc'] = current_time( 'mysql', true );
		} else {
			$data = array_filter(
				$request,
				function ( $value ) {
					return '' !== $value && null !== $value;
				}
			);
		}

		return $data;
	}

	/**
	 * Get legal consent validation rules.
	 *
	 * @since 4.0.0
	 *
	 * @return array
	 */
	private function get_legal_consent_validation_rules(): array {
		return array(
			'consent_title'   => 'required',
			'display_on'      => 'required',
			'consent_message' => 'required',
			'version'         => 'required',
			'consent_method'  => 'required',
		);
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

	public static function render_consent_field( object $consent ): void {
		$is_required  = self::is_required( $consent );
		$is_text_only = self::METHOD_TEXT_ONLY === $consent->consent_method;
		$field_name   = strtolower( str_replace( ' ', '_', $consent->consent_title ) );

		?>
		<div class="tutor-form-row tutor-mb-8">
			<div class="tutor-input-field">
				<div class="tutor-input-wrapper">
					<?php if ( ! $is_text_only ) : ?>
						<input type="checkbox" id="<?php echo esc_attr( $field_name ); ?>" name="<?php echo esc_attr( $field_name ); ?>" class="tutor-checkbox tutor-checkbox-md" <?php echo esc_attr( $is_required ? 'required' : '' ); ?>>
					<?php endif; ?>
					<label for="<?php echo esc_attr( $field_name ); ?>" class="tutor-label">
						<?php self::render_constructed_label_text( $consent ); ?>
					</label>
				</div>
			</div>
		</div>
		<?php
	}

	public static function is_required( object $consent ): bool {
		return self::METHOD_MANDATORY_CHECK === $consent->consent_method;
	}

	public static function render_constructed_label_text( object $consent ): void {
		if ( empty( $consent ) || empty( $consent->consent_message ) ) {
			return;
		}

		// Decode message (fix &amp; etc).
		$message = html_entity_decode( $consent->consent_message );

		// Normalize map (JSON → array).
		$map = is_array( $consent->consent_map )
			? $consent->consent_map
			: json_decode( $consent->consent_map, true );

		if ( empty( $map ) || ! is_array( $map ) ) {
			echo esc_html( $message );
		}

		// Find all {placeholders}.
		preg_match_all( '/\{([a-zA-Z0-9_\-]+)\}/', $message, $matches );

		if ( empty( $matches[1] ) ) {
			echo esc_html( $message );
		}

		foreach ( $matches[1] as $key ) {

			if ( empty( $map[ $key ] ) ) {
				continue;
			}

			$page_id = (int) $map[ $key ];

			if ( ! $page_id || 'publish' !== get_post_status( $page_id ) ) {
				continue;
			}

			$url   = get_permalink( $page_id );
			$title = get_the_title( $page_id );

			$anchor = sprintf(
				'<a href="%s" target="_blank" rel="noopener noreferrer" class="tutor-consent-link">%s</a>',
				esc_url( $url ),
				esc_html( $title )
			);

			$message = str_replace( '{' . $key . '}', $anchor, $message );
		}

		echo wp_kses(
			$message,
			array(
				'a' => array(
					'href'   => array(),
					'target' => array(),
					'rel'    => array(),
					'class'  => array(),
				),
			)
		);
	}
}
