<?php
/**
 * Interface for single item processor.
 *
 * @package Tutor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.8.2
 */

namespace Tutor\Migrations\Contracts;

/**
 * Interface SingleProcessor
 *
 * @since 3.8.2
 */
interface SingleProcessor {
	/**
	 * Process a single item
	 *
	 * @since 3.8.2
	 *
	 * @param object $item item.
	 *
	 * @return void
	 */
	public function process_item( $item ): void;
}
