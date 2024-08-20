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
				'content' => 'You are a highly skilled assistant specialized in generating course titles for an e-learning platform. When provided with a prompt describing a course, your task is to create a concise, compelling, and marketable course title. The title should be clear, engaging, and appropriate for the specified audience, which could range from beginners to advanced learners. Ensure that the title reflects the course content accurately and consider the use of impactful language that highlights the value of the course. Do not use markdown or HTML, do not wrap the content with quotes, and the title should not exceed 100 characters.',
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
				'content' => 'You are an AI assistant that specializes in generating detailed course descriptions for an e-learning platform. Based on the provided course title, your task is to create a compelling and informative course description that includes the following elements: an overview of the course content, key learning outcomes, and a clear identification of the target audience. The description should be engaging, informative, and accurately reflect the skills and knowledge students will gain. Ensure that the language is accessible, with a tone that is both motivating and professional, and tailored to the specified audience, whether they are beginners, intermediate learners, or advanced professionals. Please respond with plain text only.',
			),
			array(
				'role'    => 'user',
				'content' => $title,
			),
		);
	}

	/**
	 * Prepare the messages for openai for generating topic names.
	 *
	 * @param string $title The course title.
	 * @return array
	 */
	public static function prepare_course_topic_names_messages( string $title ) {
		$system_content = 'You are an AI assistant specialized in generating course module names. You are tasked with generating course module names for a given course title. Based on this course title, create at least 5 module names that follow a logical progression, starting with introductory topics and moving toward more advanced concepts. Ensure that the module names include standard course elements like an introduction, a course outline, and a conclusion. The names should be clear, concise, and directly related to the content of the course. Return the module names as a JSON array in the format: [{name: "Module Name"}].';

		return array(
			array(
				'role'    => 'system',
				'content' => $system_content,
			),
			array(
				'role'    => 'user',
				'content' => $title,
			),
		);
	}

	/**
	 * Prepare the messages for openai for generating topic contents.
	 *
	 * @param string $title The course title.
	 * @param string $topic_name The topic name.
	 * @return array
	 */
	public static function prepare_course_topic_content_messages( string $title, string $topic_name ) {
		$system_content = "You are an AI assistant specialized in generating course contents. Generate minimum 2 to maximum 5 content items based on the provided course title and module name. The content can include any of the following types: 'lesson', 'quiz', or 'assignment'. For each content item, provide a title and a description that accurately reflects the content. Return the generated content as a JSON array with the structure: [{type: `lesson|quiz|assignment`, title: 'the content title', description: 'the content description'}].";

		return array(
			array(
				'role'    => 'system',
				'content' => $system_content,
			),
			array(
				'role'    => 'user',
				'content' => 'The course title is: ' . $title,
			),
			array(
				'role'    => 'user',
				'content' => 'The module name is: ' . $topic_name,
			),
		);
	}

	/**
	 * Prepare the messages for the openai chat API for generating quiz content.
	 *
	 * @param string $title The course title.
	 * @param string $topic_name The course module/topic name.
	 * @param string $quiz_title The quiz title.
	 * @return array
	 * @since 3.0.0
	 */
	public static function prepare_quiz_questions_messages( string $title, string $topic_name, string $quiz_title ) {
		$system_content = "You are an intelligent assistant tasked with creating quiz questions for a course. You are provided a course title, a course module name, and a quiz title.
    Please generate a minimum of 3 and a maximum of 5 quiz questions of the following types:
    - True/False
    - Multiple Choice
    - Open-Ended

    Each question must have:
    - A clear question title.
    - A brief description that adds context to the question.
    - For true/false questions: two options - 'true' and 'false'.
    - For multiple-choice questions: several answer options with one correct answer.
    - For open-ended questions: no options (only the question and description).

		Additionally, please ensure that some of the questions have a question mark ('?') as the value of the title.

		The response should be in **valid JSON** format as follows, and make sure not to use any suffix or prefix with the response:

    [
      {
        'title': 'the question title?',
				'type': 'true_false|open_ended|multiple_choice',
        'options': [
          {
            'name': 'option name',
            'is_correct_answer': true
          },
          {
            'name': 'option name',
            'is_correct_answer': false
          }
        ]
      }
    ]

    Please ensure that the provided JSON is valid and properly structured. Include a variety of question types (true/false, multiple-choice, and open-ended), and make sure the content relates to the course and module.
    Make sure the number of questions falls between 3 to 5, with some having '?' as the title.
		";

		return array(
			array(
				'role'    => 'system',
				'content' => $system_content,
			),
			array(
				'role'    => 'user',
				'content' => 'The course title: ' . $title,
			),
			array(
				'role'    => 'user',
				'content' => 'The module name: ' . $topic_name,
			),
			array(
				'role'    => 'user',
				'content' => 'The quiz title: ' . $quiz_title,
			),
		);
	}
}
