<?php
/**
 * Withdraw Model
 *
 * @package Tutor\Models
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.7
 */

namespace Tutor\Models;

use Tutor\Helpers\QueryHelper;
use Tutor\Helpers\UrlHelper;
use TUTOR\Input;

/**
 * WithdrawModel Class
 *
 * @since 2.0.7
 */
class WithdrawModel {
	/**
	 * All withdraw status
	 */
	const STATUS_PENDING  = 'pending';
	const STATUS_APPROVED = 'approved';
	const STATUS_REJECTED = 'rejected';

	/**
	 * Get withdrawal status list
	 *
	 * @since 4.0.0
	 *
	 * @return array
	 */
	public static function get_withdrawal_status_list() {
		return array(
			self::STATUS_PENDING  => __( 'Pending', 'tutor' ),
			self::STATUS_APPROVED => __( 'Approved', 'tutor' ),
			self::STATUS_REJECTED => __( 'Rejected', 'tutor' ),
		);
	}

	/**
	 * Get withdrawal count
	 *
	 * @since 4.0.0
	 *
	 * @param array $where where.
	 *
	 * @return int
	 */
	public static function get_withdrawal_count( $where = array() ) {
		return QueryHelper::get_count( 'tutor_withdraws', $where, array(), 'withdraw_id' );
	}

	/**
	 * Get status count
	 *
	 * @since 4.0.0
	 */
	public static function get_status_filter_options() {
		$url   = get_pagenum_link();
		$where = array(
			'user_id' => get_current_user_id(),
		);

		$tabs [] = array(
			'key'   => '',
			'title' => __( 'All', 'tutor' ),
			'value' => self::get_withdrawal_count( $where ),
			'url'   => UrlHelper::add_query_params( $url, array( 'data' => 'all' ) ),
		);

		foreach ( self::get_withdrawal_status_list() as $status => $title ) {
			$where['status'] = $status;

			$tabs[] = array(
				'key'   => $status,
				'title' => $title,
				'value' => self::get_withdrawal_count( $where ),
				'url'   => UrlHelper::add_query_params( $url, array( 'data' => $status ) ),
			);
		}

		return $tabs;
	}

	/**
	 * Get withdraw summary info for an user
	 *
	 * @since 2.0.7
	 * @since 4.0.0 $args parameter added.
	 *
	 * @param  int   $instructor_id instructor id.
	 * @param array $args Optional additional WHERE conditions.
	 * @return array|object|null|void
	 */
	public static function get_withdraw_summary( $instructor_id, $args = array() ) {
		global $wpdb;

		$args        = tutor_utils()->sanitize_array( $args );
		$date_clause = '';

		if ( ! empty( $args['from'] ) && ! empty( $args['to'] ) ) {
			$from = Input::sanitize( $args['from'] );
			$to   = Input::sanitize( $args['to'] );

			$where['created_at'] = array( 'BETWEEN', array( $from, $to ) );
			$date_clause         = ' AND ' . QueryHelper::prepare_where_clause( $where );
		}

		$maturity_days = tutor_utils()->get_option( 'minimum_days_for_balance_to_be_available' );

		//phpcs:disable
		$data = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT ID, display_name, 
                    total_income,
					total_withdraw, 
                    (total_income-total_withdraw) current_balance, 
                    total_matured,
					total_pending,
                    greatest(0, total_matured - total_withdraw) available_for_withdraw 
                
                FROM (
                        SELECT ID,display_name, 
                    COALESCE((SELECT SUM(instructor_amount) FROM {$wpdb->prefix}tutor_earnings WHERE order_status='%s' {$date_clause} GROUP BY user_id HAVING user_id=u.ID),0) total_income,
                    
                        COALESCE((
                        SELECT sum(amount) total_withdraw FROM {$wpdb->prefix}tutor_withdraws 
                        WHERE status='%s' {$date_clause}
                        GROUP BY user_id
                        HAVING user_id=u.ID
                    ),0) total_withdraw,
					
					COALESCE((
                        SELECT sum(amount) total_pending FROM {$wpdb->prefix}tutor_withdraws 
                        WHERE status='pending' {$date_clause}
                        GROUP BY user_id
                        HAVING user_id=u.ID
                    ),0) total_pending,

                    COALESCE((
                        SELECT SUM(instructor_amount) FROM(
                            SELECT user_id, instructor_amount, created_at, DATEDIFF(NOW(),created_at) AS days_old FROM {$wpdb->prefix}tutor_earnings WHERE order_status='%s' {$date_clause}
                        ) a
                        WHERE days_old >= %d
                        GROUP BY user_id
                        HAVING user_id = u.ID
                    ),0) total_matured
                    
                FROM {$wpdb->prefix}users u WHERE u.ID=%d
                
                ) a",
				'completed',
				self::STATUS_APPROVED,
				'completed',
				$maturity_days,
				$instructor_id
			)
		);

		//phpcs:enable

		return $data;
	}

	/**
	 * Get withdrawal history
	 *
	 * @since 1.0.0
	 *
	 * @param int   $user_id | optional.
	 * @param array $filter | ex: array('status' => '','date' => '', 'order' => '', 'start' => 10, 'per_page' => 10,'search' => '').
	 * @param int   $start start.
	 * @param int   $limit limit.
	 *
	 * @return object
	 */
	public static function get_withdrawals_history( $user_id = 0, $filter = array(), $start = 0, $limit = 20 ) {
		global $wpdb;

		$filter = (array) $filter;
		extract( $filter ); //phpcs:ignore WordPress.PHP.DontExtract.extract_extract

		$query_by_status_sql = '';
		$query_by_user_sql   = '';

		if ( ! empty( $status ) ) {
			$status = (array) $status;
			$status = QueryHelper::prepare_in_clause( $status );

			$query_by_status_sql = " AND status IN({$status}) ";
		}

		if ( $user_id ) {
			$query_by_user_sql = " AND user_id = {$user_id} ";
		}

		// Order query @since 2.0.0.
		$order_query = '';
		if ( isset( $order ) && '' !== $order ) {
			$is_valid_sql = sanitize_sql_orderby( $order );
			if ( $is_valid_sql ) {
				$order_query = "ORDER BY created_at {$order}";
			}
		} else {
			$order_query = 'ORDER BY created_at DESC';
		}

		// Date query @since 2.0.0.
		$date_query = '';
		if ( isset( $date ) && '' !== $date ) {
			$date_query = "AND DATE(created_at) = CAST( '$date' AS DATE )";
		}

		// Date range @since 4.0.0.
		$date_query = '';
		if ( isset( $start_date ) && isset( $end_date ) ) {
			$date_query = "AND DATE(created_at) BETWEEN CAST( '$start_date' AS DATE ) AND CAST( '$end_date' AS DATE )";
		}

		// Search query @since 2.0.0.
		$search_term_raw = empty( $search ) ? '' : $search;
		$search_query    = '%%';
		if ( ! empty( $search_term_raw ) ) {
			$search_query = '%' . $wpdb->esc_like( $search_term_raw ) . '%';
		}

		//phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(withdraw_id)
			FROM 	{$wpdb->prefix}tutor_withdraws  withdraw_tbl
					INNER JOIN {$wpdb->users} user_tbl
						ON withdraw_tbl.user_id = user_tbl.ID
			WHERE 	1 = 1
					{$query_by_user_sql}
					{$query_by_status_sql}
					{$date_query}
					AND (user_tbl.display_name LIKE %s OR user_tbl.user_login LIKE %s OR user_tbl.user_nicename LIKE %s OR user_tbl.user_email = %s)
			",
				$search_query,
				$search_query,
				$search_query,
				$search_term_raw
			)
		);

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT 	withdraw_tbl.*,
					user_tbl.display_name AS user_name,
					user_tbl.user_email
				FROM {$wpdb->prefix}tutor_withdraws withdraw_tbl
					INNER JOIN {$wpdb->users} user_tbl
							ON withdraw_tbl.user_id = user_tbl.ID
				WHERE 1 = 1
					{$query_by_user_sql}
					{$query_by_status_sql}
					{$date_query}

					AND (user_tbl.display_name LIKE %s OR user_tbl.user_login LIKE %s OR user_tbl.user_nicename LIKE %s OR user_tbl.user_email = %s)
				{$order_query}
				LIMIT %d, %d
			",
				$search_query,
				$search_query,
				$search_query,
				$search_term_raw,
				$start,
				$limit
			)
		);

		//phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$withdraw_history = array(
			'count'   => $count ? $count : 0,
			'results' => is_array( $results ) ? $results : array(),
		);

		return (object) $withdraw_history;
	}

	/**
	 * Get withdraw method for a specific
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id user id.
	 * @return bool|mixed
	 */
	public static function get_user_withdraw_method( $user_id = 0 ) {
		$user_id = tutor_utils()->get_user_id( $user_id );
		$account = get_user_meta( $user_id, '_tutor_withdraw_method_data', true );

		if ( $account ) {
			return maybe_unserialize( $account );
		}

		return false;
	}
}
