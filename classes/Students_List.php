<?php
/**
 * Student List page
 *
 * @package Tutor\Student
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use TUTOR\Backend_Page_Trait;

/**
 * Manage student lists
 *
 * @since 1.0.0
 */
class Students_List {

	/**
	 * Page slug
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	const STUDENTS_LIST_PAGE = 'tutor-students';

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
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		$this->page_title = __( 'Students', 'tutor' );
		/**
		 * Handle bulk action
		 *
		 * @since 2.0.0
		 */
		add_action( 'wp_ajax_tutor_student_bulk_action', array( $this, 'student_bulk_action' ) );
	}

	/**
	 * Prepare bulk actions that will show on dropdown options
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function prpare_bulk_actions(): array {
		$actions = array(
			$this->bulk_action_default(),
			$this->bulk_action_delete(),
		);
		return $actions;
	}


	/**
	 * Handle bulk action for student delete
	 *
	 * @since 2.0.0
	 *
	 * @return string wp_json response
	 */
	public function student_bulk_action() {
		// check nonce.
		tutor_utils()->checking_nonce();

		// Check if user is privileged.
		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$action   = Input::post( 'bulk-action', '' );
		$bulk_ids = Input::post( 'bulk-ids', array() );
		if ( 'delete' === $action ) {
			return true === self::delete_students( $bulk_ids ) ? wp_send_json_success() : wp_send_json_error();
		}
		return wp_send_json_error();
	}

	/**
	 * Delete student
	 *
	 * @since v2.0.0
	 *
	 * @param string $student_ids ids that need to delete.
	 * @param int    $reassign_id reassign to other user.
	 *
	 * @return bool
	 */
	public static function delete_students( string $student_ids, $reassign_id = null ): bool {
		$student_ids     = explode( ',', $student_ids );
		$current_user_id = get_current_user_id();

		foreach ( $student_ids as $id ) {
			if ( $id !== $current_user_id ) {
				null === $reassign_id ? wp_delete_user( $id ) : wp_delete_user( $id, $reassign_id );
			}
		}
		return true;
	}
}
