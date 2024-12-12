<?php
/**
 * Manage user
 *
 * @package Tutor\User
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace TUTOR;

use Tutor\Helpers\HttpHelper;
use Tutor\Models\UserModel;
use Tutor\Traits\JsonResponse;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User class
 *
 * @since 1.0.0
 */
class User {
	use JsonResponse;

	const STUDENT    = 'subscriber';
	const INSTRUCTOR = 'tutor_instructor';
	const ADMIN      = 'administrator';

	/**
	 * User meta keys.
	 */
	const REVIEW_POPUP_META      = 'tutor_review_course_popup';
	const LAST_LOGIN_META        = 'tutor_last_login';
	const TIMEZONE_META          = '_tutor_timezone';
	const PROFILE_PHOTO_META     = '_tutor_profile_photo';
	const PHONE_NUMBER_META      = 'phone_number';
	const COVER_PHOTO_META       = '_tutor_cover_photo';
	const PROFILE_BIO_META       = '_tutor_profile_bio';
	const PROFILE_JOB_TITLE_META = '_tutor_profile_job_title';
	const TUTOR_STUDENT_META     = '_is_tutor_student';

	/**
	 * User model
	 *
	 * @since 3.0.0
	 *
	 * @var UserModel
	 */
	private $model;

	/**
	 * Registration notice
	 *
	 * @since 1.0.0
	 *
	 * @var boolean
	 */
	private static $hide_registration_notice = false;

	/**
	 * Register hooks
	 *
	 * @since 1.0.0
	 * @since 2.2.0 $register_hooks param added to resuse the class without hooks register.
	 *
	 * @param bool $register_hooks register hooks.
	 *
	 * @return void
	 */
	public function __construct( $register_hooks = true ) {
		$this->model = new UserModel();
		if ( ! $register_hooks ) {
			return;
		}

		add_action( 'edit_user_profile', array( $this, 'edit_user_profile' ) );
		add_action( 'show_user_profile', array( $this, 'edit_user_profile' ), 10, 1 );

		add_action( 'profile_update', array( $this, 'profile_update' ) );
		add_action( 'set_user_role', array( $this, 'set_user_role' ), 10, 3 );

		add_action( 'wp_ajax_tutor_user_photo_remove', array( $this, 'tutor_user_photo_remove' ) );
		add_action( 'wp_ajax_tutor_user_photo_upload', array( $this, 'update_user_photo' ) );

		add_action( 'admin_notices', array( $this, 'show_registration_disabled' ) );
		add_action( 'admin_init', array( $this, 'hide_notices' ) );
		add_action( 'wp_login', array( $this, 'update_user_last_login' ), 10, 2 );
		add_action( 'login_form', array( $this, 'add_timezone_input' ) );
		add_action( 'wp_login', array( $this, 'set_timezone' ), 10, 2 );

		add_action( 'wp_ajax_tutor_user_list', array( $this, 'ajax_user_list' ) );
	}

	/**
	 * Get meta key name for review popup.
	 *
	 * @since 2.4.0
	 *
	 * @param int $course_id course id.
	 *
	 * @return string user meta key name.
	 */
	public static function get_review_popup_meta( $course_id ) {
		return self::REVIEW_POPUP_META . '_' . $course_id;
	}

	/**
	 * Check user has provided role.
	 *
	 * @since 2.2.0
	 *
	 * @param string $role role.
	 *
	 * @return boolean
	 */
	public static function is( string $role ) {
		return current_user_can( $role );
	}

	/**
	 * Check user has any role.
	 *
	 * @since 2.2.0
	 * @since 2.6.2 $user_id param added.
	 *
	 * @param array $roles roles.
	 * @param int   $user_id user id.
	 *
	 * @return boolean
	 */
	public static function has_any_role( array $roles, $user_id = 0 ) {
		$user = get_userdata( tutor_utils()->get_user_id( $user_id ) );
		if ( empty( $user->roles ) || empty( $roles ) ) {
			return false;
		}

		foreach ( $roles as $role ) {
			if ( in_array( $role, $user->roles, true ) ) {
				return true;
				break;
			}
		}

		return false;
	}

	/**
	 * Check user is student.
	 *
	 * @since 2.2.0
	 * @since 2.6.2 $user_id param added.
	 *
	 * @param int $user_id user id.
	 *
	 * @return boolean
	 */
	public static function is_student( $user_id = 0 ) {
		$is_tutor_student = get_user_meta( tutor_utils()->get_user_id( $user_id ), self::TUTOR_STUDENT_META, true );
		return $is_tutor_student ? true : false;
	}

	/**
	 * Check user is admin.
	 *
	 * @since 2.2.0
	 *
	 * @return boolean
	 */
	public static function is_admin() {
		return current_user_can( self::ADMIN );
	}

	/**
	 * Check current user is instructor.
	 *
	 * @since 2.2.0
	 *
	 * @param bool $is_approved instructor is approved or not.
	 *
	 * @return boolean
	 */
	public static function is_instructor( $is_approved = true ) {
		return tutils()->is_instructor( 0, $is_approved );
	}

	/**
	 * Profile layouts
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $profile_layout = array(
		'pp-circle',
		'pp-rectangle',
		'no-cp',
	);

	/**
	 * Include edit user template
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $user user.
	 *
	 * @return void
	 */
	public function edit_user_profile( $user ) {
		include tutor()->path . 'views/metabox/user-profile-fields.php';
	}

	/**
	 * Delete existing user's photo
	 *
	 * @since 1.0.0
	 *
	 * @param int    $user_id user id.
	 * @param string $type photo type.
	 *
	 * @return void
	 */
	private function delete_existing_user_photo( $user_id, $type ) {
		$meta_key = 'cover_photo' == $type ? '_tutor_cover_photo' : '_tutor_profile_photo';
		$photo_id = get_user_meta( $user_id, $meta_key, true );
		is_numeric( $photo_id ) ? wp_delete_attachment( $photo_id, true ) : 0;
		delete_user_meta( $user_id, $meta_key );
	}

	/**
	 * User photo remove
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function tutor_user_photo_remove() {
		tutor_utils()->checking_nonce();
		$this->delete_existing_user_photo(
			get_current_user_id(),
			Input::post( 'photo_type', '' )
		);
	}

	/**
	 * User photo update
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function update_user_photo() {
		tutor_utils()->checking_nonce();

		$user_id    = get_current_user_id();
		$photo_type = Input::post( 'photo_type', '' );
		$meta_key   = 'cover_photo' === $photo_type ? '_tutor_cover_photo' : '_tutor_profile_photo';

		/**
		 * Photo Update from profile
		 */
		$photo      = tutor_utils()->array_get( 'photo_file', $_FILES );
		$photo_size = tutor_utils()->array_get( 'size', $photo );
		$photo_type = tutor_utils()->array_get( 'type', $photo );

		if ( $photo_size && strpos( $photo_type, 'image' ) !== false ) {
			if ( ! function_exists( 'wp_handle_upload' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}
			$upload_overrides = array( 'test_form' => false );
			$movefile         = wp_handle_upload( $photo, $upload_overrides );

			if ( $movefile && ! isset( $movefile['error'] ) ) {
				$file_path = tutor_utils()->array_get( 'file', $movefile );
				$file_url  = tutor_utils()->array_get( 'url', $movefile );
				$mime_type = '';
				if ( file_exists( $file_path ) ) {
					$image_info = getimagesize( $file_path );
					$mime_type  = is_array( $image_info ) && count( $image_info ) ? $image_info['mime'] : '';
				}

				$media_id = wp_insert_attachment(
					array(
						'guid'           => $file_path,
						'post_mime_type' => $mime_type,
						'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_url ) ),
						'post_content'   => '',
						'post_status'    => 'inherit',
					),
					$file_path,
					0
				);

				if ( $media_id ) {
					// wp_generate_attachment_metadata() won't work if you do not include this file.
					require_once ABSPATH . 'wp-admin/includes/image.php';

					// Generate and save the attachment metas into the database.
					wp_update_attachment_metadata( $media_id, wp_generate_attachment_metadata( $media_id, $file_path ) );

					// Update it to user profile.
					$this->delete_existing_user_photo( $user_id, Input::post( 'photo_type', '' ) );
					update_user_meta( $user_id, $meta_key, $media_id );

					exit( wp_json_encode( array( 'status' => 'success' ) ) );
				}
			}
		}
	}

	/**
	 * Get user timezone string.
	 *
	 * @param int|object $user user id or user object. Default: current user.
	 *
	 * @return string user timezone string, fallback site timezone string.
	 */
	public static function get_user_timezone_string( $user = 0 ) {
		if ( is_numeric( $user ) ) {
			$user = get_user_by( 'id', tutor_utils()->get_user_id( $user ) );
		}

		if ( ! is_a( $user, 'WP_User' ) ) {
			return wp_timezone_string();
		}

		$timezone = get_user_meta( $user->ID, self::TIMEZONE_META, true );
		if ( self::is_admin() || empty( $timezone ) ) {
			$timezone = wp_timezone_string();
		}

		return $timezone;
	}

	/**
	 * Profile update
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id user id.
	 *
	 * @return void
	 */
	public function profile_update( $user_id ) {
		if ( 'tutor_profile_update_by_wp' !== Input::post( 'tutor_action' ) ) {
			return;
		}

		$timezone                 = Input::post( 'timezone', '' );
		$_tutor_profile_job_title = Input::post( self::PROFILE_JOB_TITLE_META, '' );
		$_tutor_profile_bio       = Input::post( self::PROFILE_BIO_META, '', Input::TYPE_KSES_POST );
		$_tutor_profile_image     = Input::post( self::PROFILE_PHOTO_META, '', Input::TYPE_KSES_POST );

		update_user_meta( $user_id, self::PROFILE_JOB_TITLE_META, $_tutor_profile_job_title );
		update_user_meta( $user_id, self::PROFILE_BIO_META, $_tutor_profile_bio );
		update_user_meta( $user_id, self::PROFILE_PHOTO_META, $_tutor_profile_image );
		update_user_meta( $user_id, self::TIMEZONE_META, $timezone );
	}

	/**
	 * Set user role
	 *
	 * @since 1.0.0
	 *
	 * @param int    $user_id user id.
	 * @param string $role user role.
	 * @param array  $old_roles old role.
	 *
	 * @return void
	 */
	public function set_user_role( $user_id, $role, $old_roles ) {
		$instructor_role = tutor()->instructor_role;

		if ( $role === $instructor_role || in_array( $instructor_role, $old_roles ) ) {
			tutor_utils()->add_instructor_role( $user_id );
		}
	}

	/**
	 * Hide notices
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function hide_notices() {
		$hide_notice         = Input::get( 'tutor-hide-notice', '' );
		$is_register_enabled = Input::get( 'tutor-registration', '' );
		$has_manage_cap      = current_user_can( 'manage_options' );

		if ( $has_manage_cap && is_admin() && 'registration' === $hide_notice ) {
			tutor_utils()->checking_nonce( 'get' );

			if ( 'enable' === $is_register_enabled ) {
				update_option( 'users_can_register', 1 );
			} else {
				self::$hide_registration_notice = true;
				setcookie( 'tutor_notice_hide_registration', 1, time() + MONTH_IN_SECONDS, tutor()->basepath );
			}
		}
	}

	/**
	 * Show registration disabled
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function show_registration_disabled() {
		if ( self::$hide_registration_notice ||
				( '0' !== get_option( 'users_can_register' ) ) ||
				isset( $_COOKIE['tutor_notice_hide_registration'] ) ||
				! current_user_can( 'manage_options' )
			) {
			return;
		}

		$hide_url = wp_nonce_url( add_query_arg( 'tutor-hide-notice', 'registration' ), tutor()->nonce_action, tutor()->nonce );
		?>
		<div class="wrap tutor-user-registration-notice-wrapper">
			<div class="tutor-user-registration-notice">
				<div>
					<img src="<?php echo esc_url( tutor()->url . 'assets/images/icon-info-round.svg' ); ?>"/>
				</div>
				<div>
					<?php echo wp_kses( 'As membership is turned off, students and instructors will not be able to sign up. <strong>Press Enable</strong> or go to <strong>Settings > General > Membership</strong> and enable "Anyone can register".', array( 'strong' => true ) ); ?>
				</div>
				<div>
					<a href="<?php echo esc_url( add_query_arg( 'tutor-registration', 'enable', $hide_url ) ); ?>"><?php esc_html_e( 'Enable', 'tutor' ); ?></a>
					<hr/>
					<a href="<?php echo esc_url( $hide_url ); ?>">
						<?php esc_html_e( 'Dismiss', 'tutor' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Set the user last active timestamp to now.
	 *
	 * @since 2.5.0
	 *
	 * @param string   $user_login active user name.
	 * @param \WP_User $user User object data.
	 *
	 * @return void
	 */
	public function update_user_last_login( $user_login, $user ) {
		update_user_meta( $user->ID, self::LAST_LOGIN_META, time() );
	}

	/**
	 * Add timezone input field to login form.
	 *
	 * @since 3.0.0
	 */
	public function add_timezone_input() {
		?>
		<input type="hidden" name="timezone" value="<?php echo esc_attr( wp_timezone_string() ); ?>" />
		<script>
			document.addEventListener('DOMContentLoaded', function() {
				const timezone = document.querySelector('input[name="timezone"]');
				if ( timezone) {
					const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
					timezone.value = tz
				}
			});
		</script>
		<?php
	}

	/**
	 * Set user timezone if not it set.
	 *
	 * @since 3.0.0
	 *
	 * @param string   $user_login active user name.
	 * @param \WP_User $user User object data.
	 *
	 * @return void
	 */
	public function set_timezone( $user_login, $user ) {
		if ( Input::has( 'timezone' ) ) {
			$timezone = get_user_meta( $user->ID, self::TIMEZONE_META, true );
			if ( empty( $timezone ) ) {
				update_user_meta( $user->ID, self::TIMEZONE_META, Input::post( 'timezone', wp_timezone_string() ) );
			}
		}
	}

	/**
	 * Get user list with pagination & support
	 * search term
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function ajax_user_list() {
		tutor_utils()->check_nonce();

		$can_access = apply_filters( 'tutor_user_list_access', current_user_can( 'manage_options' ) );
		if ( ! $can_access ) {
			$this->json_response( tutor_utils()->error_message( 'forbidden' ), HttpHelper::STATUS_FORBIDDEN );
		}

		$response = array(
			'results'     => array(),
			'total_items' => 0,
		);

		$limit  = Input::post( 'limit', 10, Input::TYPE_INT );
		$offset = Input::post( 'offset', 0, Input::TYPE_INT );

		$args = array(
			'limit'  => $limit,
			'offset' => $offset,
		);

		$filter = json_decode( wp_unslash( $_POST['filter'] ?? '{}' ) );//phpcs:ignore
		if ( ! empty( $filter ) && property_exists( $filter, 'search' ) && ! empty( $filter->search ) ) {
			$args['search']         = '*' . Input::sanitize( $filter->search ) . '*';
			$args['search_columns'] = array( 'user_login', 'user_email', 'user_nicename', 'display_name', 'ID' );
		}

		$user_list = $this->model->get_users_list( $args );

		if ( is_object( $user_list ) ) {
			foreach ( $user_list->get_results() as $user ) {
				// Set user avatar.
				$user->avatar_url      = get_avatar_url( $user->ID, array( 'size' => 32 ) );
				$response['results'][] = $user;
			}

			$response['total_items'] = $user_list->get_total();
		}

		$this->json_response(
			__( 'User list fetched successfully!', 'tutor' ),
			$response
		);
	}
}
