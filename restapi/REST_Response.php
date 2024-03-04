<?php
/**
 * Ensure REST response
 *
 * @package Tutor\RestAPI
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.7.1
 */

namespace TUTOR;

use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait REST_Response {

	/**
	 * Send response
	 *
	 * @since 1.7.1
	 *
	 * @param array $response The response data.
	 *
	 * @return WP_REST_Response
	 */
	public static function send( array $response ) {
		$response = new WP_REST_Response( $response );
		return apply_filters( 'tutor_rest_response', $response );
	}
}
