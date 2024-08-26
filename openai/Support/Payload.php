<?php
/**
 * Helper class for handling magic ai functionalities
 *
 * @package Tutor\MagicAI
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\OpenAI\Support;

use Tutor\OpenAI\Constants\ContentTypes;
use Tutor\OpenAI\Http\Request;

/**
 * Create a resource payload class.
 *
 * @since 3.0.0
 */
final class Payload {
	/**
	 * The resource uri.
	 *
	 * @var string
	 * @since 3.0.0
	 */
	private $uri = null;

	/**
	 * The request parameters.
	 *
	 * @var array
	 * @since 3.0.0
	 */
	private array $parameters = array();

	/**
	 * The request content type.
	 *
	 * @var string|null
	 * @since 3.0.0
	 */
	private $content_type = null;

	/**
	 * The constructor method for creating a route.
	 *
	 * @param string $resource_uri The resource uri.
	 * @param array  $parameters The request parameters.
	 * @param string $content_type The request content type header.
	 */
	private function __construct( string $resource_uri, array $parameters, string $content_type ) {
		$this->uri          = trim( trim( $resource_uri ), '/' );
		$this->parameters   = $parameters;
		$this->content_type = $content_type;
	}

	/**
	 * Get the content type of the payload.
	 *
	 * @return string
	 * @since 3.0.0
	 */
	public function get_content_type() {
		return $this->content_type;
	}

	/**
	 * Create the resource route
	 *
	 * @param string $resource_uri The resource uri.
	 * @param array  $parameters The request parameters.
	 * @param string $content_type The request content type.
	 * @return self
	 */
	public static function create( string $resource_uri, array $parameters, string $content_type ) {
		return new self( $resource_uri, $parameters, $content_type );
	}

	/**
	 * Create the post request payload.
	 *
	 * @param string $uri The resource uri.
	 * @param array  $parameters The request parameters.
	 * @return self
	 */
	public static function post( string $uri, array $parameters ) {
		return self::create( $uri, $parameters, ContentTypes::JSON );
	}

	/**
	 * Create the post request payload.
	 *
	 * @param string $uri The resource uri.
	 * @param array  $parameters The request parameters.
	 * @return self
	 */
	public static function multipart( string $uri, array $parameters ) {
		return self::create( $uri, $parameters, ContentTypes::MULTIPART );
	}

	/**
	 * Build the route for making a request.
	 *
	 * @param string $base_uri The request base uri.
	 * @param Header $headers The request headers.
	 * @return Request
	 * @since 3.0.0
	 */
	public function build( string $base_uri, Header $headers ) {
		$url          = $base_uri . $this->uri;
		$content_type = $this->content_type;

		if ( ContentTypes::MULTIPART === $content_type ) {
			$multipart = MultipartFormData::create( $this->parameters );
			$headers->with_content_type( $multipart->content_type_with_boundary() );
			$data = $multipart->build();
			$headers->with_custom_header( 'Content-Length', strlen( $data ) );
		} else {
			$headers->with_content_type( ContentTypes::JSON );
			$data = json_encode( $this->parameters, JSON_THROW_ON_ERROR );
		}

		return Request::post( $url, $data, $headers->to_array() );
	}
}
