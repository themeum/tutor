<?php
/**
 * Tutor MCP finance tools.
 *
 * @package Tutor\Mcp
 * @author  Themeum <support@themeum.com>
 * @link    https://themeum.com
 * @since   4.3.0
 */

namespace Tutor\Mcp\Abilities;

use Tutor\Models\WithdrawModel;
use Tutor\Mcp\Support\AbilityFactory;
use Tutor\Mcp\Support\Pagination;
use Tutor\Mcp\Support\Permissions;
use TUTOR\User;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Finance tool registration and execute handlers.
 *
 * @since 4.3.0
 */
class FinanceAbilities {

	/**
	 * Register finance abilities.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public function register(): void {
		AbilityFactory::tool(
			'tutor-mcp/get-earnings',
			'tutor-mcp-finance',
			__( 'Get Earnings', 'tutor' ),
			__( 'Get earning summary for the current user (or a user_id if admin).', 'tutor' ),
			array(
				'type'       => 'object',
				'properties' => array(
					'user_id' => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
				),
			),
			static function (): bool {
				return Permissions::can_manage_courses();
			},
			array( $this, 'get_earnings' )
		);

		AbilityFactory::tool(
			'tutor-mcp/list-withdrawals',
			'tutor-mcp-finance',
			__( 'List Withdrawals', 'tutor' ),
			__( 'Paginated withdrawal history. Admin sees all; instructors see own.', 'tutor' ),
			array(
				'type'       => 'object',
				'properties' => array(
					'user_id'  => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'status'   => array(
						'type' => 'string',
						'enum' => array( 'pending', 'approved', 'rejected' ),
					),
					'page'     => array(
						'type'    => 'integer',
						'minimum' => 1,
						'default' => 1,
					),
					'per_page' => array(
						'type'    => 'integer',
						'minimum' => 1,
						'maximum' => 50,
						'default' => 20,
					),
				),
			),
			static function (): bool {
				return Permissions::can_manage_courses();
			},
			array( $this, 'list_withdrawals' )
		);
	}

	/**
	 * Get earnings summary.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function get_earnings( array $input ) {
		$is_admin = current_user_can( 'manage_tutor' ) || User::is_admin();
		$user_id  = ! empty( $input['user_id'] ) && $is_admin
			? absint( $input['user_id'] )
			: get_current_user_id();

		$earning = tutor_utils()->get_earning_sum( $user_id );
		if ( ! is_object( $earning ) ) {
			return array(
				'user_id' => $user_id,
				'summary' => array(),
			);
		}

		return array(
			'user_id' => $user_id,
			'summary' => array(
				'course_price_total'       => (float) ( $earning->course_price_total ?? 0 ),
				'course_price_grand_total' => (float) ( $earning->course_price_grand_total ?? 0 ),
				'instructor_amount'        => (float) ( $earning->instructor_amount ?? 0 ),
				'total_withdraw'           => (float) ( $earning->total_withdraw ?? 0 ),
				'balance'                  => (float) ( $earning->balance ?? 0 ),
			),
		);
	}

	/**
	 * List withdrawals (strip PII).
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array
	 */
	public function list_withdrawals( array $input ): array {
		$bounds   = Pagination::bounds( $input );
		$is_admin = current_user_can( 'manage_tutor' ) || User::is_admin();
		$user_id  = ! empty( $input['user_id'] ) && $is_admin
			? absint( $input['user_id'] )
			: ( $is_admin ? 0 : get_current_user_id() );

		$filter = array();
		if ( ! empty( $input['status'] ) ) {
			$filter['status'] = sanitize_text_field( (string) $input['status'] );
		}

		$result = WithdrawModel::get_withdrawals_history( $user_id, $filter, $bounds['offset'], $bounds['per_page'] );
		$rows   = array();

		$items = is_object( $result ) && isset( $result->results ) ? $result->results : array();
		if ( is_array( $items ) ) {
			foreach ( $items as $w ) {
				$rows[] = array(
					'withdraw_id' => (int) ( $w->id ?? $w->withdraw_id ?? 0 ),
					'user_id'     => (int) ( $w->user_id ?? 0 ),
					'amount'      => (float) ( $w->amount ?? 0 ),
					'status'      => $w->status ?? '',
					'created_at'  => $w->created_at ?? '',
				);
			}
		}

		return array(
			'withdrawals' => $rows,
			'page'        => $bounds['page'],
			'per_page'    => $bounds['per_page'],
		);
	}
}
