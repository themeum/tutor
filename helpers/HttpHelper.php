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
	 * HTTP methods constants
	 *
	 * @var string
	 */
	const METHOD_GET    = 'GET';
	const METHOD_POST   = 'POST';
	const METHOD_PUT    = 'PUT';
	const METHOD_PATCH  = 'PATCH';
	const METHOD_DELETE = 'DELETE';

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
	 * @since 4.1.2 param $args added
	 *
	 * @param string $url     Request URL.
	 * @param array  $data    Request body. Default empty array.
	 * @param array  $headers Request headers. Default empty array.
	 * @param array  $args    Additional arguments passed through to send(),
	 *                        merged over the defaults. Default empty array.
	 *
	 * @return self
	 */
	public static function get( $url, $data = array(), $headers = array(), $args = array() ) {

		if ( ! empty( $data ) ) {
			$url = add_query_arg( $data, $url );
		}

		$args = array_merge(
			array(
				'headers' => $headers,
				'method'  => self::METHOD_GET,
			),
			$args
		);

		return self::send( $url, $args );
	}

	/**
	 * Make HTTP POST request.
	 *
	 * @since 4.1.2 param $args added
	 *
	 * @param string       $url     Request URL.
	 * @param array|string $data    Request body. An array is form-encoded; a string (e.g. JSON) is sent as-is. Default empty array.
	 * @param array        $headers Request headers. Default empty array.
	 * @param array        $args    Additional arguments passed through to send(),
	 *                              merged over the defaults. Default empty array.
	 *
	 * @return self
	 */
	public static function post( $url, $data = array(), $headers = array(), $args = array() ) {

		$args = array_merge(
			array(
				'body'    => $data,
				'headers' => $headers,
				'method'  => self::METHOD_POST,
			),
			$args
		);

		return self::send( $url, $args );
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

	/**
	 * Sends an HTTP request and parses the response.
	 *
	 * @since 4.1.2
	 *
	 * @param string $url  Request URL.
	 * @param array  $args Optional request arguments.
	 *
	 * @return self Parsed HTTP response instance.
	 */
	private static function send( $url, $args = array() ) {

		$response = wp_remote_request(
			$url,
			$args
		);

		$self = new self();
		$self->parse_response( $response );

		return $self;
	}

	/**
	 * Make HTTP PUT request.
	 *
	 * @since 4.1.2
	 *
	 * @param string       $url     Request URL.
	 * @param array|string $data    Request body. An array is form-encoded; a string (e.g. JSON) is sent as-is. Default empty array.
	 * @param array        $headers Request headers. Default empty array.
	 * @param array        $args    Additional arguments passed through to send(),
	 *                              merged over the defaults. Default empty array.
	 *
	 * @return self
	 */
	public static function put( $url, $data = array(), $headers = array(), $args = array() ) {

		$args = array_merge(
			array(
				'body'    => $data,
				'headers' => $headers,
				'method'  => self::METHOD_PUT,
			),
			$args
		);

		return self::send( $url, $args );
	}

	/**
	 * Make HTTP PATCH request.
	 *
	 * @since 4.1.2
	 *
	 * @param string       $url     Request URL.
	 * @param array|string $data    Request body. An array is form-encoded; a string (e.g. JSON) is sent as-is. Default empty array.
	 * @param array        $headers Request headers. Default empty array.
	 * @param array        $args    Additional arguments passed through to send(),
	 *                              merged over the defaults. Default empty array.
	 *
	 * @return self
	 */
	public static function patch( $url, $data = array(), $headers = array(), $args = array() ) {

		$args = array_merge(
			array(
				'body'    => $data,
				'headers' => $headers,
				'method'  => self::METHOD_PATCH,
			),
			$args
		);

		return self::send( $url, $args );
	}

	/**
	 * Make HTTP DELETE request.
	 *
	 * @since 4.1.2
	 *
	 * @param string       $url     Request URL.
	 * @param array|string $data    Request body. An array is form-encoded; a string (e.g. JSON) is sent as-is. Default empty array.
	 * @param array        $headers Request headers. Default empty array.
	 * @param array        $args    Additional arguments passed through to send(),
	 *                              merged over the defaults. Default empty array.
	 *
	 * @return self
	 */
	public static function delete( $url, $data = array(), $headers = array(), $args = array() ) {

		$args = array_merge(
			array(
				'body'    => $data,
				'headers' => $headers,
				'method'  => self::METHOD_DELETE,
			),
			$args
		);

		return self::send( $url, $args );
	}
}
