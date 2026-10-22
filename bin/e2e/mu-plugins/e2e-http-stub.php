<?php
/**
 * Plugin Name: Tutor E2E HTTP Stub
 * Description: Returns deterministic responses for external provider calls in local Playwright runs.
 *
 * @package Tutor\E2E
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter(
	'pre_http_request',
	static function ( $response, $args, $url ) {
		if ( null !== $response ) {
			return $response;
		}

		$stubs = get_option( 'e2e_http_stub', array() );
		if ( ! is_array( $stubs ) || empty( $stubs ) ) {
			return $response;
		}

		$requests = get_option( 'e2e_http_requests', array() );
		if ( ! is_array( $requests ) ) {
			$requests = array();
		}
		array_unshift(
			$requests,
			array(
				'url'  => $url,
				'args' => $args,
				'time' => time(),
			)
		);
		update_option( 'e2e_http_requests', array_slice( $requests, 0, 100 ), false );

		foreach ( $stubs as $match => $stub ) {
			if ( false === strpos( $url, (string) $match ) ) {
				continue;
			}

			$status = (int) ( $stub['status'] ?? 200 );
			return array(
				'headers'  => (array) ( $stub['headers'] ?? array( 'content-type' => 'application/json' ) ),
				'body'     => is_string( $stub['body'] ?? null ) ? $stub['body'] : wp_json_encode( $stub['body'] ?? array() ),
				'response' => array(
					'code'    => $status,
					'message' => (string) ( $stub['message'] ?? 'E2E stub' ),
				),
				'cookies'  => array(),
				'filename' => null,
			);
		}

		return $response;
	},
	10,
	3
);
