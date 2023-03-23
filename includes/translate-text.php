<?php
/**
 * Translate text
 *
 * @package Tutor\Includes
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.1.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get translate text array
 *
 * @since 2.1.7
 *
 * @return array
 */
function tutor_get_translate_text() {
	return array(
		'all'        => array(
			'text' => __( 'All', 'tutor' ),
		),
		'read'       => array(
			'text' => __( 'Read', 'tutor' ),
		),
		'unread'     => array(
			'text' => __( 'Unread', 'tutor' ),
		),
		'important'  => array(
			'text' => __( 'Important', 'tutor' ),
		),
		'archived'   => array(
			'text' => __( 'Archived', 'tutor' ),
		),
		'pending'    => array(
			'badge' => 'warning',
			'text'  => __( 'Pending', 'tutor' ),
		),
		'pass'       => array(
			'badge' => 'success',
			'text'  => __( 'Pass', 'tutor' ),
		),
		'correct'    => array(
			'badge' => 'success',
			'text'  => __( 'Correct', 'tutor' ),
		),
		'fail'       => array(
			'badge' => 'danger',
			'text'  => __( 'Fail', 'tutor' ),
		),
		'wrong'      => array(
			'badge' => 'danger',
			'text'  => __( 'Wrong', 'tutor' ),
		),
		'approved'   => array(
			'badge' => 'success',
			'text'  => __( 'Approved', 'tutor' ),
		),
		'rejected'   => array(
			'badge' => 'danger',
			'text'  => __( 'Rejected', 'tutor' ),
		),
		'completed'  => array(
			'badge' => 'success',
			'text'  => __( 'Completed', 'tutor' ),
		),
		'processing' => array(
			'badge' => 'warning',
			'text'  => __( 'Processing', 'tutor' ),
		),
		'cancelled'  => array(
			'badge' => 'danger',
			'text'  => __( 'Cancelled', 'tutor' ),
		),
		'canceled'   => array(
			'badge' => 'danger',
			'text'  => __( 'Cancelled', 'tutor' ),
		),
		'blocked'    => array(
			'badge' => 'danger',
			'text'  => __( 'Blocked', 'tutor' ),
		),
		'cancel'     => array(
			'badge' => 'danger',
			'text'  => __( 'Cancelled', 'tutor' ),
		),
		'on-hold'    => array(
			'badge' => 'warning',
			'text'  => __( 'On Hold', 'tutor' ),
		),
		'onhold'     => array(
			'badge' => 'warning',
			'text'  => __( 'On Hold', 'tutor' ),
		),
		'wc-on-hold' => array(
			'badge' => 'warning',
			'text'  => __( 'On Hold', 'tutor' ),
		),
		'publish'    => array(
			'badge' => 'success',
			'text'  => __( 'Publish', 'tutor' ),
		),
		'trash'      => array(
			'badge' => 'danger',
			'text'  => __( 'Trash', 'tutor' ),
		),
		'draft'      => array(
			'badge' => 'warning',
			'text'  => __( 'Draft', 'tutor' ),
		),
		'private'    => array(
			'badge' => 'warning',
			'text'  => __( 'Private', 'tutor' ),
		),
		'true'       => array(
			'text' => _x( 'True', 'true/false question options', 'tutor' ),
		),
		'false'      => array(
			'text' => _x( 'False', 'true/false question options', 'tutor' ),
		),
		'days'  => array(
			'text' => __( 'Days', 'tutor' ),
		),
		'day'  => array(
			'text' => __( 'Day', 'tutor' ),
		),
		'hours'  => array(
			'text' => __( 'Hours', 'tutor' ),
		),
		'hour'  => array(
			'text' => __( 'Hour', 'tutor' ),
		),
		'minutes'  => array(
			'text' => __( 'Minutes', 'tutor' ),
		),
		'minute'  => array(
			'text' => __( 'Minute', 'tutor' ),
		),
		'seconds'  => array(
			'text' => __( 'Seconds', 'tutor' ),
		),
		'second'  => array(
			'text' => __( 'Second', 'tutor' ),
		),

		// Translate able week name.
		'monday'     => array(
			'text' => _x( 'Monday', 'Week name', 'tutor' ),
		),
		'tuesday'    => array(
			'text' => _x( 'Tuesday', 'Week name', 'tutor' ),
		),
		'wednesday'  => array(
			'text' => _x( 'Wednesday', 'Week name', 'tutor' ),
		),
		'thursday'   => array(
			'text' => _x( 'Thursday', 'Week name', 'tutor' ),
		),
		'friday'     => array(
			'text' => _x( 'Friday', 'Week name', 'tutor' ),
		),
		'saturday'   => array(
			'text' => _x( 'Saturday', 'Week name', 'tutor' ),
		),
		'sunday'     => array(
			'text' => _x( 'Sunday', 'Week name', 'tutor' ),
		),

		// Translate able month name.
		'january'    => array(
			'text' => _x( 'January', 'Month name', 'tutor' ),
		),
		'february'   => array(
			'text' => _x( 'February', 'Month name', 'tutor' ),
		),
		'march'      => array(
			'text' => _x( 'March', 'Month name', 'tutor' ),
		),
		'april'      => array(
			'text' => _x( 'April', 'Month name', 'tutor' ),
		),
		'may'        => array(
			'text' => _x( 'May', 'Month name', 'tutor' ),
		),
		'june'       => array(
			'text' => _x( 'June', 'Month name', 'tutor' ),
		),
		'july'       => array(
			'text' => _x( 'July', 'Month name', 'tutor' ),
		),
		'august'     => array(
			'text' => _x( 'August', 'Month name', 'tutor' ),
		),
		'september'  => array(
			'text' => _x( 'September', 'Month name', 'tutor' ),
		),
		'october'    => array(
			'text' => _x( 'October', 'Month name', 'tutor' ),
		),
		'november'   => array(
			'text' => _x( 'November', 'Month name', 'tutor' ),
		),
		'december'   => array(
			'text' => _x( 'December', 'Month name', 'tutor' ),
		),
		'jan'    => array(
			'text' => _x( 'January', 'Month name', 'tutor' ),
		),
		'feb'   => array(
			'text' => _x( 'February', 'Month name', 'tutor' ),
		),
		'mar'      => array(
			'text' => _x( 'March', 'Month name', 'tutor' ),
		),
		'apr'      => array(
			'text' => _x( 'April', 'Month name', 'tutor' ),
		),
		'may'        => array(
			'text' => _x( 'May', 'Month name', 'tutor' ),
		),
		'jun'       => array(
			'text' => _x( 'June', 'Month name', 'tutor' ),
		),
		'jul'       => array(
			'text' => _x( 'July', 'Month name', 'tutor' ),
		),
		'aug'     => array(
			'text' => _x( 'August', 'Month name', 'tutor' ),
		),
		'sep'  => array(
			'text' => _x( 'September', 'Month name', 'tutor' ),
		),
		'oct'    => array(
			'text' => _x( 'October', 'Month name', 'tutor' ),
		),
		'nov'   => array(
			'text' => _x( 'November', 'Month name', 'tutor' ),
		),
		'dec'   => array(
			'text' => _x( 'December', 'Month name', 'tutor' ),
		),
	);
}
