<?php
/**
 * Manage Order
 *
 * @package Tutor\Order
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

namespace Tutor\Ecommerce;

use TUTOR\Backend_Page_Trait;
use Tutor\Helpers\QueryHelper;
use TUTOR\Input;
use Tutor\Models\CourseModel;
use Tutor\Models\OrderModel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * OrderController class
 *
 * @since 2.0.0
 */
class OrderController {
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
		$this->page_title = __( 'Orders', 'tutor' );
		/**
		 * Handle bulk action
		 *
		 * @since v2.0.0
		 */
		add_action( 'wp_ajax_tutor_order_list_bulk_action', array( $this, 'order_list_bulk_action' ) );
		/**
		 * Handle ajax request for updating order status
		 *
		 * @since v2.0.0
		 */
		add_action( 'wp_ajax_tutor_change_order_status', array( $this, 'tutor_change_order_status' ) );
		/**
		 * Handle ajax request for delete order
		 *
		 * @since v2.0.0
		 */
		add_action( 'wp_ajax_tutor_order_delete', array( $this, 'tutor_order_delete' ) );
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
		return apply_filters( 'tutor_order_bulk_actions', $actions );
	}

	/**
	 * Available tabs that will visible on the right side of page navbar
	 *
	 * @param string  $category_slug category slug.
	 * @param integer $order_id order ID.
	 * @param string  $date selected date | optional.
	 * @param string  $search search by user name or email | optional.
	 *
	 * @return array
	 *
	 * @since v2.0.0
	 */
	public function tabs_key_value( $category_slug, $order_id, $date, $search ): array {
		$url = get_pagenum_link();

		$all       = self::count_order( 'all', $order_id, $date, $search );
		$mine      = self::count_order( 'mine', $order_id, $date, $search );
		$published = self::count_order( 'publish', $order_id, $date, $search );
		$draft     = self::count_order( 'draft', $order_id, $date, $search );
		$pending   = self::count_order( 'pending', $order_id, $date, $search );
		$trash     = self::count_order( 'trash', $order_id, $date, $search );
		$private   = self::count_order( 'private', $order_id, $date, $search );
		$future    = self::count_order( 'future', $order_id, $date, $search );

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
				'key'   => 'future',
				'title' => __( 'Scheduled', 'tutor' ),
				'value' => $future,
				'url'   => $url . '&data=future',
			),
			array(
				'key'   => 'private',
				'title' => __( 'Private', 'tutor' ),
				'value' => $private,
				'url'   => $url . '&data=private',
			),
			array(
				'key'   => 'trash',
				'title' => __( 'Trash', 'tutor' ),
				'value' => $trash,
				'url'   => $url . '&data=trash',
			),
		);
		return apply_filters( 'tutor_order_tabs', $tabs );
	}

	/**
	 * Count orders by status & filters
	 * Count all | min | published | pending | draft
	 *
	 * @param string $status | required.
	 * @param string $order_id selected order id | optional.
	 * @param string $date selected date | optional.
	 * @param string $search_term search by user name or email | optional.
	 *
	 * @return int
	 *
	 * @since 2.0.0
	 */
	protected static function count_order( string $status, $order_id = '', $date = '', $search_term = '' ): int {
		$user_id     = get_current_user_id();
		$status      = sanitize_text_field( $status );
		$order_id    = sanitize_text_field( $order_id );
		$date        = sanitize_text_field( $date );
		$search_term = sanitize_text_field( $search_term );

		$args = array(
			'post_type' => tutor()->order_post_type,
		);

		if ( 'all' === $status || 'mine' === $status ) {
			$args['post_status'] = array( 'publish', 'pending', 'draft', 'private', 'future' );
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

		if ( '' !== $order_id ) {
			$args['p'] = $order_id;
		}

		// Search filter.
		if ( '' !== $search_term ) {
			$args['s'] = $search_term;
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
	public function order_list_bulk_action() {

		tutor_utils()->checking_nonce();

		// Check if user is privileged.
		if ( ! current_user_can( 'administrator' ) ) {
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
			do_action( 'before_tutor_order_bulk_action_delete', $bulk_ids );

			$delete_orders = self::bulk_delete_order( $bulk_ids );

			do_action( 'after_tutor_order_bulk_action_delete', $bulk_ids );
			$delete_orders ? wp_send_json_success() : wp_send_json_error( array( 'message' => __( 'Could not delete selected orders', 'tutor' ) ) );
			exit;
		}

		/**
		 * Do action before order update
		 *
		 * @param string $action (publish | pending | draft | trash).
		 * @param array $bulk_ids, order id.
		 */
		do_action( 'before_tutor_order_bulk_action_update', $action, $bulk_ids );

		$update_status = self::update_order_status( $action, $bulk_ids );

		do_action( 'after_tutor_order_bulk_action_update', $action, $bulk_ids );

		$update_status ? wp_send_json_success() : wp_send_json_error(
			array(
				'message' => 'Could not update order status',
				'tutor',
			)
		);

		exit;
	}

	/**
	 * Handle ajax request for updating order status
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public static function tutor_change_order_status() {
		tutor_utils()->checking_nonce();

		// Check if user is privileged.
		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$status = Input::post( 'status' );
		$id     = Input::post( 'id' );
		$order  = get_post( $id );

		if ( OrderModel::POST_TYPE !== $order->post_type ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$args = array(
			'ID'          => $id,
			'post_status' => $status,
		);

		if ( OrderModel::STATUS_FUTURE === $order->post_status && OrderModel::STATUS_PUBLISH === $status ) {
			$args['post_status']   = OrderModel::STATUS_PUBLISH;
			$args['post_date']     = current_time( 'mysql' );
			$args['post_date_gmt'] = current_time( 'mysql', 1 );
		}

		wp_update_post( $args );
		wp_send_json_success();
		exit;
	}

	/**
	 * Handle ajax request for deleting order
	 *
	 * @since 2.0.0
	 *
	 * @return void JSON response
	 */
	public static function tutor_order_delete() {
		tutor_utils()->checking_nonce();

		$user_id  = get_current_user_id();
		$order_id = Input::post( 'id', 0, Input::TYPE_INT );

		// Check if user is privileged.
		if ( ! tutor_utils()->has_user_role( array( 'administrator', 'editor' ) ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$delete = CourseModel::delete_course( $order_id );

		if ( $delete ) {
			wp_send_json_success( __( 'Order has been deleted ', 'tutor' ) );
		} else {
			wp_send_json_error( __( 'Order delete failed ', 'tutor' ) );
		}

		exit;
	}

	/**
	 * Execute bulk delete action
	 *
	 * @param string $bulk_ids ids that need to update.
	 * @return bool
	 * @since 2.0.0
	 */
	public static function bulk_delete_order( $bulk_ids ): bool {
		$bulk_ids = explode( ',', sanitize_text_field( $bulk_ids ) );

		foreach ( $bulk_ids as $post_id ) {
			CourseModel::delete_course( $post_id );
		}

		return true;
	}

	/**
	 * Update order status
	 *
	 * @param string $status for updating order status.
	 * @param string $bulk_ids comma separated ids.
	 *
	 * @return bool
	 *
	 * @since 2.0.0
	 */
	public static function update_order_status( string $status, $bulk_ids ): bool {
		global $wpdb;
		$post_table = $wpdb->posts;
		$status     = sanitize_text_field( $status );
		$bulk_ids   = sanitize_text_field( $bulk_ids );

		$ids       = array_map( 'intval', explode( ',', $bulk_ids ) );
		$in_clause = QueryHelper::prepare_in_clause( $ids );

		$update = $wpdb->query(
			$wpdb->prepare(
				"UPDATE {$post_table} SET post_status = %s WHERE ID IN ($in_clause)", //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$status
			)
		);

		return true;
	}

	/**
	 * Get orders list
	 *
	 * @since 3.0.0
	 *
	 * @param array  $where where clause conditions.
	 * @param int    $limit limit default 10.
	 * @param int    $offset default 0.
	 * @param string $order_by column default 'o.id'.
	 * @param string $order list order default 'desc'.
	 *
	 * @return array
	 */
	public static function get_orders( array $where = array(), int $limit = 10, int $offset = 0, string $order_by = 'o.id', string $order = 'desc' ) {

		global $wpdb;

		$primary_table  = "{$wpdb->prefix}tutor_orders o";
		$joining_tables = array(
			array(
				'type'  => 'INNER',
				'table' => "{$wpdb->users} u",
				'on'    => 'o.user_id = u.ID',
			),
			array(
				'type'  => 'LEFT',
				'table' => "{$wpdb->usermeta} um1",
				'on'    => 'u.ID = um1.user_id AND um1.meta_key = "tutor_customer_billing_name"',
			),
			array(
				'type'  => 'LEFT',
				'table' => "{$wpdb->usermeta} um2",
				'on'    => 'u.ID = um2.user_id AND um2.meta_key = "tutor_customer_billing_name"',
			),
		);

		$select_columns = array( 'o.*', 'u.user_login', 'um1.meta_value as billing_name', 'um2.meta_value as billing_email' );

		return QueryHelper::get_joined_data( $primary_table, $joining_tables, $select_columns, $where, $order_by, $limit, $offset, $order );

	}
}
