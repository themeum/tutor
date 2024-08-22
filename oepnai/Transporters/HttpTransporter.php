<?php
/**
 * Helper class for handling magic ai functionalities
 *
 * @package Tutor\MagicAI
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\OpenAI\Transporters;

use Tutor\OpenAI\Contracts\TransporterContract;

/**
 * The HTTP request transporter. Use the WP transporter by default.
 *
 * @since 3.0.0
 */
final class HttpTransporter implements TransporterContract {
	/**
	 * The base uri
	 *
	 * @var string|null
	 * @since 3.0.0
	 */
	private $base_uri = null;

	/**
	 * The request headers
	 *
	 * @var array<string, string>
	 * @since 3.0.0
	 */
	private $headers = array();

	/**
	 * The constructor method for the HttpTransporter
	 *
	 * @param string $base_uri The base uri.
	 * @param array  $headers The request headers.
	 *
	 * @since 3.0.0
	 */
	public function __construct( string $base_uri, array $headers, ) {
		$this->base_uri = $base_uri;
		$this->headers  = $headers;
	}

	/**
	 * Send the request to the requested endpoint
	 *
	 * @param string $endpoint The request endpoint.
	 * @param array  $payload The request payload.
	 * @return void
	 */
	public function send( string $endpoint, array $payload ) {
		// Send the request.
	}
}
