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
	 * @since   3.0.0
	 */
	private static function create_system_message() {
		$input = array(
			'tone'       => Input::post( 'tone', 'formal' ),
			'format'     => Input::post( 'format', 'essay' ),
			'language'   => Input::post( 'language', 'english' ),
			'characters' => Input::post( 'characters', 250 ),
			'is_html'    => Input::post( 'is_html', false, Input::TYPE_BOOL ),
		);

		$system_content = 'You are a friendly and helpful assistant. You will be provided a prompt, and your task to generate a {format}. The content will be in the {language} language and has a {tone} tone. Make sure the content will not exceed the length of {characters} characters.';

		if ( ! $input['is_html'] ) {
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
	public static function prepare_text_generation_messages() {
		$prompt = Input::post( 'prompt', '' );

		return array(
			array(
				'role'    => 'system',
				'content' => self::create_system_message(),
			),
			array(
				'role'    => 'user',
				'content' => $prompt,
			),
		);
	}

	/**
	 * Prepare the input array for translating content to a specific language.
	 *
	 * @return array
	 * @since 3.0.0
	 */
	public static function prepare_translation_messages() {
		$content        = Input::post( 'content', '' );
		$language       = Input::post( 'language', '' );
		$is_html        = Input::post( 'is_html', false, Input::TYPE_BOOL );
		$system_content = 'Your task is to translate the provided text into {language}. Identify the original language if needed and ensure the translation accurately conveys the meaning of the original content.';
		$system_content = str_replace( '{language}', $language, $system_content );

		if ( ! $is_html ) {
			$system_content .= ' Please respond with plain text only. Do not use markdown or HTML.';
		}

		return array(
			array(
				'role'    => 'system',
				'content' => $system_content,
			),
			array(
				'role'    => 'user',
				'content' => $content,
			),
		);
	}

	/**
	 * Prepare the input array for rephrasing content
	 *
	 * @return array
	 * @since 3.0.0
	 */
	public static function prepare_rephrase_messages() {
		$content        = Input::post( 'content', '' );
		$is_html        = Input::post( 'is_html', false, Input::TYPE_BOOL );
		$system_content = 'Your task is to rephrase any text content provided to you, ensuring that the original meaning is preserved while expressing it differently.';

		if ( ! $is_html ) {
			$system_content .= ' Please respond with plain text only. Do not use markdown or HTML.';
		}

		return array(
			array(
				'role'    => 'system',
				'content' => $system_content,
			),
			array(
				'role'    => 'user',
				'content' => $content,
			),
		);
	}

	/**
	 * Prepare the input array for making the content shorten.
	 *
	 * @return array
	 * @since 3.0.0
	 */
	public static function prepare_make_shorter_messages() {
		$content        = Input::post( 'content', '' );
		$is_html        = Input::post( 'is_html', false, Input::TYPE_BOOL );
		$system_content = 'Your task is to condense the provided text, retaining the key points and meaning while making the content as concise as possible.';

		if ( ! $is_html ) {
			$system_content .= ' Please respond with plain text only. Do not use markdown or HTML.';
		}

		return array(
			array(
				'role'    => 'system',
				'content' => $system_content,
			),
			array(
				'role'    => 'user',
				'content' => $content,
			),
		);
	}

	/**
	 * Prepare the input array for changing the tone of the content.
	 *
	 * @return array
	 * @since 3.0.0
	 */
	public static function prepare_change_tone_messages() {
		$content        = Input::post( 'content', '' );
		$tone           = Input::post( 'tone', '' );
		$is_html        = Input::post( 'is_html', false, Input::TYPE_BOOL );
		$system_content = "Your task is to change the tone of the provided text to match the specified style, which is {tone}. Ensure that the content's meaning remains consistent while reflecting this new tone.";
		$system_content = str_replace( '{tone}', $tone, $system_content );

		if ( ! $is_html ) {
			$system_content .= ' Please respond with plain text only. Do not use markdown or HTML.';
		}

		return array(
			array(
				'role'    => 'system',
				'content' => $system_content,
			),
			array(
				'role'    => 'user',
				'content' => $content,
			),
		);
	}

	/**
	 * Prepare the input array for converting the content into bullet points.
	 *
	 * @return array
	 * @since 3.0.0
	 */
	public static function prepare_write_as_bullets_messages() {
		$content        = Input::post( 'content', '' );
		$is_html        = Input::post( 'is_html', false, Input::TYPE_BOOL );
		$system_content = 'Your task is to rewrite the provided text as bullet points. Ensure that each point is clear and concise while preserving the original meaning.';

		if ( ! $is_html ) {
			$system_content .= ' Please respond with plain text only. Do not use markdown or HTML.';
		}

		return array(
			array(
				'role'    => 'system',
				'content' => $system_content,
			),
			array(
				'role'    => 'user',
				'content' => $content,
			),
		);
	}

	/**
	 * Prepare the input array for making the content larger.
	 *
	 * @return array
	 * @since 3.0.0
	 */
	public static function prepare_make_longer_messages() {
		$content        = Input::post( 'content', '' );
		$is_html        = Input::post( 'is_html', false, Input::TYPE_BOOL );
		$system_content = 'Your task is to expand the provided text, adding more detail and depth while maintaining the original meaning and intent.';

		if ( ! $is_html ) {
			$system_content .= ' Please respond with plain text only. Do not use markdown or HTML.';
		}

		return array(
			array(
				'role'    => 'system',
				'content' => $system_content,
			),
			array(
				'role'    => 'user',
				'content' => $content,
			),
		);
	}

	/**
	 * Prepare the input array for simplifying the language of the generated content.
	 *
	 * @return array
	 * @since 3.0.0
	 */
	public static function prepare_simplify_language_messages() {
		$content        = Input::post( 'content', '' );
		$is_html        = Input::post( 'is_html', false, Input::TYPE_BOOL );
		$system_content = 'Your task is to simplify the language of the provided text, making it easier to understand while preserving the original meaning.';

		if ( ! $is_html ) {
			$system_content .= ' Please respond with plain text only. Do not use markdown or HTML.';
		}

		return array(
			array(
				'role'    => 'system',
				'content' => $system_content,
			),
			array(
				'role'    => 'user',
				'content' => $content,
			),
		);
	}

	/**
	 * Prepare the input array for creating the course title.
	 *
	 * @return array
	 */
	public static function prepare_course_title_messages() {
		$prompt = Input::post( 'prompt' );

		return array(
			array(
				'role'    => 'system',
				'content' => 'You are a highly skilled assistant specialized in generating course titles for an e-learning platform. When provided with a prompt describing a course, your task is to create a concise, compelling, and marketable course title. The title should be clear, engaging, and appropriate for the specified audience, which could range from beginners to advanced learners. Ensure that the title reflects the course content accurately and consider the use of impactful language that highlights the value of the course.',
			),
			array(
				'role'    => 'user',
				'content' => $prompt,
			),
		);
	}

	/**
	 * Prepare the course description with the help of the course title.
	 *
	 * @param string $title The course title.
	 * @return array
	 */
	public static function prepare_course_description_messages( string $title ) {
		return array(
			array(
				'role'    => 'system',
				'content' => 'You are an AI assistant that specializes in generating detailed course descriptions for an e-learning platform. Based on the provided course title, your task is to create a compelling and informative course description that includes the following elements: an overview of the course content, key learning outcomes, and a clear identification of the target audience. The description should be engaging, informative, and accurately reflect the skills and knowledge students will gain. Ensure that the language is accessible, with a tone that is both motivating and professional, and tailored to the specified audience, whether they are beginners, intermediate learners, or advanced professionals.',
			),
			array(
				'role'    => 'user',
				'content' => $title,
			),
		);
	}
}
