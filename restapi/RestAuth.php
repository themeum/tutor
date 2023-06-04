<?php
/**
 * Manage Rest API Authentication
 *
 * Token create, invoke etc
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
	 * Permissions
	 *
	 * @var string
	 */
	const READ = 'read';

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

		$permission = Input::post( 'permission' );

		$info = json_encode(
			array(
				'key'        => $api_key,
				'secret'     => $api_secret,
				'permission' => $permission,
			)
		);

		// Update user meta.
		$add = add_user_meta(
			get_current_user_id(),
			self::KEYS_USER_META_KEY,
			$info
		);

		if ( $add ) {
			$response = self::prepare_response( $api_key, $api_secret, $permission );
			wp_send_json_success( $response );
		} else {
			wp_send_json_error( tutor_utils()->error_message( '0' ) );
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
			array( 'meta_key' => self::KEYS_USER_META_KEY ),
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
				$credentials         = base64_decode( $base_64_credentials );

				list($api_key, $api_secret) = explode( ':', $credentials );

				if ( self::validate_api_key_secret( $api_key, $api_secret ) ) {
					return true;
				}
			}
		}

		// Key and secret are invalid or not provided.
		header( 'HTTP/1.0 401 Unauthorized' );
		return false;
	}

    /**
     * Prepare html response
     *
     * @since 2.2.1
     *
     * @param string $key api key.
     * @param string $secret api secret.
     * @param string $permission authorization permission.
     *
     * @return string
     */
	public static function prepare_response( $key, $secret, $permission ) {
		$user_id = get_current_user_id();
		ob_start();
		?>
		<tr>
			<td>
				<?php echo esc_html( tutor_utils()->display_name( $user_id ) ); ?>
			</td>
			<td>
				<?php echo esc_html( $key ); ?>
			</td>
			<td>
				<?php echo esc_html( $secret ); ?>
			</td>
			<td>
				<?php echo esc_html( $permission ); ?>
			</td>
			<td>
				<button class="tutor-btn tutor-btn-sm tutor-btn-danger">
					<?php esc_html_e( 'Revoke', 'tutor' ); ?>
				</button>
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
		return $permissions;
	}
}
