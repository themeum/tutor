<?php

namespace DOZENT;


class Tools {

	public function __construct() {
		add_action('dozent_once_in_day_run_schedule', array($this, 'delete_auto_draft_posts'));
	}

	/**
	 * Delete draft question schedule basis
	 */
	public function delete_auto_draft_posts() {
		global $wpdb;

		$draft_questions_ids = $wpdb->get_col("SELECT ID from {$wpdb->posts} WHERE post_type = 'dozent_question' AND post_status = 'auto-draft' ");
		if (is_array($draft_questions_ids) && count($draft_questions_ids)){
			foreach ($draft_questions_ids as $draft_questions_id){
				wp_delete_post($draft_questions_id, true);
			}
		}
	}

}