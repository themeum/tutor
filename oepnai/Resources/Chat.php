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

/**
 * The chat resource for making chat completion requests to openai.
 *
 * @since 3.0.0
 */
final class Chat implements ClientContract {
	use Transportable;

	/**
	 * Create the resource for making http request.
	 *
	 * @param array $options The options to send to openai endpoint.
	 * @return void
	 */
	public function create( array $options ) {
		$this->transporter->send( 'chat/completions', $options );
	}
}
