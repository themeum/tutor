<?php
/**
 * Helper class for handling magic ai functionalities
 *
 * @package Tutor\MagicAI
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\OpenAI\Contracts;

use Tutor\OpenAI\Support\Payload;
use Tutor\OpenAI\Http\Response;

/**
 * The transporter interface
 *
 * @since 3.0.0
 */
interface TransporterContract {
	/**
	 * Send the request to the requested endpoint
	 *
	 * @param Payload $route A route instance.
	 * @return Response
	 */
	public function request( Payload $route );
}
