<?php
namespace Ollyo\PaymentHub\Contracts\Support;

/**
 * Contract for an HTTP response received by the payment gateways.
 *
 * @since 4.1.2
 */
interface ResponseContract {

	/**
	 * Get the HTTP status code.
	 *
	 * @since 4.1.2
	 *
	 * @return int
	 */
	public function get_status_code(): int;

	/**
	 * Get the HTTP reason phrase, e.g. "Not Found".
	 *
	 * @since 4.1.2
	 *
	 * @return string
	 */
	public function get_reason_phrase(): string;

	/**
	 * Determine if the status code is in the 2xx range.
	 *
	 * @since 4.1.2
	 *
	 * @return bool
	 */
	public function is_successful(): bool;

	/**
	 * Get all response headers as an associative array with lowercase keys.
	 *
	 * @since 4.1.2
	 *
	 * @return array
	 */
	public function get_headers(): array;

	/**
	 * Get a single response header (case-insensitive).
	 *
	 * @since 4.1.2
	 *
	 * @param string $name    The header name.
	 * @param mixed  $default The value to return when the header is missing.
	 *
	 * @return string|array|mixed
	 */
	public function get_header( string $name, $default = null );

	/**
	 * Get the response body.
	 *
	 * @since 4.1.2
	 *
	 * @return string
	 */
	public function get_body(): string;

	/**
	 * Decode the JSON response body.
	 *
	 * @since 4.1.2
	 *
	 * @param bool $assoc Return associative arrays instead of objects.
	 *
	 * @return mixed Null if the body is empty or not valid JSON.
	 */
	public function json( bool $assoc = false );

	/**
	 * Get the response body as a string.
	 *
	 * @since 4.1.2
	 *
	 * @return string
	 */
	public function __toString();
}
