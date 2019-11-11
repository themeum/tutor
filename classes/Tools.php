<?php

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) )
	exit;


class Tools {

	public function __construct() {
		//add_action('tutor_once_in_day_run_schedule', array($this, 'delete_auto_draft_posts'));
		add_action('tutor_action_regenerate_tutor_pages', array($this, 'regenerate_tutor_pages'));


		add_action('tutor_option_save_after', array($this, 'tutor_option_save_after'));
		add_action('init', array($this, 'check_if_maintenance'));

	}

	/**
	 * Re-Generate Tutor Missing Pages
	 * @since v.1.4.3
	 */
	public function regenerate_tutor_pages(){
		tutils()->checking_nonce();

		$tutor_pages = tutils()->tutor_pages();
		
		foreach ($tutor_pages as $page){
			$visible = tutils()->array_get('page_visible', $page);
			$page_title = tutils()->array_get('page_name', $page);
			$option_key = tutils()->array_get('option_key', $page);

			if ( ! $visible){
				$page_arg = array(
					'post_title'    => $page_title,
					'post_content'  => '',
					'post_type'     => 'page',
					'post_status'   => 'publish',
				);
				$page_id = wp_insert_post( $page_arg );
				update_tutor_option($option_key, $page_id);
			}
		}
	}


	/**
	 * Enable Maintenance Mode
	 */

	public function tutor_option_save_after(){
		$maintenance_mode = (bool) get_tutor_option('enable_tutor_maintenance_mode');
		if ($maintenance_mode){
			tutor_maintenance_mode(true);
		}else{
			tutor_maintenance_mode();
		}
	}

	public function check_if_maintenance(){
		if ( ! is_admin()) {
			$maintenance_mode = (bool) get_tutor_option( 'enable_tutor_maintenance_mode' );
			if ( ! $maintenance_mode){
				return;
			}

			header( 'Retry-After: 600' );
			include tutor()->path.'views/maintenance.php';
			die();
		}
	}



}