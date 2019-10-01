<?php

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) )
	exit;


class Tools {

	public function __construct() {
		add_action('tutor_once_in_day_run_schedule', array($this, 'delete_auto_draft_posts'));

		add_action('tutor_action_regenerate_tutor_pages', array($this, 'regenerate_tutor_pages'));
	}

	/**
	 * Delete draft question schedule basis
	 */
	public function delete_auto_draft_posts() {
		global $wpdb;

		$draft_questions_ids = $wpdb->get_col("SELECT ID from {$wpdb->posts} WHERE post_type = 'tutor_question' AND post_status = 'auto-draft' ");
		if (is_array($draft_questions_ids) && count($draft_questions_ids)){
			foreach ($draft_questions_ids as $draft_questions_id){
				wp_delete_post($draft_questions_id, true);
			}
		}
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

}