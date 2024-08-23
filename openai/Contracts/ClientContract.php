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

interface ClientContract {
	/**
	 * Create the resource for making http request.
	 *
	 * @param array $options The options to send to openai endpoint.
	 * @return Response
	 */
	public function create( array $options );
}
