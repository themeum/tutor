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
	 * @throws RuntimeException
	 * @since 3.0.0
	 */
	public static function get_client() {
		if ( is_null( static::$client ) ) {
			$api_key = tutor_utils()->get_option( 'chatgpt_api_key' );

			if ( empty( $api_key ) ) {
				throw new RuntimeException(
					__('Missing openai api key, please add the api key into the settings', 'tutor')
				);
			}

			static::$client = OpenAI::client( $api_key );
		}

		return static::$client;
	}

	/**
	 * Convert markdown text to html
	 *
	 * @param string $content
	 * @return string
	 * @since 3.0.0
	 */
	public static function markdown_to_html( string $content ) {
		$markdown = new Parsedown();
		$markdown->setSafeMode(true);

		return $markdown->text( $content );
	}
}