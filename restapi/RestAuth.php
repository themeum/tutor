<?php
/**
 * Manage Rest API Authentication
 *
 * API key, secret create, invoke etc
 *
 * @package Tutor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.2.1
 */

namespace TUTOR;

use Tutor\Helpers\QueryHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rest API authentication
 *
 * @since 2.2.1
 */
class RestAuth {

	/**
	 * Read Permissions
	 *
	 * @var string
	 */
	const READ = 'Read';

	/**
	 * Write Permissions
	 *
	 * @var string
	 */
	const WRITE = 'Write';

	/**
	 * Delete Permissions
	 *
	 * @var string
	 */
	const DELETE = 'Delete';

	/**
	 * Read Write Permissions
	 *
	 * @var string
	 */
	const READ_WRITE = 'Read/Write';

	/**
	 * All Permissions
	 *
	 * @var string
	 */
	const ALL = 'All';

	/**
	 * User meta key to store key, secret, permission info
	 *
	 * @var string
	 */
	const KEYS_USER_META_KEY = 'tutor-api-key-secret';

	/**
	 * Register hooks.
	 *
	 * @since 2.2.1
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'wp_ajax_tutor_generate_api_keys', __CLASS__ . '::generate_api_keys' );
		add_action( 'wp_ajax_tutor_update_api_permission', __CLASS__ . '::update_api_permission' );
		add_action( 'wp_ajax_tutor_revoke_api_keys', __CLASS__ . '::revoke_api_keys' );
	}

	/**
	 * Generate api keys
	 *
	 * @since 2.2.1
	 *
	 * @return void send wp_json response
	 */
	public static function generate_api_keys() {
		// Validate nonce.
		tutor_utils()->checking_nonce();

		// Check user permission.
		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$api_key    = 'key_' . bin2hex( random_bytes( 16 ) );
		$api_secret = 'secret_' . bin2hex( random_bytes( 32 ) );

		$permission  = Input::post( 'permission' );
		$description = Input::post( 'description', '', Input::TYPE_TEXTAREA );

		$info = wp_json_encode(
			array(
				'key'         => $api_key,
				'secret'      => $api_secret,
				'permission'  => $permission,
				'description' => $description,
			)
		);

		// Update user meta.
		$add = add_user_meta(
			get_current_user_id(),
			self::KEYS_USER_META_KEY,
			$info
		);

		if ( $add ) {
			$response = self::prepare_response( $add, $api_key, $api_secret, $permission, $description );
			wp_send_json_success( $response );
		} else {
			wp_send_json_error( tutor_utils()->error_message( '0' ) );
		}

	}


	/**
	 * Update api permission
	 *
	 * @since 2.5.0
	 *
	 * @return void send wp_json response
	 */
	public static function update_api_permission() {
		global $wpdb;

		// Validate nonce.
		tutor_utils()->checking_nonce();

		// Check user permission.
		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$meta_id     = Input::post( 'meta_id', 0, Input::TYPE_INT );
		$permission  = Input::post( 'permission' );
		$description = Input::post( 'description', '', Input::TYPE_TEXTAREA );

		$info       = QueryHelper::get_row( $wpdb->usermeta, array( 'umeta_id' => $meta_id ), 'umeta_id' );
		$meta_value = json_decode( $info->meta_value );

		$meta_value->permission  = $permission;
		$meta_value->description = $description;

		// Update user meta.
		try {
			QueryHelper::update(
				$wpdb->usermeta,
				array( 'meta_value' => json_encode( $meta_value ) ),
				array( 'umeta_id' => $meta_id )
			);

			$response = self::prepare_response( $meta_id, $meta_value->key, $meta_value->secret, $permission, $description );
			wp_send_json_success( $response );

		} catch ( \Throwable $th ) {
			wp_send_json_error( $th->getMessage() );
		}
	}

	/**
	 * Revoke api keys
	 *
	 * @since 2.2.1
	 *
	 * @return void send wp_json response
	 */
	public static function revoke_api_keys() {
		// Validate nonce.
		tutor_utils()->checking_nonce();

		// Check user permission.
		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$meta_id = Input::post( 'meta_id', 0, Input::TYPE_INT );

		if ( ! $meta_id ) {
			wp_send_json_error( __( 'Invalid meta id', 'tutor' ) );
		}

		// Delete api keys.
		global $wpdb;
		$delete = QueryHelper::delete( $wpdb->usermeta, array( 'umeta_id' => $meta_id ) );

		if ( $delete ) {
			wp_send_json_success( __( 'API keys permanently revoked', 'tutor' ) );
		} else {
			wp_send_json_error( __( 'API keys revoke failed, please try again.', 'tutor' ) );
		}
	}

	/**
	 * Check if api key & secret is valid
	 *
	 * @since 2.2.1
	 *
	 * @param string $api_key api key.
	 * @param string $api_secret api secret.
	 *
	 * @return boolean
	 */
	public static function validate_api_key_secret( $api_key, $api_secret ) {
		global $wpdb;
		$table = $wpdb->usermeta;

		$valid = false;

		$results = QueryHelper::get_all(
			$table,
			array( 'meta_key' => self::KEYS_USER_META_KEY ), //phpcs:ignore
			'umeta_id'
		);

		if ( is_array( $results ) && count( $results ) ) {
			foreach ( $results as $result ) {
				$result = json_decode( $result->meta_value );
				if ( $result->key === $api_key && $result->secret === $api_secret ) {
					$valid = true;
					break;
				}
			}
		}

		return $valid;
	}

	/**
	 * Process api request
	 *
	 * @since 2.2.1
	 *
	 * @return boolean
	 */
	public static function process_api_request() {
		$headers = apache_request_headers();

		if ( isset( $headers['Authorization'] ) ) {
			$authorization_header = $headers['Authorization'];

			if ( strpos( $authorization_header, 'Basic' ) !== false ) {
				$base_64_credentials = str_replace( 'Basic ', '', $authorization_header );
				$credentials         = base64_decode( $base_64_credentials ); //phpcs:ignore

				list($api_key, $api_secret) = explode( ':', $credentials );

				if ( self::validate_api_key_secret( $api_key, $api_secret ) ) {
					return true;
				}
			}
		}

		// Key and secret are invalid or not provided.
		return false;
	}

	/**
	 * Prepare html response
	 *
	 * @since 2.2.1
	 *
	 * @param int    $meta_id meta id.
	 * @param string $key api key.
	 * @param string $secret api secret.
	 * @param string $permission authorization permission.
	 *
	 * @return string
	 */
	public static function prepare_response( $meta_id, $key, $secret, $permission, $description = '' ) {
		$user_id = get_current_user_id();
		ob_start();
		?>
		<tr id="<?php echo esc_attr( $meta_id ); ?>">
			<td>
				<?php echo esc_html( tutor_utils()->display_name( $user_id ) ); ?>
			</td>
			<td>
				<a class="tutor-btn tutor-btn-outline-primary tutor-btn-sm">
					<span class="tutor-icon-copy tutor-mr-8"></span>
					<span class="tutor-copy-text" data-text="<?php echo esc_attr( $key ); ?>">
						<?php echo esc_html( substr( $key, 0, 5 ) . '...' ); ?>
					</span>
				</a>
			</td>
			<td>
				<a class="tutor-btn tutor-btn-outline-primary tutor-btn-sm">
					<span class="tutor-icon-copy tutor-mr-8"></span>
					<span class="tutor-copy-text" data-text="<?php echo esc_attr( $secret ); ?>">
						<?php echo esc_html( substr( $secret, 0, 9 ) . '...' ); ?>
					</span>
				</a>
			</td>
			<td>
				<?php echo esc_html( $permission ); ?>
				<?php if ( ! empty( $description ) ) : ?>
				<div class="tooltip-wrap tooltip-icon-custom" >
					<i class="tutor-fs-7 tutor-icon-circle-info-o tutor-color-muted tutor-ml-4"></i>
					<span class="tooltip-txt tooltip-bottom">
						<?php echo esc_textarea( $description ); ?>
					</span>
				</div>
				<?php endif; ?>
			</td>
			<td>
				<div class="tutor-dropdown-parent">
					<button type="button" class="tutor-iconic-btn" action-tutor-dropdown="toggle">
						<span class="tutor-icon-kebab-menu" area-hidden="true"></span>
					</button>
					<div class="tutor-dropdown tutor-dropdown-dark tutor-text-left">
						<a href="javascript:void(0)" class="tutor-dropdown-item" data-tutor-modal-target="tutor-update-permission-modal" data-update-id="<?php echo esc_attr( $meta_id ); ?>" data-permission="<?php echo esc_attr( $permission ); ?>" data-description="<?php echo esc_attr( $description ); ?>">
							<i class="tutor-icon-edit tutor-mr-8" area-hidden="true" data-update-id="<?php echo esc_attr( $meta_id ); ?>" data-permission="<?php echo esc_attr( $permission ); ?>" data-description="<?php echo esc_attr( $description ); ?>"></i>
							<span data-update-id="<?php echo esc_attr( $meta_id ); ?>" data-permission="<?php echo esc_attr( $permission ); ?>" data-description="<?php echo esc_attr( $description ); ?>"><?php esc_html_e( 'Edit', 'tutor' ); ?></span>
						</a>
						<a href="javascript:void(0)" class="tutor-dropdown-item" data-meta-id="<?php echo esc_attr( $meta_id ); ?>">
							<i class="tutor-icon-trash-can-bold tutor-mr-8" area-hidden="true" data-meta-id="<?php echo esc_attr( $meta_id ); ?>"></i>
							<span data-meta-id="<?php echo esc_attr( $meta_id ); ?>"><?php esc_html_e( 'Revoke', 'tutor' ); ?></span>
						</a>
					</div>
				</div>
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get available permission
	 *
	 * @since 2.2.1
	 *
	 * @return array
	 */
	public static function available_permissions(): array {
		$permissions = array(
			array(
				'value' => self::READ,
				'label' => __( 'Read', 'tutor' ),
			),
		);
		return apply_filters( 'tutor_rest_api_permissions', $permissions );
	}
}
