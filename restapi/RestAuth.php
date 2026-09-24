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
use Tutor\Models\EnrollmentModel;
use Tutor\Models\QuizModel;
use WP_REST_Request;

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
	 * Usermeta: refresh token hashes.
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
	 * Access JWT lifetime in seconds (~10 minutes).
	 *
	 * @var int
	 */
	const ACCESS_TTL = 600;

	/**
	 * Refresh token lifetime in seconds (30 days).
	 *
	 * @var int
	 */
	const REFRESH_TTL = 2592000;

	/**
	 * Max failed login attempts before rate limit.
	 *
	 * @var int
	 */
	const LOGIN_MAX_ATTEMPTS = 5;

	/**
	 * Login rate-limit window in seconds.
	 *
	 * @var int
	 */
	const LOGIN_WINDOW = 900;

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
		add_filter( 'determine_current_user', array( $this, 'api_auth' ) );
		add_action( 'profile_update', array( $this, 'maybe_invalidate_tokens_on_profile_update' ), 10, 2 );
		add_action( 'after_password_reset', array( $this, 'invalidate_user_tokens' ), 10, 1 );
		add_action( 'password_reset', array( $this, 'invalidate_user_tokens' ), 10, 1 );
		add_filter( 'rest_request_before_callbacks', array( __CLASS__, 'enforce_actor_identity' ), 20, 3 );
	}

	/**
	 * Authenticate Tutor REST requests from access JWT only.
	 *
	 * @since 2.7.1
	 * @since 4.0.8 Identity is never taken from the API key owner.
	 *
	 * @param int|false $user_id user id.
	 *
	 * @return int|false
	 */
	public function api_auth( $user_id ) {
		if ( ! empty( $user_id ) || ! static::is_tutor_api_request() ) {
			return $user_id;
		}

		$token = static::get_access_token_from_request();
		if ( ! $token ) {
			return $user_id;
		}

		$jwt_user_id = static::verify_access_token( $token );
		if ( ! $jwt_user_id ) {
			return $user_id;
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
	 * @since 4.0.10
	 *
	 * @return bool
	 */
	public static function is_auth_route() {
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
		$base = '/' . $rest_prefix . 'tutor/v1/auth/';

		return (
			false !== strpos( $path, $base . 'login/' )
			|| false !== strpos( $path, $base . 'refresh/' )
			|| false !== strpos( $path, $base . 'logout/' )
			|| false !== strpos( $path, $base . 'login' )
			|| false !== strpos( $path, $base . 'refresh' )
			|| false !== strpos( $path, $base . 'logout' )
		);
	}

	/**
	 * Generate api keys
	 *
	 * @since 2.2.1
	 *
	 * @return void send wp_json response
	 */
	public static function generate_api_keys() {
		tutor_utils()->checking_nonce();

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

		$add = add_user_meta(
			get_current_user_id(),
			static::KEYS_USER_META_KEY,
			$info
		);

		if ( $add ) {
			$response = static::prepare_response( $add, $api_key, $api_secret, $permission, $description );
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

		tutor_utils()->checking_nonce();

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

		try {
			QueryHelper::update(
				$wpdb->usermeta,
				array( 'meta_value' => wp_json_encode( $meta_value ) ),
				array( 'umeta_id' => $meta_id )
			);

			$response = static::prepare_response( $meta_id, $meta_value->key, $meta_value->secret, $permission, $description );
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
		tutor_utils()->checking_nonce();

		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$meta_id = Input::post( 'meta_id', 0, Input::TYPE_INT );

		if ( ! $meta_id ) {
			wp_send_json_error( __( 'Invalid meta id', 'tutor' ) );
		}

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
	 * @since 2.7.1 $return_result param added.
	 *
	 * @param string $api_key api key.
	 * @param string $api_secret api secret.
	 * @param bool   $return_result return matched meta record.
	 *
	 * @return bool|object
	 */
	public static function validate_api_key_secret( $api_key, $api_secret, $return_result = false ) {
		global $wpdb;
		$table = $wpdb->usermeta;

		$valid = false;

		$results = QueryHelper::get_all(
			$table,
			array( 'meta_key' => static::KEYS_USER_META_KEY ), //phpcs:ignore
			'umeta_id'
		);

		if ( is_array( $results ) && count( $results ) ) {
			foreach ( $results as $result ) {
				$obj = json_decode( $result->meta_value );
				if ( is_object( $obj ) && isset( $obj->key, $obj->secret ) && $obj->key === $api_key && $obj->secret === $api_secret ) {
					$valid = true;
					if ( $return_result ) {
						return $result;
					}
					break;
				}
			}
		}

		return $valid;
	}

	/**
	 * Process api request — validate key/secret and honor Read/Write/All vs HTTP method.
	 *
	 * @since 2.2.1
	 * @since 4.0.10 Honor key permission; accept X-Tutor-Api-Key headers.
	 *
	 * @return boolean
	 */
	public static function process_api_request() {
		$credentials = static::get_api_credentials_from_request();
		if ( ! $credentials ) {
			return false;
		}

		$record = static::validate_api_key_secret( $credentials['key'], $credentials['secret'], true );
		if ( ! $record ) {
			return false;
		}

		$meta = json_decode( $record->meta_value );
		if ( ! is_object( $meta ) || empty( $meta->permission ) ) {
			return false;
		}

		$method     = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : 'GET';
		$permission = $meta->permission;

		// Auth routes may POST with a Read key (login/refresh/logout).
		if ( static::is_auth_route() ) {
			return in_array( $permission, array( static::READ, static::READ_WRITE, static::ALL ), true );
		}

		if ( 'DELETE' === $method ) {
			return in_array( $permission, array( static::DELETE, static::WRITE, static::READ_WRITE, static::ALL ), true );
		}

		$write_methods = array( 'POST', 'PUT', 'PATCH' );
		if ( in_array( $method, $write_methods, true ) ) {
			return in_array( $permission, array( static::WRITE, static::READ_WRITE, static::ALL ), true );
		}

		return in_array( $permission, array( static::READ, static::READ_WRITE, static::ALL ), true );
	}

	/**
	 * Whether the request has a JWT-authenticated WordPress user.
	 *
	 * @since 4.0.10
	 *
	 * @return bool
	 */
	public static function has_authenticated_user() {
		return (int) get_current_user_id() > 0;
	}

	/**
	 * Valid API key for this HTTP method and an authenticated end user (JWT).
	 *
	 * Used by Tutor Pro REST routes.
	 *
	 * @since 4.0.10
	 *
	 * @return bool
	 */
	public static function process_authenticated_api_request() {
		return static::process_api_request() && static::has_authenticated_user();
	}

	/**
	 * Whether the current user may act as the given user (self or privileged admin).
	 *
	 * @since 4.0.10
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
	 * @since 4.0.10
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
	 * routes and unauthenticated requests are skipped. Object-level checks
	 * can plug in via the `tutor_rest_enforce_object_access` filter.
	 *
	 * @since 4.1.0
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

		/**
		 * Object-level access for extensions (Tutor Pro ObjectAccess).
		 *
		 * @since 4.1.0
		 *
		 * @param true|\WP_Error  $result  Pass-through true, or WP_Error to deny.
		 * @param WP_REST_Request $request Request.
		 * @param array           $handler Route handler.
		 */
		$object_access = apply_filters( 'tutor_rest_enforce_object_access', true, $request, $handler );
		if ( is_wp_error( $object_access ) ) {
			return $object_access;
		}

		return $response;
	}

	/**
	 * Permission: valid API key and may view course learning content.
	 *
	 * @since 4.0.10
	 *
	 * @param WP_REST_Request $request request.
	 *
	 * @return bool
	 */
	public static function permission_course_content( WP_REST_Request $request ) {
		if ( ! static::process_api_request() ) {
			return false;
		}

		$course_id = absint( $request->get_param( 'id' ) );
		if ( ! $course_id ) {
			$course_id = absint( $request->get_param( 'course_id' ) );
		}

		return static::can_view_course_content( $course_id );
	}

	/**
	 * Permission: topics by course_id.
	 *
	 * @since 4.0.10
	 *
	 * @param WP_REST_Request $request request.
	 *
	 * @return bool
	 */
	public static function permission_topics( WP_REST_Request $request ) {
		if ( ! static::process_api_request() ) {
			return false;
		}

		return static::can_view_course_content( absint( $request->get_param( 'course_id' ) ) );
	}

	/**
	 * Permission: lessons or quizzes listed by topic_id.
	 *
	 * @since 4.0.10
	 *
	 * @param WP_REST_Request $request request.
	 *
	 * @return bool
	 */
	public static function permission_by_topic( WP_REST_Request $request ) {
		if ( ! static::process_api_request() ) {
			return false;
		}

		$topic_id  = absint( $request->get_param( 'topic_id' ) );
		$course_id = (int) tutor_utils()->get_course_id_by( 'topic', $topic_id );

		return static::can_view_course_content( $course_id );
	}

	/**
	 * Permission: quiz by quiz id.
	 *
	 * @since 4.0.10
	 *
	 * @param WP_REST_Request $request request.
	 *
	 * @return bool
	 */
	public static function permission_quiz( WP_REST_Request $request ) {
		if ( ! static::process_api_request() ) {
			return false;
		}

		$quiz_id   = absint( $request->get_param( 'id' ) );
		$course_id = (int) tutor_utils()->get_course_id_by( 'quiz', $quiz_id );

		return static::can_view_course_content( $course_id );
	}

	/**
	 * Whether the user may view full course learning content.
	 *
	 * @since 4.0.10
	 *
	 * @param int $course_id course id.
	 * @param int $user_id user id.
	 *
	 * @return bool
	 */
	public static function can_view_course_content( $course_id, $user_id = 0 ) {
		$course_id = absint( $course_id );
		if ( ! $course_id ) {
			return false;
		}

		if ( Course_List::is_public( $course_id ) ) {
			return true;
		}

		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		if ( ! $user_id ) {
			return false;
		}

		if ( EnrollmentModel::is_enrolled( $course_id, $user_id ) ) {
			return true;
		}

		return (bool) tutor_utils()->has_user_course_content_access( $user_id, $course_id );
	}

	/**
	 * Whether answer keys (is_correct) may be revealed.
	 *
	 * @since 4.0.10
	 *
	 * @param int $quiz_id quiz id.
	 * @param int $user_id user id.
	 *
	 * @return bool
	 */
	public static function can_reveal_quiz_answers( $quiz_id, $user_id = 0 ) {
		$quiz_id = absint( $quiz_id );
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		if ( ! $quiz_id || ! $user_id ) {
			return false;
		}

		$course_id = (int) tutor_utils()->get_course_id_by( 'quiz', $quiz_id );
		if ( $course_id && tutor_utils()->has_user_course_content_access( $user_id, $course_id ) ) {
			return true;
		}

		$attempt = ( new QuizModel() )->get_quiz_attempt( $quiz_id, $user_id );
		return is_object( $attempt ) && ! empty( $attempt->attempt_ended_at );
	}

	/**
	 * Whether viewer may see private user fields (email, login, registered).
	 *
	 * @since 4.0.10
	 *
	 * @param int $target_user_id target user.
	 * @param int $viewer_id viewer.
	 *
	 * @return bool
	 */
	public static function can_view_user_private_fields( $target_user_id, $viewer_id = 0 ) {
		$target_user_id = absint( $target_user_id );
		$viewer_id      = $viewer_id ? absint( $viewer_id ) : get_current_user_id();

		if ( ! $target_user_id || ! $viewer_id ) {
			return false;
		}

		if ( $target_user_id === $viewer_id ) {
			return true;
		}

		if ( user_can( $viewer_id, 'list_users' ) ) {
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
	 * Login — issue access + refresh tokens.
	 *
	 * @since 4.0.10
	 *
	 * @param WP_REST_Request $request request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public static function rest_login( WP_REST_Request $request ) {
		$ssl_error = static::require_ssl_for_auth();
		if ( is_wp_error( $ssl_error ) ) {
			return $ssl_error;
		}

		$username = sanitize_text_field( (string) $request->get_param( 'username' ) );
		$password = (string) $request->get_param( 'password' );

		if ( '' === $username || '' === $password ) {
			return new \WP_Error(
				'rest_invalid_credentials',
				__( 'Invalid username or password.', 'tutor' ),
				array( 'status' => 401 )
			);
		}

		if ( is_email( $username ) ) {
			$user_by_email = get_user_by( 'email', $username );
			if ( $user_by_email ) {
				$username = $user_by_email->user_login;
			}
		}

		if ( static::is_login_rate_limited( $username ) ) {
			return new \WP_Error(
				'rest_login_limited',
				__( 'Too many failed login attempts. Please try again later.', 'tutor' ),
				array( 'status' => 429 )
			);
		}

		$user = wp_authenticate( $username, $password );
		if ( is_wp_error( $user ) ) {
			static::bump_login_rate_limit( $username );
			return new \WP_Error(
				'rest_invalid_credentials',
				__( 'Invalid username or password.', 'tutor' ),
				array( 'status' => 401 )
			);
		}

		static::clear_login_rate_limit( $username );

		return rest_ensure_response( static::build_token_response( (int) $user->ID ) );
	}

	/**
	 * Refresh access token (rotates refresh token).
	 *
	 * @since 4.0.10
	 *
	 * @param WP_REST_Request $request request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public static function rest_refresh( WP_REST_Request $request ) {
		$ssl_error = static::require_ssl_for_auth();
		if ( is_wp_error( $ssl_error ) ) {
			return $ssl_error;
		}

		$refresh = sanitize_text_field( (string) $request->get_param( 'refresh_token' ) );
		if ( '' === $refresh ) {
			return new \WP_Error(
				'rest_invalid_refresh',
				__( 'Invalid refresh token.', 'tutor' ),
				array( 'status' => 401 )
			);
		}

		$user_id = static::consume_refresh_token( $refresh );
		if ( ! $user_id ) {
			return new \WP_Error(
				'rest_invalid_refresh',
				__( 'Invalid refresh token.', 'tutor' ),
				array( 'status' => 401 )
			);
		}

		return rest_ensure_response( static::build_token_response( $user_id ) );
	}

	/**
	 * Logout — delete refresh token(s).
	 *
	 * @since 4.0.10
	 *
	 * @param WP_REST_Request $request request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public static function rest_logout( WP_REST_Request $request ) {
		$ssl_error = static::require_ssl_for_auth();
		if ( is_wp_error( $ssl_error ) ) {
			return $ssl_error;
		}

		$refresh = sanitize_text_field( (string) $request->get_param( 'refresh_token' ) );
		$all     = (bool) $request->get_param( 'all' );

		if ( $all ) {
			$token   = static::get_access_token_from_request();
			$user_id = $token ? static::verify_access_token( $token ) : 0;
			if ( ! $user_id && $refresh ) {
				$user_id = static::find_user_id_by_refresh_token( $refresh );
			}
			if ( $user_id ) {
				static::delete_all_refresh_tokens( $user_id );
			}
		} elseif ( $refresh ) {
			static::delete_refresh_token( $refresh );
		}

		return rest_ensure_response(
			array(
				'success' => true,
			)
		);
	}

	/**
	 * Invalidate tokens when password changes on profile update.
	 *
	 * @since 4.0.10
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
	 * @since 4.0.10
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
		static::delete_all_refresh_tokens( $user_id );
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
	 * @param string $description description.
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
	 * Build login/refresh response payload.
	 *
	 * @param int $user_id user id.
	 *
	 * @return array
	 */
	private static function build_token_response( $user_id ) {
		$access  = static::issue_access_token( $user_id );
		$refresh = static::issue_refresh_token( $user_id );
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
	 *
	 * @return array{token:string,expires_in:int}
	 */
	private static function issue_access_token( $user_id ) {
		$now = time();
		$tv  = (int) get_user_meta( $user_id, static::TOKEN_VERSION_META, true );

		$header  = static::base64url_encode( wp_json_encode( array( 'alg' => 'HS256', 'typ' => 'JWT' ) ) );
		$payload = static::base64url_encode(
			wp_json_encode(
				array(
					'sub' => (int) $user_id,
					'iat' => $now,
					'exp' => $now + static::ACCESS_TTL,
					'iss' => 'tutor',
					'tv'  => $tv,
				)
			)
		);
		$sig     = static::base64url_encode( hash_hmac( 'sha256', $header . '.' . $payload, static::jwt_secret(), true ) );

		return array(
			'token'      => $header . '.' . $payload . '.' . $sig,
			'expires_in' => static::ACCESS_TTL,
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
		$parts = explode( '.', $jwt );
		if ( 3 !== count( $parts ) ) {
			return 0;
		}

		list( $header_b64, $payload_b64, $sig_b64 ) = $parts;

		$expected = static::base64url_encode(
			hash_hmac( 'sha256', $header_b64 . '.' . $payload_b64, static::jwt_secret(), true )
		);

		if ( ! hash_equals( $expected, $sig_b64 ) ) {
			return 0;
		}

		$payload_json = static::base64url_decode( $payload_b64 );
		$payload      = json_decode( $payload_json );
		if ( ! is_object( $payload ) || empty( $payload->sub ) || empty( $payload->exp ) ) {
			return 0;
		}

		if ( (int) $payload->exp < time() ) {
			return 0;
		}

		if ( empty( $payload->iss ) || 'tutor' !== $payload->iss ) {
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
	 * Base64 URL encode.
	 *
	 * @param string $data raw.
	 *
	 * @return string
	 */
	private static function base64url_encode( $data ) {
		return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}

	/**
	 * Base64 URL decode.
	 *
	 * @param string $data encoded.
	 *
	 * @return string
	 */
	private static function base64url_decode( $data ) {
		$remainder = strlen( $data ) % 4;
		if ( $remainder ) {
			$data .= str_repeat( '=', 4 - $remainder );
		}
		$decoded = base64_decode( strtr( $data, '-_', '+/' ), true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		return false === $decoded ? '' : $decoded;
	}

	/**
	 * Read access token from Authorization Bearer or X-Tutor-User-Token.
	 *
	 * @return string
	 */
	private static function get_access_token_from_request() {
		$headers = static::get_request_headers();

		if ( ! empty( $headers['x-tutor-user-token'] ) ) {
			return trim( $headers['x-tutor-user-token'] );
		}

		if ( ! empty( $headers['authorization'] ) && 0 === stripos( $headers['authorization'], 'Bearer ' ) ) {
			return trim( substr( $headers['authorization'], 7 ) );
		}

		return '';
	}

	/**
	 * Read API key/secret from Basic auth or X-Tutor-Api-* headers.
	 *
	 * @return array{key:string,secret:string}|null
	 */
	private static function get_api_credentials_from_request() {
		$headers = static::get_request_headers();

		if ( ! empty( $headers['x-tutor-api-key'] ) && ! empty( $headers['x-tutor-api-secret'] ) ) {
			return array(
				'key'    => sanitize_text_field( $headers['x-tutor-api-key'] ),
				'secret' => sanitize_text_field( $headers['x-tutor-api-secret'] ),
			);
		}

		$auth = $headers['authorization'] ?? '';
		if ( $auth && 0 === stripos( $auth, 'Basic ' ) ) {
			$decoded = base64_decode( substr( $auth, 6 ), true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
			if ( is_string( $decoded ) && false !== strpos( $decoded, ':' ) ) {
				list( $key, $secret ) = explode( ':', $decoded, 2 );
				return array(
					'key'    => sanitize_text_field( $key ),
					'secret' => sanitize_text_field( $secret ),
				);
			}
		}

		if ( isset( $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'] ) ) {
			return array(
				'key'    => sanitize_text_field( wp_unslash( $_SERVER['PHP_AUTH_USER'] ) ),
				'secret' => sanitize_text_field( wp_unslash( $_SERVER['PHP_AUTH_PW'] ) ),
			);
		}

		return null;
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
			$headers['authorization'] = wp_unslash( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		}

		return $headers;
	}

	/**
	 * Issue opaque refresh token; store hash in usermeta.
	 *
	 * @param int $user_id user id.
	 *
	 * @return string
	 */
	private static function issue_refresh_token( $user_id ) {
		$token = bin2hex( random_bytes( 32 ) );
		$hash  = hash( 'sha256', $token );
		$list  = static::get_refresh_token_list( $user_id );
		$now   = time();

		$list   = array_values(
			array_filter(
				$list,
				function ( $row ) use ( $now ) {
					return is_array( $row ) && ! empty( $row['hash'] ) && ! empty( $row['exp'] ) && (int) $row['exp'] > $now;
				}
			)
		);
		$list[] = array(
			'hash' => $hash,
			'exp'  => $now + static::REFRESH_TTL,
		);

		update_user_meta( $user_id, static::REFRESH_META_KEY, wp_json_encode( $list ) );

		return $token;
	}

	/**
	 * Validate and remove refresh token; return user id.
	 *
	 * @param string $token refresh token.
	 *
	 * @return int
	 */
	private static function consume_refresh_token( $token ) {
		$user_id = static::find_user_id_by_refresh_token( $token );
		if ( ! $user_id ) {
			return 0;
		}

		static::delete_refresh_token( $token );
		return $user_id;
	}

	/**
	 * Find user id owning a refresh token.
	 *
	 * @param string $token refresh token.
	 *
	 * @return int
	 */
	private static function find_user_id_by_refresh_token( $token ) {
		global $wpdb;

		$hash = hash( 'sha256', $token );
		$now  = time();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT user_id, meta_value FROM {$wpdb->usermeta} WHERE meta_key = %s",
				static::REFRESH_META_KEY
			)
		);

		if ( ! is_array( $rows ) ) {
			return 0;
		}

		foreach ( $rows as $row ) {
			$list = json_decode( $row->meta_value, true );
			if ( ! is_array( $list ) ) {
				continue;
			}
			foreach ( $list as $entry ) {
				if ( empty( $entry['hash'] ) || empty( $entry['exp'] ) ) {
					continue;
				}
				if ( hash_equals( $entry['hash'], $hash ) && (int) $entry['exp'] > $now ) {
					return (int) $row->user_id;
				}
			}
		}

		return 0;
	}

	/**
	 * Delete one refresh token.
	 *
	 * @param string $token refresh token.
	 *
	 * @return void
	 */
	private static function delete_refresh_token( $token ) {
		$user_id = static::find_user_id_by_refresh_token( $token );
		if ( ! $user_id ) {
			// Token may already be partially matched — scan by hash after consume path.
			$hash = hash( 'sha256', $token );
			static::delete_refresh_hash_for_all_users( $hash );
			return;
		}

		$hash = hash( 'sha256', $token );
		$list = static::get_refresh_token_list( $user_id );
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
	 * Remove a refresh hash across users (best-effort).
	 *
	 * @param string $hash sha256 hash.
	 *
	 * @return void
	 */
	private static function delete_refresh_hash_for_all_users( $hash ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT umeta_id, user_id, meta_value FROM {$wpdb->usermeta} WHERE meta_key = %s",
				static::REFRESH_META_KEY
			)
		);

		if ( ! is_array( $rows ) ) {
			return;
		}

		foreach ( $rows as $row ) {
			$list = json_decode( $row->meta_value, true );
			if ( ! is_array( $list ) ) {
				continue;
			}
			$new = array_values(
				array_filter(
					$list,
					function ( $entry ) use ( $hash ) {
						return empty( $entry['hash'] ) || ! hash_equals( $entry['hash'], $hash );
					}
				)
			);
			if ( count( $new ) !== count( $list ) ) {
				update_user_meta( (int) $row->user_id, static::REFRESH_META_KEY, wp_json_encode( $new ) );
			}
		}
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

	/**
	 * Require SSL for auth endpoints (except local).
	 *
	 * @return true|\WP_Error
	 */
	private static function require_ssl_for_auth() {
		if ( is_ssl() ) {
			return true;
		}

		if ( function_exists( 'wp_get_environment_type' ) && 'local' === wp_get_environment_type() ) {
			return true;
		}

		return new \WP_Error(
			'rest_ssl_required',
			__( 'HTTPS is required for authentication.', 'tutor' ),
			array( 'status' => 403 )
		);
	}

	/**
	 * Rate-limit key for login.
	 *
	 * @param string $username username.
	 *
	 * @return string
	 */
	private static function login_rate_limit_key( $username ) {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		return 'tutor_rest_login_' . md5( strtolower( $username ) . '|' . $ip );
	}

	/**
	 * Whether login is rate limited.
	 *
	 * @param string $username username.
	 *
	 * @return bool
	 */
	private static function is_login_rate_limited( $username ) {
		return (int) get_transient( static::login_rate_limit_key( $username ) ) >= static::LOGIN_MAX_ATTEMPTS;
	}

	/**
	 * Bump login failure counter.
	 *
	 * @param string $username username.
	 *
	 * @return void
	 */
	private static function bump_login_rate_limit( $username ) {
		$key   = static::login_rate_limit_key( $username );
		$count = (int) get_transient( $key );
		set_transient( $key, $count + 1, static::LOGIN_WINDOW );
	}

	/**
	 * Clear login rate limit on success.
	 *
	 * @param string $username username.
	 *
	 * @return void
	 */
	private static function clear_login_rate_limit( $username ) {
		delete_transient( static::login_rate_limit_key( $username ) );
	}
}
