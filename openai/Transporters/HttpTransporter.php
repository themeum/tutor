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
use Tutor\OpenAI\Http\Response;
use Tutor\OpenAI\Support\Header;
use Tutor\OpenAI\Support\Payload;

/**
 * The HTTP request transporter. Use the WP transporter by default.
 *
 * @since 3.0.0
 */
class HttpTransporter implements TransporterContract {
	/**
	 * The base request uri.
	 *
	 * @var string|null
	 * @since 3.0.0
	 */
	private $base_uri = null;

	/**
	 * The request headers
	 *
	 * @var Header
	 * @since 3.0.0
	 */
	private $headers = null;

	/**
	 * The constructor method for the HttpTransporter
	 *
	 * @param string $base_uri The base uri.
	 * @param Header $headers The request headers.
	 *
	 * @since 3.0.0
	 */
	public function __construct( string $base_uri, Header $headers ) {
		$this->base_uri = $base_uri;
		$this->headers  = $headers;
	}

	/**
	 * Send the request to the requested endpoint
	 *
	 * @param Payload $payload The route instance.
	 * @return Response
	 */
	public function request( Payload $payload ) {
		return Response::create(
			$payload->build( $this->base_uri, $this->headers )
		);
	}
}
