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
use Tutor\Traits\JsonResponse;
use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * GDPR AJAX controller for legal consents CRUD.
 *
 * @since 4.0.0
 */
class LegalConsent extends BaseController {

	use JsonResponse;

	/**
	 * Consent display places
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	const DISPLAY_ON_LOGIN        = 'login';
	const DISPLAY_ON_STD_REG      = 'student_registration';
	const DISPLAY_ON_INS_REG      = 'instructor_registration';
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
	 * @param bool $register_hooks Trigger hooks or not.
	 */
	public function __construct( $register_hooks = true ) {
		$this->model     = new LegalConsents();
		$this->log_model = new LegalConsentLogs();

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
		add_action( 'wp_ajax_tutor_gdpr_legal_consents', array( $this, 'handle_legal_consent_ajax' ) );
		add_filter( 'tutor_localize_data', array( $this, 'extend_localize_data' ) );
		add_action( 'tutor_login_form_end', array( $this, 'show_consent_field_on_login_form' ) );
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
	 * Show consent field on the login form if available
	 *
	 * @since 4.0.0
	 */
	public function show_consent_field_on_login_form() {
		$consents = self::get_consent_by_display_key( self::DISPLAY_ON_LOGIN );
		if ( tutor_utils()->count( $consents ) ) {
			foreach ( $consents as $consent ) {
				self::render_consent_field( $consent, 'tutor-mt-8 tutor-mb-24' );
			}
		}
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
			self::DISPLAY_ON_STD_REG,
			self::DISPLAY_ON_LOGIN,
			self::DISPLAY_ON_CHECKOUT,
		);

		$is_marketplace_enabled = tutor_utils()->get_option( 'enable_course_marketplace', false );

		if ( $is_marketplace_enabled ) {
			$places[] = self::DISPLAY_ON_INS_REG;
		}

		return apply_filters( 'tutor_legal_consent_display_places', $places );
	}

	/**
	 * Get display place options for the legal consent settings screen.
	 *
	 * @since 4.0.0
	 *
	 * @return array<string, string>
	 */
	public static function get_display_place_options(): array {
		$labels  = array(
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
	 * Get consent method options for the legal consent settings screen.
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
	 * Get legal consent items.
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

		$normalize_display_on = function ( $display_on ): array {
			if ( is_array( $display_on ) ) {
				return $display_on;
			}

			$display_on = array_filter( array_map( 'trim', explode( ',', (string) $display_on ) ) );
			$normalized = array_combine( $display_on, $display_on );

			if ( false === $normalized ) {
				return array();
			}

			return $normalized;
		};

		$normalize_content_map = function ( $content_map ): array {
			if ( is_array( $content_map ) ) {
				return $content_map;
			}

			if ( ! is_string( $content_map ) || '' === $content_map ) {
				return array();
			}

			$decoded = json_decode( $content_map, true );

			return is_array( $decoded ) ? $decoded : array();
		};

		return array_map(
			function ( $item ) use ( $normalize_content_map, $normalize_display_on ) {
				$item = (array) $item;

				return array(
					'id'          => isset( $item['id'] ) ? (int) $item['id'] : 0,
					'enabled'     => ! empty( $item['is_active'] ) ? 'on' : 'off',
					'title'       => $item['consent_title'] ?? '',
					'display_on'  => $normalize_display_on( $item['display_on'] ?? '' ),
					'message'     => $item['consent_message'] ?? '',
					'method'      => $item['consent_method'] ?? self::METHOD_MANDATORY_CHECK,
					'content_map' => $normalize_content_map( $item['consent_map'] ?? array() ),
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
	 * @param string $place_key Place key like signup, signin, etc.
	 *
	 * @return array Consent places.
	 */
	public static function get_consent_by_display_key( string $place_key ): array {
		if ( ! in_array( $place_key, self::get_consent_places(), true ) ) {
			return array();
		}

		$res = ( new self( false ) )->model->get_consents_by_display_key( $place_key );

		return $res ? $res : array();
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
			$this->json_response( '', $data->errors, 400 );
		}

		$consent_map = tutor_is_json( $data['consent_map'] ) ? $data['consent_map'] : null;
		if ( is_null( $consent_map ) ) {
			$this->json_response( __( 'Invalid consent map', 'tutor' ), '', 400 );
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
				'created_at_gmt'   => current_time( 'mysql', true ),
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
			$this->response_bad_request( __( 'Validation error', 'tutor' ), $data->errors, 400 );
		}

		if ( isset( $data['consent_map'] ) && ! tutor_is_json( $data['consent_map'] ) ) {
			$this->response_fail( __( 'Invalid consent map.', 'tutor' ), 400 );
		}

		if ( empty( $data ) ) {
			$this->response_fail( __( 'No update data found.', 'tutor' ), 400 );
		}

		$wpdb->query( 'START TRANSACTION' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery

		$data['version']        = (int) $existing->version + 1;
		$data['updated_at_gmt'] = current_time( 'mysql', true );
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
				'created_at_gmt'   => current_time( 'mysql', true ),
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
				'created_at_gmt'   => current_time( 'mysql', true ),
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

			$data['created_at_gmt'] = current_time( 'mysql', true );
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

	/**
	 * Render consent field markup.
	 *
	 * @since 4.0.0
	 *
	 * @param object $consent Consent settings object.
	 * @param string $wrapper_cs_class Wrapper css class for styling.
	 *
	 * @return void
	 */
	public static function render_consent_field( object $consent, string $wrapper_cs_class = '' ): void {
		if ( ! $consent->is_active ) {
			return;
		}

		$allowed_places = self::get_consent_places();

		// Normalize display_on to array.
		if ( is_array( $consent->display_on ) ) {
			$display_on = array_map( 'strval', array_values( $consent->display_on ) );
		} else {
			$display_on = array_filter( array_map( 'trim', explode( ',', (string) ( $consent->display_on ?? '' ) ) ) );
		}

		if ( empty( array_intersect( $display_on, $allowed_places ) ) ) {
			return;
		}

		$is_required  = self::is_required( $consent );
		$is_text_only = self::METHOD_TEXT_ONLY === $consent->consent_method;
		$field_name   = self::get_field_name( $consent );

		?>
		<div class="tutor-form-row <?php echo esc_attr( $wrapper_cs_class ); ?>">
			<div class="tutor-input-field">
				<div class="tutor-input-wrapper tutor-form-check tutor-d-flex" style="align-items: start;">
					<?php if ( ! $is_text_only ) : ?>
						<input type="checkbox" id="<?php echo esc_attr( $field_name ); ?>" name="<?php echo esc_attr( $field_name ); ?>" class="tutor-checkbox tutor-checkbox-md tutor-form-check-input" style="margin-top: 2px!important;" <?php echo esc_attr( $is_required ? 'required' : '' ); ?>>
					<?php endif; ?>
					<label for="<?php echo esc_attr( $field_name ); ?>" class="tutor-label">
						<?php self::render_constructed_label_text( $consent ); ?>
					</label>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Check whether a consent is required.
	 *
	 * @since 4.0.0
	 *
	 * @param object $consent Consent settings object.
	 *
	 * @return bool
	 */
	public static function is_required( object $consent ): bool {
		return self::METHOD_MANDATORY_CHECK === $consent->consent_method;
	}

	/**
	 * Check whether a consent is active.
	 *
	 * @since 4.0.0
	 *
	 * @param object $consent Consent settings object.
	 *
	 * @return bool
	 */
	public static function is_active( object $consent ): bool {
		$is_active = (int) $consent->is_active;
		return $is_active ? true : false;
	}

	/**
	 * Check whether a consent is text only without checkbox.
	 *
	 * @since 4.0.0
	 *
	 * @param object $consent Consent settings object.
	 *
	 * @return bool
	 */
	public static function is_text_only( object $consent ): bool {
		return self::METHOD_TEXT_ONLY === $consent->consent_method;
	}

	/**
	 * Get the consent field name
	 *
	 * @since 4.0.0
	 *
	 * @param object $consent Consent settings object.
	 *
	 * @return string
	 */
	public static function get_field_name( object $consent ): string {
		return strtolower( str_replace( ' ', '_', $consent->consent_title . '_' . $consent->id ) );
	}


	/**
	 * Render consent label text with optional linked placeholders.
	 *
	 * @since 4.0.0
	 *
	 * @param object $consent Consent settings object.
	 *
	 * @return void
	 */
	private static function render_constructed_label_text( object $consent ): void {
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
			return;
		}

		// Find all {placeholders}.
		preg_match_all( '/\{([a-zA-Z0-9_\-]+)\}/', $message, $matches );

		if ( empty( $matches[1] ) ) {
			echo esc_html( $message );
			return;
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
				'<a href="%s" target="_blank" rel="noopener noreferrer" class="tutor-consent-link" style="display:contents;">%s</a>',
				esc_url( $url ),
				esc_html( $title )
			);

			$message = str_replace( '{' . $key . '}', $anchor, $message );
		}

		add_filter(
			'safe_style_css',
			function ( $styles ) {
				$styles[] = 'display';
				return $styles;
			}
		);

		echo wp_kses(
			$message,
			array(
				'a' => array(
					'href'   => array(),
					'target' => array(),
					'rel'    => array(),
					'class'  => array(),
					'style'  => true,
				),
			)
		);
	}

	/**
	 * Check if the display place has consent & validate it
	 *
	 * @since 4.0.0
	 *
	 * @param string $display_key Display key.
	 * @param array  $request Input request.
	 *
	 * @return WP_Error|array WP_Error when the consent is required but not present in the req. Array
	 * contain the given consent fields.
	 */
	public static function validate_consent( string $display_key, array $request ) {
		$consents = self::get_consent_by_display_key( $display_key );

		// Keep the fields where user has given consent.
		$res = array();

		if ( tutor_utils()->count( $consents ) ) {
			foreach ( $consents as $consent ) {
				$is_active = (int) $consent->is_active;
				if ( ! $is_active ) {
					continue;
				}

				$field_name  = self::get_field_name( $consent );
				$is_required = self::is_required( $consent );
				$is_checked  = $request[ $field_name ] ?? 0;

				if ( $is_required && ! $is_checked ) {
					return new WP_Error( 'consent_error', __( 'Please accept the consent field', 'tutor' ) );
				}

				if ( $is_checked ) {
					array_push( $res, $field_name );
				}
			}
		} else {
			$terms_conditions_link = tutor_utils()->get_toc_page_link();
			if ( $terms_conditions_link ) {
				$is_checked = self::DISPLAY_ON_CHECKOUT === $display_key
					? ( $request['agree_to_terms'] ?? 0 )
					: ( $request['terms_conditions'] ?? 0 );
				if ( ! $is_checked ) {
					$required_fields['terms_conditions'] = __( 'Please accept the Terms and Conditions to continue', 'tutor' );
				}

				array_push( $res, self::DISPLAY_ON_CHECKOUT === $display_key ? 'agree_to_terms' : 'terms_conditions' );
			}
		}

		return $res;
	}

	/**
	 * Build a snapshot of the consent message and associated links.
	 *
	 * This method decodes the consent message, replaces any placeholders with finalized anchor tags,
	 * and constructs a links snapshot containing details about the linked pages.
	 *
	 * @since 4.0.0
	 *
	 * @param object $consent Consent object containing message and map details.
	 *
	 * @return array Array containing the processed consent message and the links snapshot.
	 */
	public static function build_consent_snapshot( object $consent ): array {
		if ( empty( $consent ) || empty( $consent->consent_message ) ) {
			return array();
		}

		// Decode message.
		$message = html_entity_decode( $consent->consent_message );

		// Normalize map.
		$map = is_array( $consent->consent_map )
			? $consent->consent_map
			: json_decode( $consent->consent_map, true );

		$links_snapshot = array();

		// Replace placeholders with final anchors.
		preg_match_all( '/\{([a-zA-Z0-9_\-]+)\}/', $message, $matches );

		if ( ! empty( $matches[1] ) && is_array( $map ) ) {

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

				// Build anchor (snapshot should NOT depend on future changes).
				$anchor = sprintf(
					'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
					esc_url( $url ),
					esc_html( $title )
				);

				$message = str_replace( '{' . $key . '}', $anchor, $message );

				// Store link snapshot separately (optional but powerful).
				$links_snapshot[ $key ] = array(
					'page_id' => $page_id,
					'url'     => $url,
					'title'   => $title,
				);
			}
		}

		$plain_text = wp_strip_all_tags( $message );

		return array(
			'consent_title'             => $consent->consent_title ?? '',
			'version'                   => $consent->version ?? 1,
			'label_snapshot'            => $message,      // PRIMARY legal proof.
			'label_snapshot_plain_text' => $plain_text, // Fallback plain text.
			'links_snapshot'            => wp_json_encode( $links_snapshot ),
			'consent_method'            => $consent->consent_method ?? null,
			'created_at_gmt'            => gmdate( 'Y-m-d H:i:s' ),
			'ip_address'                => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
			'user_agent'                => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
		);
	}
}
