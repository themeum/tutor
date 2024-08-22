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

/**
 * The transporter interface
 *
 * @since 3.0.0
 */
interface TransporterContract {
	/**
	 * Send the request to the requested endpoint
	 *
	 * @param string $endpoint The request endpoint.
	 * @param array  $payload The request payload.
	 * @return void
	 */
	public function send( string $endpoint, array $payload );
}
