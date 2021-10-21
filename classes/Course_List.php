<?php
/**
 * Course List class
 *
 * @package Course List
 * @since v2.0.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Announcements class for handling logics
 */
class Course_List {
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
		$this->page_title = __( 'Courses', 'tutor' );
		/**
		 * Handle bulk action
		 *
		 * @since v2.0.0
		 */
		add_action( 'wp_ajax_tutor_course_list_bulk_action', array( $this, 'course_list_bulk_action' ) );
		/**
		 * Handle ajax request for updating course status
		 *
		 * @since v2.0.0
		 */
		add_action( 'wp_ajax_tutor_change_course_status', array( __CLASS__, 'tutor_change_course_status' ) );
		/**
		 * Handle ajax request for delete course
		 *
		 * @since v2.0.0
		 */
		add_action( 'wp_ajax_tutor_course_delete', array( __CLASS__, 'tutor_course_delete' ) );
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
			$this->bulk_action_publish(),
			$this->bulk_action_pending(),
			$this->bulk_action_draft(),
			$this->bulk_action_delete(),
		);
		return $actions;
	}

	/**
	 * Available tabs that will visible on the right side of page navbar
	 *
	 * @param string $course_id selected course id | optional.
	 * @param string $date selected date | optional.
	 * @param string $search search by user name or email | optional.
	 * @return array
	 * @since v2.0.0
	 */
	public function tabs_key_value( $course_id, $date, $search ): array {
		$url = get_pagenum_link();

		$all       = self::count_course( 'all', $course_id, $date, $search );
		$mine      = self::count_course( 'mine', $course_id, $date, $search );
		$published = self::count_course( 'publish', $course_id, $date, $search );
		$draft     = self::count_course( 'draft', $course_id, $date, $search );
		$pending   = self::count_course( 'pending', $course_id, $date, $search );

		$tabs = array(
			array(
				'key'   => 'all',
				'title' => __( 'All', 'tutor-pro' ),
				'value' => $all,
				'url'   => $url . '&data=all',
			),
			array(
				'key'   => 'mine',
				'title' => __( 'Mine', 'tutor-pro' ),
				'value' => $mine,
				'url'   => $url . '&data=mine',
			),
			array(
				'key'   => 'published',
				'title' => __( 'Published', 'tutor-pro' ),
				'value' => $published,
				'url'   => $url . '&data=published',
			),
			array(
				'key'   => 'draft',
				'title' => __( 'Draft', 'tutor-pro' ),
				'value' => $draft,
				'url'   => $url . '&data=draft',
			),
			array(
				'key'   => 'pending',
				'title' => __( 'Pending', 'tutor-pro' ),
				'value' => $pending,
				'url'   => $url . '&data=pending',
			),
		);
		return $tabs;
	}

	/**
	 * Count courses by status & filters
	 * Count all | min | published | pending | draft
	 *
	 * @param string $status | required.
	 * @param string $course_id selected course id | optional.
	 * @param string $date selected date | optional.
	 * @param string $search_term search by user name or email | optional.
	 * @return int
	 * @since v2.0.0
	 */
	protected static function count_course( string $status, $course_id = '', $date = '', $search_term = '' ): int {
		global $wpdb;
		$user_id     = get_current_user_id();
		$status      = sanitize_text_field( $status );
		$course_id   = sanitize_text_field( $course_id );
		$date        = sanitize_text_field( $date );
		$search_term = sanitize_text_field( $search_term );

		$search_term = '%' . $wpdb->esc_like( $search_term ) . '%';

		// add course id in where clause.
		$course_query = '';
		if ( '' !== $course_id ) {
			$course_query = "AND course.ID = $course_id";
		}

		// add date in where clause.
		$date_query = '';
		if ( '' !== $date ) {
			$date_query = "AND DATE(course.post_date) = CAST('$date' AS DATE) ";
		}

		// status query.
		$status_query = '';
		if ( 'all' === $status ) {
			$status_query = "AND course.post_status IN ('publish', 'pending', 'draft')";
		} elseif ( 'mine' === $status ) {
			$status_query = "AND course.post_author = {$user_id} AND course.post_status IN ('publish', 'pending', 'draft')";
		} else {
			// if $status == published | pending | draft then user $status var.
			$status_query = "AND course.post_status = '$status'";
		}
		$post_table = $wpdb->posts;
		$count      = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
					FROM 	{$post_table} AS course
					WHERE 	course.post_type = %s
                            {$status_query}
							{$date_query}
							{$course_query}
							AND ( course.post_title LIKE %s )
					",
				tutor()->course_post_type,
				$search_term
			)
		);
		return $count ? $count : 0;
	}

	/**
	 * Handle bulk action for enrollment cancel | delete
	 *
	 * @return string JSON response.
	 * @since v2.0.0
	 */
	public function course_list_bulk_action() {
		// check nonce.
		tutor_utils()->checking_nonce();
		$action   = isset( $_POST['bulk-action'] ) ? sanitize_text_field( $_POST['bulk-action'] ) : '';
		$bulk_ids = isset( $_POST['bulk-ids'] ) ? sanitize_text_field( $_POST['bulk-ids'] ) : '';

		if ( '' === $action || '' === $bulk_ids ) {
			return wp_send_json_error();
		} elseif ( 'delete' === $action ) {
			$delete_courses = self::delete_course( $bulk_ids );
			return $delete_courses ? wp_send_json_success() : wp_send_json_error();
		} else {
			$update_status = self::update_course_status( $action, $bulk_ids );
			return $update_status ? wp_send_json_success() : wp_send_json_error();
		}
		exit;
	}

	/**
	 * Handle ajax request for updating course status
	 *
	 * @return json response
	 * @since v2.0.0
	 */
	public static function tutor_change_course_status() {
		tutor_utils()->checking_nonce();
		$status = sanitize_text_field( $_POST['status'] );
		$id     = sanitize_text_field( $_POST['id'] );
		$update = self::update_course_status( $status, $id );
		//return $update ? wp_send_json_success( $update ) : wp_send_json_error();
		return wp_send_json( $update );
		exit;
	}

	/**
	 * Handle ajax request for deleting course
	 *
	 * @return json response
	 * @since v2.0.0
	 */
	public static function tutor_course_delete() {
		tutor_utils()->checking_nonce();
		$id     = sanitize_text_field( $_POST['id'] );
		$delete = self::delete_course( $id );
		return wp_send_json( $delete );
		exit;
	}

	/**
	 * Execute bulk action for enrollments ex: complete | cancel
	 *
	 * @param string $bulk_ids ids that need to update.
	 * @return bool
	 * @since v2.0.0
	 */
	public static function delete_course( $bulk_ids ): bool {
		global $wpdb;
		$post_table = $wpdb->posts;
		$bulk_ids   = sanitize_text_field( $bulk_ids );
		$delete     = $wpdb->query(
			$wpdb->prepare(
				" DELETE FROM {$post_table}
				WHERE ID IN ($bulk_ids)
			"
			)
		);
		return false === $delete ? false : true;
	}

	/**
	 * Update course status
	 *
	 * @param string $status for updating course status.
	 * @param string $bulk_ids comma separated ids.
	 * @return bool
	 * @since v2.0.0
	 */
	public static function update_course_status( string $status, $bulk_ids ): bool {
		global $wpdb;
		$post_table = $wpdb->posts;
		$status     = sanitize_text_field( $status );
		$bulk_ids   = sanitize_text_field( $bulk_ids );
		$update     = $wpdb->query(
			$wpdb->prepare(
				"UPDATE {$post_table} 
				SET post_status = %s
				WHERE ID IN ($bulk_ids)
			",
				$status
			)
		);
		return $update ? true : false;
	}

}
