<?php

namespace Ollyo\PaymentHub\Core\Support;

use Ollyo\PaymentHub\Contracts\Support\RequestContract;
use Ollyo\PaymentHub\Contracts\Support\ResponseContract;
use Ollyo\PaymentHub\Exceptions\HttpRequestException;

/**
 * Sends HTTP requests through the WordPress HTTP API.
 *
 * Accepts Guzzle-style options so existing request data can be passed as-is:
 *
 * - headers     (array)        Request headers.
 * - body        (string|array) Raw request body.
 * - json        (mixed)        Encoded as JSON; sets Content-Type to application/json.
 * - form_params (array)        Sent as application/x-www-form-urlencoded.
 * - query       (array)        Appended to the URL query string.
 * - auth        (array|string) [ user, pass ] for Basic auth, or a raw Authorization value.
 * - timeout     (int)          Timeout in seconds. Default 30.
 * - http_errors (bool)         Throw on 4xx/5xx responses. Default true.
 */
class Request implements RequestContract {

	/**
	 * The HTTP method.
	 *
	 * @var string
	 */
	protected $method;

	/**
	 * The request URL.
	 *
	 * @var string
	 */
	protected $url;

	/**
	 * The Guzzle-style request options.
	 *
	 * @var array
	 */
	protected $options;

	/**
	 * Create a new request instance.
	 *
	 * @param string $method  The HTTP method.
	 * @param string $url     The request URL.
	 * @param array  $options The request options.
	 */
	public function __construct( string $method, string $url, array $options = array() ) {
		$this->method  = strtoupper( $method );
		$this->url     = $url;
		$this->options = $options;
	}

	/**
	 * Create a request from the { method, url, options } object used by the gateways.
	 *
	 * @param object $request_data The request data object.
	 *
	 * @return static
	 */
	public static function from_object( object $request_data ) {
		return new static( $request_data->method, $request_data->url, (array) ( $request_data->options ?? array() ) );
	}

	/**
	 * Send a GET request.
	 *
	 * @param string $url     The request URL.
	 * @param array  $options The request options.
	 *
	 * @return ResponseContract
	 * @throws HttpRequestException
	 */
	public static function get( string $url, array $options = array() ): ResponseContract {
		return ( new static( 'GET', $url, $options ) )->send();
	}

	/**
	 * Send a POST request.
	 *
	 * @param string $url     The request URL.
	 * @param array  $options The request options.
	 *
	 * @return ResponseContract
	 * @throws HttpRequestException
	 */
	public static function post( string $url, array $options = array() ): ResponseContract {
		return ( new static( 'POST', $url, $options ) )->send();
	}

	/**
	 * Send a PUT request.
	 *
	 * @param string $url     The request URL.
	 * @param array  $options The request options.
	 *
	 * @return ResponseContract
	 * @throws HttpRequestException
	 */
	public static function put( string $url, array $options = array() ): ResponseContract {
		return ( new static( 'PUT', $url, $options ) )->send();
	}

	/**
	 * Send a PATCH request.
	 *
	 * @param string $url     The request URL.
	 * @param array  $options The request options.
	 *
	 * @return ResponseContract
	 * @throws HttpRequestException
	 */
	public static function patch( string $url, array $options = array() ): ResponseContract {
		return ( new static( 'PATCH', $url, $options ) )->send();
	}

	/**
	 * Send the request and decode the JSON response.
	 *
	 * Replacement for System::sendHttpRequest().
	 *
	 * @param object $request_data The { method, url, options } request data object.
	 * @param bool   $raw          Return the response object instead of the decoded body.
	 *
	 * @return mixed|ResponseContract
	 * @throws HttpRequestException
	 */
	public static function send_http_request( object $request_data, bool $raw = false ) {
		$response = static::from_object( $request_data )->send();

		return $raw ? $response : $response->json();
	}

	/**
	 * Send the request.
	 *
	 * @return ResponseContract
	 * @throws HttpRequestException On a transport error, or on a 4xx/5xx status unless http_errors is false.
	 */
	public function send(): ResponseContract {
		$raw = wp_remote_request( $this->build_url(), $this->build_args() );

		if ( is_wp_error( $raw ) ) {
			throw new HttpRequestException( esc_html( $raw->get_error_message() ) );
		}

		$response = new Response( $raw );
		$status   = $response->get_status_code();

		if ( $status >= 400 && ( $this->options['http_errors'] ?? true ) ) {
			throw new HttpRequestException(
				esc_html( sprintf( 'HTTP %d %s returned from %s', $status, $response->get_reason_phrase(), $this->url ) ),
				$status,
				$response
			);
		}

		return $response;
	}

	/**
	 * Get the HTTP method.
	 *
	 * @return string
	 */
	public function get_method(): string {
		return $this->method;
	}

	/**
	 * Get the request URL, without the query option applied.
	 *
	 * @return string
	 */
	public function get_url(): string {
		return $this->url;
	}

	/**
	 * Get the request options.
	 *
	 * @return array
	 */
	public function get_options(): array {
		return $this->options;
	}

	/**
	 * Build the final URL with the query option applied.
	 *
	 * @return string
	 */
	protected function build_url(): string {
		if ( empty( $this->options['query'] ) ) {
			return $this->url;
		}

		return add_query_arg( urlencode_deep( $this->options['query'] ), $this->url );
	}

	/**
	 * Convert the Guzzle-style options to wp_remote_request() arguments.
	 *
	 * @return array
	 */
	protected function build_args(): array {
		$options = $this->options;
		$headers = $options['headers'] ?? array();
		$body    = $options['body'] ?? null;

		if ( array_key_exists( 'json', $options ) ) {
			$body = wp_json_encode( $options['json'] );

			if ( ! $this->has_header( $headers, 'Content-Type' ) ) {
				$headers['Content-Type'] = 'application/json';
			}
		} elseif ( isset( $options['form_params'] ) ) {
			// WordPress encodes array bodies as application/x-www-form-urlencoded.
			$body = $options['form_params'];
		}

		if ( ! empty( $options['auth'] ) ) {
			$headers['Authorization'] = is_array( $options['auth'] )
				? 'Basic ' . base64_encode( $options['auth'][0] . ':' . ( $options['auth'][1] ?? '' ) ) // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
				: $options['auth'];
		}

		return array(
			'method'  => $this->method,
			'headers' => $headers,
			'body'    => $body,
			'timeout' => $options['timeout'] ?? 30,
		);
	}

	/**
	 * Determine if a header is set, ignoring case.
	 *
	 * @param array  $headers The headers.
	 * @param string $name    The header name.
	 *
	 * @return bool
	 */
	protected function has_header( array $headers, string $name ): bool {
		return in_array( strtolower( $name ), array_map( 'strtolower', array_keys( $headers ) ), true );
	}
}
