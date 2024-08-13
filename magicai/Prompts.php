<?php
/**
 * Helper class for creating the AI prompts for text generation.
 *
 * @package Tutor\MagicAI
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */
namespace Tutor\MagicAI;

use TUTOR\Input;
use Tutor\MagicAI\Constants\Models;

/**
 * Helper class for generating AI prompts for text generation
 *
 * @since 3.0.0
 */
final class Prompts {
	/**
	 * Create the system message for generating text content using tone, format, language, etc.
	 *
	 * @return string
	 * @since	3.0.0
	 */
	private static function create_system_message() {
		$input = array(
			'tone' => Input::post( 'tone', 'formal' ),
			'format' => Input::post( 'format', 'essay' ),
			'language' => Input::post( 'language', 'english' ),
			'characters' => Input::post( 'characters', 250 ),
			'is_html' => Input::post( 'is_html', false, Input::TYPE_BOOL ),
		);

		$system_content = "You are a friendly and helpful assistant. You will be provided a prompt, and your task to generate a {format}. The content will be in the {language} language and has a {tone} tone. Make sure the content will not exceed the length of {characters} characters.";

		if ( !$input['is_html'] ) {
			$system_content .= ' Please respond with plain text only. Do not use markdown or HTML.';
		}

		foreach ( $input as $key => $value ) {
			$system_content = str_replace( "{$key}", $value, $system_content );
		}

		return $system_content;
	}

	/**
	 * Prepare the input array for generating text content from the request prompt.
	 *
	 * @return array
	 * @since 3.0.0
	 */
	public static function prepare_text_generation_input() {
		$prompt = Input::post( 'prompt', '' );

		return array(
			'model' => Models::GPT_4O,
			'messages' => array(
					array( 'role' => 'system', 'content' => static::create_system_message() ),
					array( 'role' => 'user', 'content' => $prompt )
			),
			'temperature' => 0.7
		);
	}

	/**
	 * Prepare the input array for translating content to a specific language.
	 *
	 * @return array
	 * @since 3.0.0
	 */
	public static function prepare_translation_input() {
		$content = Input::post( 'content', '' );
		$language = Input::post( 'language', '' );
		$is_html = Input::post( 'is_html', false, Input::TYPE_BOOL );
		$system_content = 'Your task is to translate the provided text into {language}. Identify the original language if needed and ensure the translation accurately conveys the meaning of the original content.';
		$system_content = str_replace( '{language}', $language, $system_content );

		if ( !$is_html ) {
			$system_content .= ' Please respond with plain text only. Do not use markdown or HTML.';
		}

		return array(
			'model' => Models::GPT_4O,
			'messages' => array(
					array( 'role' => 'system', 'content' => $system_content ),
					array( 'role' => 'user', 'content' => $content )
			),
			'temperature' => 0.7
		);
	}

	/**
	 * Prepare the input array for rephrasing content
	 *
	 * @return array
	 * @since 3.0.0
	 */
	public static function prepare_rephrase_input() {
		$content = Input::post( 'content', '' );
		$is_html = Input::post( 'is_html', false, Input::TYPE_BOOL );
		$system_content = 'Your task is to rephrase any text content provided to you, ensuring that the original meaning is preserved while expressing it differently.';

		if ( !$is_html ) {
			$system_content .= ' Please respond with plain text only. Do not use markdown or HTML.';
		}

		return array(
			'model' => Models::GPT_4O,
			'messages' => array(
					array( 'role' => 'system', 'content' => $system_content ),
					array( 'role' => 'user', 'content' => $content )
			),
			'temperature' => 0.7
		);
	}

	/**
	 * Prepare the input array for making the content shorten.
	 *
	 * @return array
	 * @since 3.0.0
	 */
	public static function prepare_make_shorter_input() {
		$content = Input::post( 'content', '' );
		$is_html = Input::post( 'is_html', false, Input::TYPE_BOOL );
		$system_content = 'Your task is to condense the provided text, retaining the key points and meaning while making the content as concise as possible.';

		if ( !$is_html ) {
			$system_content .= ' Please respond with plain text only. Do not use markdown or HTML.';
		}

		return array(
			'model' => Models::GPT_4O,
			'messages' => array(
					array( 'role' => 'system', 'content' => $system_content ),
					array( 'role' => 'user', 'content' => $content )
			),
			'temperature' => 0.7
		);
	}

	/**
	 * Prepare the input array for changing the tone of the content.
	 *
	 * @return array
	 * @since 3.0.0
	 */
	public static function prepare_change_tone_input() {
		$content = Input::post( 'content', '' );
		$tone = Input::post( 'tone', '' );
		$is_html = Input::post( 'is_html', false, Input::TYPE_BOOL );
		$system_content = "Your task is to change the tone of the provided text to match the specified style, which is {tone}. Ensure that the content's meaning remains consistent while reflecting this new tone.";
		$system_content = str_replace( '{tone}', $tone, $system_content );

		if ( !$is_html ) {
			$system_content .= ' Please respond with plain text only. Do not use markdown or HTML.';
		}

		return array(
			'model' => Models::GPT_4O,
			'messages' => array(
					array( 'role' => 'system', 'content' => $system_content ),
					array( 'role' => 'user', 'content' => $content )
			),
			'temperature' => 0.7
		);
	}
	
	/**
	 * Prepare the input array for converting the content into bullet points.
	 *
	 * @return array
	 * @since 3.0.0
	 */
	public static function prepare_write_as_bullets_input() {
		$content = Input::post( 'content', '' );
		$is_html = Input::post( 'is_html', false, Input::TYPE_BOOL );
		$system_content = "Your task is to rewrite the provided text as bullet points. Ensure that each point is clear and concise while preserving the original meaning.";

		if ( !$is_html ) {
			$system_content .= ' Please respond with plain text only. Do not use markdown or HTML.';
		}

		return array(
			'model' => Models::GPT_4O,
			'messages' => array(
					array( 'role' => 'system', 'content' => $system_content ),
					array( 'role' => 'user', 'content' => $content )
			),
			'temperature' => 0.7
		);
	}

	/**
	 * Prepare the input array for making the content larger.
	 *
	 * @return array
	 * @since 3.0.0
	 */
	public static function prepare_make_longer_input() {
		$content = Input::post( 'content', '' );
		$is_html = Input::post( 'is_html', false, Input::TYPE_BOOL );
		$system_content = "Your task is to expand the provided text, adding more detail and depth while maintaining the original meaning and intent.";

		if ( !$is_html ) {
			$system_content .= ' Please respond with plain text only. Do not use markdown or HTML.';
		}

		return array(
			'model' => Models::GPT_4O,
			'messages' => array(
					array( 'role' => 'system', 'content' => $system_content ),
					array( 'role' => 'user', 'content' => $content )
			),
			'temperature' => 0.7
		);
	}

	/**
	 * Prepare the input array for simplifying the language of the generated content.
	 *
	 * @return array
	 * @since 3.0.0
	 */
	public static function prepare_simplify_language_input() {
		$content = Input::post( 'content', '' );
		$is_html = Input::post( 'is_html', false, Input::TYPE_BOOL );
		$system_content = "Your task is to simplify the language of the provided text, making it easier to understand while preserving the original meaning.";

		if ( !$is_html ) {
			$system_content .= ' Please respond with plain text only. Do not use markdown or HTML.';
		}

		return array(
			'model' => Models::GPT_4O,
			'messages' => array(
					array( 'role' => 'system', 'content' => $system_content ),
					array( 'role' => 'user', 'content' => $content )
			),
			'temperature' => 0.7
		);
	}
}