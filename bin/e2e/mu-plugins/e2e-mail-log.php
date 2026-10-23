<?php
/**
 * Plugin Name: Tutor E2E Mail Log
 * Description: Captures wp_mail() calls for local Playwright assertions.
 *
 * @package Tutor\E2E
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter(
	'pre_wp_mail',
	static function ( $preempt, $atts ) {
		if ( null !== $preempt ) {
			return $preempt;
		}

		$messages = get_option( 'e2e_mail_log', array() );
		if ( ! is_array( $messages ) ) {
			$messages = array();
		}

		array_unshift(
			$messages,
			array(
				'to'          => array_values( (array) ( $atts['to'] ?? array() ) ),
				'subject'     => (string) ( $atts['subject'] ?? '' ),
				'message'     => (string) ( $atts['message'] ?? '' ),
				'headers'     => array_values( (array) ( $atts['headers'] ?? array() ) ),
				'attachments' => array_values( (array) ( $atts['attachments'] ?? array() ) ),
				'time'        => time(),
			)
		);

		update_option( 'e2e_mail_log', array_slice( $messages, 0, 200 ), false );

		// Returning true short-circuits wp_mail() without sending real mail.
		return true;
	},
	10,
	2
);
