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

use Tutor\Core\DB;
use Tutor\Core\Input;
use Tutor\Helpers\HttpHelper;
use Tutor\Models\CourseModel;
use Tutor\Models\EnrollmentModel;
use Tutor\Models\QuizModel;
use WP_REST_Request;

defined( 'ABSPATH' ) || exit;

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
	 * Usermeta: refresh token hashes (keyed per user; lookup via signed JWT `sub`).
	 *
	 * @var string
	 */
	const REFRESH_META_KEY = 'tutor_api_refresh_tokens';

	/**
	 * Usermeta: access token version (invalidates JWTs).
	 *
	 * @var string
	 */
	const TOKEN_VERSION_META = 'tutor_api_token_version';

	/**
	 * Option for JWT HMAC secret override.
	 *
	 * @var string
	 */
	const JWT_SECRET_OPTION = 'tutor_rest_jwt_secret';

	/**
	 * TTL value meaning no expiration (unlimited).
	 *
	 * @since 4.2.0
	 *
	 * @var int
	 */
	const TTL_UNLIMITED = 0;

	/**
	 * Minimum token lifetime in days when not unlimited.
	 *
	 * @since 4.2.0
	 *
	 * @var int
	 */
	const MIN_TTL_DAYS = 1;

	/**
	 * Maximum token lifetime in days.
	 *
	 * @since 4.2.0
	 *
	 * @var int
	 */
	const MAX_TTL_DAYS = 365;

	/**
	 * Default access JWT lifetime in seconds (1 day).
	 *
	 * @var int
	 */
	const ACCESS_TTL = DAY_IN_SECONDS;

	/**
	 * Default refresh token lifetime in seconds (30 days).
	 *
	 * @var int
	 */
	const REFRESH_TTL = DAY_IN_SECONDS * 30;

	/**
	 * Option key for access JWT lifetime (seconds).
	 *
	 * @since 4.2.0
	 *
	 * @var string
	 */
	const OPTION_ACCESS_TTL = 'rest_api_access_token_ttl';

	/**
	 * Option key for refresh token lifetime (seconds).
	 *
	 * @since 4.2.0
	 *
	 * @var string
	 */
	const OPTION_REFRESH_TTL = 'rest_api_refresh_token_ttl';

	/**
	 * Option flag: plaintext API secrets have been migrated to HMAC hashes.
	 *
	 * @since 4.2.0
	 *
	 * @var string
	 */
	const OPTION_API_SECRETS_HASHED = 'tutor_api_secrets_hashed';

	/**
	 * Verified access-token claims for the current request (user_id, kid).
	 *
	 * @var array{user_id:int,kid:int}|null
	 */
	private static $verified_token_claims = null;

	/**
	 * Register hooks.
	 *
	 * @since 2.2.1
	 * @since 4.2.0 $register_hooks param added to reuse this class without registering hooks.
	 *
	 * @param bool $register_hooks Whether to register hooks. Default true.
	 *
	 * @return void
	 */
	public function __construct( $register_hooks = true ) {
		if ( ! $register_hooks ) {
			return;
		}

		add_action( 'wp_ajax_tutor_generate_api_keys', array( $this, 'generate_api_keys' ) );
		add_action( 'wp_ajax_tutor_update_api_permission', array( $this, 'update_api_permission' ) );
		add_action( 'wp_ajax_tutor_revoke_api_keys', array( $this, 'revoke_api_keys' ) );
		add_action( 'wp_ajax_tutor_save_rest_api_token_settings', array( $this, 'save_token_settings' ) );
		add_action( 'admin_init', array( $this, 'maybe_migrate_plaintext_api_secrets' ) );
		add_filter( 'determine_current_user', array( $this, 'api_auth' ) );
		add_action( 'profile_update', array( $this, 'maybe_invalidate_tokens_on_profile_update' ), 10, 2 );
		add_action( 'after_password_reset', array( $this, 'invalidate_user_tokens' ), 10, 1 );
		add_action( 'password_reset', array( $this, 'invalidate_user_tokens' ), 10, 1 );
		add_filter( 'rest_request_before_callbacks', array( $this, 'enforce_actor_identity' ), 20, 3 );
	}

	/**
	 * One-time migration: hash any leftover plaintext API secrets in usermeta.
	 *
	 * @since 4.2.0
	 *
	 * @return void
	 */
	public function maybe_migrate_plaintext_api_secrets() {
		if ( get_option( static::OPTION_API_SECRETS_HASHED ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$results = DB::table( 'usermeta' )
			->where( 'meta_key', static::KEYS_USER_META_KEY )
			->order_by( 'umeta_id', 'DESC' )
			->get();

		if ( is_array( $results ) ) {
			foreach ( $results as $row ) {
				$meta = json_decode( $row->meta_value );
				if ( is_object( $meta ) && ! empty( $meta->secret ) ) {
					self::persist_normalized_api_key_meta( (int) $row->umeta_id, $meta );
				}
			}
		}

		update_option( static::OPTION_API_SECRETS_HASHED, 1, false );
	}

	/**
	 * Configured access JWT lifetime in seconds (0 = unlimited).
	 *
	 * @since 4.2.0
	 *
	 * @return int
	 */
	public static function get_access_ttl() {
		return self::normalize_ttl_seconds(
			(int) tutor_utils()->get_option( static::OPTION_ACCESS_TTL, static::ACCESS_TTL ),
			static::ACCESS_TTL
		);
	}

	/**
	 * Configured refresh token lifetime in seconds (0 = unlimited).
	 *
	 * @since 4.2.0
	 *
	 * @return int
	 */
	public static function get_refresh_ttl() {
		return self::normalize_ttl_seconds(
			(int) tutor_utils()->get_option( static::OPTION_REFRESH_TTL, static::REFRESH_TTL ),
			static::REFRESH_TTL
		);
	}

	/**
	 * Convert stored TTL seconds to whole days for admin UI.
	 *
	 * @since 4.2.0
	 *
	 * @param int $seconds TTL in seconds (0 = unlimited).
	 *
	 * @return int Days (0 = unlimited).
	 */
	public static function ttl_seconds_to_days( $seconds ) {
		$seconds = (int) $seconds;
		if ( $seconds <= static::TTL_UNLIMITED ) {
			return static::TTL_UNLIMITED;
		}

		$days = (int) round( $seconds / DAY_IN_SECONDS );
		return max( static::MIN_TTL_DAYS, min( static::MAX_TTL_DAYS, $days ) );
	}

	/**
	 * Convert admin UI days to stored TTL seconds.
	 *
	 * @since 4.2.0
	 *
	 * @param int $days Days (0 = unlimited).
	 *
	 * @return int Seconds (0 = unlimited).
	 */
	public static function ttl_days_to_seconds( $days ) {
		$days = (int) $days;
		if ( $days <= static::TTL_UNLIMITED ) {
			return static::TTL_UNLIMITED;
		}

		return $days * DAY_IN_SECONDS;
	}

	/**
	 * Whether a day-based TTL is allowed (0 or 1–365).
	 *
	 * @since 4.2.0
	 *
	 * @param int $days Days value from admin UI.
	 *
	 * @return bool
	 */
	public static function is_valid_ttl_days( $days ) {
		$days = (int) $days;
		if ( static::TTL_UNLIMITED === $days ) {
			return true;
		}

		return $days >= static::MIN_TTL_DAYS && $days <= static::MAX_TTL_DAYS;
	}

	/**
	 * Normalize a stored TTL in seconds (0 = unlimited, otherwise clamp to max).
	 *
	 * @since 4.2.0
	 *
	 * @param int $ttl             Stored seconds.
	 * @param int $default_seconds Fallback when value is invalid.
	 *
	 * @return int
	 */
	private static function normalize_ttl_seconds( $ttl, $default_seconds ) {
		$ttl = (int) $ttl;
		if ( static::TTL_UNLIMITED === $ttl ) {
			return static::TTL_UNLIMITED;
		}

		if ( $ttl < 0 ) {
			return (int) $default_seconds;
		}

		$max_seconds = static::MAX_TTL_DAYS * DAY_IN_SECONDS;
		return min( $ttl, $max_seconds );
	}

	/**
	 * Whether a refresh-token exp timestamp is still valid (0 = unlimited).
	 *
	 * @since 4.2.0
	 *
	 * @param int $exp Expiration unix timestamp, or 0 for unlimited.
	 * @param int $now Current unix timestamp.
	 *
	 * @return bool
	 */
	private static function is_refresh_exp_valid( $exp, $now ) {
		$exp = (int) $exp;
		if ( static::TTL_UNLIMITED === $exp ) {
			return true;
		}

		return $exp > (int) $now;
	}

	/**
	 * Save Rest API token lifetime settings from Tools.
	 *
	 * Admin UI posts lifetimes in days; values are stored as seconds.
	 * 0 means unlimited / no expiration.
	 *
	 * @since 4.2.0
	 *
	 * @return void
	 */
	public static function save_token_settings() {
		tutor_utils()->check_nonce();
		tutor_utils()->check_current_user_capability();

		$default_access_days  = self::ttl_seconds_to_days( static::ACCESS_TTL );
		$default_refresh_days = self::ttl_seconds_to_days( static::REFRESH_TTL );
		$access_days          = Input::post( static::OPTION_ACCESS_TTL, $default_access_days, Input::TYPE_INT );
		$refresh_days         = Input::post( static::OPTION_REFRESH_TTL, $default_refresh_days, Input::TYPE_INT );

		if ( ! self::is_valid_ttl_days( $access_days ) ) {
			tutor_utils()->json_response(
				sprintf(
					/* translators: 1: min days, 2: max days */
					__( 'Access token lifetime must be 0 (unlimited) or between %1$d and %2$d days.', 'tutor' ),
					static::MIN_TTL_DAYS,
					static::MAX_TTL_DAYS
				),
				null,
				HttpHelper::STATUS_BAD_REQUEST
			);
		}

		if ( ! self::is_valid_ttl_days( $refresh_days ) ) {
			tutor_utils()->json_response(
				sprintf(
					/* translators: 1: min days, 2: max days */
					__( 'Refresh token lifetime must be 0 (unlimited) or between %1$d and %2$d days.', 'tutor' ),
					static::MIN_TTL_DAYS,
					static::MAX_TTL_DAYS
				),
				null,
				HttpHelper::STATUS_BAD_REQUEST
			);
		}

		if ( static::TTL_UNLIMITED === $access_days && static::TTL_UNLIMITED !== $refresh_days ) {
			tutor_utils()->json_response(
				__( 'When the access token has no expiration, the refresh token must also have no expiration.', 'tutor' ),
				null,
				HttpHelper::STATUS_BAD_REQUEST
			);
		}

		if (
			static::TTL_UNLIMITED !== $access_days
			&& static::TTL_UNLIMITED !== $refresh_days
			&& $refresh_days <= $access_days
		) {
			tutor_utils()->json_response(
				__( 'Refresh token lifetime must be greater than access token lifetime.', 'tutor' ),
				null,
				HttpHelper::STATUS_BAD_REQUEST
			);
		}

		tutor_utils()->update_option( static::OPTION_ACCESS_TTL, self::ttl_days_to_seconds( $access_days ) );
		tutor_utils()->update_option( static::OPTION_REFRESH_TTL, self::ttl_days_to_seconds( $refresh_days ) );

		tutor_utils()->json_response( __( 'Token settings saved successfully', 'tutor' ) );
	}

	/**
	 * Authenticate Tutor REST requests from access JWT only.
	 *
	 * Identity comes only from a valid
	 * access token so it stays aligned with kid-based API permission.
	 *
	 * @since 2.7.1
	 * @since 4.2.0 Ignore cookie sessions on tutor/*; JWT-only identity.
	 *
	 * @param int|false $user_id user id.
	 *
	 * @return int|false
	 */
	public function api_auth( $user_id ) {
		if ( ! static::is_tutor_api_request() ) {
			return $user_id;
		}

		$token = self::get_access_token_from_request();
		if ( ! $token ) {
			return false;
		}

		$jwt_user_id = self::verify_access_token( $token );
		if ( ! $jwt_user_id ) {
			return false;
		}

		return $jwt_user_id;
	}

	/**
	 * Whether the current request targets a Tutor REST API route.
	 *
	 * @since 2.7.1
	 * @since 4.0.8 Path-only detection; ignore unrelated query string values.
	 *
	 * @return boolean
	 */
	public static function is_tutor_api_request() {
		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}

		$request_uri = wp_unslash( $_SERVER['REQUEST_URI'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$path        = wp_parse_url( $request_uri, PHP_URL_PATH );

		if ( is_string( $path ) && '' !== $path ) {
			$path        = trailingslashit( $path );
			$rest_prefix = trailingslashit( rest_get_url_prefix() );
			$needle      = '/' . $rest_prefix . 'tutor/';

			if ( false !== strpos( $path, $needle ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Whether request is a Tutor auth login/refresh/logout route.
	 *
	 * @since 4.2.0
	 *
	 * @return bool
	 */
	public static function is_auth_route() {
		return static::is_login_route() || static::is_refresh_route() || static::is_logout_route();
	}

	/**
	 * Whether request is the auth login route (requires API key + secret).
	 *
	 * @since 4.2.0
	 *
	 * @return bool
	 */
	public static function is_login_route() {
		return self::auth_path_matches( 'login' );
	}

	/**
	 * Whether request is the auth refresh route.
	 *
	 * @since 4.2.0
	 *
	 * @return bool
	 */
	public static function is_refresh_route() {
		return self::auth_path_matches( 'refresh' );
	}

	/**
	 * Whether request is the auth logout route.
	 *
	 * @since 4.2.0
	 *
	 * @return bool
	 */
	public static function is_logout_route() {
		return self::auth_path_matches( 'logout' );
	}

	/**
	 * Whether the request path matches a Tutor auth endpoint segment.
	 *
	 * @since 4.2.0
	 *
	 * @param string $segment login|refresh|logout.
	 *
	 * @return bool
	 */
	private static function auth_path_matches( $segment ) {
		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}

		$request_uri = wp_unslash( $_SERVER['REQUEST_URI'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$path        = wp_parse_url( $request_uri, PHP_URL_PATH );

		if ( ! is_string( $path ) || '' === $path ) {
			return false;
		}

		$path        = trailingslashit( $path );
		$rest_prefix = trailingslashit( rest_get_url_prefix() );
		$base        = '/' . $rest_prefix . 'tutor/v1/auth/' . $segment;

		return false !== strpos( $path, $base . '/' ) || false !== strpos( $path, $base );
	}

	/**
	 * Generate api keys
	 *
	 * Stores only a HMAC of the secret. The plaintext secret is returned once
	 * in the AJAX response for the admin to copy; it is never persisted.
	 *
	 * @since 2.2.1
	 * @since 4.2.0 Validate permissions; store secret_hash instead of plaintext.
	 *
	 * @return void JSON response.
	 */
	public static function generate_api_keys() {
		tutor_utils()->check_nonce();
		tutor_utils()->check_current_user_capability();

		$api_key    = 'key_' . bin2hex( random_bytes( 16 ) );
		$api_secret = 'secret_' . bin2hex( random_bytes( 32 ) );

		$permission  = Input::post( 'permission' );
		$description = Input::post( 'description', '', Input::TYPE_TEXTAREA );

		if ( ! self::is_allowed_api_permission( $permission ) ) {
			tutor_utils()->json_response(
				__( 'Invalid permission.', 'tutor' ),
				null,
				HttpHelper::STATUS_BAD_REQUEST
			);
		}

		$info = wp_json_encode(
			array(
				'key'         => $api_key,
				'secret_hash' => self::hash_api_secret( $api_secret ),
				'permission'  => $permission,
				'description' => $description,
			)
		);

		$add = add_user_meta(
			get_current_user_id(),
			static::KEYS_USER_META_KEY,
			$info
		);

		if ( ! $add ) {
			tutor_utils()->json_response(
				tutor_utils()->error_message( '0' ),
				null,
				HttpHelper::STATUS_BAD_REQUEST
			);
		}

		// Plaintext secret is returned once for the modal; it is not stored in usermeta.
		tutor_utils()->json_response(
			__( 'API key & secret generated. Copy the secret now and store it securely — it will not be shown again.', 'tutor' ),
			array(
				'html'   => static::prepare_response( $add, $api_key, $permission, $description ),
				'key'    => $api_key,
				'secret' => $api_secret,
			)
		);
	}

	/**
	 * Update api permission
	 *
	 * @since 2.5.0
	 * @since 4.2.0 Validate permissions and meta_key; never re-expose secrets.
	 *
	 * @return void JSON response.
	 */
	public static function update_api_permission() {
		tutor_utils()->check_nonce();
		tutor_utils()->check_current_user_capability();

		$meta_id     = Input::post( 'meta_id', 0, Input::TYPE_INT );
		$permission  = Input::post( 'permission' );
		$description = Input::post( 'description', '', Input::TYPE_TEXTAREA );

		if ( ! $meta_id ) {
			tutor_utils()->json_response(
				__( 'Invalid meta id', 'tutor' ),
				null,
				HttpHelper::STATUS_BAD_REQUEST
			);
		}

		if ( ! self::is_allowed_api_permission( $permission ) ) {
			tutor_utils()->json_response(
				__( 'Invalid permission.', 'tutor' ),
				null,
				HttpHelper::STATUS_BAD_REQUEST
			);
		}

		$info = DB::table( 'usermeta' )
			->where( 'umeta_id', $meta_id )
			->first();
		if ( ! is_object( $info ) || static::KEYS_USER_META_KEY !== $info->meta_key ) {
			tutor_utils()->json_response(
				__( 'Invalid API key record.', 'tutor' ),
				null,
				HttpHelper::STATUS_BAD_REQUEST
			);
			return;
		}

		$meta_value = json_decode( $info->meta_value );
		if ( ! is_object( $meta_value ) || empty( $meta_value->key ) ) {
			tutor_utils()->json_response(
				__( 'Invalid API key record.', 'tutor' ),
				null,
				HttpHelper::STATUS_BAD_REQUEST
			);
		}

		$meta_value              = self::normalize_api_key_meta( $meta_value );
		$meta_value->permission  = $permission;
		$meta_value->description = $description;

		try {
			DB::table( 'usermeta' )
				->where( 'umeta_id', $meta_id )
				->where( 'meta_key', static::KEYS_USER_META_KEY )
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Primary-key update by umeta_id; writing the column, not querying by it.
				->update( array( 'meta_value' => wp_json_encode( $meta_value ) ) );

			$response = static::prepare_response( $meta_id, $meta_value->key, $permission, $description );
			tutor_utils()->json_response(
				__( 'API key permission updated successfully', 'tutor' ),
				$response
			);

		} catch ( \Throwable $th ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional server-side logging; do not expose to clients.
			error_log(
				sprintf(
					'Tutor REST API permission update failed: %s in %s at line %d',
					$th->getMessage(),
					$th->getFile(),
					$th->getLine()
				)
			);
			tutor_utils()->json_response(
				__( 'API permission update failed, please try again.', 'tutor' ),
				null,
				HttpHelper::STATUS_BAD_REQUEST
			);
		}
	}

	/**
	 * Revoke api keys
	 *
	 * @since 2.2.1
	 * @since 4.2.0 Require meta_key = KEYS_USER_META_KEY before delete.
	 *
	 * @return void JSON response.
	 */
	public static function revoke_api_keys() {
		tutor_utils()->check_nonce();
		tutor_utils()->check_current_user_capability();

		$meta_id = Input::post( 'meta_id', 0, Input::TYPE_INT );

		if ( ! $meta_id ) {
			tutor_utils()->json_response(
				__( 'Invalid meta id', 'tutor' ),
				null,
				HttpHelper::STATUS_BAD_REQUEST
			);
		}

		$info = DB::table( 'usermeta' )
			->where( 'umeta_id', $meta_id )
			->first();
		if ( ! is_object( $info ) || static::KEYS_USER_META_KEY !== $info->meta_key ) {
			tutor_utils()->json_response(
				__( 'Invalid API key record.', 'tutor' ),
				null,
				HttpHelper::STATUS_BAD_REQUEST
			);
		}

		$deleted = DB::table( 'usermeta' )
			->where( 'umeta_id', $meta_id )
			->where( 'meta_key', static::KEYS_USER_META_KEY )
			->delete();

		if ( ! $deleted ) {
			tutor_utils()->json_response(
				__( 'API keys revoke failed, please try again.', 'tutor' ),
				null,
				HttpHelper::STATUS_BAD_REQUEST
			);
		}

		tutor_utils()->json_response( __( 'API keys permanently revoked', 'tutor' ) );
	}

	/**
	 * Check if api key & secret is valid
	 *
	 * Looks up by public key (indexed LIKE on meta_value), then verifies the
	 * secret against a stored HMAC with hash_equals. Legacy plaintext secrets
	 * are accepted once and upgraded to secret_hash.
	 *
	 * @since 2.2.1
	 * @since 2.7.1 $return_result param added.
	 * @since 4.2.0 Keyed lookup; hashed secrets; constant-time compare.
	 *
	 * @param string $api_key api key.
	 * @param string $api_secret api secret.
	 * @param bool   $return_result return matched meta record.
	 *
	 * @return bool|object
	 */
	public static function validate_api_key_secret( $api_key, $api_secret, $return_result = false ) {
		$api_key    = is_string( $api_key ) ? $api_key : '';
		$api_secret = is_string( $api_secret ) ? $api_secret : '';

		if ( '' === $api_key || '' === $api_secret ) {
			return false;
		}

		// Generated keys are key_ + 32 hex chars; reject other shapes early.
		if ( ! preg_match( '/^key_[a-f0-9]{32}$/', $api_key ) ) {
			return false;
		}

		// where_like() auto-wraps with % and applies esc_like.
		$results = DB::table( 'usermeta' )
			->where( 'meta_key', static::KEYS_USER_META_KEY )
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Narrowed by meta_key first; public key substring match (no secret scan).
			->where_like( 'meta_value', '"key":"' . $api_key . '"' )
			->order_by( 'umeta_id', 'DESC' )
			->limit( 5 )
			->get();

		if ( ! is_array( $results ) || ! count( $results ) ) {
			return false;
		}

		foreach ( $results as $result ) {
			$obj = json_decode( $result->meta_value );
			if ( ! is_object( $obj ) || empty( $obj->key ) ) {
				continue;
			}

			if ( ! hash_equals( (string) $obj->key, $api_key ) ) {
				continue;
			}

			if ( ! self::verify_api_secret( $api_secret, $obj ) ) {
				continue;
			}

			self::maybe_upgrade_api_secret_meta( (int) $result->umeta_id, $obj, $api_secret );

			if ( $return_result ) {
				return $result;
			}

			return true;
		}

		return false;
	}

	/**
	 * HMAC-SHA256 of an API secret using the site auth salt as pepper.
	 *
	 * @since 4.2.0
	 *
	 * @param string $api_secret Plaintext API secret.
	 *
	 * @return string Hex digest.
	 */
	private static function hash_api_secret( $api_secret ) {
		return hash_hmac( 'sha256', (string) $api_secret, wp_salt( 'auth' ) );
	}

	/**
	 * Verify a plaintext secret against stored meta (hash or legacy plaintext).
	 *
	 * @since 4.2.0
	 *
	 * @param string $api_secret Plaintext secret from the request.
	 * @param object $meta       Decoded API key meta object.
	 *
	 * @return bool
	 */
	private static function verify_api_secret( $api_secret, $meta ) {
		if ( ! empty( $meta->secret_hash ) && is_string( $meta->secret_hash ) ) {
			return hash_equals( (string) $meta->secret_hash, self::hash_api_secret( $api_secret ) );
		}

		// Legacy rows stored the secret in plaintext.
		if ( ! empty( $meta->secret ) && is_string( $meta->secret ) ) {
			return hash_equals( (string) $meta->secret, (string) $api_secret );
		}

		return false;
	}

	/**
	 * Upgrade a legacy plaintext secret to secret_hash after a successful match.
	 *
	 * @since 4.2.0
	 *
	 * @param int    $umeta_id   Usermeta row id.
	 * @param object $meta       Decoded meta object.
	 * @param string $api_secret Verified plaintext secret.
	 *
	 * @return void
	 */
	private static function maybe_upgrade_api_secret_meta( $umeta_id, $meta, $api_secret ) {
		$umeta_id = absint( $umeta_id );
		if ( ! $umeta_id || ! is_object( $meta ) ) {
			return;
		}

		$needs_upgrade = empty( $meta->secret_hash ) || ! empty( $meta->secret );
		if ( ! $needs_upgrade ) {
			return;
		}

		$meta->secret_hash = self::hash_api_secret( $api_secret );
		unset( $meta->secret );

		DB::table( 'usermeta' )
			->where( 'umeta_id', $umeta_id )
			->where( 'meta_key', static::KEYS_USER_META_KEY )
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Primary-key update by umeta_id; writing the column, not querying by it.
			->update( array( 'meta_value' => wp_json_encode( $meta ) ) );
	}

	/**
	 * Normalize API key meta: hash any leftover plaintext secret, drop secret field.
	 *
	 * Safe to call from admin list/update when the plaintext is still in the row.
	 *
	 * @since 4.2.0
	 *
	 * @param object $meta Decoded meta object.
	 *
	 * @return object
	 */
	public static function normalize_api_key_meta( $meta ) {
		if ( ! is_object( $meta ) ) {
			return $meta;
		}

		if ( ! empty( $meta->secret ) && is_string( $meta->secret ) ) {
			if ( empty( $meta->secret_hash ) ) {
				$meta->secret_hash = self::hash_api_secret( $meta->secret );
			}
			unset( $meta->secret );
		}

		return $meta;
	}

	/**
	 * Persist normalization for a usermeta API key row (admin list migration).
	 *
	 * @since 4.2.0
	 *
	 * @param int    $umeta_id Usermeta id.
	 * @param object $meta     Decoded meta (may contain plaintext secret).
	 *
	 * @return object Normalized meta.
	 */
	public static function persist_normalized_api_key_meta( $umeta_id, $meta ) {
		$umeta_id = absint( $umeta_id );
		$before   = is_object( $meta ) ? wp_json_encode( $meta ) : '';
		$meta     = self::normalize_api_key_meta( $meta );
		$after    = is_object( $meta ) ? wp_json_encode( $meta ) : '';

		if ( ! $umeta_id || $before === $after ) {
			return $meta;
		}

		DB::table( 'usermeta' )
			->where( 'umeta_id', $umeta_id )
			->where( 'meta_key', static::KEYS_USER_META_KEY )
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Primary-key update by umeta_id; writing the column, not querying by it.
			->update( array( 'meta_value' => $after ) );

		return $meta;
	}

	/**
	 * Permission string for this request.
	 *
	 * Login: from API key/secret headers.
	 * All other Tutor REST routes: from the API key id (`kid`) bound into the access JWT.
	 *
	 * @since 4.2.0
	 * @since 4.2.0 Non-login routes resolve permission from the access token kid.
	 *
	 * @return string Empty when credentials/token are missing, invalid, or revoked.
	 */
	private static function get_api_key_permission() {
		if ( static::is_login_route() ) {
			return self::get_permission_from_api_credentials();
		}

		$kid = self::get_access_token_kid();
		if ( ! $kid ) {
			return '';
		}

		return self::get_permission_by_kid( $kid );
	}

	/**
	 * Permission from API key/secret headers.
	 *
	 * @since 4.2.0
	 *
	 * @return string
	 */
	private static function get_permission_from_api_credentials() {
		$credentials = self::get_api_credentials_from_request();
		if ( ! $credentials ) {
			return '';
		}

		$record = static::validate_api_key_secret( $credentials['key'], $credentials['secret'], true );
		if ( ! is_object( $record ) ) {
			return '';
		}

		return self::permission_from_key_meta( $record->meta_value );
	}

	/**
	 * Permission for an API key usermeta row id (kid).
	 *
	 * @since 4.2.0
	 *
	 * @param int $kid usermeta umeta_id of the API key row.
	 *
	 * @return string Empty when missing or revoked.
	 */
	private static function get_permission_by_kid( $kid ) {
		$kid = absint( $kid );
		if ( ! $kid ) {
			return '';
		}

		$record = DB::table( 'usermeta' )
			->where( 'umeta_id', $kid )
			->first();
		if ( ! is_object( $record ) || static::KEYS_USER_META_KEY !== $record->meta_key ) {
			return '';
		}

		return self::permission_from_key_meta( $record->meta_value );
	}

	/**
	 * Extract permission string from API key meta JSON.
	 *
	 * @since 4.2.0
	 *
	 * @param string $meta_value JSON meta value.
	 *
	 * @return string
	 */
	private static function permission_from_key_meta( $meta_value ) {
		$meta = json_decode( $meta_value );
		if ( ! is_object( $meta ) || empty( $meta->permission ) ) {
			return '';
		}

		return (string) $meta->permission;
	}

	/**
	 * API key id (umeta_id) from the verified access token on this request.
	 *
	 * @since 4.2.0
	 *
	 * @return int
	 */
	private static function get_access_token_kid() {
		$token = self::get_access_token_from_request();
		if ( ! $token ) {
			return 0;
		}

		if ( null !== self::$verified_token_claims && isset( self::$verified_token_claims['kid'] ) ) {
			return absint( self::$verified_token_claims['kid'] );
		}

		if ( ! self::verify_access_token( $token ) ) {
			return 0;
		}

		return isset( self::$verified_token_claims['kid'] ) ? absint( self::$verified_token_claims['kid'] ) : 0;
	}

	/**
	 * Whether the API key grants Read (or higher).
	 *
	 * @since 4.2.0
	 *
	 * @return bool
	 */
	public static function process_read_request() {
		$permission = self::get_api_key_permission();
		if ( '' === $permission ) {
			return false;
		}

		return in_array( $permission, array( static::READ, static::READ_WRITE, static::ALL ), true );
	}

	/**
	 * Whether the API key grants Write (or higher).
	 *
	 * @since 4.2.0
	 *
	 * @return bool
	 */
	public static function process_write_request() {
		$permission = self::get_api_key_permission();
		if ( '' === $permission ) {
			return false;
		}

		return in_array( $permission, array( static::WRITE, static::READ_WRITE, static::ALL ), true );
	}

	/**
	 * Whether the API key grants Delete (or All).
	 *
	 * Matches pre-4.0.10 Pro route allowlists: Delete and All only.
	 * Write / Read/Write do not authorize DELETE.
	 *
	 * @since 4.2.0
	 *
	 * @return bool
	 */
	public static function process_delete_request() {
		$permission = self::get_api_key_permission();
		if ( '' === $permission ) {
			return false;
		}

		return in_array( $permission, array( static::DELETE, static::ALL ), true );
	}

	/**
	 * Process api request — honor Read/Write/All vs HTTP method.
	 *
	 * Login uses API key/secret. All other routes use the access token's bound key permission.
	 *
	 * @since 2.2.1
	 * @since 4.2.0 Key permission by method; login accepts any valid key; other routes use JWT kid.
	 *
	 * @return boolean
	 */
	public static function process_api_request() {
		// Login exchanges credentials for a JWT; any valid key permission may authenticate.
		if ( static::is_login_route() ) {
			return '' !== self::get_api_key_permission();
		}

		$method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : HttpHelper::METHOD_GET;

		if ( HttpHelper::METHOD_DELETE === $method ) {
			return static::process_delete_request();
		}

		if ( in_array(
			$method,
			array(
				HttpHelper::METHOD_POST,
				HttpHelper::METHOD_PUT,
				HttpHelper::METHOD_PATCH,
			),
			true
		) ) {
			return static::process_write_request();
		}

		return static::process_read_request();
	}

	/**
	 * WP_Error when the access token is missing or invalid.
	 *
	 * @since 4.2.0
	 *
	 * @param bool $require_token When false, an empty token is allowed (returns null);
	 *                            a present but invalid token still returns an error.
	 *
	 * @return \WP_Error|null Null when the token is acceptable for the given mode.
	 */
	public static function get_access_token_error( $require_token = true ) {
		$token = self::get_access_token_from_request();

		if ( '' === $token ) {
			if ( ! $require_token ) {
				return null;
			}

			return new \WP_Error(
				'rest_missing_access_token',
				__( 'Access token is required.', 'tutor' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		if ( ! self::verify_access_token( $token ) ) {
			return new \WP_Error(
				'rest_invalid_access_token',
				__( 'Invalid access token.', 'tutor' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return null;
	}

	/**
	 * Authorize JWT + API key method scope for permission_callback use.
	 *
	 * Distinguishes missing/invalid access tokens from insufficient key permissions.
	 *
	 * @since 4.2.0
	 *
	 * @param string $scope One of: read, write, delete, api.
	 *
	 * @return true|\WP_Error
	 */
	public static function authorize_authenticated_request( $scope = 'api' ) {
		$token_error = self::get_access_token_error();
		if ( is_wp_error( $token_error ) ) {
			return $token_error;
		}

		switch ( $scope ) {
			case 'read':
				$allowed = static::process_authenticated_read_request();
				break;
			case 'write':
				$allowed = static::process_authenticated_write_request();
				break;
			case 'delete':
				$allowed = static::process_authenticated_delete_request();
				break;
			default:
				$allowed = static::process_authenticated_api_request();
				break;
		}

		if ( ! $allowed ) {
			return new \WP_Error(
				'rest_forbidden',
				__( 'API key does not have permission for this request.', 'tutor' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Whether the request has a JWT-authenticated WordPress user.
	 *
	 * @since 4.2.0
	 *
	 * @return bool
	 */
	public static function has_authenticated_user() {
		return (int) get_current_user_id() > 0;
	}

	/**
	 * Read-capable API key and an authenticated end user (JWT).
	 *
	 * @since 4.2.0
	 *
	 * @return bool
	 */
	public static function process_authenticated_read_request() {
		return static::process_read_request() && static::has_authenticated_user();
	}

	/**
	 * Write-capable API key and an authenticated end user (JWT).
	 *
	 * @since 4.2.0
	 *
	 * @return bool
	 */
	public static function process_authenticated_write_request() {
		return static::process_write_request() && static::has_authenticated_user();
	}

	/**
	 * Delete-capable API key and an authenticated end user (JWT).
	 *
	 * @since 4.2.0
	 *
	 * @return bool
	 */
	public static function process_authenticated_delete_request() {
		return static::process_delete_request() && static::has_authenticated_user();
	}

	/**
	 * Valid API key for this HTTP method and an authenticated end user (JWT).
	 *
	 * Used when the route does not declare a specific read/write/delete check.
	 *
	 * @since 4.2.0
	 *
	 * @return bool
	 */
	public static function process_authenticated_api_request() {
		return static::process_api_request() && static::has_authenticated_user();
	}

	/**
	 * Whether the current user may act as the given user (self or privileged admin).
	 *
	 * @since 4.2.0
	 *
	 * @param int $target_user_id target user id.
	 *
	 * @return bool
	 */
	public static function can_act_as_user( $target_user_id ) {
		$current = get_current_user_id();
		$target  = absint( $target_user_id );

		if ( ! $current || ! $target ) {
			return false;
		}

		if ( $current === $target ) {
			return true;
		}

		return user_can( $current, 'list_users' ) || user_can( $current, 'manage_options' );
	}

	/**
	 * Whether the current user may act as a student for a course.
	 *
	 * Self, admin, or instructor/admin with course content access.
	 *
	 * @since 4.2.0
	 *
	 * @param int $student_id student user id.
	 * @param int $course_id course id when known.
	 *
	 * @return bool
	 */
	public static function can_act_as_student( $student_id, $course_id = 0 ) {
		if ( static::can_act_as_user( $student_id ) ) {
			return true;
		}

		$current   = get_current_user_id();
		$course_id = absint( $course_id );
		if ( ! $current || ! $course_id ) {
			return false;
		}

		return (bool) tutor_utils()->has_user_course_content_access( $current, $course_id );
	}

	/**
	 * Prevent client-supplied user IDs from impersonating other users.
	 *
	 * Runs for all Tutor REST routes after permission callbacks. Auth login
	 *
	 * @since 4.2.0
	 *
	 * @param mixed           $response response.
	 * @param array           $handler  handler.
	 * @param WP_REST_Request $request  request.
	 *
	 * @return mixed|\WP_Error
	 */
	public static function enforce_actor_identity( $response, $handler, $request ) {
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( ! static::is_tutor_api_request() || static::is_auth_route() ) {
			return $response;
		}

		if ( ! static::has_authenticated_user() ) {
			return $response;
		}

		$author_keys = array( 'post_author', 'lesson_author', 'topic_author', 'quiz_author', 'assignment_author' );
		foreach ( $author_keys as $key ) {
			if ( null === $request->get_param( $key ) || '' === $request->get_param( $key ) ) {
				continue;
			}
			$requested = absint( $request->get_param( $key ) );
			if ( $requested && ! static::can_act_as_user( $requested ) ) {
				return new \WP_Error(
					'rest_forbidden_user',
					__( 'You are not allowed to act as this user.', 'tutor' ),
					array( 'status' => rest_authorization_required_code() )
				);
			}
		}

		$course_id = absint( $request->get_param( 'course_id' ) );

		if ( null !== $request->get_param( 'student_id' ) && '' !== $request->get_param( 'student_id' ) ) {
			$student_id = absint( $request->get_param( 'student_id' ) );
			if ( $student_id && ! static::can_act_as_student( $student_id, $course_id ) ) {
				return new \WP_Error(
					'rest_forbidden_user',
					__( 'You are not allowed to act as this student.', 'tutor' ),
					array( 'status' => rest_authorization_required_code() )
				);
			}
		}

		// Enrollment / profile style user_id.
		if ( null !== $request->get_param( 'user_id' ) && '' !== $request->get_param( 'user_id' ) ) {
			$user_id = absint( $request->get_param( 'user_id' ) );
			if ( $user_id ) {
				$allowed = $course_id
					? static::can_act_as_student( $user_id, $course_id )
					: static::can_act_as_user( $user_id );

				if ( ! $allowed ) {
					return new \WP_Error(
						'rest_forbidden_user',
						__( 'You are not allowed to act as this user.', 'tutor' ),
						array( 'status' => rest_authorization_required_code() )
					);
				}
			}
		}

		return $response;
	}

	/**
	 * WP_Error when a REST resource ID is missing or the wrong type.
	 *
	 * Prefer this over returning false (which WordPress maps to rest_forbidden).
	 *
	 * @since 4.2.0
	 *
	 * @param string $message Optional human-readable message.
	 *
	 * @return \WP_Error
	 */
	public static function invalid_resource_error( $message = '' ) {
		if ( '' === $message ) {
			$message = __( 'Invalid resource.', 'tutor' );
		}

		return new \WP_Error(
			'rest_invalid_resource',
			$message,
			array( 'status' => HttpHelper::STATUS_NOT_FOUND )
		);
	}

	/**
	 * True when $course_id is a course post type; otherwise a 404 WP_Error.
	 *
	 * @since 4.2.0
	 *
	 * @param int $course_id course id.
	 *
	 * @return true|\WP_Error
	 */
	public static function require_valid_course( $course_id ) {
		$course_id = absint( $course_id );
		if ( ! $course_id || ! CourseModel::get_post_types( $course_id ) ) {
			return self::invalid_resource_error( __( 'Course not found.', 'tutor' ) );
		}

		return true;
	}

	/**
	 * Resolve the parent course from a topic id, or 404 when the topic is invalid.
	 *
	 * @since 4.2.0
	 *
	 * @param int $topic_id topic post id.
	 *
	 * @return int|\WP_Error Course id on success.
	 */
	public static function require_course_from_topic( $topic_id ) {
		$topic_id = absint( $topic_id );
		if ( ! $topic_id || tutor()->topics_post_type !== get_post_type( $topic_id ) ) {
			return self::invalid_resource_error( __( 'Topic not found.', 'tutor' ) );
		}

		$course_id = (int) tutor_utils()->get_course_id_by( 'topic', $topic_id );
		$valid     = self::require_valid_course( $course_id );
		if ( true !== $valid ) {
			return $valid;
		}

		return $course_id;
	}

	/**
	 * Resolve the parent course from a quiz id, or 404 when the quiz is invalid.
	 *
	 * @since 4.2.0
	 *
	 * @param int $quiz_id quiz post id.
	 *
	 * @return int|\WP_Error Course id on success.
	 */
	public static function require_course_from_quiz( $quiz_id ) {
		$quiz_id = absint( $quiz_id );
		if ( ! $quiz_id || tutor()->quiz_post_type !== get_post_type( $quiz_id ) ) {
			return self::invalid_resource_error( __( 'Quiz not found.', 'tutor' ) );
		}

		$course_id = (int) tutor_utils()->get_course_id_by( 'quiz', $quiz_id );
		$valid     = self::require_valid_course( $course_id );
		if ( true !== $valid ) {
			return $valid;
		}

		return $course_id;
	}

	/**
	 * Whether a user's public profile layout is open (not private).
	 *
	 * Mirrors templates/public-profile.php layout keys.
	 *
	 * @since 4.2.0
	 *
	 * @param int $user_id user id.
	 *
	 * @return bool
	 */
	public static function is_public_profile_layout_open( $user_id ) {
		$user_id = absint( $user_id );
		if ( ! $user_id || ! get_userdata( $user_id ) ) {
			return false;
		}

		$is_instructor  = tutor_utils()->is_instructor( $user_id, true );
		$layout_key     = $is_instructor ? 'public_profile_layout' : 'student_public_profile_layout';
		$profile_layout = tutor_utils()->get_option( $layout_key, 'private' );

		return 'private' !== $profile_layout;
	}

	/**
	 * Permission: public route with optional access token.
	 *
	 * Empty token → allow (guest). Present but invalid token → reject.
	 * Used by catalog/list routes that do not need resource-specific gates.
	 *
	 * @since 4.2.0
	 *
	 * @param WP_REST_Request $request request.
	 *
	 * @return true|\WP_Error
	 */
	public static function permission_optional_access_token( WP_REST_Request $request ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		$token_error = self::get_access_token_error( false );
		if ( is_wp_error( $token_error ) ) {
			return $token_error;
		}

		return true;
	}

	/**
	 * Permission: author card — same gate as public-profile.php.
	 *
	 * Open instructor/student layout → allow (anonymous or JWT).
	 * Private layout → deny (JWT does not bypass). Unknown user → 404.
	 * Present but invalid access token → reject (optional auth).
	 *
	 * @since 4.2.0
	 *
	 * @param WP_REST_Request $request request.
	 *
	 * @return bool|\WP_Error
	 */
	public static function permission_public_author( WP_REST_Request $request ) {
		$token_error = self::get_access_token_error( false );
		if ( is_wp_error( $token_error ) ) {
			return $token_error;
		}

		$user_id = absint( $request->get_param( 'id' ) );
		if ( ! $user_id || ! get_userdata( $user_id ) ) {
			return self::invalid_resource_error( __( 'Author not found.', 'tutor' ) );
		}

		return static::is_public_profile_layout_open( $user_id );
	}

	/**
	 * Permission: course marketing detail (overview for purchase/enroll).
	 *
	 * @since 4.2.0
	 *
	 * @param WP_REST_Request $request request.
	 *
	 * @return bool|\WP_Error
	 */
	public static function permission_course_detail( WP_REST_Request $request ) {
		$course_id = absint( $request->get_param( 'id' ) );
		$valid     = self::require_valid_course( $course_id );
		if ( true !== $valid ) {
			return $valid;
		}

		$token_error = self::get_access_token_error( false );
		if ( is_wp_error( $token_error ) ) {
			return $token_error;
		}

		if ( 'publish' !== get_post_status( $course_id ) ) {
			$token_error = self::get_access_token_error();
			if ( is_wp_error( $token_error ) ) {
				return $token_error;
			}

			if ( ! static::process_api_request() ) {
				return false;
			}

			$user_id = tutor_utils()->get_user_id();
			return $user_id && tutor_utils()->can_user_edit_course( $user_id, $course_id );
		}

		if ( Course_List::is_public( $course_id ) ) {
			return true;
		}

		// Authenticated users may open the marketing/overview page without enrollment.
		if ( static::process_api_request() ) {
			return true;
		}

		return ! tutor_utils()->get_option( 'student_must_login_to_view_course' );
	}

	/**
	 * Permission: course learning content — JWT path or public course (no token).
	 *
	 * Used by course-contents. Announcements use permission_authenticated_course_content.
	 *
	 * @since 4.2.0
	 *
	 * @param WP_REST_Request $request request.
	 *
	 * @return bool|\WP_Error
	 */
	public static function permission_course_content( WP_REST_Request $request ) {
		$course_id = absint( $request->get_param( 'id' ) );
		if ( ! $course_id ) {
			$course_id = absint( $request->get_param( 'course_id' ) );
		}

		$valid = self::require_valid_course( $course_id );
		if ( true !== $valid ) {
			return $valid;
		}

		$token_error = self::get_access_token_error( false );
		if ( is_wp_error( $token_error ) ) {
			return $token_error;
		}

		if ( static::process_api_request() ) {
			return static::can_view_course_content( $course_id );
		}

		return Course_List::is_public( $course_id );
	}

	/**
	 * Permission: course content that always requires a valid access token.
	 *
	 * @since 4.2.0
	 *
	 * @param WP_REST_Request $request request.
	 *
	 * @return bool|\WP_Error
	 */
	public static function permission_authenticated_course_content( WP_REST_Request $request ) {
		$token_error = self::get_access_token_error();
		if ( is_wp_error( $token_error ) ) {
			return $token_error;
		}

		if ( ! static::process_api_request() ) {
			return false;
		}

		$course_id = absint( $request->get_param( 'id' ) );
		if ( ! $course_id ) {
			$course_id = absint( $request->get_param( 'course_id' ) );
		}

		$valid = self::require_valid_course( $course_id );
		if ( true !== $valid ) {
			return $valid;
		}

		return static::can_view_course_content( $course_id );
	}

	/**
	 * Permission: topics by course_id — JWT or public course.
	 *
	 * @since 4.2.0
	 *
	 * @param WP_REST_Request $request request.
	 *
	 * @return bool|\WP_Error
	 */
	public static function permission_topics( WP_REST_Request $request ) {
		$course_id = absint( $request->get_param( 'course_id' ) );

		$token_error = self::get_access_token_error( false );
		if ( is_wp_error( $token_error ) ) {
			return $token_error;
		}

		$valid = self::require_valid_course( $course_id );
		if ( true !== $valid ) {
			return $valid;
		}

		if ( static::process_api_request() ) {
			return static::can_view_course_content( $course_id );
		}

		return Course_List::is_public( $course_id );
	}

	/**
	 * Permission: lessons or quizzes listed by topic_id — JWT or public course.
	 *
	 * @since 4.2.0
	 *
	 * @param WP_REST_Request $request request.
	 *
	 * @return bool|\WP_Error
	 */
	public static function permission_by_topic( WP_REST_Request $request ) {
		$token_error = self::get_access_token_error( false );
		if ( is_wp_error( $token_error ) ) {
			return $token_error;
		}

		$course_id = self::require_course_from_topic( absint( $request->get_param( 'topic_id' ) ) );
		if ( is_wp_error( $course_id ) ) {
			return $course_id;
		}

		if ( static::process_api_request() ) {
			return static::can_view_course_content( $course_id );
		}

		return Course_List::is_public( $course_id );
	}

	/**
	 * Permission: topic children that always require a valid access token (e.g. quiz list).
	 *
	 * @since 4.2.0
	 *
	 * @param WP_REST_Request $request request.
	 *
	 * @return bool|\WP_Error
	 */
	public static function permission_authenticated_by_topic( WP_REST_Request $request ) {
		$token_error = self::get_access_token_error();
		if ( is_wp_error( $token_error ) ) {
			return $token_error;
		}

		if ( ! static::process_api_request() ) {
			return false;
		}

		$course_id = self::require_course_from_topic( absint( $request->get_param( 'topic_id' ) ) );
		if ( is_wp_error( $course_id ) ) {
			return $course_id;
		}

		return static::can_view_course_content( $course_id );
	}

	/**
	 * Permission: quiz by quiz id (always requires access token).
	 *
	 * @since 4.2.0
	 *
	 * @param WP_REST_Request $request request.
	 *
	 * @return bool|\WP_Error
	 */
	public static function permission_quiz( WP_REST_Request $request ) {
		$token_error = self::get_access_token_error();
		if ( is_wp_error( $token_error ) ) {
			return $token_error;
		}

		if ( ! static::process_api_request() ) {
			return false;
		}

		$course_id = self::require_course_from_quiz( absint( $request->get_param( 'id' ) ) );
		if ( is_wp_error( $course_id ) ) {
			return $course_id;
		}

		return static::can_view_course_content( $course_id );
	}

	/**
	 * Whether the user may view full course learning content.
	 *
	 * Matches learning-area UI: students may access publish/private only.
	 * Draft, pending, trash, future, etc. require edit or IA content access —
	 * enrollment alone is not enough.
	 *
	 * @since 4.2.0
	 *
	 * @param int $course_id course id.
	 * @param int $user_id user id.
	 *
	 * @return bool
	 */
	public static function can_view_course_content( $course_id, $user_id = 0 ) {
		$course_id = absint( $course_id );
		if ( ! $course_id || ! CourseModel::get_post_types( $course_id ) ) {
			return false;
		}

		$user_id = tutor_utils()->get_user_id( $user_id );
		$status  = get_post_status( $course_id );

		// Non-publish / non-private: editors and IA content access only (not enrollment).
		if ( ! in_array( $status, array( CourseModel::STATUS_PUBLISH, CourseModel::STATUS_PRIVATE ), true ) ) {
			if ( ! $user_id ) {
				return false;
			}

			return tutor_utils()->can_user_edit_course( $user_id, $course_id )
				|| (bool) tutor_utils()->has_user_course_content_access( $user_id, $course_id );
		}

		if ( Course_List::is_public( $course_id ) ) {
			return true;
		}

		if ( ! $user_id ) {
			return false;
		}

		if ( EnrollmentModel::is_enrolled( $course_id, $user_id ) ) {
			return true;
		}

		return (bool) tutor_utils()->has_user_course_content_access( $user_id, $course_id );
	}

	/**
	 * Whether internal course_settings meta may be included in REST payloads.
	 *
	 * Unlike can_view_course_content(), public courses alone do not qualify —
	 * settings are not marketing fields on single-course.php.
	 *
	 * @since 4.2.0
	 *
	 * @param int $course_id course id.
	 * @param int $user_id user id.
	 *
	 * @return bool
	 */
	public static function can_reveal_course_internal_meta( $course_id, $user_id = 0 ) {
		if ( ! static::is_rest_authenticated() ) {
			return false;
		}

		$course_id = absint( $course_id );
		$user_id   = tutor_utils()->get_user_id( $user_id );
		if ( ! $course_id || ! $user_id || ! CourseModel::get_post_types( $course_id ) ) {
			return false;
		}

		if ( EnrollmentModel::is_enrolled( $course_id, $user_id ) ) {
			return true;
		}

		if ( tutor_utils()->can_user_edit_course( $user_id, $course_id ) ) {
			return true;
		}

		return (bool) tutor_utils()->has_user_course_content_access( $user_id, $course_id );
	}

	/**
	 * Whether full lesson/quiz bodies, video, and attachments may be returned.
	 *
	 * Matches Template::load_single_lesson_template: enrolled / instructor-admin,
	 * or a public course that is not purchasable (guest-readable lessons).
	 *
	 * @since 4.2.0
	 *
	 * @param int $course_id course id.
	 * @param int $user_id user id.
	 *
	 * @return bool
	 */
	public static function can_reveal_learning_payload( $course_id, $user_id = 0 ) {
		$course_id = absint( $course_id );
		if ( ! $course_id || ! CourseModel::get_post_types( $course_id ) ) {
			return false;
		}

		$user_id = tutor_utils()->get_user_id( $user_id );

		if ( $user_id && EnrollmentModel::is_enrolled( $course_id, $user_id ) ) {
			return true;
		}

		if ( $user_id && tutor_utils()->has_user_course_content_access( $user_id, $course_id ) ) {
			return true;
		}

		return Course_List::is_public( $course_id ) && ! tutor_utils()->is_course_purchasable( $course_id );
	}

	/**
	 * Whether this Tutor REST call has a valid access JWT (key permission).
	 *
	 * @since 4.2.0
	 *
	 * @return bool
	 */
	public static function is_rest_authenticated() {
		return static::process_api_request();
	}

	/**
	 * Whether quiz attempt details are hidden for a student viewer.
	 *
	 * Matches views/quiz/attempt-details.php and attempt-table.php when the
	 * "Hide Quiz Details" option is enabled.
	 *
	 * @since 4.2.0
	 *
	 * @param int $user_id user id.
	 *
	 * @return bool
	 */
	public static function is_quiz_details_hidden_for_user( $user_id = 0 ) {
		$user_id = tutor_utils()->get_user_id( $user_id );
		if ( ! $user_id || ! tutor_utils()->get_option( 'hide_quiz_details' ) ) {
			return false;
		}

		// Match views/quiz/attempt-details.php (admin / instructor still see details).
		if ( User::is_admin( $user_id ) || User::is_instructor( $user_id, false ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Whether answer keys (is_correct) may be revealed.
	 *
	 * Students never receive answer keys when "Hide Quiz Details" is on,
	 * matching the frontend attempt-details UI.
	 *
	 * @since 4.2.0
	 *
	 * @param int $quiz_id quiz id.
	 * @param int $user_id user id.
	 *
	 * @return bool
	 */
	public static function can_reveal_quiz_answers( $quiz_id, $user_id = 0 ) {
		$quiz_id = absint( $quiz_id );
		$user_id = tutor_utils()->get_user_id( $user_id );
		if ( ! $quiz_id || ! $user_id ) {
			return false;
		}

		$course_id = (int) tutor_utils()->get_course_id_by( 'quiz', $quiz_id );
		if ( $course_id && tutor_utils()->has_user_course_content_access( $user_id, $course_id ) ) {
			return true;
		}

		if ( self::is_quiz_details_hidden_for_user( $user_id ) ) {
			return false;
		}

		$attempt = ( new QuizModel() )->get_quiz_attempt( $quiz_id, $user_id );
		return is_object( $attempt ) && ! empty( $attempt->attempt_ended_at );
	}

	/**
	 * Whether viewer may see private user fields (email, login, registered).
	 *
	 * @since 4.2.0
	 *
	 * @param int $target_user_id target user.
	 * @param int $viewer_id viewer.
	 *
	 * @return bool
	 */
	public static function can_view_user_private_fields( $target_user_id, $viewer_id = 0 ) {
		$target_user_id = absint( $target_user_id );
		$viewer_id      = tutor_utils()->get_user_id( $viewer_id );

		if ( ! $viewer_id ) {
			return false;
		}

		// Privileged users may see private fields, including guest reviews (user_id 0).
		if ( user_can( $viewer_id, 'list_users' ) ) {
			return true;
		}

		if ( ! $target_user_id ) {
			return false;
		}

		if ( $target_user_id === $viewer_id ) {
			return true;
		}

		$instructor_courses = get_user_meta( $viewer_id, '_tutor_instructor_course_id', false );
		if ( ! is_array( $instructor_courses ) ) {
			return false;
		}

		foreach ( $instructor_courses as $course_id ) {
			$course_id = absint( $course_id );
			if ( $course_id && EnrollmentModel::is_enrolled( $course_id, $target_user_id ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Shared REST response helper for static auth handlers.
	 *
	 * Avoids constructing RestAuth (which registers hooks) and keeps a single
	 * instance `response()` API like Tutor Pro.
	 *
	 * @since 4.2.0
	 *
	 * @return object Object using REST_Response.
	 */
	private static function responder() {
		static $responder = null;

		if ( null === $responder ) {
			$responder = new class() {
				use REST_Response;
			};
		}

		return $responder;
	}

	/**
	 * Login — issue access + refresh tokens.
	 *
	 * Requires a valid API key/secret with any permission (permission_callback). The key id
	 * is bound into issued tokens so later requests need only the Bearer token.
	 *
	 * @since 4.2.0
	 * @since 4.2.0 Bind API key id (kid) into access and refresh tokens.
	 *
	 * @param WP_REST_Request $request request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public static function login( WP_REST_Request $request ) {
		$responder   = self::responder();
		$credentials = self::get_api_credentials_from_request();
		if ( ! $credentials ) {
			return $responder->response(
				'tutor_auth_login',
				__( 'API key and secret are required.', 'tutor' ),
				'',
				HttpHelper::STATUS_FORBIDDEN
			);
		}

		$record = static::validate_api_key_secret( $credentials['key'], $credentials['secret'], true );
		if ( ! is_object( $record ) ) {
			return $responder->response(
				'tutor_auth_login',
				__( 'Invalid API key or secret.', 'tutor' ),
				'',
				HttpHelper::STATUS_FORBIDDEN
			);
		}

		$kid = absint( $record->umeta_id );
		if ( ! $kid || '' === self::permission_from_key_meta( $record->meta_value ) ) {
			return $responder->response(
				'tutor_auth_login',
				__( 'Invalid API key or secret.', 'tutor' ),
				'',
				HttpHelper::STATUS_FORBIDDEN
			);
		}

		$username = sanitize_text_field( (string) $request->get_param( 'username' ) );
		$password = (string) $request->get_param( 'password' );

		if ( '' === $username || '' === $password ) {
			return $responder->response(
				'tutor_auth_login',
				__( 'Invalid username or password.', 'tutor' ),
				'',
				HttpHelper::STATUS_UNAUTHORIZED
			);
		}

		if ( is_email( $username ) ) {
			$user_by_email = get_user_by( 'email', $username );
			if ( $user_by_email ) {
				$username = $user_by_email->user_login;
			}
		}

		$user = wp_authenticate( $username, $password );
		if ( is_wp_error( $user ) ) {
			return $responder->response(
				'tutor_auth_login',
				__( 'Invalid username or password.', 'tutor' ),
				'',
				HttpHelper::STATUS_UNAUTHORIZED
			);
		}

		return $responder->response(
			'tutor_auth_login',
			__( 'Login successful.', 'tutor' ),
			self::build_token_response( (int) $user->ID, $kid ),
			HttpHelper::STATUS_OK
		);
	}

	/**
	 * Refresh access token (rotates refresh token).
	 *
	 * @since 4.2.0
	 *
	 * @param WP_REST_Request $request request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public static function refresh( WP_REST_Request $request ) {
		$responder = self::responder();
		$refresh   = sanitize_text_field( (string) $request->get_param( 'refresh_token' ) );
		if ( '' === $refresh ) {
			return $responder->response(
				'tutor_auth_refresh',
				__( 'Invalid refresh token.', 'tutor' ),
				'',
				HttpHelper::STATUS_UNAUTHORIZED
			);
		}

		$session = self::consume_refresh_token( $refresh );
		if ( ! $session ) {
			return $responder->response(
				'tutor_auth_refresh',
				__( 'Invalid refresh token.', 'tutor' ),
				'',
				HttpHelper::STATUS_UNAUTHORIZED
			);
		}

		if ( '' === self::get_permission_by_kid( $session['kid'] ) ) {
			return $responder->response(
				'tutor_auth_refresh',
				__( 'API key has been revoked.', 'tutor' ),
				'',
				HttpHelper::STATUS_FORBIDDEN
			);
		}

		return $responder->response(
			'tutor_auth_refresh',
			__( 'Token refreshed successfully.', 'tutor' ),
			self::build_token_response( $session['user_id'], $session['kid'] ),
			HttpHelper::STATUS_OK
		);
	}

	/**
	 * Logout — revoke refresh token(s); when all=true, also bump token_version.
	 *
	 * @since 4.2.0
	 *
	 * @param WP_REST_Request $request request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public static function logout( WP_REST_Request $request ) {
		$responder = self::responder();
		$refresh   = sanitize_text_field( (string) $request->get_param( 'refresh_token' ) );
		$all       = (bool) $request->get_param( 'all' );

		if ( $all ) {
			$token   = self::get_access_token_from_request();
			$user_id = $token ? self::verify_access_token( $token ) : 0;
			if ( ! $user_id && $refresh ) {
				$user_id = self::find_user_id_by_refresh_token( $refresh );
			}
			if ( $user_id ) {
				static::invalidate_user_tokens( $user_id );
			}
		} elseif ( $refresh ) {
			self::delete_refresh_token( $refresh );
		}

		return $responder->response(
			'tutor_auth_logout',
			__( 'Logged out successfully.', 'tutor' ),
			array(
				'success' => true,
			),
			HttpHelper::STATUS_OK
		);
	}

	/**
	 * Invalidate tokens when password changes on profile update.
	 *
	 * @since 4.2.0
	 *
	 * @param int      $user_id user id.
	 * @param \WP_User $old_user_data old user.
	 *
	 * @return void
	 */
	public function maybe_invalidate_tokens_on_profile_update( $user_id, $old_user_data ) {
		$user = get_userdata( $user_id );
		if ( ! $user || ! is_a( $old_user_data, 'WP_User' ) ) {
			return;
		}

		if ( $user->user_pass !== $old_user_data->user_pass ) {
			static::invalidate_user_tokens( $user_id );
		}
	}

	/**
	 * Bump token_version and delete refresh tokens.
	 *
	 * @since 4.2.0
	 *
	 * @param int|\WP_User $user user id or object.
	 *
	 * @return void
	 */
	public static function invalidate_user_tokens( $user ) {
		$user_id = is_object( $user ) ? (int) $user->ID : absint( $user );
		if ( ! $user_id ) {
			return;
		}

		$version = (int) get_user_meta( $user_id, static::TOKEN_VERSION_META, true );
		update_user_meta( $user_id, static::TOKEN_VERSION_META, $version + 1 );
		self::delete_all_refresh_tokens( $user_id );
	}

	/**
	 * Prepare html response for the API keys table row.
	 *
	 * Secrets are never included — they are shown only in the generate modal once.
	 *
	 * @since 2.2.1
	 * @since 4.2.0 Drop secret column; credentials revealed only in the generate modal.
	 *
	 * @param int    $meta_id meta id.
	 * @param string $key api key.
	 * @param string $permission authorization permission.
	 * @param string $description description.
	 *
	 * @return string
	 */
	public static function prepare_response( $meta_id, $key, $permission, $description = '' ) {
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
						<span class="tutor-icon-kebab-menu" aria-hidden="true"></span>
					</button>
					<div class="tutor-dropdown tutor-dropdown-dark tutor-text-left">
						<a href="javascript:void(0)" class="tutor-dropdown-item" data-tutor-modal-target="tutor-update-permission-modal" data-update-id="<?php echo esc_attr( $meta_id ); ?>" data-permission="<?php echo esc_attr( $permission ); ?>" data-description="<?php echo esc_attr( $description ); ?>">
							<i class="tutor-icon-edit tutor-mr-8" aria-hidden="true" data-update-id="<?php echo esc_attr( $meta_id ); ?>" data-permission="<?php echo esc_attr( $permission ); ?>" data-description="<?php echo esc_attr( $description ); ?>"></i>
							<span data-update-id="<?php echo esc_attr( $meta_id ); ?>" data-permission="<?php echo esc_attr( $permission ); ?>" data-description="<?php echo esc_attr( $description ); ?>"><?php esc_html_e( 'Edit', 'tutor' ); ?></span>
						</a>
						<a href="javascript:void(0)" class="tutor-dropdown-item" data-meta-id="<?php echo esc_attr( $meta_id ); ?>">
							<i class="tutor-icon-trash-can-bold tutor-mr-8" aria-hidden="true" data-meta-id="<?php echo esc_attr( $meta_id ); ?>"></i>
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
				'value' => static::READ,
				'label' => __( 'Read', 'tutor' ),
			),
		);
		return apply_filters( 'tutor_rest_api_permissions', $permissions );
	}

	/**
	 * Whether a permission string is in the available_permissions allowlist.
	 *
	 * Core only offers Read; Pro may expand via tutor_rest_api_permissions.
	 *
	 * @since 4.2.0
	 *
	 * @param string $permission Permission value from the request.
	 *
	 * @return bool
	 */
	private static function is_allowed_api_permission( $permission ): bool {
		if ( ! is_string( $permission ) || '' === $permission ) {
			return false;
		}

		$allowed = array_column( self::available_permissions(), 'value' );

		return in_array( $permission, $allowed, true );
	}

	/**
	 * Build login/refresh response payload.
	 *
	 * @param int $user_id user id.
	 * @param int $kid     API key usermeta id.
	 *
	 * @return array
	 */
	private static function build_token_response( $user_id, $kid ) {
		$access  = self::issue_access_token( $user_id, $kid );
		$refresh = self::issue_refresh_token( $user_id, $kid );
		$user    = get_userdata( $user_id );

		return array(
			'access_token'  => $access['token'],
			'expires_in'    => $access['expires_in'],
			'refresh_token' => $refresh,
			'user_id'       => $user_id,
			'display_name'  => $user ? $user->display_name : '',
		);
	}

	/**
	 * Issue HS256 access JWT.
	 *
	 * @param int $user_id user id.
	 * @param int $kid     API key usermeta id.
	 *
	 * @return array{token:string,expires_in:int}
	 */
	private static function issue_access_token( $user_id, $kid ) {
		$now        = time();
		$tv         = (int) get_user_meta( $user_id, static::TOKEN_VERSION_META, true );
		$kid        = absint( $kid );
		$access_ttl = self::get_access_ttl();

		$claims = array(
			'sub' => (int) $user_id,
			'iat' => $now,
			'iss' => 'tutor',
			'tv'  => $tv,
			'kid' => $kid,
		);

		if ( $access_ttl > static::TTL_UNLIMITED ) {
			$claims['exp'] = $now + $access_ttl;
		}

		return array(
			'token'      => self::encode_jwt( $claims ),
			'expires_in' => $access_ttl,
		);
	}

	/**
	 * Verify access JWT. Returns user id or 0.
	 *
	 * @param string $jwt token.
	 *
	 * @return int
	 */
	private static function verify_access_token( $jwt ) {
		self::$verified_token_claims = null;

		$payload = self::decode_jwt( $jwt );
		if ( ! $payload ) {
			return 0;
		}

		// Access tokens must not carry the refresh typ claim.
		if ( ! empty( $payload->typ ) && 'refresh' === $payload->typ ) {
			return 0;
		}

		$kid = isset( $payload->kid ) ? absint( $payload->kid ) : 0;
		if ( ! $kid || '' === self::get_permission_by_kid( $kid ) ) {
			return 0;
		}

		$user_id = (int) $payload->sub;
		$user    = get_userdata( $user_id );
		if ( ! $user || ! $user->exists() ) {
			return 0;
		}

		if ( function_exists( 'is_user_spammy' ) && is_user_spammy( $user ) ) {
			return 0;
		}

		$tv = (int) get_user_meta( $user_id, static::TOKEN_VERSION_META, true );
		if ( (int) ( $payload->tv ?? -1 ) !== $tv ) {
			return 0;
		}

		self::$verified_token_claims = array(
			'user_id' => $user_id,
			'kid'     => $kid,
		);

		return $user_id;
	}

	/**
	 * JWT HMAC secret.
	 *
	 * @return string
	 */
	private static function jwt_secret() {
		$stored = get_option( static::JWT_SECRET_OPTION, '' );
		if ( is_string( $stored ) && strlen( $stored ) >= 32 ) {
			return $stored;
		}

		try {
			$secret = bin2hex( random_bytes( 32 ) );
		} catch ( \Exception $e ) {
			$secret = hash_hmac( 'sha256', 'tutor-rest-jwt', wp_salt( 'auth' ) );
		}

		update_option( static::JWT_SECRET_OPTION, $secret, false );
		return $secret;
	}

	/**
	 * Encode claims as an HS256 JWT.
	 *
	 * @since 4.2.0
	 *
	 * @param array $claims JWT payload claims.
	 *
	 * @return string
	 */
	private static function encode_jwt( array $claims ) {
		$header = self::base64url_encode(
			wp_json_encode(
				array(
					'alg' => 'HS256',
					'typ' => 'JWT',
				)
			)
		);

		$payload = self::base64url_encode( wp_json_encode( $claims ) );
		$sig     = self::base64url_encode( hash_hmac( 'sha256', $header . '.' . $payload, self::jwt_secret(), true ) );

		return $header . '.' . $payload . '.' . $sig;
	}

	/**
	 * Decode and verify HS256 JWT signature + shared claims (iss, optional exp).
	 *
	 * @since 4.2.0
	 *
	 * @param string $jwt Token string.
	 *
	 * @return object|null Payload object on success, null on failure.
	 */
	private static function decode_jwt( $jwt ) {
		$parts = explode( '.', (string) $jwt );
		if ( 3 !== count( $parts ) ) {
			return null;
		}

		list( $header_b64, $payload_b64, $sig_b64 ) = $parts;

		$expected = self::base64url_encode(
			hash_hmac( 'sha256', $header_b64 . '.' . $payload_b64, self::jwt_secret(), true )
		);

		if ( ! hash_equals( $expected, $sig_b64 ) ) {
			return null;
		}

		$payload = json_decode( self::base64url_decode( $payload_b64 ) );
		if ( ! is_object( $payload ) || empty( $payload->sub ) ) {
			return null;
		}

		if ( empty( $payload->iss ) || 'tutor' !== $payload->iss ) {
			return null;
		}

		// Missing exp means unlimited; otherwise require a future expiration.
		if ( isset( $payload->exp ) && (int) $payload->exp < time() ) {
			return null;
		}

		return $payload;
	}

	/**
	 * Verify refresh JWT. Returns payload or null.
	 *
	 * @since 4.2.0
	 *
	 * @param string $jwt Refresh token.
	 *
	 * @return object|null
	 */
	private static function verify_refresh_jwt( $jwt ) {
		$payload = self::decode_jwt( $jwt );
		if ( ! $payload ) {
			return null;
		}

		if ( empty( $payload->typ ) || 'refresh' !== $payload->typ ) {
			return null;
		}

		$user_id = (int) $payload->sub;
		$user    = get_userdata( $user_id );
		if ( ! $user || ! $user->exists() ) {
			return null;
		}

		if ( function_exists( 'is_user_spammy' ) && is_user_spammy( $user ) ) {
			return null;
		}

		return $payload;
	}

	/**
	 * Base64 URL encode (JWT-safe, no padding).
	 *
	 * @param string $data raw.
	 *
	 * @return string
	 */
	private static function base64url_encode( $data ) {
		if ( function_exists( 'sodium_bin2base64' ) ) {
			return sodium_bin2base64( $data, SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING );
		}

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- JWT Base64URL fallback when ext-sodium is unavailable.
		return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' );
	}

	/**
	 * Base64 URL decode (JWT-safe, no padding).
	 *
	 * @param string $data encoded.
	 *
	 * @return string
	 */
	private static function base64url_decode( $data ) {
		if ( function_exists( 'sodium_base642bin' ) ) {
			try {
				return sodium_base642bin( $data, SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING );
			} catch ( \Throwable $e ) {
				return '';
			}
		}

		$remainder = strlen( $data ) % 4;
		if ( $remainder ) {
			$data .= str_repeat( '=', 4 - $remainder );
		}

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- JWT Base64URL fallback when ext-sodium is unavailable.
		$decoded = base64_decode( strtr( $data, '-_', '+/' ), true );
		return false === $decoded ? '' : $decoded;
	}

	/**
	 * Read access token from Authorization Bearer or Tutor-User-Token.
	 *
	 * @return string
	 */
	private static function get_access_token_from_request() {
		$headers = self::get_request_headers();

		if ( ! empty( $headers['tutor-user-token'] ) ) {
			return trim( $headers['tutor-user-token'] );
		}

		if ( ! empty( $headers['authorization'] ) && 0 === stripos( $headers['authorization'], 'Bearer ' ) ) {
			return trim( substr( $headers['authorization'], 7 ) );
		}

		return '';
	}

	/**
	 * Read API key/secret from Tutor-Api-* headers.
	 *
	 * @return array{key:string,secret:string}|null
	 */
	private static function get_api_credentials_from_request() {
		$headers = self::get_request_headers();

		if ( empty( $headers['tutor-api-key'] ) || empty( $headers['tutor-api-secret'] ) ) {
			return null;
		}

		return array(
			'key'    => sanitize_text_field( $headers['tutor-api-key'] ),
			'secret' => sanitize_text_field( $headers['tutor-api-secret'] ),
		);
	}

	/**
	 * Normalized request headers (lowercase keys).
	 *
	 * @return array<string,string>
	 */
	private static function get_request_headers() {
		$headers = array();

		if ( function_exists( 'apache_request_headers' ) ) {
			$raw = apache_request_headers();
			if ( is_array( $raw ) ) {
				foreach ( $raw as $key => $value ) {
					$headers[ strtolower( $key ) ] = $value;
				}
			}
		}

		foreach ( $_SERVER as $key => $value ) {
			if ( 0 === strpos( $key, 'HTTP_' ) ) {
				$header_key             = strtolower( str_replace( '_', '-', substr( $key, 5 ) ) );
				$headers[ $header_key ] = wp_unslash( $value );
			}
		}

		if ( isset( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ) && empty( $headers['authorization'] ) ) {
			$headers['authorization'] = sanitize_text_field( wp_unslash( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ) );
		}

		return $headers;
	}

	/**
	 * Issue signed refresh JWT; store hash + kid in that user's usermeta.
	 *
	 * The JWT carries `sub` (user id) so refresh/logout can load a single
	 * usermeta row instead of scanning all users.
	 *
	 * @since 4.2.0
	 *
	 * @param int $user_id user id.
	 * @param int $kid     API key usermeta id.
	 *
	 * @return string
	 */
	private static function issue_refresh_token( $user_id, $kid ) {
		$list        = self::get_refresh_token_list( $user_id );
		$now         = time();
		$kid         = absint( $kid );
		$refresh_ttl = self::get_refresh_ttl();
		$exp         = $refresh_ttl > static::TTL_UNLIMITED ? $now + $refresh_ttl : static::TTL_UNLIMITED;

		$list = array_values(
			array_filter(
				$list,
				function ( $row ) use ( $now ) {
					if ( ! is_array( $row ) || empty( $row['hash'] ) || ! isset( $row['exp'] ) ) {
						return false;
					}

					return self::is_refresh_exp_valid( $row['exp'], $now );
				}
			)
		);

		$claims = array(
			'sub' => (int) $user_id,
			'iat' => $now,
			'iss' => 'tutor',
			'typ' => 'refresh',
			'kid' => $kid,
		);

		if ( $exp > static::TTL_UNLIMITED ) {
			$claims['exp'] = $exp;
		}

		$token = self::encode_jwt( $claims );
		$hash  = hash( 'sha256', $token );

		$list[] = array(
			'hash' => $hash,
			'exp'  => $exp,
			'kid'  => $kid,
		);

		update_user_meta( $user_id, static::REFRESH_META_KEY, wp_json_encode( $list ) );

		return $token;
	}

	/**
	 * Validate and remove refresh token; return user id + kid.
	 *
	 * @param string $token refresh token.
	 *
	 * @return array{user_id:int,kid:int}|null
	 */
	private static function consume_refresh_token( $token ) {
		$session = self::find_refresh_session( $token );
		if ( ! $session ) {
			return null;
		}

		self::delete_refresh_token( $token, $session['user_id'] );
		return $session;
	}

	/**
	 * Find user id owning a refresh token.
	 *
	 * @param string $token refresh token.
	 *
	 * @return int
	 */
	private static function find_user_id_by_refresh_token( $token ) {
		$session = self::find_refresh_session( $token );
		return $session ? $session['user_id'] : 0;
	}

	/**
	 * Find refresh session (user id + kid) for a refresh token.
	 *
	 * Verifies the signed refresh JWT, then checks the hash only for that user.
	 *
	 * @since 4.2.0
	 *
	 * @param string $token refresh token.
	 *
	 * @return array{user_id:int,kid:int}|null
	 */
	private static function find_refresh_session( $token ) {
		$payload = self::verify_refresh_jwt( $token );
		if ( ! $payload ) {
			return null;
		}

		$user_id = (int) $payload->sub;
		$hash    = hash( 'sha256', $token );
		$now     = time();

		foreach ( self::get_refresh_token_list( $user_id ) as $entry ) {
			if ( empty( $entry['hash'] ) || ! isset( $entry['exp'] ) ) {
				continue;
			}
			if ( ! hash_equals( $entry['hash'], $hash ) || ! self::is_refresh_exp_valid( $entry['exp'], $now ) ) {
				continue;
			}

			$kid = isset( $entry['kid'] ) ? absint( $entry['kid'] ) : 0;
			if ( ! $kid ) {
				return null;
			}

			return array(
				'user_id' => $user_id,
				'kid'     => $kid,
			);
		}

		return null;
	}

	/**
	 * Delete one refresh token for a known (or JWT-derived) user.
	 *
	 * @since 4.2.0
	 *
	 * @param string $token   Refresh token.
	 * @param int    $user_id Optional. Skip JWT parse when already known.
	 *
	 * @return void
	 */
	private static function delete_refresh_token( $token, $user_id = 0 ) {
		$user_id = absint( $user_id );
		if ( ! $user_id ) {
			$payload = self::verify_refresh_jwt( $token );
			if ( ! $payload ) {
				return;
			}
			$user_id = (int) $payload->sub;
		}

		$hash = hash( 'sha256', $token );
		$list = self::get_refresh_token_list( $user_id );
		$list = array_values(
			array_filter(
				$list,
				function ( $row ) use ( $hash ) {
					return empty( $row['hash'] ) || ! hash_equals( $row['hash'], $hash );
				}
			)
		);
		update_user_meta( $user_id, static::REFRESH_META_KEY, wp_json_encode( $list ) );
	}

	/**
	 * Delete all refresh tokens for a user.
	 *
	 * @param int $user_id user id.
	 *
	 * @return void
	 */
	private static function delete_all_refresh_tokens( $user_id ) {
		delete_user_meta( $user_id, static::REFRESH_META_KEY );
	}

	/**
	 * Get refresh token list from usermeta.
	 *
	 * @param int $user_id user id.
	 *
	 * @return array
	 */
	private static function get_refresh_token_list( $user_id ) {
		$raw = get_user_meta( $user_id, static::REFRESH_META_KEY, true );
		if ( empty( $raw ) ) {
			return array();
		}
		$list = json_decode( $raw, true );
		return is_array( $list ) ? $list : array();
	}
}
