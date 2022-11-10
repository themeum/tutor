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
	 * Bind display name in column
	 *
	 * @since 1.0.0
	 * @deprecated 2.0.0
	 *
	 * @param object $item item.
	 * @return mixed
	 */
	public function column_display_name( $item ) {

		$actions = array();
		$status  = tutor_utils()->instructor_status( $item->ID, false );

		switch ( $status ) {
			case 'pending':
				$actions['approved'] = sprintf( '<a class="tutor-btn tutor-btn-outline-primary instructor-action" data-action="approve" data-instructor-id="' . $item->ID . '" href="?page=%s&action=%s&instructor=%s">' . __( 'Approve', 'tutor' ) . '</a>', self::INSTRUCTOR_LIST_PAGE, 'approve', $item->ID );
				break;
			case 'approved':
				$actions['blocked'] = sprintf( '<a data-prompt-message="' . __( 'Sure to Block?', 'tutor' ) . '" class="tutor-btn tutor-btn-outline-primary instructor-action" data-action="blocked" data-instructor-id="' . $item->ID . '" href="?page=%s&action=%s&instructor=%s">' . __( 'Block', 'tutor' ) . '</a>', self::INSTRUCTOR_LIST_PAGE, 'blocked', $item->ID );
				break;
			case 'blocked':
				$actions['approved'] = sprintf( '<a data-prompt-message="' . __( 'Sure to Un Block?', 'tutor' ) . '" class="tutor-btn tutor-btn-outline-primary instructor-action" data-action="approve" data-instructor-id="' . $item->ID . '" href="?page=%s&action=%s&instructor=%s">' . __( 'Unblock', 'tutor' ) . '</a>', self::INSTRUCTOR_LIST_PAGE, 'approve', $item->ID );
				break;
		}

		// Add user edit link.
		$edit_link                             = get_edit_user_link( $item->ID );
		$edit_link                             = '<a href="' . esc_url( $edit_link ) . '">' . esc_html__( 'Edit' ) . '</a>';
		$actions['tutor-instructor-edit-link'] = $edit_link;

		// Add remove instructor action.
		$removal_title                      = 'pending' == $status ? __( 'Reject', 'tutor' ) : __( 'Remove as Instructor', 'tutor' );
		$removal_warning                    = 'pending' == $status ? __( 'Sure to Reject?', 'tutor' ) : __( 'Sure to Remove as Instructor?', 'tutor' );
		$actions['tutor-remove-instructor'] = sprintf( '<a data-prompt-message="' . $removal_warning . '" class="instructor-action" data-action="remove-instructor" data-instructor-id="' . $item->ID . '"  href="?page=%s&action=%s&instructor=%s">' . $removal_title . '</a>', self::INSTRUCTOR_LIST_PAGE, 'remove-instructor', $item->ID );

		// Return the title contents.
		return sprintf(
			'%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
			$item->display_name,
			$item->ID,
			$this->row_actions( $actions )
		);
	}

	/**
	 * Bind action in column.
	 *
	 * @since 1.0.0
	 * @deprecated 2.0.0
	 *
	 * @param object $item item.
	 * @return mixed
	 */
	public function column_action( $item ) {

		$actions = array();
		$status  = tutor_utils()->instructor_status( $item->ID, false );

		switch ( $status ) {
			case 'pending':
				$actions['approved'] = sprintf( '<a class="tutor-btn tutor-btn-outline-primary instructor-action" data-action="approve" data-instructor-id="' . $item->ID . '" href="?page=%s&action=%s&instructor=%s">' . __( 'Approve', 'tutor' ) . '</a>', self::INSTRUCTOR_LIST_PAGE, 'approve', $item->ID );
				break;
			case 'approved':
				$actions['blocked'] = sprintf( '<a data-title="' . ( '' ) . '" data-prompt-message="' . __( 'Are you sure, want to block?', 'tutor' ) . '" class="tutor-btn tutor-btn-outline-primary instructor-action" data-action="blocked" data-instructor-id="' . $item->ID . '" href="?page=%s&action=%s&instructor=%s">' . __( 'Block', 'tutor' ) . '</a>', self::INSTRUCTOR_LIST_PAGE, 'blocked', $item->ID );
				break;
			case 'blocked':
				$actions['approved'] = sprintf( '<a data-title="' . ( '' ) . '" data-prompt-message="' . __( 'Are you sure, want to approve?', 'tutor' ) . '" class="tutor-btn tutor-btn-outline-primary instructor-action" data-action="approve" data-instructor-id="' . $item->ID . '" href="?page=%s&action=%s&instructor=%s">' . __( 'Approve', 'tutor' ) . '</a>', self::INSTRUCTOR_LIST_PAGE, 'approve', $item->ID );
				break;
		}

		return $this->row_actions( $actions );
	}

	/**
	 * Column callback
	 *
	 * @since 1.0.0
	 *
	 * @param object $item item.
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ $this->_args['singular'],  // Let's simply repurpose the table's singular label ("instructor").
			/*$2%s*/ $item->ID                // The value of the checkbox should be the record's id.
		);
	}

	/**
	 * Bind instructor commission data in column
	 *
	 * @since 1.0.0
	 * @deprecated 2.0.0
	 *
	 * @param object $item item.
	 * @return mixed
	 */
	public function column_instructor_commission( $item ) {
		$commision = apply_filters( 'tutor_pro_instructor_commission_string', $item->ID );

		// If the return value is numeric, it means the filter was not executed.
		// may be pro is not installed. So show N\A. The return value will something like '23 percent'.

		return ! is_numeric( $commision ) ? $commision : 'N\\A';
	}

	/**
	 * Get columns
	 *
	 * @since 1.0.0
	 * @deprecated 2.0.0
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'                    => '<input type="checkbox" />', // Render a checkbox instead of text.
			'display_name'          => __( 'Name', 'tutor' ),
			'user_email'            => __( 'E-Mail', 'tutor' ),
			'total_course'          => __( 'Total Course', 'tutor' ),
			'instructor_commission' => __( 'Instructor Commission', 'tutor' ),
			'registration_date'     => __( 'Date', 'tutor' ),
			'status'                => __( 'Status', 'tutor' ),
		);
		return $columns;
	}

	/**
	 * Get sortable columns
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			// 'display_name'     => array('title',false),     //true means it's already sorted
		);
		return $sortable_columns;
	}

	/**
	 * Handle single instructor approve | block action
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function process_bulk_action() {
		$instructor_id = Input::get( 'instructor', 0, Input::TYPE_INT );
		if ( 'approve' === $this->current_action() ) {
			self::add_instructor_role( $instructor_id, 'approved' );
		}

		if ( 'blocked' === $this->current_action() ) {
			self::add_instructor_role( $instructor_id, 'blocked' );
		}
	}

	/**
	 * Filter support added
	 *
	 * @since 1.0.0
	 * @deprecated 2.0.0
	 *
	 * @param string $search_filter search filter.
	 * @param string $course_filter course filter.
	 * @param string $date_filter date filter.
	 * @param string $order_filter order filter.
	 *
	 * @return void
	 */
	public function prepare_items( $search_filter = '', $course_filter = '', $date_filter = '', $order_filter = '' ) {
		$per_page = 20;

		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->process_bulk_action();
		$current_page = $this->get_pagenum();

		$total_items = tutor_utils()->get_total_instructors( $search_filter );
		$this->items = tutor_utils()->get_instructors( ( $current_page - 1 ) * $per_page, $per_page, $search_filter, $course_filter, $date_filter, $order_filter, $status = null, $cat_ids = array() );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
			)
		);
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
