<?php
/**
 * Migration quiz attempt for generate result column data.
 *
 * @package Tutor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.8.0
 */

namespace Tutor\Migrations;

use Tutor\Models\QuizModel;

/**
 * Class QuizAttemptMigrator
 */
class QuizAttemptMigrator extends BatchProcessor {
	/**
	 * Name of the migration
	 *
	 * @var string
	 */
	protected $name = 'Quiz Attempt Migration';

	/**
	 * Action
	 *
	 * @var string
	 */
	protected $action = 'quiz_attempt_migrator';

	/**
	 * Batch size
	 *
	 * @var integer
	 */
	protected $batch_size = 1000;

	/**
	 * Schedule interval.
	 *
	 * @var integer
	 */
	protected $schedule_interval = 5;

	/**
	 * Get total unprocessed result.
	 *
	 * @return int
	 */
	protected function get_total_items() {
		global $wpdb;
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}tutor_quiz_attempts WHERE result IS NULL" );
	}

	/**
	 * Get items to batch process.
	 *
	 * @param int $offset offset.
	 * @param int $limit limit.
	 *
	 * @return array
	 */
	protected function get_items( $offset, $limit ) {
		global $wpdb;
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}tutor_quiz_attempts 
				WHERE result IS NULL 
				ORDER BY attempt_id DESC 
				LIMIT %d, %d",
				$offset,
				$limit
			)
		);
	}

	/**
	 * Process each quiz attempt record to prepare result.
	 *
	 * @param object $item item.
	 *
	 * @return void
	 */
	protected function process_item( $item ) {
		QuizModel::update_attempt_result( $item->attempt_id );
	}

	/**
	 * On migration complete event.
	 *
	 * @return void
	 */
	protected function on_complete() {
		error_log( 'Quiz attempt migration completed!' );
	}
}

