<?php

namespace TUTOR;


if ( ! defined( 'ABSPATH' ) )
	exit;

class Upgrader {

	public function __construct() {
		add_action('admin_init', array($this, 'init_upgrader'));

		add_action( 'in_plugin_update_message-tutor/tutor.php', array( $this, 'in_plugin_update_message' ), 10, 2 );
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


	public function in_plugin_update_message( $args, $response ){
		$upgrade_notice = strip_tags(tutils()->array_get('upgrade_notice', $response));
		if ($upgrade_notice){

			$upgrade_notice = "<span class='version'><code>v.{$response->new_version}</code></span> <br />".$upgrade_notice;

			echo apply_filters( 'tutor_in_plugin_update_message', $upgrade_notice ? '</p> <div class="tutor_plugin_update_notice">' .$upgrade_notice. '</div> <p class="dummy">' : '' );
		}

	}




}