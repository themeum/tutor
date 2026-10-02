<?php
namespace Ollyo\PaymentHub\Contracts\Support;

use Ollyo\PaymentHub\Exceptions\HttpRequestException;

/**
 * Contract for an HTTP request sent by the payment gateways.
 *
 * @since 4.1.2
 */
interface RequestContract {

	/**
	 * Send the request.
	 *
	 * @since 4.1.2
	 *
	 * @return ResponseContract
	 * @throws HttpRequestException On a transport error, or on a 4xx/5xx status unless http_errors is false.
	 */
	public function send(): ResponseContract;

	/**
	 * Get the HTTP method in uppercase.
	 *
	 * @since 4.1.2
	 *
	 * @return string
	 */
	public function get_method(): string;

	/**
	 * Get the request URL, without the query option applied.
	 *
	 * @since 4.1.2
	 *
	 * @return string
	 */
	public function get_url(): string;

	/**
	 * Get the request options.
	 *
	 * @since 4.1.2
	 *
	 * @return array
	 */
	public function get_options(): array;
}
