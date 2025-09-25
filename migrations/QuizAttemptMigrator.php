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

use Tutor\Helpers\QueryHelper;
use Tutor\Migrations\Contracts\SingleProcessor;
use Tutor\Models\QuizModel;

/**
 * Class QuizAttemptMigrator
 *
 * @since 3.8.0
 */
class QuizAttemptMigrator extends BatchProcessor implements SingleProcessor {
	/**
	 * Name of the migration
	 *
	 * @since 3.8.0
	 *
	 * @var string
	 */
	protected $name = 'Quiz Attempt Migration';

	/**
	 * Action
	 *
	 * @since 3.8.0
	 *
	 * @var string
	 */
	protected $action = 'quiz_attempt_migrator';

	/**
	 * Batch size
	 *
	 * @since 3.8.0
	 *
	 * @var integer
	 */
	protected $batch_size = 100;

	/**
	 * Schedule interval.
	 *
	 * @since 3.8.0
	 *
	 * @var integer
	 */
	protected $schedule_interval = 10;

	/**
	 * Get total unprocessed result.
	 *
	 * @since 3.8.0
	 *
	 * @return int
	 */
	protected function get_total_items(): int {
		return QueryHelper::get_count( 'tutor_quiz_attempts', array( 'result' => array( 'IS', 'NULL' ) ), array(), 'attempt_id' );
	}

	/**
	 * Get items to batch process.
	 *
	 * @since 3.8.0
	 *
	 * @param int $offset offset.
	 * @param int $limit limit.
	 *
	 * @return array
	 */
	protected function get_items( $offset, $limit ) : array {
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
	 * @since 3.8.0
	 *
	 * @param object $item item.
	 *
	 * @return void
	 */
	public function process_item( $item ) : void {
		QuizModel::update_attempt_result( $item->attempt_id );
	}

	/**
	 * On migration complete event.
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	protected function on_complete() {
		error_log( 'Quiz attempt migration completed!' );
	}
}

