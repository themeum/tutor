<?
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
use Tutor\Traits\JsonResponse;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AI generator TextController class
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
	 * @param boolean $register_hooks
	 * @since 3.0.0
	 */
	public function __construct( $register_hooks = true ) {
		
		if ( !$register_hooks ) {
			return;
		}

		/**
		 * Handle AJAX request for generating AI content
		 *
		 * @since 3.0.0
		 */
		add_action( 'wp_ajax_tutor_generate_text_content', array ( $this, 'generate_text_content' ) );

		/**
		 * Handle AJAX request for modifying previously generated AI content
		 *
		 * @since 3.0.0
		 */
		add_action( 'wp_ajax_tutor_modify_text_content', array ( $this, 'modify_text_content' ) );
	}

	/**
	 * Generate image using the user prompt and the styles
	 *
	 * @return void
	 * @since 3.0.0
	 */
	public function generate_text_content() {
		tutor_utils()->checking_nonce();
		$is_html = Input::post( 'is_html', false, Input::TYPE_BOOL );

		try {
			$client = Helper::get_client();
			$response = $client->chat()->create( Prompts::prepare_text_generation_input() );
			$content = $response->choices[0]->message->content;
			$content = $is_html ? Helper::markdown_to_html( $content ) : $content;

			return $this->json_response( __( 'Content generated', 'tutor' ), $content );
		} catch (Exception $error) {
			return $this->json_response( $error->getMessage(), null, HttpHelper::STATUS_INTERNAL_SERVER_ERROR );
		}
	}

	/**
	 * Modify the text content by using openai
	 *
	 * @return void
	 * @since 3.0.0
	 */
	public function modify_text_content() {
		tutor_utils()->checking_nonce();

		$type = Input::post( 'type' );
		$is_html = Input::post( 'is_html', false, Input::TYPE_BOOL );
		$available_types = array( 'rephrase', 'make_shorter', 'change_tone', 'translation', 'write_as_bullets', 'make_longer', 'simplify_language' );

		if ( !in_array( $type, $available_types ) ) {
			throw new InvalidArgumentException( sprintf( 'There is no such a type %s exists.', $type ) );
		}

		$method = 'prepare_' . $type . '_input';

		if ( !method_exists( Prompts::class, $method ) ) {
			throw new InvalidArgumentException( sprintf( 'There is no such a method %s into the class %s.', $method, Prompts::class ) );
		}

		$input = Prompts::$method();

		try {
			$client = Helper::get_client();
			$response = $client->chat()->create( $input );
			$content = $response->choices[0]->message->content;
			$content = $is_html ? Helper::markdown_to_html( $content ) : $content;

			return $this->json_response( __( 'Content updated', 'tutor' ), $content );
		} catch (Exception $error) {
			return $this->json_response( $error->getMessage(), null, HttpHelper::STATUS_INTERNAL_SERVER_ERROR );
		}
	}
}
