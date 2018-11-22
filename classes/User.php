<?php

namespace DOZENT;


class User {

	public function __construct() {
		add_action('edit_user_profile', array($this, 'edit_user_profile'));
		add_action('show_user_profile', array($this, 'edit_user_profile'), 10, 1);

		add_action('profile_update', array($this, 'profile_update'));
	}

	public function edit_user_profile($user){
		include  dozent()->path.'views/metabox/user-profile-fields.php';
	}

	public function profile_update($user_id){
		$_dozent_profile_job_title = sanitize_text_field(dozent_utils()->avalue_dot('_dozent_profile_job_title', $_POST));
		$_dozent_profile_bio = wp_kses_post(dozent_utils()->avalue_dot('_dozent_profile_bio', $_POST));
		$_dozent_profile_photo = sanitize_text_field(dozent_utils()->avalue_dot('_dozent_profile_photo', $_POST));

		update_user_meta($user_id, '_dozent_profile_job_title', $_dozent_profile_job_title);
		update_user_meta($user_id, '_dozent_profile_bio', $_dozent_profile_bio);
		update_user_meta($user_id, '_dozent_profile_photo', $_dozent_profile_photo);
	}

}