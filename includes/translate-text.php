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
	);
}
