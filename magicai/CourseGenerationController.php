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
	}

	/**
	 * Generate the course title from the user prompt.
	 *
	 * @return string|null
	 * @throws Throwable Catch if there any exceptions then throw it.
	 * @since 3.0.0
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
	 * @param string $title Generated course title.
	 * @return string|null
	 * @throws Throwable Catch if there any exceptions then throw it.
	 * @since 3.0.0
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
	 * @param string $title The course title.
	 * @throws Throwable If any exception happens, then throw it.
	 * @return string
	 * @since 3.0.0
	 */
	private function generate_course_image( string $title ) {
		try {
			$client = Helper::get_client();

			$prompt = "Design a clean and informative banner for an e-learning course titled '{title}'. The banner should feature modern and professional design elements that are relevant to the course topic. Use a visually appealing color scheme that complements the course theme. Display the course title prominently in clear and stylish typography. Include relevant graphics or icons that represent the subject matter of the course, ensuring a clean and easy-to-understand layout. Avoid cluttering the image with too many components.";
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
	 * Generate course content for a topic
	 *
	 * @param string $title The course title.
	 * @param string $topic_name The topic name.
	 * @throws Throwable If there any error happens then throw it.
	 * @return array
	 * @since 3.0.0
	 */
	private function generate_course_topic_contents( string $title, string $topic_name ) {
		try {
			$client   = Helper::get_client();
			$input    = Helper::create_openai_chat_input(
				Prompts::prepare_course_topic_content_messages( $title, $topic_name )
			);
			$response = $client->chat()->create( $input );
			$content  = $response->choices[0]->message->content;
			$content  = Helper::is_valid_json( $content ) ? json_decode( $content ) : array();
			return $content;
		} catch ( Throwable $error ) {
			throw $error;
		}
	}

	/**
	 * Generate course content from the course title.
	 *
	 * @param string $title The course title.
	 * @throws Throwable If there any exception thrown.
	 * @return array
	 * @since 3.0.0
	 */
	private function generate_course_content( string $title ) {
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

			$course_contents = array();

			if ( ! empty( $modules ) ) {
				foreach ( $modules as $module ) {
					$module_contents = $this->generate_course_topic_contents( $title, $module->name );

					if ( empty( $module_contents ) || ! is_array( $module_contents ) ) {
						continue;
					}

					$content = array(
						'name'    => $module->name,
						'content' => $module_contents,
					);

					$course_contents[] = $content;
				}
			}

			return $course_contents;
		} catch ( Throwable $error ) {
			throw $error;
		}
	}

	/**
	 * Generate course contents using the prompt.
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
}
