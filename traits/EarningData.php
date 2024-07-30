<?php
/**
 * Trait for preparing earning data
 *
 * @package Tutor\Traits
 * @since 3.0.0
 */

namespace Tutor\Traits;

trait EarningData {

	/**
	 * Prepare earning data
	 *
	 * @since 3.0.0
	 *
	 * @param mixed  $total_price Total price of an item.
	 * @param int    $course_id Connected course id.
	 * @param int    $order_id Order id.
	 * @param string $order_status Order status.
	 *
	 * @return array
	 */
	public function prepare_earning_data( $total_price, $course_id, $order_id, $order_status ) {
		$fees_deduct_data      = array();
		$tutor_earning_fees    = tutor_utils()->get_option( 'fee_amount_type' );
		$enable_fees_deducting = tutor_utils()->get_option( 'enable_fees_deducting' );

		$course_price_grand_total = $total_price;

		// Deduct predefined amount (percent or fixed).
		if ( $enable_fees_deducting ) {
			$fees_name   = tutor_utils()->get_option( 'fees_name', '' );
			$fees_amount = (int) tutor_utils()->avalue_dot( 'fees_amount', $tutor_earning_fees );
			$fees_type   = tutor_utils()->avalue_dot( 'fees_type', $tutor_earning_fees );

			if ( $fees_amount > 0 ) {
				if ( 'percent' === $fees_type ) {
					$fees_amount = ( $total_price * $fees_amount ) / 100;
				}

				$course_price_grand_total = $total_price - $fees_amount;
			}

			$fees_deduct_data = array(
				'deduct_fees_amount' => $fees_amount,
				'deduct_fees_name'   => $fees_name,
				'deduct_fees_type'   => $fees_type,
			);
		}

		// Distribute amount between admin and instructor.
		$sharing_enabled   = tutor_utils()->get_option( 'enable_revenue_sharing' );
		$instructor_rate   = $sharing_enabled ? tutor_utils()->get_option( 'earning_instructor_commission' ) : 0;
		$admin_rate        = $sharing_enabled ? tutor_utils()->get_option( 'earning_admin_commission' ) : 100;
		$commission_type   = 'percent';
		$instructor_amount = $instructor_rate > 0 ? ( ( $course_price_grand_total * $instructor_rate ) / 100 ) : 0;
		$admin_amount      = $admin_rate > 0 ? ( ( $course_price_grand_total * $admin_rate ) / 100 ) : 0;

		// Course author id.
		$user_id = get_post_field( 'post_author', $course_id );

		// (Use Pro Filter - Start)
		// The response must be same array structure.
		// Do not change used variable names here, or change in both of here and pro plugin
		$pro_arg         = array(
			'user_id'                  => $user_id,
			'instructor_rate'          => $instructor_rate,
			'admin_rate'               => $admin_rate,
			'instructor_amount'        => $instructor_amount,
			'admin_amount'             => $admin_amount,
			'course_price_grand_total' => $course_price_grand_total,
			'commission_type'          => $commission_type,
		);
		$pro_calculation = apply_filters( 'tutor_pro_earning_calculator', $pro_arg );
		extract( $pro_calculation ); //phpcs:ignore
		// (Use Pro Filter - End).

		// Prepare insertable earning data.
		$earning_data = array(
			'user_id'                  => $user_id,
			'course_id'                => $course_id,
			'order_id'                 => $order_id,
			'order_status'             => $order_status,
			'course_price_total'       => $total_price,
			'course_price_grand_total' => $course_price_grand_total,

			'instructor_amount'        => $instructor_amount,
			'instructor_rate'          => $instructor_rate,
			'admin_amount'             => $admin_amount,
			'admin_rate'               => $admin_rate,

			'commission_type'          => $commission_type,
			'process_by'               => 'Tutor',
			'created_at'               => current_time( 'mysql', true ),
		);
		$earning_data = apply_filters( 'tutor_new_earning_data', array_merge( $earning_data, $fees_deduct_data ) );

		return $earning_data;
	}
}
