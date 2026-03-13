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
	 * Withdrawal method keys
	 */
	const METHOD_BANK_TRANSFER_WITHDRAW = 'bank_transfer_withdraw';
	const METHOD_PAYPAL_WITHDRAW        = 'paypal_withdraw';
	const METHOD_ECHECK_WITHDRAW        = 'echeck_withdraw';

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
	 * Get withdrawal method list
	 *
	 * @since 4.0.0
	 *
	 * @return array
	 */
	public static function get_withdrawal_method_list() {
		return array(
			self::METHOD_BANK_TRANSFER_WITHDRAW => __( 'Bank Transfer', 'tutor' ),
			self::METHOD_PAYPAL_WITHDRAW        => __( 'PayPal', 'tutor' ),
			self::METHOD_ECHECK_WITHDRAW        => __( 'E-Check', 'tutor' ),
		);
	}

	/**
	 * Get withdrawal method icons
	 *
	 * @since 4.0.0
	 *
	 * @return array
	 */
	public static function get_method_icons() {
		return array(
			self::METHOD_BANK_TRANSFER_WITHDRAW => UrlHelper::asset( 'images/icon-bank.svg' ),
			self::METHOD_PAYPAL_WITHDRAW        => UrlHelper::asset( 'images/icon-paypal.svg' ),
			self::METHOD_ECHECK_WITHDRAW        => UrlHelper::asset( 'images/icon-echeck.svg' ),
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

		$start_date = Input::get( 'start_date' );
		$end_date   = Input::get( 'end_date' );

		if ( ! empty( $start_date ) && ! empty( $end_date ) ) {
			$where['DATE(created_at)'] = array( 'BETWEEN', array( $start_date, $end_date ) );
		}

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

		$where = array();
		if ( $user_id ) {
			$where['withdraw_tbl.user_id'] = $user_id;
		}

		if ( isset( $filter['status'] ) && ! empty( $filter['status'] ) ) {
			$where['withdraw_tbl.status'] = (array) $filter['status'];
		}

		if ( isset( $filter['date'] ) && ! empty( $filter['date'] ) ) {
			$where['DATE(withdraw_tbl.created_at) = %s'] = array( 'RAW', array( $filter['date'] ) );
		}

		if ( isset( $filter['start_date'], $filter['end_date'] ) ) {
			$where['DATE(withdraw_tbl.created_at)'] = array( 'BETWEEN', array( $filter['start_date'], $filter['end_date'] ) );
		}

		if ( isset( $filter['search'] ) && ! empty( $filter['search'] ) ) {
			$term = $filter['search'];
			$like = '%' . $wpdb->esc_like( $term ) . '%';

			$where['(user_tbl.display_name LIKE %s OR user_tbl.user_login LIKE %s OR user_tbl.user_nicename LIKE %s OR user_tbl.user_email = %s)'] = array(
				'RAW',
				array( $like, $like, $like, $term ),
			);
		}

		$orderby = 'withdraw_tbl.created_at';
		$order   = isset( $filter['order'] ) ? QueryHelper::get_valid_sort_order( $filter['order'] ) : 'DESC';

		$args = array(
			'select'  => array( 'withdraw_tbl.*', 'user_tbl.display_name AS user_name', 'user_tbl.user_email' ),
			'alias'   => 'withdraw_tbl',
			'joins'   => array(
				array(
					'type'  => 'INNER',
					'table' => $wpdb->users . ' AS user_tbl',
					'on'    => 'withdraw_tbl.user_id = user_tbl.ID',
				),
			),
			'where'   => $where,
			'orderby' => $orderby,
			'order'   => $order,
			'limit'   => (int) $limit,
			'offset'  => (int) $start,
		);

		$results = QueryHelper::query( 'tutor_withdraws', $args );

		$args['count'] = true;
		$count         = QueryHelper::query( 'tutor_withdraws', $args );

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
