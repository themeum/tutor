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
use Tutor\OpenAI\Resources\Chat;
use Tutor\OpenAI\Contracts\ClientContract;
use Tutor\OpenAI\Resources\Edits;
use Tutor\OpenAI\Resources\Images;

/**
 * The openai client
 */
class Client {
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
	 * @return ClientContract
	 * @since 3.0.0
	 */
	public function images() {
		return new Images( $this->transporter );
	}

	/**
	 * The chat completion client instance.
	 *
	 * @return ClientContract
	 * @since 3.0.0
	 */
	public function chat() {
		return new Chat( $this->transporter );
	}

	/**
	 * The chat completion client instance.
	 *
	 * @return ClientContract
	 * @since 3.0.0
	 */
	public function edits() {
		return new Edits( $this->transporter );
	}
}
