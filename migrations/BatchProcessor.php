<?php
/**
 * Class to handle process large amount of data with batch.
 *
 * @package Tutor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.8.0
 */

namespace Tutor\Migrations;

use Tutor\Migrations\Contracts\BulkProcessor;
use Tutor\Migrations\Contracts\SingleProcessor;

/**
 * Class BatchProcessor
 *
 * @since 3.8.0
 */
abstract class BatchProcessor {
	/**
	 * Batch size.
	 *
	 * @since 3.8.0
	 *
	 * @var int
	 */
	protected $batch_size;

	/**
	 * Schedule interval
	 *
	 * @since 3.8.0
	 *
	 * @var int
	 */
	protected $schedule_interval;

	/**
	 * Progress option name to keep track of each process status.
	 *
	 * @since 3.8.0
	 *
	 * @var string
	 */
	protected $progress_option;

	/**
	 * Manage child class as singleton behavior.
	 *
	 * @since 3.8.0
	 *
	 * @var object
	 */
	protected static $instances = array();

	/**
	 * Action name to invoke wp-cron.
	 *
	 * @since 3.8.0
	 *
	 * @var string
	 */
	protected $action;

	/**
	 * Name of the process.
	 *
	 * @since 3.8.0
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Constructor.
	 *
	 * @since 3.8.0
	 *
	 * @throws \Exception If action is not set.
	 */
	protected function __construct() {
		if ( empty( $this->action ) ) {
			throw new \Exception( 'Action property must be defined in the subclass.' );
		}

		$this->progress_option = "tutor_batch_processor_{$this->action}";
		add_action( $this->action, array( $this, 'process_batch' ) );
	}

	/**
	 * Get child class instance.
	 *
	 * @since 3.8.0
	 *
	 * @return object
	 */
	public static function instance() {
		if ( ! isset( static::$instances[ static::class ] ) ) {
			static::$instances[ static::class ] = new static();
		}
		return static::$instances[ static::class ];
	}

	/**
	 * Get items to process.
	 * Must be implemented in child class.
	 *
	 * @since 3.8.0
	 *
	 * @param int $offset offset.
	 * @param int $limit limit.
	 *
	 * @return array
	 */
	abstract protected function get_items( $offset, $limit) : array;

	/**
	 * Get total items to process.
	 * Must be implemented in child class.
	 *
	 * @since 3.8.0
	 *
	 * @return int
	 */
	abstract protected function get_total_items() : int;

	/**
	 * Schedule the batch processing.
	 *
	 * This method checks if the action is already scheduled, and if not, it schedules a single event
	 * to trigger the batch processing after the specified interval.
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	public function schedule() {
		if ( ! wp_next_scheduled( $this->action ) ) {
			wp_schedule_single_event( time() + $this->schedule_interval, $this->action );
		}
	}

	/**
	 * Process a batch of items.
	 *
	 * This method retrieves a batch of items based on the current offset and batch size,
	 * processes each item, updates the progress, and schedules the next batch processing.
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 *
	 * @throws \Exception If not implemented any interface on child class..
	 */
	public function process_batch() {
		$progress = get_option(
			$this->progress_option,
			array(
				'name'          => $this->name,
				'started_at'    => gmdate( 'Y-m-d H:i:s' ),
				'completed_at'  => null,
				'offset'        => 0,
				'completed'     => false,
				'total_items'   => null,
				'total_batch'   => null,
				'per_batch'     => $this->batch_size,
				'current_batch' => 1,
			)
		);

		if ( $progress['completed'] ) {
			return;
		}

		// Set total_items and total_batch if not already set.
		if ( is_null( $progress['total_items'] ) ) {
			$progress['total_items'] = $this->get_total_items();
			$progress['total_batch'] = (int) ceil( $progress['total_items'] / $progress['per_batch'] );
		}

		$items = $this->get_items( $progress['offset'], $this->batch_size );

		if ( empty( $items ) ) {
			$this->mark_complete();
			return;
		}

		if ( $this instanceof BulkProcessor ) {
			$this->process_items( $items );
		} elseif ( $this instanceof SingleProcessor ) {
			foreach ( $items as $item ) {
				$this->process_item( $item );
			}
		} else {
			throw new \Exception( 'Child class must implement SingleProcessor or BulkProcessor interface.' );
		}

		$progress['offset']       += count( $items );
		$progress['current_batch'] = (int) ceil( $progress['offset'] / $progress['per_batch'] );
		update_option( $this->progress_option, $progress );

		wp_schedule_single_event( time() + $this->schedule_interval, $this->action );
	}

	/**
	 * Mark the batch processing as complete.
	 *
	 * This method updates the progress option to indicate that the batch processing is complete
	 * and calls the optional `on_complete` method for any additional actions needed upon completion.
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	protected function mark_complete() {
		$data = wp_parse_args(
			array(
				'offset'       => 0,
				'completed'    => true,
				'completed_at' => gmdate( 'Y-m-d H:i:s' ),
			),
			$this->get_stats()
		);

		update_option( $this->progress_option, $data );

		$this->on_complete();
	}

	/**
	 * Optional method to override in child classes to perform actions when the batch processing is complete.
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	protected function on_complete() {}

	/**
	 * Check is complete.
	 *
	 * @since 3.8.0
	 *
	 * @return boolean
	 */
	public function is_completed(): bool {
		$progress = get_option( $this->progress_option, array() );
		return ! empty( $progress['completed'] );
	}

	/**
	 * Get the progress option name.
	 *
	 * @since 3.8.0
	 *
	 * @return string
	 */
	public function get_progress_option_name() {
		return $this->progress_option;
	}

	/**
	 * Get the progress stats.
	 *
	 * @since 3.8.0
	 *
	 * @return array
	 */
	public function get_stats(): array {
		$progress = get_option( $this->progress_option, array() );
		return wp_parse_args(
			$progress,
			array(
				'offset'        => 0,
				'completed'     => false,
				'total_items'   => 0,
				'total_batch'   => 0,
				'per_batch'     => $this->batch_size,
				'current_batch' => 1,
			)
		);
	}

	/**
	 * Reset the progress option.
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	public function reset() {
		delete_option( $this->progress_option );
	}
}
