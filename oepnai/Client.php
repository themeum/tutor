<?php
/**
 * Helper class for handling magic ai functionalities
 *
 * @package Tutor\MagicAI
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\OpenAI;

use Tutor\OpenAI\Contracts\TransporterContract;

/**
 * The openai client
 */
final class Client {

	/**
	 * The transporter instance with required metadata.
	 *
	 * @var TransporterContract
	 */
	private $transporter = null;

	/**
	 * The constructor function of the client class.
	 *
	 * @param TransporterContract $transporter The transporter instance.
	 * @since 3.0.0
	 */
	public function __construct( TransporterContract $transporter ) {
		$this->transporter = $transporter;
	}

	/**
	 * The image generation client instance
	 *
	 * @return void
	 * @since 3.0.0
	 */
	public function images() {
		// return the image client.
	}

	/**
	 * The chat completion client instance.
	 *
	 * @return void
	 */
	public function chat() {
		// return the chat client.
	}
}
