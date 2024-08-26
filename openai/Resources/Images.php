<?php
/**
 * Helper class for handling magic ai functionalities
 *
 * @package Tutor\MagicAI
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\OpenAI\Resources;

use Tutor\OpenAI\Concerns\Transportable;
use Tutor\OpenAI\Contracts\ClientContract;
use Tutor\OpenAI\Support\Payload;

/**
 * The chat resource for making chat completion requests to openai.
 *
 * @since 3.0.0
 */
final class Images implements ClientContract {
	use Transportable;

	/**
	 * Create the resource for making http request.
	 *
	 * @param array $options The options to send to openai endpoint.
	 * @return array
	 * @since 3.0.0
	 */
	public function create( array $options ) {
		$payload = Payload::post( 'images/generations', $options );
		return $this->transporter->request( $payload )->as_base64_image();
	}
}
