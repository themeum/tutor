<?php
/**
 * Manage Instructor List
 *
 * @package Tutor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use TUTOR\Students_List;
use TUTOR\Backend_Page_Trait;

/**
 * Instructors_List class
 *
 * @since 1.0.0
 */
class Instructors_List {

	const INSTRUCTOR_LIST_PAGE = 'tutor-instructors';

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
	 * Constructor
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct() {
		$this->page_title = __( 'Instructor', 'tutor' );

		/**
		 * Handle bulk action
		 *
		 * @since 2.0.0
		 */
		add_action( 'wp_ajax_tutor_instructor_bulk_action', array( $this, 'instructor_bulk_action' ) );
	}

	/**
	 * Available tabs that will visible on the right side of page navbar
	 *
	 * @since 2.0.0
	 *
	 * @param string $search instructor search | optional.
	 * @param string $course_id course id that belong to instructor | optional.
	 * @param string $date user registered date | optional.
	 *
	 * @return array
	 */
	public function tabs_key_value( $search = '', $course_id = '', $date = '' ): array {
		$url     = get_pagenum_link();
		$approve = tutor_utils()->get_total_instructors( $search, array( 'approved' ), $course_id, $date );
		$pending = tutor_utils()->get_total_instructors( $search, array( 'pending' ), $course_id, $date );
		$blocked = tutor_utils()->get_total_instructors( $search, array( 'blocked' ), $course_id, $date );
		$tabs    = array(
			array(
				'key'   => 'all',
				'title' => __( 'All', 'tutor' ),
				'value' => $approve + $pending + $blocked,
				'url'   => $url . '&data=all',
			),
			array(
				'key'   => 'approved',
				'title' => __( 'Approve', 'tutor' ),
				'value' => $approve,
				'url'   => $url . '&data=approved',
			),
			array(
				'key'   => 'pending',
				'title' => __( 'Pending', 'tutor' ),
				'value' => $pending,
				'url'   => $url . '&data=pending',
			),
			array(
				'key'   => 'blocked',
				'title' => __( 'Block', 'tutor' ),
				'value' => $blocked,
				'url'   => $url . '&data=blocked',
			),
		);
		return $tabs;
	}

	/**
	 * Prepare bulk actions that will show on dropdown options
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function prpare_bulk_actions(): array {
		$actions = array(
			$this->bulk_action_default(),
			$this->bulk_action_approved(),
			$this->bulk_action_pending(),
			$this->bulk_action_blocked(),
		);
		return $actions;
	}

	/**
	 * Handle bulk action for instructor delete
	 *
	 * @since 2.0.0
	 * @return string JSON response.
	 */
	public function instructor_bulk_action() {
		tutor_utils()->checking_nonce();

		$action   = Input::post( 'bulk-action', '' );
		$bulk_ids = Input::post( 'bulk-ids', '' );

		Input::has( 'bulkIds' ) ? $bulk_ids = Input::post( 'bulkIds' ) : 0;

		if ( '' === $action || '' === $bulk_ids ) {
			return wp_send_json_error();
		}
		if ( 'delete' === $action ) {
			// Delete user from student_list class.
			do_action( 'tutor_before_instructor_delete', $bulk_ids );
			$response = Students_List::delete_students( $bulk_ids );
			do_action( 'tutor_after_instructor_delete', $bulk_ids );
		} else {
			do_action( 'tutor_before_instructor_update', $bulk_ids );
			$response = self::update_instructors( $action, $bulk_ids );
			do_action( 'tutor_after_instructor_delete', $bulk_ids );
		}

		$message = 'Instructor status updated';

		return true === $response ? wp_send_json_success( array( 'status' => $message ) ) : wp_send_json_error();
	}

	/**
	 * Execute bulk action for enrollment list ex: complete | cancel
	 *
	 * @since 2.0.0
	 *
	 * @param string $status hold status for updating.
	 * @param string $user_ids ids that need to update.
	 *
	 * @return bool
	 */
	public static function update_instructors( $status, $user_ids ): bool {
		global $wpdb;
		$status           = sanitize_text_field( $status );
		$instructor_table = $wpdb->usermeta;

		//phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$update = $wpdb->query(
			$wpdb->prepare(
				"UPDATE {$instructor_table} SET meta_value = %s 
				WHERE user_id IN ($user_ids) 
				AND meta_key = %s",
				$status,
				'_tutor_instructor_status'
			)
		);
		//phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		// Remove role.
		if ( 'pending' === $status || 'blocked' === $status ) {
			$arr = explode( ',', $user_ids );
			foreach ( $arr as $instructor_id ) {
				$instructor_id = (int) sanitize_text_field( $instructor_id );
				self::remove_instructor_role( $instructor_id, $status );
			}
		}

		if ( 'approved' === $status ) {
			$arr = explode( ',', $user_ids );
			foreach ( $arr as $instructor_id ) {
				$instructor_id = (int) sanitize_text_field( $instructor_id );
				self::add_instructor_role( $instructor_id, $status );
			}
		}
		return false === $update ? false : true;
	}

	/**
	 * Get total course.
	 *
	 * @since 1.0.0
	 *
	 * @param object $item item.
	 * @return void
	 */
	public function column_total_course( $item ) {
		global $wpdb;
		$course_post_type = tutor()->course_post_type;

		$total_course = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT count(ID) from {$wpdb->posts}
			WHERE post_author=%d AND post_type=%s ",
				$item->ID,
				$course_post_type
			)
		);

		echo esc_html( $total_course );
	}

	/**
	 * Initialize instructor_role to a user
	 *
	 * @since 1.0.0
	 *
	 * @param integer $instructor_id | user id that need to add role.
	 * @param string  $status | status that will added with role (approved).
	 *
	 * @return void
	 */
	protected static function add_instructor_role( int $instructor_id, string $status ) {
		$instructor_id = sanitize_text_field( $instructor_id );
		$status        = sanitize_text_field( $status );

		do_action( 'tutor_before_approved_instructor', $instructor_id );

		update_user_meta( $instructor_id, '_tutor_instructor_status', $status );
		update_user_meta( $instructor_id, '_tutor_instructor_approved', tutor_time() );

		$instructor = new \WP_User( $instructor_id );
		$instructor->add_role( tutor()->instructor_role );

		// TODO: send E-Mail to this user about instructor approval, should via hook.
		do_action( 'tutor_after_approved_instructor', $instructor_id );
	}

	/**
	 * Initialize instructor_role to a user
	 *
	 * @since 1.0.0
	 *
	 * @param int    $instructor_id | user id that need to add role.
	 * @param string $status | status that will added with role (approved).
	 *
	 * @return void
	 */
	protected static function remove_instructor_role( int $instructor_id, string $status ) {
		$instructor_id = sanitize_text_field( $instructor_id );
		$status        = sanitize_text_field( $status );

		do_action( 'tutor_before_blocked_instructor', $instructor_id );
		update_user_meta( $instructor_id, '_tutor_instructor_status', $status );

		$instructor = new \WP_User( $instructor_id );
		$instructor->remove_role( tutor()->instructor_role );
		do_action( 'tutor_after_blocked_instructor', $instructor_id );
	}

}
