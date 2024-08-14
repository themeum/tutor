<?php
/**
 * Helper class for handling magic ai functionalities
 *
 * @package Tutor\MagicAI
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\MagicAI;

use OpenAI;
use OpenAI\Client;
use Parsedown;
use RuntimeException;

/**
 * Helper class for openai related functionalities.
 *
 * @since 3.0.0
 */
final class Helper {

	/**
	 * OpenAI\Client instance
	 *
	 * @var OpenAI\Client | null
	 * @since 3.0.0
	 */
	private static $client = null;

	/**
	 * Get the instance of the OpenAI\Client
	 *
	 * @return OpenAI\Client
	 * @throws RuntimeException If openai api key is not found.
	 * @since 3.0.0
	 */
	public static function get_client() {
		if ( is_null( self::$client ) ) {
			$api_key = tutor_utils()->get_option( 'chatgpt_api_key' );

			if ( empty( $api_key ) ) {
				throw new RuntimeException( 'Missing openai api key, please add the api key into the settings.' );
			}

			self::$client = OpenAI::client( $api_key );
		}

		return self::$client;
	}

	/**
	 * Convert markdown text to html
	 *
	 * @param string $content The content that will be converted to html.
	 * @return string
	 * @since 3.0.0
	 */
	public static function markdown_to_html( string $content ) {
		$markdown = new Parsedown();
		$markdown->setSafeMode( true );

		return $markdown->text( $content );
	}
}
