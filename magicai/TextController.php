<?php
/**
 * Handle AI Generations
 *
 * @package Tutor\MagicAI
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\MagicAI;

use Exception;
use InvalidArgumentException;
use Tutor\Helpers\HttpHelper;
use TUTOR\Input;
use Tutor\OpenAI\OpenAI;
use Tutor\OpenAI\OpenAIClient;
use Tutor\OpenAI\Support\Payload;
use Tutor\Traits\JsonResponse;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Text controller class.
 * This class is responsible for generating text content using openai.
 *
 * @since 3.0.0
 */
class TextController {

	/**
	 * Use the JsonResponse trait for sending HTTP Response.
	 *
	 * @since 3.0.0
	 */
	use JsonResponse;

	/**
	 * Constructor method for generating AI Content.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		/**
		 * Handle AJAX request for generating AI content
		 *
		 * @since 3.0.0
		 */
		add_action( 'wp_ajax_tutor_generate_text_content', array( $this, 'generate_text_content' ) );

		/**
		 * Handle AJAX request for modifying previously generated AI content
		 *
		 * @since 3.0.0
		 */
		add_action( 'wp_ajax_tutor_modify_text_content', array( $this, 'modify_text_content' ) );

		add_action( 'wp_ajax_tutor_test_new_openai', array( $this, 'test_openai' ) );
	}

	/**
	 * test function.
	 */
	public function test_openai() {

		try {
			$client = OpenAI::client( tutor_utils()->get_option( 'chatgpt_api_key' ) );
			// $chat     = $client->chat();
			// $response = $chat->create(
			// array(
			// 'model'       => 'gpt-4o',
			// 'messages'    => array(
			// array(
			// 'role'    => 'system',
			// 'content' => 'You are a helpful assistant could generate funny content.',
			// ),
			// array(
			// 'role'    => 'user',
			// 'content' => 'Tell me a joke.',
			// ),
			// ),
			// 'temperature' => 0.7,
			// )
			// );
			// $response = $client->images()->create(
			// array(
			// 'model'           => 'dall-e-2',
			// 'prompt'          => 'create an image of a tree with 100 years old besides a river',
			// 'n'               => 1,
			// 'response_format' => 'url',
			// 'size'            => '256x256',
			// )
			// );
			$response = $client->edits()->create(
				array(
					'model'           => 'dall-e-2',
					'prompt'          => 'an accurium with gold fish and a small cat fish.',
					'n'               => 1,
					'response_format' => 'url',
					'size'            => '256x256',
					'image'           => array(
						'tmp_name' => '/Users/ahamed/Desktop/sample.png',
						'name'     => 'sample.png',
						'type'     => 'image/png',
					),
				)
			);
			$this->json_response( '', $response );
		} catch ( Exception $error ) {
			$this->json_response( $error->getMessage(), null, HttpHelper::STATUS_INTERNAL_SERVER_ERROR );
		}
	}

	/**
	 * Generating the text content from a user prompt, along with the tone, format, language, etc.
	 *
	 * @return void
	 * @since 3.0.0
	 */
	public function generate_text_content() {
		tutor_utils()->check_nonce();

		$input = array(
			'prompt'     => Input::post( 'prompt', '' ),
			'tone'       => Input::post( 'tone', 'formal' ),
			'format'     => Input::post( 'format', 'essay' ),
			'language'   => Input::post( 'language', 'english' ),
			'characters' => Input::post( 'characters', 250 ),
			'is_html'    => Input::post( 'is_html', false, Input::TYPE_BOOL ),
		);

		try {
			$client   = Helper::get_openai_client();
			$response = $client->chat()->create(
				Helper::create_openai_chat_input(
					Prompts::prepare_text_generation_messages( $input )
				)
			);

			$this->json_response( __( 'Content generated', 'tutor' ), $response );

			$content = $response->choices[0]->message->content;
			$content = $input['is_html'] ? Helper::markdown_to_html( $content ) : $content;

			$this->json_response( __( 'Content generated', 'tutor' ), $content );
		} catch ( Exception $error ) {
			$this->json_response( $error->getMessage(), null, HttpHelper::STATUS_INTERNAL_SERVER_ERROR );
		}
	}

	/**
	 * Modify previously generated text content by using openai.
	 * You can modify the tone, language, even make the content shorter or longer and also rephrase the content.
	 *
	 * @return void
	 * @throws InvalidArgumentException If the provided payloads are not valid.
	 * @since 3.0.0
	 */
	public function modify_text_content() {
		tutor_utils()->check_nonce();

		$type            = Input::post( 'type' );
		$is_html         = Input::post( 'is_html', false, Input::TYPE_BOOL );
		$available_types = array( 'rephrase', 'make_shorter', 'change_tone', 'translation', 'write_as_bullets', 'make_longer', 'simplify_language' );

		if ( ! in_array( $type, $available_types, true ) ) {
			throw new InvalidArgumentException( sprintf( 'There is no such a type %s exists.', esc_html( $type ) ) );
		}

		$arguments = array(
			Input::post( 'content', '' ),
			Input::post( 'is_html', false, Input::TYPE_BOOL ),
		);

		switch ( $type ) {
			case 'change_tone':
				$arguments[] = Input::post( 'tone', '' );
				break;
			case 'translation':
				$arguments[] = Input::post( 'language', '' );
				break;
		}

		$method = 'prepare_' . $type . '_messages';

		if ( ! method_exists( Prompts::class, $method ) ) {
			throw new InvalidArgumentException(
				sprintf(
					'There is no such a method %s into the class %s.',
					esc_html( $method ),
					esc_html( Prompts::class )
				)
			);
		}

		$input = Helper::create_openai_chat_input(
			call_user_func_array( array( Prompts::class, $method ), $arguments )
		);

		try {
			$client   = Helper::get_openai_client();
			$response = $client->chat()->create( $input );
			$content  = $response->choices[0]->message->content;
			$content  = $is_html ? Helper::markdown_to_html( $content ) : $content;

			$this->json_response( __( 'Content updated', 'tutor' ), $content );
		} catch ( Exception $error ) {
			$this->json_response( $error->getMessage(), null, HttpHelper::STATUS_INTERNAL_SERVER_ERROR );
		}
	}
}
