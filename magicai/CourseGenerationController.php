<?php
/**
 * Helper class for handling magic ai functionalities
 *
 * @package Tutor\MagicAI
 * @author  Themeum <support@themeum.com>
 * @link    https://themeum.com
 * @since   3.0.0
 */

namespace Tutor\MagicAI;

use Exception;
use Throwable;
use Tutor\Helpers\HttpHelper;
use TUTOR\Input;
use Tutor\MagicAI\Constants\Models;
use Tutor\MagicAI\Constants\Sizes;
use Tutor\Traits\JsonResponse;

/**
 * Controller class for generating course with content using openai
 *
 * @since 3.0.0
 */
class CourseGenerationController {


	/**
	 * Use the trait JsonResponse for sending response in application/json content type
	 *
	 * @var JsonResponse
	 */
	use JsonResponse;

	/**
	 * Constructor method for the course generation controller
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		/**
		 * Handle AJAX request for generating AI images
		 *
		 * @since 3.0.0
		 */
		add_action( 'wp_ajax_tutor_generate_course_content', array( $this, 'course_content_generation' ) );

		/**
		 * Handle AJAX request for generating course content for a topic
		 *
		 * @since 3.0.0
		 */
		add_action( 'wp_ajax_tutor_generate_course_topic_content', array( $this, 'generate_course_topic_content' ) );

		/**
		 * Handle AJAX request for saving AI generated course contents.
		 *
		 * @since 3.0.0
		 */
		add_action( 'wp_ajax_tutor_save_ai_generated_course_content', array( $this, 'save_course_content' ) );

		/**
		 * Handle AJAX request for generating quiz question by using openai.
		 *
		 * @since 3.0.0
		 */
		add_action( 'wp_ajax_tutor_generate_quiz_questions', array( $this, 'generate_quiz_questions' ) );
	}

	/**
	 * Generate the course title from the user prompt.
	 *
	 * @return string|null
	 * @throws Throwable Catch if there any exceptions then throw it.
	 * @since  3.0.0
	 */
	private function generate_course_title() {
		try {
			$client   = Helper::get_client();
			$response = $client->chat()->create(
				Helper::create_openai_chat_input(
					Prompts::prepare_course_title_messages()
				)
			);

			if ( ! empty( $response->choices ) ) {
					return $response->choices[0]->message->content;
			}

			return null;
		} catch ( Throwable $error ) {
			throw $error;
		}
	}

	/**
	 * Generate the course description from the user prompt.
	 *
	 * @param  string $title Generated course title.
	 * @return string|null
	 * @throws Throwable Catch if there any exceptions then throw it.
	 * @since  3.0.0
	 */
	private function generate_course_description( string $title ) {
		try {
			$client   = Helper::get_client();
			$response = $client->chat()->create(
				Helper::create_openai_chat_input(
					Prompts::prepare_course_description_messages( $title )
				)
			);

			if ( ! empty( $response->choices ) ) {
					$content = $response->choices[0]->message->content;
					return Helper::markdown_to_html( $content );
			}

			return null;
		} catch ( Throwable $error ) {
			throw $error;
		}
	}

	/**
	 * Generate course image using the course title.
	 *
	 * @param  string $title The course title.
	 * @throws Throwable If any exception happens, then throw it.
	 * @return string
	 * @since  3.0.0
	 */
	private function generate_course_image( string $title ) {
		try {
			$client = Helper::get_client();
			$prompt = "You are an AI assistant specialized in generating e-learning course banner image. Design a clean and informative banner for an e-learning course titled '{title}'. The banner should feature modern and professional design elements that are relevant to the course topic. Use a visually appealing color scheme that complements the course theme. Incorporate relevant graphics or icons that visually represent the subject matter of the course, maintaining a clean, easy-to-understand layout. Ensure that the design is clear, professional, and visually engaging. **Do not include any text in the banner**. Focus solely on design elements like colors, graphics, icons, and layout to visually convey the course theme.";
			$prompt = str_replace( '{title}', $title, $prompt );

			$response = $client->images()->create(
				array(
					'model'           => Models::DALL_E_3,
					'prompt'          => $prompt,
					'size'            => Sizes::LANDSCAPE,
					'n'               => 1,
					'response_format' => 'b64_json',
				)
			);
			$response = $response->toArray();
			return 'data:image/png;base64,' . $response['data'][0]['b64_json'];
		} catch ( Throwable $error ) {
			throw $error;
		}
	}

	/**
	 * Generate the course topic names from the title
	 *
	 * @param string $title The course title.
	 * @return array
	 * @throws Throwable If any exception happens then throws it.
	 * @since 3.0.0
	 */
	private function generate_course_topic_names( string $title ) {
		try {
			$client = Helper::get_client();
			$input  = Helper::create_openai_chat_input(
				Prompts::prepare_course_topic_names_messages( $title ),
				array( 'response_format' => array( 'type' => 'json_object' ) )
			);

			$response = $client->chat()->create( $input );
			$content  = $response->choices[0]->message->content;
			$content  = Helper::is_valid_json( $content ) ? json_decode( $content ) : array();
			$modules  = ! empty( $content->modules ) ? $content->modules : array();

			return $modules;
		} catch ( Throwable $error ) {
			throw $error;
		}
	}

	/**
	 * API endpoint for generate course content for a topic by the course title and the topic name.
	 *
	 * @return void
	 * @since  3.0.0
	 */
	public function generate_course_topic_content() {
		$title      = Input::post( 'title' );
		$topic_name = Input::post( 'topic_name' );
		$index      = Input::post( 'index', 0, Input::TYPE_INT );

		try {
			$client   = Helper::get_client();
			$input    = Helper::create_openai_chat_input(
				Prompts::prepare_course_topic_content_messages( $title, $topic_name )
			);
			$response = $client->chat()->create( $input );
			$content  = $response->choices[0]->message->content;
			$content  = Helper::is_valid_json( $content ) ? json_decode( $content ) : array();

			$this->json_response(
				__( 'Content generated', 'tutor' ),
				array(
					'topic_contents' => $content,
					'index'          => $index,
				)
			);
		} catch ( Exception $error ) {
			$this->json_response( $error->getMessage(), null, HttpHelper::STATUS_INTERNAL_SERVER_ERROR );
		}
	}

	/**
	 * API endpoint for generating course contents using the prompt.
	 *
	 * @return void
	 */
	public function course_content_generation() {
		$type  = Input::post( 'type' );
		$title = Input::post( 'title' );

		try {
			$method    = 'generate_course_' . $type;
			$arguments = 'title' === $type ? array() : array( $title );

			if ( ! method_exists( $this, $method ) ) {
				$this->json_response( __( 'Invalid type provided', 'tutor' ), null, HttpHelper::STATUS_BAD_REQUEST );
			}

			$content = call_user_func_array( array( $this, $method ), $arguments );

			$this->json_response(
				__( 'Content generated', 'tutor' ),
				$content
			);
		} catch ( Exception $error ) {
			$this->json_response( $error->getMessage(), null, HttpHelper::STATUS_INTERNAL_SERVER_ERROR );
		}
	}

	/**
	 * API endpoint for saving the course content generated by AI.
	 *
	 * @return void
	 * @since 3.0.0
	 */
	public function save_course_content() {
		tutor_utils()->check_nonce();
		$course_id = Input::post( 'course_id' );
		$content   = Input::post( 'content' );

		if ( empty( $course_id ) || empty( $content ) ) {
			$this->json_response( __( 'Missing required payload.', 'tutor' ), null, HttpHelper::STATUS_BAD_REQUEST );
		}

		if ( ! Helper::is_valid_json( $content ) ) {
			$this->json_response( __( 'Invalid content data provided.', 'tutor' ), null, HttpHelper::STATUS_BAD_REQUEST );

		}

		try {
			$filename   = 'mock-course-' . $course_id . '.json';
			$upload_dir = wp_upload_dir();
			$file_path  = $upload_dir['basedir'] . '/course-contents/' . $filename;

			if ( ! file_exists( dirname( $file_path ) ) ) {
				wp_mkdir_p( dirname( $file_path ) );
			}

			if ( false === file_put_contents( $file_path, $content ) ) {
				$this->json_response( __( 'Failed to write data to file.', 'tutor' ), null, HttpHelper::STATUS_BAD_REQUEST );
			}
		} catch ( Exception $error ) {
			$this->json_response( $error->getMessage(), null, HttpHelper::STATUS_INTERNAL_SERVER_ERROR );
		}
	}

	/**
	 * Generate quiz questions by the help of course title, topic, and quiz title.
	 *
	 * @return void
	 * @since 3.0.0
	 */
	public function generate_quiz_questions() {
		$title      = Input::post( 'title' );
		$topic_name = Input::post( 'topic_name' );
		$quiz_title = Input::post( 'quiz_title' );

		if ( empty( $title ) || empty( $topic_name ) || empty( $quiz_title ) ) {
			$this->json_response( __( 'Missing required payloads.', 'tutor' ), null, HttpHelper::STATUS_BAD_REQUEST );
		}

		try {
			$client = Helper::get_client();
			$input  = Helper::create_openai_chat_input(
				Prompts::prepare_quiz_questions_messages( $title, $topic_name, $quiz_title )
			);

			$response = $client->chat()->create( $input );
			$content  = $response->choices[0]->message->content;
			$content  = Helper::is_valid_json( $content ) ? json_decode( $content ) : array();

			$this->json_response( __( 'Quiz generated', 'tutor' ), $content );
		} catch ( Exception $error ) {
			$this->json_response( $error->getMessage(), null, HttpHelper::STATUS_INTERNAL_SERVER_ERROR );
		}
	}
}
