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
    const KEYS_USER_META_KEY = 'api-key-secret';
    
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

    public static function generate_api_keys() {
        // Validate nonce.
		tutor_utils()->checking_nonce();

		// Check user permission.
		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

        $api_key    = 'key_' . bin2hex(random_bytes(16));
        $api_secret = 'secret_' . bin2hex(random_bytes(32));

        $permission = Input::post('permission');

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

    function validateApiKeyAndSecret($apiKey, $apiSecret) {
        // Retrieve the stored API key and secret from your storage mechanism based on the given apiKey
        $storedApiKey = "stored_api_key"; // Replace with the actual stored API key
        $storedApiSecret = "stored_api_secret"; // Replace with the actual stored API secret
        
        if ($apiKey === $storedApiKey && $apiSecret === $storedApiSecret) {
            return true; // Key and secret are valid
        }
        
        return false; // Key and secret are invalid
    }
    

    function processApiRequest() {
        $headers = apache_request_headers();
        
        if (isset($headers['Authorization'])) {
            $authorizationHeader = $headers['Authorization'];
            
            if (strpos($authorizationHeader, 'Basic') !== false) {
                $base64Credentials = str_replace('Basic ', '', $authorizationHeader);
                $credentials = base64_decode($base64Credentials);
                
                list($apiKey, $apiSecret) = explode(':', $credentials);
                
                if (validateApiKeyAndSecret($apiKey, $apiSecret)) {
                    // Key and secret are valid, process the API request
                    // Your code to handle the API request goes here
                    echo "API request authorized!";
                    return;
                }
            }
        }
        
        // Key and secret are invalid or not provided
        header('HTTP/1.0 401 Unauthorized');
        echo "Unauthorized";
    }
    

    private static function prepare_response( $key, $secret, $permission ) {
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
            )
        );
        return $permissions;
    }
}