<?php
/**
 * Interface for bulk item processor.
 *
 * @package Tutor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.8.2
 */

namespace Tutor\Migrations\Contracts;

/**
 * Interface BulkProcessor
 *
 * @since 3.8.2
 */
interface BulkProcessor {
	/**
	 * Process a batch of items
	 *
	 * @since 3.8.2
	 *
	 * @param array $items items.
	 *
	 * @return void
	 */
	public function process_items( $items ): void;
}
