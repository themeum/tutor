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
use RuntimeException;
use Tutor\Helpers\HttpHelper;
use TUTOR\Input;
use Tutor\MagicAI\Constants\Models;
use Tutor\MagicAI\Constants\Sizes;
use Tutor\Traits\JsonResponse;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * OrderController class
 *
 * @since 3.0.0
 */
class ImageController {

	/**
	 * Use the JsonResponse trait for sending HTTP Response.
	 *
	 * @since 3.0.0
	 */
	use JsonResponse;

	/**
	 * Constructor method for generating AI Images.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		/**
		 * Handle AJAX request for generating AI images
		 *
		 * @since 3.0.0
		 */
		add_action( 'wp_ajax_tutor_generate_image', array( $this, 'generate_image' ) );

		/**
		 * Handle AJAX request for editing AI image
		 *
		 * @since 3.0.0
		 */
		add_action( 'wp_ajax_tutor_magic_fill_image', array( $this, 'magic_fill_image' ) );

		/**
		 * Handle AJAX request for using the AI generated image to the WP system.
		 *
		 * @since 3.0.0
		 */
		add_action( 'wp_ajax_tutor_use_magic_image', array( $this, 'use_magic_image' ) );
	}

	/**
	 * Generate the prompt for the specific styles
	 *
	 * @param string $prompt The user prompt for generating image.
	 * @param string $style The style of the output image.
	 * @return string
	 *
	 * @since 3.0.0
	 */
	private static function generate_prompt( $prompt, $style ) {
		$style_prompts = array(
			'filmic'          => 'Create an image of {user_prompt} with a cinematic quality, incorporating deep contrasts, rich colors, and dramatic lighting. The scene should evoke the feeling of a classic film.',
			'photo'           => 'Generate a high-resolution photograph {user_prompt} of with realistic lighting, shadows, and textures. The image should have a natural, lifelike quality as if captured by a professional camera.',
			'neon'            => 'Create an image of {user_prompt} with vibrant neon colors and glowing elements. The design should feature bright, fluorescent lights and a modern, urban aesthetic reminiscent of neon signs and cityscapes.',
			'dreamy'          => 'Design an image of {user_prompt} with a dreamy, ethereal quality, using soft focus, pastel colors, and gentle lighting. The scene should evoke a sense of whimsy and surreal beauty, like something out of a fantasy.',
			'black_and_white' => 'Generate a black and white image of {user_prompt} with high contrast and a wide range of grays. The absence of color should emphasize the shapes, textures, and lighting to create a dramatic and timeless look.',
			'retrowave'       => 'Design an image of {user_prompt} with a retro 80s aesthetic, featuring neon colors, grid patterns, and futuristic elements that evoke the style of synthwave music and retro video games.',
			'3d'              => 'Produce an image of {user_prompt} with a 3D effect, emphasizing depth, shading, and realistic textures to a create a sense of three-dimensionality and immersion.',
			'concept_art'     => 'Produce a piece of concept art of {user_prompt} that showcases a creative and imaginative design. Use detailed textures, dynamic compositions, and a strong visual narrative to convey the concept effectively.',
			'sketch'          => 'Create a sketch-style image of {user_prompt} with clean, hand-drawn lines and minimal shading. The design should look like a detailed pencil or ink drawing, capturing the essence of the subject with simplicity and elegance.',
			'illustration'    => "Create an illustration of {user_prompt} with vibrant colors, clear outlines, and stylized elements. The design should have a playful and imaginative quality, with detailed characters and scenes that capture the viewer's attention and convey a strong visual story.",
			'painting'        => 'Design an image of {user_prompt} with the texture and style of a traditional painting. Use brushstroke effects, rich colors, and painterly techniques to create a piece that looks like it was painted by hand on canvas.',
		);

		if ( empty( $style ) || 'none' === $style ) {
			return $prompt;
		}

		if ( empty( $style_prompts[ $style ] ) ) {
			return $prompt;
		}

		$style_prompt = 'You are an intelligent assistant tasked with generating banner images for an e-learning application. ' . $style_prompts[ $style ];

		return str_replace( '{user_prompt}', $prompt, $style_prompt );
	}

	/**
	 * Generate image using the user prompt and the styles
	 *
	 * @return void
	 * @since 3.0.0
	 */
	public function generate_image() {
		tutor_utils()->check_nonce();

		$prompt = Input::post( 'prompt' );
		$style  = Input::post( 'style' );

		if ( empty( $prompt ) ) {
			$this->json_response(
				__( 'Prompt is required to generating images', 'tutor' ),
				null,
				HttpHelper::STATUS_BAD_REQUEST
			);
		}

		$prompt = self::generate_prompt( $prompt, $style );

		$input = array(
			'model'           => Models::DALL_E_3,
			'prompt'          => $prompt,
			'n'               => 1,
			'size'            => Sizes::LANDSCAPE,
			'response_format' => 'b64_json',
		);

		try {
			$client   = Helper::get_client();
			$response = $client->images()->create( $input );
			$response = $response->toArray();

			if ( ! empty( $response ) ) {
				$response['data'][0]['b64_json'] = 'data:image/png;base64,' . $response['data'][0]['b64_json'];
			}

			$this->json_response( __( 'Image created', 'tutor' ), $response );
		} catch ( Exception $error ) {
			$this->json_response( $error->getMessage(), null, HttpHelper::STATUS_INTERNAL_SERVER_ERROR );
		}
	}

	/**
	 * Edit image by selecting an area.
	 *
	 * @return void
	 * @since 3.0.0
	 */
	public function magic_fill_image() {
		tutor_utils()->check_nonce();

		$prompt         = Input::post( 'prompt' );
		$image          = Input::post( 'image' );
		$base64         = explode( ',', $image )[1];
		$image          = base64_decode( $base64 );
		$revised_prompt = 'Fill the image and replace the selected area by {prompt}';

		$input = array(
			'model'           => Models::DALL_E_2,
			'image'           => $image,
			'prompt'          => str_replace( '{prompt}', $prompt, $revised_prompt ),
			'n'               => 1,
			'size'            => Sizes::REGULAR,
			'response_format' => 'b64_json',
		);

		try {
			$client   = Helper::get_client();
			$response = $client->images()->edit( $input );
			$response = $response->toArray();

			if ( ! empty( $response ) ) {
				$response['data'][0]['b64_json'] = 'data:image/png;base64,' . $response['data'][0]['b64_json'];
			}

			$this->json_response( __( 'Mask applied successfully.', 'tutor' ), $response );
		} catch ( Exception $error ) {
			$this->json_response( $error->getMessage(), null, HttpHelper::STATUS_INTERNAL_SERVER_ERROR );
		}
	}

	/**
	 * Use the image generated by the AI, upload this image to the media.
	 *
	 * @return void
	 * @throws RuntimeException Throws an exception if any error happens while uploading the bits.
	 * @since 3.0.0
	 */
	public function use_magic_image() {
		tutor_utils()->check_nonce();

		$image = Input::post( 'image' );

		if ( empty( $image ) ) {
			$this->json_response( __( 'Image is missing to use', 'tutor' ), null, HttpHelper::STATUS_BAD_REQUEST );
		}

		if ( false === stripos( $image, ',' ) ) {
			$this->json_response( __( 'Invalid image content', 'tutor' ), null, HttpHelper::STATUS_BAD_REQUEST );
		}

		$image_data    = base64_decode( explode( ',', $image, 2 )[1] );
		$unique_string = wp_generate_password( 8, false );
		$filename      = 'course-banner-' . $unique_string . '.png';

		try {
			$uploaded = wp_upload_bits( $filename, null, $image_data );

			if ( ! empty( $uploaded['error'] ) ) {
				throw new RuntimeException( $uploaded['error'] );
			}

			$attachment = array(
				'guid'           => $uploaded['url'],
				'post_mime_type' => $uploaded['type'],
				'post_title'     => $filename,
				'post_content'   => '',
				'post_status'    => 'inherit',
			);

			$media_id = wp_insert_attachment( $attachment, $uploaded['url'], 0 );
			$response = array(
				'id'    => $media_id,
				'url'   => $uploaded['url'],
				'title' => $filename,
			);

			$this->json_response( __( 'Image stored', 'tutor' ), $response );
		} catch ( Exception $error ) {
			$this->json_response( $error->getMessage(), null, HttpHelper::STATUS_INTERNAL_SERVER_ERROR );
		}
	}
}
