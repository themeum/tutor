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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User class
 *
 * @since 1.0.0
 */
class User {

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
	 */
	public function __construct() {
		add_action( 'edit_user_profile', array( $this, 'edit_user_profile' ) );
		add_action( 'show_user_profile', array( $this, 'edit_user_profile' ), 10, 1 );

		add_action( 'profile_update', array( $this, 'profile_update' ) );
		add_action( 'set_user_role', array( $this, 'set_user_role' ), 10, 3 );

		add_action( 'wp_ajax_tutor_user_photo_remove', array( $this, 'tutor_user_photo_remove' ) );
		add_action( 'wp_ajax_tutor_user_photo_upload', array( $this, 'update_user_photo' ) );

		add_action( 'admin_notices', array( $this, 'show_registration_disabled' ) );
		add_action( 'admin_init', array( $this, 'hide_notices' ) );
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
	 * Profile update
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id user id.
	 *
	 * @return void
	 */
	public function profile_update( $user_id ) {
		if ( tutor_utils()->array_get( 'tutor_action', $_POST ) !== 'tutor_profile_update_by_wp' ) {
			return;
		}

		$_tutor_profile_job_title = Input::post( '_tutor_profile_job_title', '' );
		$_tutor_profile_bio       = Input::post( '_tutor_profile_bio', '', Input::TYPE_KSES_POST );
		$_tutor_profile_image     = Input::post( '_tutor_profile_photo', '', Input::TYPE_KSES_POST );

		update_user_meta( $user_id, '_tutor_profile_job_title', $_tutor_profile_job_title );
		update_user_meta( $user_id, '_tutor_profile_bio', $_tutor_profile_bio );
		update_user_meta( $user_id, '_tutor_profile_photo', $_tutor_profile_image );
	}

	/**
	 * Set user role
	 *
	 * @since 1.0.0
	 *
	 * @param int    $user_id user id.
	 * @param string $role user role.
	 * @param string $old_roles old role.
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
		if ( is_admin() && 'registration' === $hide_notice ) {
			tutor_utils()->checking_nonce( 'get' );

			if ( 'enable' === $is_register_enabled ) {
				update_option( 'users_can_register', 1 );
			} else {
				self::$hide_registration_notice = true;
				setcookie( 'tutor_notice_hide_registration', 1, time() + ( 86400 * 30 ), tutor()->basepath );
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
				! tutor_utils()->is_tutor_dashboard() ||
				get_option( 'users_can_register' ) ||
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
}
