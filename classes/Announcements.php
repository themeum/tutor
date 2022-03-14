<?php
/**
 * Announcement class
 *
 * @package Announcement List
 * @since v2.0.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Announcements class for handling logics
 */
class Announcements {
	/**
	 * Trait for utilities
	 *
	 * @var $page_title
	 */

	use Backend_Page_Trait;
	/**
	 * Page Title
	 *
	 * @var $page_title
	 */
	public $page_title;

	/**
	 * Bulk Action
	 *
	 * @var $bulk_action
	 */
	public $bulk_action = true;

	/**
	 * Handle dependencies
	 */
	public function __construct() {
		$this->page_title = __( 'Announcements', 'tutor' );
		/**
		 * Handle bulk action
		 *
		 * @since v2.0.0
		 */
		add_action( 'wp_ajax_tutor_announcement_bulk_action', array( $this, 'announcement_bulk_action' ) );
	}

	/**
	 * Prepare bulk actions that will show on dropdown options
	 *
	 * @return array
	 * @since v2.0.0
	 */
	public function prepare_bulk_actions(): array {
		$actions = array(
			$this->bulk_action_default(),
			$this->bulk_action_delete(),
		);
		return $actions;
	}

	/**
	 * Handle bulk action for enrollment cancel | delete
	 *
	 * @return string JSON response.
	 * @since v2.0.0
	 */
	public function announcement_bulk_action() {
		// check nonce.
		tutor_utils()->checking_nonce();
		$action   = isset( $_POST['bulk-action'] ) ? sanitize_text_field( $_POST['bulk-action'] ) : '';
		$bulk_ids = isset( $_POST['bulk-ids'] ) ? sanitize_text_field( $_POST['bulk-ids'] ) : '';
		$update   = self::delete_announcements( $action, $bulk_ids );
		return true === $update ? wp_send_json_success() : wp_send_json_error();
		exit;
	}

	/**
	 * Execute bulk action for enrollments ex: complete | cancel
	 *
	 * @param string $action hold action.
	 * @param string $bulk_ids ids that need to update.
	 * @return bool
	 * @since v2.0.0
	 */
	public static function delete_announcements( $action, $bulk_ids ): bool {
		global $wpdb;
		$post_table = $wpdb->posts;
		if ( 'delete' === $action ) {
			$delete = $wpdb->query(
				$wpdb->prepare(
					" DELETE FROM {$post_table}
                    WHERE ID IN ($bulk_ids)
                "
				)
			);
			return false === $delete ? false : true;
		}
		return false;
	}

}
