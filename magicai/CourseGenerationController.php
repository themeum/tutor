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
		add_action( 'wp_ajax_tutor_generate_course_content', array( $this, 'course_generation' ) );
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
				return $response->choices[0]->message->content;
			}

			return null;
		} catch ( Throwable $error ) {
			throw $error;
		}
	}

	/**
	 * Generate course contents
	 *
	 * @return void
	 */
	public function course_generation() {

		try {
			$course_title       = $this->generate_course_title();
			$course_description = $this->generate_course_description( $course_title );

			$this->json_response(
				__( 'Content generated', 'tutor' ),
				array(
					'title'       => $course_title,
					'description' => $course_description,
				)
			);
		} catch ( Exception $error ) {
			$this->json_response( $error->getMessage(), null, HttpHelper::STATUS_INTERNAL_SERVER_ERROR );
		}
	}
}
