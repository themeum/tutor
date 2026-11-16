<?php
/**
 * Bridge between Tutor REST responses and MCP execute payloads.
 *
 * @package Tutor\Mcp
 * @author  Themeum <support@themeum.com>
 * @link    https://themeum.com
 * @since   4.3.0
 */

namespace Tutor\Mcp\Support;

use TUTOR\RestAuth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Unwrap Tutor REST response envelopes and run controllers in MCP auth context.
 *
 * @since 4.3.0
 */
class RestBridge {

	/**
	 * Run a REST controller callback with MCP privileged auth context.
	 *
	 * Avoids guest-like redaction that depends on Tutor JWT while keeping the
	 * current WordPress user as identity. Call only after Permissions checks.
	 *
	 * @since 4.3.0
	 *
	 * @param callable $callback Callback that returns a REST response or value.
	 * @return mixed
	 */
	public static function call( callable $callback ) {
		RestAuth::enter_mcp_context();
		try {
			return call_user_func( $callback );
		} finally {
			RestAuth::leave_mcp_context();
		}
	}

	/**
	 * Call a REST controller and unwrap its data envelope.
	 *
	 * @since 4.3.0
	 *
	 * @param callable $callback Callback returning \WP_REST_Response|\WP_Error.
	 * @return array|\WP_Error
	 */
	public static function unwrap_call( callable $callback ) {
		return self::unwrap( self::call( $callback ) );
	}

	/**
	 * Unwrap Tutor REST response envelope to its data payload.
	 *
	 * @since 4.3.0
	 *
	 * @param \WP_REST_Response|\WP_Error $response REST response.
	 * @return array|\WP_Error
	 */
	public static function unwrap( $response ) {
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( ! $response instanceof \WP_REST_Response ) {
			return array();
		}

		$payload = $response->get_data();
		if ( ! is_array( $payload ) ) {
			return array();
		}

		$data = $payload['data'] ?? null;
		if ( null === $data || '' === $data ) {
			return array();
		}

		$decoded = json_decode( wp_json_encode( $data ), true );
		return is_array( $decoded ) ? $decoded : array();
	}
}
