<?php
/**
 * Helper class for handling magic ai functionalities
 *
 * @package Tutor\MagicAI
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\OpenAI\Concerns;

use Tutor\OpenAI\Contracts\TransporterContract;

trait Transportable {
	/**
	 * The transporter instance.
	 *
	 * @var TransporterContract The transporter contract.
	 * @since 3.0.0
	 */
	protected $transporter = null;

	/**
	 * The constructor method for storing the transporter instance.
	 *
	 * @param TransporterContract $transporter The transporter instance.
	 * @since 3.0.0
	 */
	public function __construct( TransporterContract $transporter ) {
		$this->transporter = $transporter;
	}
}
