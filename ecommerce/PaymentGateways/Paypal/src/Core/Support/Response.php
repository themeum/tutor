<?php

namespace Ollyo\PaymentHub\Core\Support;

use Ollyo\PaymentHub\Contracts\Support\ResponseContract;

/**
 * Wraps the array returned by the WordPress HTTP API (wp_remote_request).
 *
 * @since 4.1.2
 */
class Response implements ResponseContract {

	/**
	 * The raw response array returned by wp_remote_request().
	 *
	 * @since 4.1.2
	 *
	 * @var array
	 */
	protected $raw;

	/**
	 * Cached response body with the UTF-8 BOM stripped.
	 *
	 * @since 4.1.2
	 *
	 * @var string|null
	 */
	protected $body;

	/**
	 * Create a new response instance.
	 *
	 * @since 4.1.2
	 *
	 * @param array $raw The raw response array returned by wp_remote_request().
	 */
	public function __construct( array $raw ) {
		$this->raw = $raw;
	}

	/**
	 * Get the HTTP status code.
	 *
	 * @since 4.1.2
	 *
	 * @return int
	 */
	public function get_status_code(): int {
		return (int) wp_remote_retrieve_response_code( $this->raw );
	}

	/**
	 * Get the HTTP reason phrase, e.g. "Not Found".
	 *
	 * @since 4.1.2
	 *
	 * @return string
	 */
	public function get_reason_phrase(): string {
		return (string) wp_remote_retrieve_response_message( $this->raw );
	}

	/**
	 * Determine if the status code is in the 2xx range.
	 *
	 * @since 4.1.2
	 *
	 * @return bool
	 */
	public function is_successful(): bool {
		$status = $this->get_status_code();

		return $status >= 200 && $status < 300;
	}

	/**
	 * Get all response headers as an associative array with lowercase keys.
	 *
	 * @since 4.1.2
	 *
	 * @return array
	 */
	public function get_headers(): array {
		$headers = wp_remote_retrieve_headers( $this->raw );

		if ( is_object( $headers ) && method_exists( $headers, 'getAll' ) ) {
			return $headers->getAll();
		}

		return is_array( $headers ) ? $headers : array();
	}

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
	public function get_header( string $name, $default = null ) {
		$value = wp_remote_retrieve_header( $this->raw, $name );

		return '' === $value ? $default : $value;
	}

	/**
	 * Get the response body with any UTF-8 BOM stripped.
	 *
	 * @since 4.1.2
	 *
	 * @return string
	 */
	public function get_body(): string {
		if ( is_null( $this->body ) ) {
			$body = (string) wp_remote_retrieve_body( $this->raw );

			if ( 0 === strncmp( $body, "\xEF\xBB\xBF", 3 ) ) {
				$body = substr( $body, 3 );
			}

			$this->body = $body;
		}

		return $this->body;
	}

	/**
	 * Decode the JSON response body.
	 *
	 * @since 4.1.2
	 *
	 * @param bool $assoc Return associative arrays instead of objects.
	 *
	 * @return mixed Null if the body is empty or not valid JSON.
	 */
	public function json( bool $assoc = false ) {
		$body = $this->get_body();

		if ( '' === $body ) {
			return null;
		}

		return json_decode( $body, $assoc );
	}

	/**
	 * Get the raw response array returned by wp_remote_request().
	 *
	 * @since 4.1.2
	 *
	 * @return array
	 */
	public function get_raw(): array {
		return $this->raw;
	}

	/**
	 * Get the response body as a string.
	 *
	 * @since 4.1.2
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->get_body();
	}
}
