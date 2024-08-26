<?php
/**
 * Helper class for handling magic ai functionalities
 *
 * @package Tutor\MagicAI
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\OpenAI;

use Tutor\OpenAI\Client;
use Tutor\OpenAI\Constants\ContentTypes;
use Tutor\OpenAI\Support\BaseUri;
use Tutor\OpenAI\Support\Header;
use Tutor\OpenAI\Transporters\HttpTransporter;

/**
 * The factory class for making a openai client.
 *
 * @since 3.0.0
 */
final class Factory {
	/**
	 * The openai API key.
	 *
	 * @var string|null
	 * @since 3.0.0
	 */
	private $api_key = null;

	/**
	 * The openai organization for the request.
	 *
	 * @var string|null
	 * @since 3.0.0
	 */
	private $organization = null;

	/**
	 * The request headers
	 *
	 * @var array<string, string>
	 * @since 3.0.0
	 */
	private array $headers = array();

	/**
	 * The openai request base uri.
	 *
	 * @var string|null
	 * @since 3.0.0
	 */
	private $base_uri = null;

	/**
	 * Set the API key for the openai requests.
	 *
	 * @param string $api_key The openai api key.
	 * @return self
	 * @since 3.0.0
	 */
	public function with_api_key( string $api_key ) {
		$this->api_key = trim( $api_key );

		return $this;
	}

	/**
	 * Undocumented function
	 *
	 * @param string|null $organization The request organization.
	 * @return self
	 * @since 3.0.0
	 */
	public function with_organization( $organization ) {
		$this->organization = $organization;

		return $this;
	}

	/**
	 * Set the base uri of the openai request.
	 *
	 * @param string $base_uri The base uri.
	 * @return self
	 * @since 3.0.0
	 */
	public function with_base_uri( string $base_uri ) {
		$this->base_uri = $base_uri;

		return $this;
	}

	/**
	 * Set HTTP header.
	 *
	 * @param string $name The header name.
	 * @param string $value The header value.
	 * @return self
	 * @since 3.0.0
	 */
	public function with_http_header( string $name, string $value ) {
		$this->headers[ $name ] = $value;

		return $this;
	}

	/**
	 * Make the openai client instance
	 *
	 * @return Client
	 * @since 3.0.0
	 */
	public function make() {
		$base_uri = BaseUri::from( $this->base_uri ?? 'api.openai.com/v1' );
		$headers  = Header::create();

		$headers->with_content_type( ContentTypes::JSON );

		if ( ! is_null( $this->api_key ) ) {
			$headers->with_authorization( $this->api_key );
		}

		if ( ! is_null( $this->organization ) ) {
			$headers->with_organization( $this->organization );
		}

		if ( ! empty( $this->headers ) ) {
			foreach ( $this->headers as $name => $value ) {
				$headers->with_custom_header( $name, $value );
			}
		}

		return new Client(
			new HttpTransporter( $base_uri, $headers )
		);
	}
}
