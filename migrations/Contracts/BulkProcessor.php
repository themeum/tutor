<?php
/**
 * Bulk processor interface.
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
	 * Process items of a batch.
	 *
	 * @since 3.8.2
	 *
	 * @param array $items Items to process.
	 */
	public function process_items( array $items ): void;
}
