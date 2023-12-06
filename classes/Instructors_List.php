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
use Tutor\Cache\TutorCache;
use Tutor\Helpers\QueryHelper;

/**
 * Instructors_List class
 *
 * @since 1.0.0
 */
class Instructors_List {

	const INSTRUCTOR_LIST_PAGE       = 'tutor-instructors';
	const INSTRUCTOR_LIST_CACHE_KEY  = 'tutor-instructors-list';
	const INSTRUCTOR_COUNT_CACHE_KEY = 'tutor-instructors-count';

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
		$approve = self::count_total_instructors( array( 'approved' ), $search, $course_id, $date, 'approved' );
		$pending = self::count_total_instructors( array( 'pending' ), $search, $course_id, $date, 'pending' );
		$blocked = self::count_total_instructors( array( 'blocked' ), $search, $course_id, $date, 'blocked' );

		$tabs = array(
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

		// Check if user is privileged.
		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

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
	 * @param string $user_ids comma seperated user ids.
	 *
	 * @return bool
	 */
	public static function update_instructors( $status, $user_ids ): bool {
		global $wpdb;
		$status           = sanitize_text_field( $status );
		$instructor_table = $wpdb->usermeta;

		$ids       = array_map( 'intval', explode( ',', $user_ids ) );
		$in_clause = QueryHelper::prepare_in_clause( $ids );

		//phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$update = $wpdb->query(
			$wpdb->prepare(
				"UPDATE {$instructor_table} SET meta_value = %s 
				WHERE user_id IN ($in_clause) 
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
				if ( 'pending' === $status ) {
					self::remove_instructor_role( $instructor_id, $status );
				} else {
					self::instructor_blockage( $instructor_id );
				}
			}
		}
		if ( 'reject' === $status ) {
			$arr = explode( ',', $user_ids );
			foreach ( $arr as $instructor_id ) {
				$instructor_id = (int) sanitize_text_field( $instructor_id );
				self::instructor_rejection( $instructor_id );
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
		update_user_meta( $instructor_id, '_tutor_instructor_status', $status );
		$instructor = new \WP_User( $instructor_id );
		$instructor->remove_role( tutor()->instructor_role );
	}
	/**
	 * Instructor blocking function
	 *
	 * @since 2.5.0
	 *
	 * @param int $instructor_id | user id that need to add role.
	 * @return void
	 */
	protected static function instructor_blockage( int $instructor_id ) {
		$instructor_id = sanitize_text_field( $instructor_id );
		do_action( 'tutor_before_blocked_instructor', $instructor_id );
		self::remove_instructor_role( $instructor_id, 'blocked' );
		do_action( 'tutor_after_blocked_instructor', $instructor_id );
	}
	/**
	 * Instructor rejection function
	 *
	 * @since 2.5.0
	 *
	 * @param int $instructor_id | user id that need to add role.
	 * @return void
	 */
	protected static function instructor_rejection( int $instructor_id ) {
		$instructor_id = sanitize_text_field( $instructor_id );
		do_action( 'tutor_before_rejected_instructor', $instructor_id );

		/**
		 * Removed tutor_instructor role and set `try_again` status
		 * for apply again as instructor with show message to applier in frontend.
		 */
		self::remove_instructor_role( $instructor_id, 'try_again' );
		delete_user_meta( $instructor_id, '_is_tutor_instructor' );

		do_action( 'tutor_after_rejected_instructor', $instructor_id );
	}

	/**
	 * Get instructors list
	 *
	 * @since 2.1.7
	 *
	 * @param array  $status instructor status: approved, pending, block.
	 * @param int    $offset offset for pagination.
	 * @param int    $per_page per page limit.
	 * @param string $search search keyword.
	 * @param string $course_id course id.
	 * @param string $date instructor registration date.
	 * @param string $order sorting order.
	 *
	 * @return wpdb::results
	 */
	public static function get_instructors( array $status, $offset, $per_page, $search = '', $course_id = '', $date = '', $order = 'DESC' ) {
		global $wpdb;

		$wild = '%';

		$search_clause = $wild . $wpdb->esc_like( $search ) . $wild;
		$course_clause = '' !== $course_id ? "AND umeta.meta_value = {$course_id}" : '';
		$date_clause   = '' !== $date ? "AND DATE(user.user_registered) = CAST('$date' AS DATE )" : '';
		$order_clause  = '' !== $order ? "ORDER BY user.ID {$order}" : '';
		$in_clause     = QueryHelper::prepare_in_clause( $status );

		$query  = "SELECT
					DISTINCT user.*,
					ins_status.meta_value AS status,
					(
						SELECT
							COUNT(*)
							FROM {$wpdb->posts}
							WHERE post_author = user.ID
								AND post_type = 'courses'
					) total_courses
					FROM {$wpdb->users} AS user
						
					INNER JOIN {$wpdb->usermeta} AS ins_status
						ON ( user.ID = ins_status.user_id )
						AND ins_status.meta_key = '_tutor_instructor_status'
					LEFT JOIN {$wpdb->usermeta} AS umeta
						ON umeta.user_id = user.ID
						AND umeta.meta_key = '_tutor_instructor_course_id'
					WHERE ins_status.meta_value IN ($in_clause)
						AND (user.user_email LIKE %s OR user.display_name LIKE %s)
						{$course_clause}
						{$date_clause}
					{$order_clause}
					LIMIT %d, %d;
				";
		$result = TutorCache::get( self::INSTRUCTOR_LIST_CACHE_KEY );
		if ( false === $result ) {
			TutorCache::set(
				self::INSTRUCTOR_LIST_CACHE_KEY,
				//phpcs:disable
				$result = $wpdb->get_results(
					$wpdb->prepare(
						$query,
						$search_clause,
						$search_clause,
						$offset,
						$per_page
					)
				)
				//phpcs:enable
			);
		}

		return $result;
	}

	/**
	 * Count total instructors
	 *
	 * @since 2.1.7
	 *
	 * @param array  $status instructor status: approved, pending, block.
	 * @param string $search search keyword.
	 * @param string $course_id course id.
	 * @param string $date instructor registration date.
	 * @param string $unique_cache_key unique key will be append with
	 * self::INSTRUCTOR_COUNT_CACHE_KEY so that multiple count value could be
	 * stored as unique data.
	 *
	 * @return int count value of instructors
	 */
	public static function count_total_instructors( array $status, $search = '', $course_id = '', $date = '', $unique_cache_key = '' ) {
		global $wpdb;

		$wild = '%';

		$search_clause = $wild . $wpdb->esc_like( $search ) . $wild;
		$course_clause = '' !== $course_id ? "AND umeta.meta_value = {$course_id}" : '';
		$date_clause   = '' !== $date ? "AND DATE(user.user_registered) = CAST('$date' AS DATE )" : '';
		$in_clause     = QueryHelper::prepare_in_clause( $status );

		$query  = "SELECT
					COUNT(DISTINCT user.ID)
					
					FROM {$wpdb->users} AS user
						
					INNER JOIN {$wpdb->usermeta} AS ins_status
						ON ( user.ID = ins_status.user_id )
						AND ins_status.meta_key = '_tutor_instructor_status'
					LEFT JOIN {$wpdb->usermeta} AS umeta
						ON umeta.user_id = user.ID
						AND umeta.meta_key = '_tutor_instructor_course_id'
					WHERE ins_status.meta_value IN ($in_clause)
						AND (user.user_email LIKE %s OR user.display_name LIKE %s)
						{$course_clause}
						{$date_clause}
				";
		$result = TutorCache::get( self::INSTRUCTOR_COUNT_CACHE_KEY . $unique_cache_key );
		if ( false === $result ) {
			TutorCache::set(
				self::INSTRUCTOR_COUNT_CACHE_KEY,
				//phpcs:disable
				$result = $wpdb->get_var(
					$wpdb->prepare(
						$query,
						$search_clause,
						$search_clause
					)
				)
				//phpcs:enable
			);
		}
		return $result;
	}
}
