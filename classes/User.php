<?php

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) )
	exit;


class User {

	public function __construct() {
		add_action('edit_user_profile', array($this, 'edit_user_profile'));
		add_action('show_user_profile', array($this, 'edit_user_profile'), 10, 1);

		add_action('profile_update', array($this, 'profile_update'));
		add_action('set_user_role', array($this, 'set_user_role'), 10, 3);
		add_action('wp_ajax_tutor_profile_photo_remove', array($this, 'tutor_profile_photo_remove'));
	}

	public function edit_user_profile($user){
		include  tutor()->path.'views/metabox/user-profile-fields.php';
	}

	public function profile_update($user_id){
		$_tutor_profile_job_title = sanitize_text_field(tutor_utils()->avalue_dot('_tutor_profile_job_title', $_POST));
		$_tutor_profile_bio = wp_kses_post(tutor_utils()->avalue_dot('_tutor_profile_bio', $_POST));
		$_tutor_profile_photo_field = sanitize_text_field(tutor_utils()->avalue_dot('_tutor_profile_photo_field', $_POST));

		update_user_meta($user_id, '_tutor_profile_job_title', $_tutor_profile_job_title);
		update_user_meta($user_id, '_tutor_profile_bio', $_tutor_profile_bio);

		/**
		 * Profile Photo Update from profile
		 *
		 */
		$profile_photo = tutils()->array_get('tutor_profile_photo_file', $_FILES);
		$profile_photo_size = tutils()->array_get('size', $profile_photo);
		$profile_photo_type = tutils()->array_get('type', $profile_photo);

		if ($profile_photo_size && strpos($profile_photo_type, 'image') !== false) {
			if ( ! function_exists( 'wp_handle_upload' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
			}

			$upload_overrides = array( 'test_form' => false );
			$movefile         = wp_handle_upload( $profile_photo, $upload_overrides );

			if ( $movefile && ! isset( $movefile['error'] ) ) {
				$file_path = tutils()->array_get( 'file', $movefile );
				$file_url  = tutils()->array_get( 'url', $movefile );

				$media_id = wp_insert_attachment( array(
					'guid'           => $file_path,
					'post_mime_type' => mime_content_type( $file_path ),
					'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_url ) ),
					'post_content'   => '',
					'post_status'    => 'inherit'
				), $file_path, 0 );

				if ($media_id) {
					// wp_generate_attachment_metadata() won't work if you do not include this file
					require_once( ABSPATH . 'wp-admin/includes/image.php' );

					// Generate and save the attachment metas into the database
					wp_update_attachment_metadata( $media_id, wp_generate_attachment_metadata( $media_id, $file_path ) );

					//Update it to user profile
					update_user_meta( $user_id, '_tutor_profile_photo', $media_id );
				}
			}
		}elseif ($_tutor_profile_photo_field){
			update_user_meta( $user_id, '_tutor_profile_photo', $_tutor_profile_photo_field );
		}

	}

	public function set_user_role($user_id, $role, $old_roles ){
		$instructor_role = tutor()->instructor_role;

		if (in_array($instructor_role, $old_roles)){
			tutor_utils()->remove_instructor_role($user_id);
		}

		if ($role === $instructor_role){
			tutor_utils()->add_instructor_role($user_id);
		}
	}



	/**
	 *
	 * Delete profile photo
	 * @since v.1.4.5
	 */
	public function tutor_profile_photo_remove(){
		$user_id = get_current_user_id();
		delete_user_meta($user_id, '_tutor_profile_photo');
	}

}