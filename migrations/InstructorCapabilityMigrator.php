<?php
/**
 * Migration instructor to add capability.
 *
 * @package Tutor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.8
 */

namespace Tutor\Migrations;

use Tutor\Migrations\Contracts\SingleProcessor;
use Tutor\Models\UserModel;

/**
 * Class InstructorCapabilityMigrator
 *
 * @since 4.0.8
 */
class InstructorCapabilityMigrator extends BatchProcessor implements SingleProcessor {

	/**
	 * User model
	 *
	 * @var UserModel
	 */
	private $user_model;

	/**
	 * Name of the migration
	 *
	 * @since 4.0.8
	 *
	 * @var string
	 */
	protected $name = 'Instructor Capability Migrator';

	/**
	 * Action
	 *
	 * @since 4.0.8
	 *
	 * @var string
	 */
	protected $action = 'instructor_capability_migrator';

	/**
	 * Batch size
	 *
	 * @since 4.0.8
	 *
	 * @var integer
	 */
	protected $batch_size = 100;

	/**
	 * Schedule interval.
	 *
	 * @since 4.0.8
	 *
	 * @var integer
	 */
	protected $schedule_interval = 10;

	/**
	 * Get total unprocessed result.
	 *
	 * @since 4.0.8
	 *
	 * @return int
	 */
	protected function get_total_items(): int {
		$this->user_model = new UserModel();
		$users            = $this->user_model->get_users_list(
			array(
				'role'       => tutor()->instructor_role,
				'meta_key'   => '_tutor_instructor_status',
				'meta_value' => 'approved',
				'fields'     => 'ID',
			)
		);
		return $users->get_total();
	}

	/**
	 * Get items to batch process.
	 *
	 * @since 4.0.8
	 *
	 * @param int $offset offset.
	 * @param int $limit limit.
	 *
	 * @return array
	 */
	protected function get_items( $offset, $limit ): array {
		$this->user_model = new UserModel();
		$users            = $this->user_model->get_users_list(
			array(
				'role'       => tutor()->instructor_role,
				'meta_key'   => '_tutor_instructor_status',
				'meta_value' => 'approved',
				'number'     => $limit,
				'offset'     => $offset,
			)
		);
		return $users->get_results();
	}

	/**
	 * Process instructor to add capability.
	 *
	 * @since 4.0.8
	 *
	 * @param object $item item.
	 *
	 * @return void
	 */
	public function process_item( $item ): void {
		$user = new \WP_User( $item->ID );
		if ( ! $user->has_cap( 'edit_published_posts' ) ) {
			$user->add_cap( 'edit_published_posts' );
		}
	}

	/**
	 * On migration complete event.
	 *
	 * @since 4.0.8
	 *
	 * @return void
	 */
	protected function on_complete() {
		error_log( 'Instructor capability migration completed!' );
	}
}
