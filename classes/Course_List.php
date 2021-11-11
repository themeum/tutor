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
		);
		$active_tab = isset( $_GET['data'] ) ? $_GET['data'] : '';
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
		$trash     = self::count_course( 'trash', $course_id, $date, $search );

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
		return $tabs;
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
	 * @return int
	 * @since v2.0.0
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
			$args['post_status'] = array( 'publish', 'pending', 'draft', 'trash' );
		} else {
			$status              = $status === 'published' ? 'publish' : $status;
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

		return ! is_null( $the_query ) && isset( $the_query->found_posts ) ? $the_query->found_posts : 0;

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
		// return $update ? wp_send_json_success( $update ) : wp_send_json_error();
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
	/**
	 * Count quiz for a course
	 *
	 * @param $course_id | required.
	 */
	public static function get_all_quiz_by_course( int $course_id): int {
		global $wpdb;
		$quiz_number = $wpdb->get_var($wpdb->prepare(
			"SELECT COUNT(ID) FROM {$wpdb->posts}
			WHERE post_parent IN (SELECT ID FROM {$wpdb->posts} WHERE post_type ='topics' AND post_parent = %d AND post_status = 'publish')
			AND post_type ='tutor_quiz' 
			AND post_status = 'publish'", $course_id));
		return $quiz_number ? $quiz_number : 0;
	}

    /**
     * Get course enrollments with student info
     * 
     * @param $course_id int | required
     * 
     * @period string | optional ( today | monthly | yearly ) if not provide then it will 
     * 
     * retrieve all records
     * 
     * @param $start_date string | optional 
     * 
     * @param $end_date string | optional
     * 
     * @return array
     * 
     * @since v2.0.0
     */
    public static function course_enrollments_with_student_details( int $course_id ) {
		global $wpdb;
        $course_id          = sanitize_text_field( $course_id );
        $course_completed   = 0;
        $course_inprogress  = 0;

		$enrollments = $wpdb->get_results($wpdb->prepare(
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
		) );

        foreach( $enrollments as $enrollment ) {
            $course_progress = tutor_utils()->get_course_completed_percent( $course_id, $enrollment->enroll_author);
            if ( $course_progress == 100 ) {
                $course_completed++;
            } else {
                $course_inprogress++;
            }
        }

        return array(
            'enrollments'       => $enrollments,
            'total_completed'   => $course_completed,
            'total_inprogress'  => $course_inprogress,
            'total_enrollments' => count( $enrollments )
        );
    } 
}
