<?php

namespace TUTOR;


if ( ! defined( 'ABSPATH' ) )
	exit;

class Upgrader {

	public function __construct() {
		add_action('admin_init', array($this, 'init_upgrader'));
	}

	public function init_upgrader(){
		$upgrades = $this->available_upgrades();

		if (tutor_utils()->count($upgrades)){
			foreach ($upgrades as $upgrade){
				$this->{$upgrade}();
			}
		}
	}

	public function available_upgrades(){
		$version = get_option('tutor_version');

		$upgrades = array();
		if ($version){
			$upgrades[] = 'upgrade_to_1_3_1';
		}

		return $upgrades;
	}

	/**
	 * Upgrade to version 1.3.1
	 */
	public function upgrade_to_1_3_1(){
		if (version_compare(get_option('tutor_version'), '1.3.1', '<')) {
			global $wpdb;

			if ( ! get_option('is_course_post_type_updated')){
				$wpdb->update($wpdb->posts, array('post_type' => 'courses'), array('post_type' => 'course'));
				update_option('is_course_post_type_updated', true);
				update_option('tutor_version', '1.3.1');
				flush_rewrite_rules();
			}

		}
	}


}