<?php
/**
 * Manage Course List
 *
 * @package Tutor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

namespace TUTOR;

use Tutor\Models\CourseModel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Course List class
 *
 * @since 2.0.0
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
	 * Constructor
	 *
	 * @return void
	 * @since 2.0.0
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
	 * @since 2.0.0
	 */
	public function prepare_bulk_actions(): array {
		$actions = array(
			$this->bulk_action_default(),
			$this->bulk_action_publish(),
			$this->bulk_action_pending(),
			$this->bulk_action_draft(),
		);

		$active_tab = Input::get( 'data', '' );

		if ( 'trash' === $active_tab ) {
			array_push( $actions, $this->bulk_action_delete() );
		}
		if ( 'trash' !== $active_tab ) {
			array_push( $actions, $this->bulk_action_trash() );
		}
		return apply_filters( 'tutor_course_bulk_actions', $actions );
	}

	/**
	 * Available tabs that will visible on the right side of page navbar
	 *
	 * @param string  $category_slug category slug.
	 * @param integer $course_id course ID.
	 * @param string  $date selected date | optional.
	 * @param string  $search search by user name or email | optional.
	 *
	 * @return array
	 *
	 * @since v2.0.0
	 */
	public function tabs_key_value( $category_slug, $course_id, $date, $search ): array {
		$url = get_pagenum_link();

		$all       = self::count_course( 'all', $category_slug, $course_id, $date, $search );
		$mine      = self::count_course( 'mine', $category_slug, $course_id, $date, $search );
		$published = self::count_course( 'publish', $category_slug, $course_id, $date, $search );
		$draft     = self::count_course( 'draft', $category_slug, $course_id, $date, $search );
		$pending   = self::count_course( 'pending', $category_slug, $course_id, $date, $search );
		$trash     = self::count_course( 'trash', $category_slug, $course_id, $date, $search );

		$tabs = array(
			array(
				'key'   => 'all',
				'title' => __( 'All', 'tutor' ),
				'value' => $all,
				'url'   => $url . '&data=all',
			),
			array(
				'key'   => 'mine',
				'title' => __( 'Mine', 'tutor' ),
				'value' => $mine,
				'url'   => $url . '&data=mine',
			),
			array(
				'key'   => 'published',
				'title' => __( 'Published', 'tutor' ),
				'value' => $published,
				'url'   => $url . '&data=published',
			),
			array(
				'key'   => 'draft',
				'title' => __( 'Draft', 'tutor' ),
				'value' => $draft,
				'url'   => $url . '&data=draft',
			),
			array(
				'key'   => 'pending',
				'title' => __( 'Pending', 'tutor' ),
				'value' => $pending,
				'url'   => $url . '&data=pending',
			),
			array(
				'key'   => 'trash',
				'title' => __( 'Trash', 'tutor' ),
				'value' => $trash,
				'url'   => $url . '&data=trash',
			),
		);
		return apply_filters( 'tutor_course_tabs', $tabs );
	}

	/**
	 * Count courses by status & filters
	 * Count all | min | published | pending | draft
	 *
	 * @param string $status | required.
	 * @param string $category_slug course category | optional.
	 * @param string $course_id selected course id | optional.
	 * @param string $date selected date | optional.
	 * @param string $search_term search by user name or email | optional.
	 *
	 * @return int
	 *
	 * @since 2.0.0
	 */
	protected static function count_course( string $status, $category_slug = '', $course_id = '', $date = '', $search_term = '' ): int {
		$user_id       = get_current_user_id();
		$status        = sanitize_text_field( $status );
		$course_id     = sanitize_text_field( $course_id );
		$date          = sanitize_text_field( $date );
		$search_term   = sanitize_text_field( $search_term );
		$category_slug = sanitize_text_field( $category_slug );

		$args = array(
			'post_type' => tutor()->course_post_type,
		);

		if ( 'all' === $status || 'mine' === $status ) {
			$args['post_status'] = array( 'publish', 'pending', 'draft', 'private' );
		} else {
			$args['post_status'] = array( $status );
		}

		// Author query.
		if ( 'mine' === $status || ! current_user_can( 'administrator' ) ) {
			$args['author'] = $user_id;
		}

		$date_filter = sanitize_text_field( $date );

		$year  = date( 'Y', strtotime( $date_filter ) );
		$month = date( 'm', strtotime( $date_filter ) );
		$day   = date( 'd', strtotime( $date_filter ) );

		// Add date query.
		if ( '' !== $date_filter ) {
			$args['date_query'] = array(
				array(
					'year'  => $year,
					'month' => $month,
					'day'   => $day,
				),
			);
		}

		if ( '' !== $course_id ) {
			$args['p'] = $course_id;
		}

		// Search filter.
		if ( '' !== $search_term ) {
			$args['s'] = $search_term;
		}

		// Category filter.
		if ( '' !== $category_slug ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'course-category',
					'field'    => 'slug',
					'terms'    => $category_slug,
				),
			);
		}

		$the_query = new \WP_Query( $args );

		return ! is_null( $the_query ) && isset( $the_query->found_posts ) ? $the_query->found_posts : $the_query;

	}

	/**
	 * Handle bulk action for enrollment cancel | delete
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public function course_list_bulk_action() {

		tutor_utils()->checking_nonce();

		// Check if user is privileged.
		if ( ! current_user_can( 'administrator' ) || ! current_user_can( tutor()->instructor_role ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$action   = Input::post( 'bulk-action', '' );
		$bulk_ids = Input::post( 'bulk-ids', '' );

		if ( '' === $action || '' === $bulk_ids ) {
			wp_send_json_error( array( 'message' => __( 'Please select appropriate action', 'tutor' ) ) );
			exit;
		}

		if ( 'delete' === $action ) {
			// Do action before delete.
			do_action( 'before_tutor_course_bulk_action_delete', $bulk_ids );

			$delete_courses = self::bulk_delete_course( $bulk_ids );

			do_action( 'after_tutor_course_bulk_action_delete', $bulk_ids );
			$delete_courses ? wp_send_json_success() : wp_send_json_error( array( 'message' => __( 'Could not delete selected courses', 'tutor' ) ) );
			exit;
		}

		/**
		 * Do action before course update
		 *
		 * @param string $action (publish | pending | draft | trash).
		 * @param array $bulk_ids, course id.
		 */
		do_action( 'before_tutor_course_bulk_action_update', $action, $bulk_ids );

		$update_status = self::update_course_status( $action, $bulk_ids );

		do_action( 'after_tutor_course_bulk_action_update', $action, $bulk_ids );

		$update_status ? wp_send_json_success() : wp_send_json_error(
			array(
				'message' => 'Could not update course status',
				'tutor',
			)
		);

		exit;
	}

	/**
	 * Handle ajax request for updating course status
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public static function tutor_change_course_status() {
		tutor_utils()->checking_nonce();

		// Check if user is privileged.
		if ( ! current_user_can( 'administrator' ) || ! current_user_can( tutor()->instructor_role ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$status = Input::post( 'status' );
		$id     = Input::post( 'id' );

		$args = array(
			'ID'          => $id,
			'post_status' => $status,
		);
		wp_update_post( $args );

		wp_send_json_success();
		exit;
	}

	/**
	 * Handle ajax request for deleting course
	 *
	 * @return json response
	 * @since 2.0.0
	 */
	public static function tutor_course_delete() {
		tutor_utils()->checking_nonce();

		// Check if user is privileged.
		if ( ! current_user_can( 'administrator' ) || ! current_user_can( tutor()->instructor_role ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$id     = Input::post( 'id', 0, Input::TYPE_INT );
		$delete = CourseModel::delete_course( $id );

		return wp_send_json( $delete );
		exit;
	}

	/**
	 * Execute bulk delete action
	 *
	 * @param string $bulk_ids ids that need to update.
	 * @return bool
	 * @since 2.0.0
	 */
	public static function bulk_delete_course( $bulk_ids ): bool {
		$bulk_ids = explode( ',', sanitize_text_field( $bulk_ids ) );

		foreach ( $bulk_ids as $post_id ) {
			CourseModel::delete_course( $post_id );
		}

		return true;
	}

	/**
	 * Update course status
	 *
	 * @param string $status for updating course status.
	 * @param string $bulk_ids comma separated ids.
	 *
	 * @return bool
	 *
	 * @since 2.0.0
	 */
	public static function update_course_status( string $status, $bulk_ids ): bool {
		global $wpdb;
		$post_table = $wpdb->posts;
		$status     = sanitize_text_field( $status );
		$bulk_ids   = sanitize_text_field( $bulk_ids );

		$update = $wpdb->query(
			$wpdb->prepare(
				"UPDATE {$post_table} SET post_status = %s WHERE ID IN ($bulk_ids)", //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$status
			)
		);

		return true;
	}

	/**
	 * Get course enrollment list with student info
	 *
	 * @param  int $course_id int | required.
	 * @return array
	 * @since 2.0.0
	 */
	public static function course_enrollments_with_student_details( int $course_id ) {
		global $wpdb;
		$course_id         = sanitize_text_field( $course_id );
		$course_completed  = 0;
		$course_inprogress = 0;

		$enrollments = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT enroll.ID AS enroll_id, enroll.post_author AS enroll_author, user.*, course.ID AS course_id
                FROM {$wpdb->posts} AS enroll
                LEFT JOIN {$wpdb->users} AS user ON user.ID = enroll.post_author
                LEFT JOIN {$wpdb->posts} AS course ON course.ID = enroll.post_parent
                WHERE enroll.post_type = %s
                    AND enroll.post_status = %s
                    AND enroll.post_parent = %d
			",
				'tutor_enrolled',
				'completed',
				$course_id
			)
		);

		foreach ( $enrollments as $enrollment ) {
			$course_progress = tutor_utils()->get_course_completed_percent( $course_id, $enrollment->enroll_author );
			if ( 100 == $course_progress ) {
				$course_completed++;
			} else {
				$course_inprogress++;
			}
		}

		return array(
			'enrollments'       => $enrollments,
			'total_completed'   => $course_completed,
			'total_inprogress'  => $course_inprogress,
			'total_enrollments' => count( $enrollments ),
		);
	}

	/**
	 * Check wheather course is public or not
	 *
	 * @param integer $course_id  course id to check with.
	 * @return boolean  true if public otherwise false.
	 * @since 1.0.0
	 */
	public static function is_public( int $course_id ): bool {
		$is_public = get_post_meta( $course_id, '_tutor_is_public_course', true );
		return 'yes' === $is_public ? true : false;
	}
}
