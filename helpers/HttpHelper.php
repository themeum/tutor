<?php
/**
 * Helper class to manager HTTP request.
 *
 * @package Tutor\Helper
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\Helpers;

/**
 * HttpHelper class
 *
 * @since 3.0.0
 */
class HttpHelper {
	/**
	 * 200 serial HTTP status code constants
	 */
	const STATUS_OK       = 200;
	const STATUS_CREATED  = 201;
	const STATUS_ACCEPTED = 202;

	/**
	 * 400 serial HTTP status code constants
	 */
	const STATUS_BAD_REQUEST          = 400;
	const STATUS_UNAUTHORIZED         = 401;
	const STATUS_FORBIDDEN            = 403;
	const STATUS_NOT_FOUND            = 404;
	const STATUS_METHOD_NOT_ALLOWED   = 405;
	const STATUS_TOO_MANY_REQUESTS    = 429;
	const STATUS_UNPROCESSABLE_ENTITY = 422;

	/**
	 * 500 serial HTTP status code constants
	 */
	const STATUS_INTERNAL_SERVER_ERROR = 500;
	const STATUS_SERVICE_UNAVAILABLE   = 503;
	const STATUS_BAD_GATEWAY           = 502;
	const STATUS_GATEWAY_TIMEOUT       = 504;

	/**
	 * Response body
	 *
	 * @var mixed
	 */
	private $body;

	/**
	 * Response headers
	 *
	 * @var mixed
	 */
	private $headers;

	/**
	 * Response status code
	 *
	 * @var int
	 */
	private $status_code;

	/**
	 * Hold WP error for request.
	 *
	 * @var \WP_Error
	 */
	private $wp_error;

	/**
	 * Parse response from HTTP response.
	 *
	 * @param mixed $response response of request.
	 *
	 * @return void
	 */
	private function parse_response( $response ) {
		if ( is_wp_error( $response ) ) {
			$this->wp_error = $response;
		} else {
			$this->body        = wp_remote_retrieve_body( $response );
			$this->headers     = wp_remote_retrieve_headers( $response );
			$this->status_code = wp_remote_retrieve_response_code( $response );
		}
	}

	/**
	 * Make HTTP GET request.
	 *
	 * @param string $url     The URL for the request.
	 * @param array  $data    Optional. The data to include in the request (added to the URL as query parameters).
	 * @param array  $headers Optional. Additional headers for the request.
	 *
	 * @return self
	 */
	public static function get( $url, $data = array(), $headers = array() ) {
		$url_with_params = add_query_arg( $data, $url );

		$response = wp_remote_get( $url_with_params, array( 'headers' => $headers ) );

		$self = new self();
		$self->parse_response( $response );

		return $self;
	}

	/**
	 * Make HTTP POST request.
	 *
	 * @param string $url     The URL for the request.
	 * @param array  $data    Optional. The data to include in the request body.
	 * @param array  $headers Optional. Additional headers for the request.
	 *
	 * @return self
	 */
	public static function post( $url, $data = array(), $headers = array() ) {
		$response = wp_remote_post(
			$url,
			array(
				'body'    => $data,
				'headers' => $headers,
			)
		);

		$self = new self();
		$self->parse_response( $response );

		return $self;
	}

	/**
	 * Get body
	 *
	 * @return mixed
	 */
	public function get_body() {
		return $this->body;
	}

	/**
	 * Get body data as JSON
	 *
	 * @return mixed
	 */
	public function get_json() {
		return json_decode( $this->body );
	}

	/**
	 * Get headers
	 *
	 * @return mixed
	 */
	public function get_headers() {
		return $this->headers;
	}

	/**
	 * Get status code.
	 *
	 * @return int
	 */
	public function get_status_code() {
		return $this->status_code;
	}

	/**
	 * Check any error occur.
	 *
	 * @return boolean
	 */
	public function has_error() {
		return ! is_null( $this->wp_error );
	}

	/**
	 * Get error message.
	 *
	 * @return mixed
	 */
	public function get_error_message() {
		return $this->wp_error->get_error_message();
	}
}
