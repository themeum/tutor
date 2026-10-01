<?php
namespace Ollyo\PaymentHub\Contracts\Support;

use Ollyo\PaymentHub\Exceptions\HttpRequestException;

interface RequestContract {

	/**
	 * Send the request.
	 *
	 * @return ResponseContract
	 * @throws HttpRequestException On a transport error, or on a 4xx/5xx status unless http_errors is false.
	 */
	public function send(): ResponseContract;

	/**
	 * Get the HTTP method in uppercase.
	 *
	 * @return string
	 */
	public function get_method(): string;

	/**
	 * Get the request URL, without the query option applied.
	 *
	 * @return string
	 */
	public function get_url(): string;

	/**
	 * Get the request options.
	 *
	 * @return array
	 */
	public function get_options(): array;
}
