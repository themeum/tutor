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
	}

	public function edit_user_profile($user){
		include  tutor()->path.'views/metabox/user-profile-fields.php';
	}

	public function profile_update($user_id){
		$_tutor_profile_job_title = sanitize_text_field(tutor_utils()->avalue_dot('_tutor_profile_job_title', $_POST));
		$_tutor_profile_bio = wp_kses_post(tutor_utils()->avalue_dot('_tutor_profile_bio', $_POST));
		$_tutor_profile_photo = sanitize_text_field(tutor_utils()->avalue_dot('_tutor_profile_photo', $_POST));

		update_user_meta($user_id, '_tutor_profile_job_title', $_tutor_profile_job_title);
		update_user_meta($user_id, '_tutor_profile_bio', $_tutor_profile_bio);
		update_user_meta($user_id, '_tutor_profile_photo', $_tutor_profile_photo);
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

}