<?php
/**
 * HTTP client for payment gateway API requests.
 *
 * @package Tutor\Ecommerce
 * @author Themeum
 * @link https://themeum.com
 * @since 4.2.0
 */

namespace Tutor\PaymentGateways;

defined( 'ABSPATH' ) || exit;

use Ollyo\PaymentHub\Exceptions\HttpRequestException;
use Tutor\Helpers\HttpHelper;

/**
 * Sends payment gateway API requests through HttpHelper.
 *
 * Replaces the Guzzle-based Ollyo\PaymentHub\Core\Support\System::sendHttpRequest().
 * Unlike HttpHelper, it throws HttpRequestException on transport errors and
 * 4xx/5xx responses so gateways can handle failures in one catch block.
 *
 * @since 4.2.0
 */
class Http {
	/**
	 * Sends an HTTP request using the specified method and options.
	 *
	 * The request data is an array or object with:
	 *
	 * - url     (string) Request URL.
	 * - method  (string) Optional. GET, POST, PUT, PATCH or DELETE, case-insensitive.
	 *                    Read from `options.method` first, then this key. Default GET.
	 * - options (array)  Optional. `headers` (array), `body` (array|string) and `method`.
	 *                    For GET, an array `body` is sent as query parameters.
	 *
	 * @since 4.2.0 Moved from System::sendHttpRequest() and sends the request through HttpHelper instead of Guzzle.
	 *
	 * @param array|object $request_data       Request data with url, optional method, and options.
	 * @param bool         $return_raw_payload Whether to return the HttpHelper response
	 *                                         instead of the JSON-decoded body. Default false.
	 * @param array        $args Optional. Additional arguments passed through to HttpHelper methods.
	 *
	 * @return object|array|null|HttpHelper The decoded JSON response body, or the HttpHelper
	 *                                      response when $return_raw_payload is true.
	 *
	 * @throws \InvalidArgumentException If the HTTP method is not supported.
	 * @throws HttpRequestException      On a transport error or a 4xx/5xx response.
	 */
	public static function send( $request_data, $return_raw_payload = false, $args = array() ) {
		$request_data = (object) $request_data;
		$url          = $request_data->url;
		$options      = (array) ( $request_data->options ?? array() );
		$method       = strtoupper( $options['method'] ?? $request_data->method ?? HttpHelper::METHOD_GET );
		$headers      = $options['headers'] ?? array();
		$body         = $options['body'] ?? array();

		unset( $options['method'] );

		switch ( $method ) {
			case HttpHelper::METHOD_GET:
				$response = HttpHelper::get( $url, $body, $headers, $args );
				break;

			case HttpHelper::METHOD_POST:
				$response = HttpHelper::post( $url, $body, $headers, $args );
				break;

			case HttpHelper::METHOD_PUT:
				$response = HttpHelper::put( $url, $body, $headers, $args );
				break;

			case HttpHelper::METHOD_PATCH:
				$response = HttpHelper::patch( $url, $body, $headers, $args );
				break;

			case HttpHelper::METHOD_DELETE:
				$response = HttpHelper::delete( $url, $body, $headers, $args );
				break;

			default:
				$error_message = sprintf(
					/* translators: %s: HTTP method name */
					__( 'Unsupported HTTP method: %s', 'tutor' ),
					$method
				);
				throw new \InvalidArgumentException( esc_html( $error_message ) );
		}

		// Transport failure (DNS, timeout, TLS): there is no response to attach.
		if ( $response->has_error() ) {
			throw new HttpRequestException( esc_html( $response->get_error_message() ) );
		}

		$status = (int) $response->get_status_code();

		// Attach the response so callers can read the gateway's error body.
		if ( $status >= 400 ) {
			throw new HttpRequestException( esc_html( sprintf( 'HTTP %d returned from %s', $status, $url ) ), $status, $response ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		if ( $return_raw_payload ) {
			return $response;
		}

		return $response->get_json();
	}
}
