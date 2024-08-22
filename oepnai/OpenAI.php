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

use Tutor\OpenAI\Factory;

/**
 * The root class for making openai client
 */
final class OpenAI {
	/**
	 * Create the openai client for making request.
	 *
	 * @param string $api_key The api key for the openai.
	 * @param string $organization The organization value.
	 * @return OpenAI\Client
	 * @since 3.0.0
	 */
	public static function client( string $api_key, string $organization = null ) {
		return self::factory()
			->with_api_key( $api_key )
			->with_organization( $organization )
			->with_base_uri( 'api.openai.com/v1' )
			->with_http_header( 'OpenAI-Beta', 'assistants=v2' )
			->make();
	}

	/**
	 * The application factory class for instantiating the client.
	 *
	 * @return Factory
	 * @since 3.0.0
	 */
	public static function factory() {
		return new Factory();
	}
}
