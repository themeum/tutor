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
			$this->bulk_action_published(),
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

		$tabs      = array(
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
			)
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
		} else if ( 'mine' === $status ) {
			$status_query = "AND course.post_author = {$user_id} AND course.post_status IN ('publish', 'pending', 'draft')";
		} else {
			// if $status == published | pending | draft then user $status var.
			$status_query = "AND course.post_status = '$status'";
		}

		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
					FROM 	{$wpdb->posts} AS course
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
		$bulk_ids = isset( $_POST['bulk-ids'] ) ? sanitize_text_field( $_POST['bulk-ids'] ) : array();
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
