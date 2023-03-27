<?php
/**
 * Manage withdrawals
 *
 * @package Tutor\Withdraw
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Handle withdraw request logic
 *
 * @since 1.0.0
 */
class Withdraw_Requests_List {

	/**
	 * Page title
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $page_title;

	/**
	 * List page slug
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const WITHDRAW_REQUEST_LIST_PAGE = 'tutor_withdraw_requests';

	/**
	 * Register hooks, resolve dependencies
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->page_title = __( 'Withdraw Request', 'tutor' );
		/**
		 * Approve or reject withdraw request
		 */
		add_action( 'wp_ajax_tutor_admin_withdraw_action', array( $this, 'update_withdraw_status' ) );
	}

	/**
	 * Available tabs that will visible on the right side of page navbar
	 *
	 * @since 2.0.0
	 *
	 * @param string $date withdraw request date | optional.
	 * @param string $search search by instructor name or email | optional.
	 *
	 * @return array
	 */
	public function tabs_key_value( $date = '', $search = '' ): array {
		$approved = self::tabs_data( 'approved', $date, $search );
		$pending  = self::tabs_data( 'pending', $date, $search );
		$rejected = self::tabs_data( 'rejected', $date, $search );

		$url  = get_pagenum_link();
		$tabs = array(
			array(
				'key'   => 'all',
				'title' => __( 'All', 'tutor-pro' ),
				'value' => $approved + $pending + $rejected,
				'url'   => $url . '&data=all',
			),
			array(
				'key'   => 'approved',
				'title' => __( 'Approved', 'tutor-pro' ),
				'value' => $approved,
				'url'   => $url . '&data=approved',
			),
			array(
				'key'   => 'pending',
				'title' => __( 'Pending', 'tutor-pro' ),
				'value' => $pending,
				'url'   => $url . '&data=pending',
			),
			array(
				'key'   => 'rejected',
				'title' => __( 'Rejected', 'tutor-pro' ),
				'value' => $rejected,
				'url'   => $url . '&data=rejected',
			),
		);
		return $tabs;
	}

	/**
	 * Get counted number of withdraw list by status ex: approved | pending | rejected
	 *
	 * @since 2.0.0
	 *
	 * @param string $status status required | available : (approved | pending | rejected).
	 * @param string $date withdraw request date | optional | YYYY-MM-DD.
	 * @param string $search search by instructor name or email | optional.
	 *
	 * @return int
	 */
	public static function tabs_data( string $status, $date = '', $search = '' ): int {
		global $wpdb;
		$withdraw_table = $wpdb->prefix . 'tutor_withdraws';
		$user_table     = $wpdb->users;
		$status         = sanitize_text_field( $status );
		$date           = sanitize_text_field( $date );
		$search         = sanitize_text_field( $search );
		// Prepare date query.
		$date_query = '';
		if ( '' !== $date ) {
			$date_query = "AND DATE(withdraw.created_at) = CAST('{$date}' AS DATE) ";
		}

		// Prepare search query.
		$search = '%' . $wpdb->esc_like( $search ) . '%';

		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT count(*) FROM {$withdraw_table} AS withdraw
				INNER JOIN {$user_table} AS user
					ON user.ID = withdraw.user_id
				WHERE  withdraw.status = %s
					{$date_query}
					AND ( user.user_login LIKE %s OR user.user_nicename LIKE %s OR user.user_email LIKE %s OR user.display_name LIKE %s )
			",
				$status,
				$search,
				$search,
				$search,
				$search
			)
		);
		return $count ? $count : 0;
	}

	/**
	 * Handle ajax request for updating withdraw status | available status (approved, rejected, pending)
	 *
	 * @since 2.0.0
	 *
	 * @return string json response.
	 */
	public function update_withdraw_status() {
		tutor_utils()->checking_nonce();

		// Check if user is privileged.
		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$status         = Input::post( 'action-type', '' );
		$withdraw_id    = Input::post( 'withdraw-id', '' );
		$reject_type    = Input::post( 'reject-type', '' );
		$reject_comment = Input::post( 'reject-comment', '' );

		if ( '' === $withdraw_id ) {
			return false;
		} else {
			$update = self::update( $status, $withdraw_id, $reject_type, $reject_comment );
			return $update ? wp_send_json( true ) : false;
		}
		exit;
	}

	/**
	 * Update withdraw status | available status (approved, rejected, pending)
	 *
	 * @since v2.0.0
	 *
	 * @param string $status | required.
	 * @param int    $withdraw_id | required.
	 * @param string $reject_type | optional.
	 * @param string $reject_comment | optional.
	 *
	 * @return bool json response.
	 */
	public static function update( string $status, int $withdraw_id, $reject_type = '', $reject_comment = '' ): bool {
		global $wpdb;
		$withdraw_table = $wpdb->prefix . 'tutor_withdraws';
		$withdraw_id    = sanitize_text_field( $withdraw_id );
		$status         = sanitize_text_field( $status );

		// Prepare data for update.
		$data = array(
			'status'     => $status,
			'updated_at' => gmdate( 'Y-m-d H:i:s' ),
		);

		// If rejected then append reject_type and comment with method_data.
		if ( 'rejected' === $status ) {
			$withdraw = self::get_withdraw_by_id( $withdraw_id );
			if ( $withdraw ) {
				$details = unserialize( $withdraw->method_data );

				$details['rejects']  = array(
					'reject_type'    => sanitize_text_field( $reject_type ),
					'reject_comment' => sanitize_text_field( $reject_comment ),
				);
				$data['method_data'] = maybe_serialize( $details );

				// Trigger email after rejecting withdraw.
				do_action( 'tutor_after_rejected_withdraw', $withdraw_id );
			}
		} else {
			do_action( 'tutor_after_approved_withdraw', $withdraw_id );
		}

		// Update.
		$update = $wpdb->update(
			$withdraw_table,
			$data,
			array(
				'withdraw_id' => $withdraw_id,
			)
		);
		return $update ? true : false;
	}


	/**
	 * Get withdraw by id
	 *
	 * @since v2.0.0
	 *
	 * @param int $withdraw_id | required.
	 *
	 * @return object withdraw list.
	 */
	public static function get_withdraw_by_id( int $withdraw_id ) {
		global $wpdb;
		$withdraw_table = $wpdb->prefix . 'tutor_withdraws';
		return $wpdb->get_row(
			$wpdb->prepare(
				" SELECT *FROM {$withdraw_table}
				WHERE withdraw_id = %d
			",
				$withdraw_id
			)
		);
	}

}
