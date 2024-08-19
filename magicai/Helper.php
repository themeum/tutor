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
use Tutor\MagicAI\Constants\Models;

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

	/**
	 * Create the openai chat input options.
	 *
	 * @param array $messages The chat messages.
	 * @param array $options Optional options for overwriting the model, temperature etc.
	 * @return array
	 * @since 3.0.0
	 */
	public static function create_openai_chat_input( array $messages, array $options = array() ) {
		$default_options = array(
			'model'       => Models::GPT_4O,
			'temperature' => 0.7,
		);

		$options             = array_merge( $default_options, $options );
		$options['messages'] = $messages;

		return $options;
	}

	/**
	 * Check if a content is a valid JSON string or not.
	 *
	 * @param string $content The string content to check.
	 * @return boolean
	 */
	public static function is_valid_json( string $content ) {
		json_decode( $content );
		return json_last_error() === JSON_ERROR_NONE;
	}
}
