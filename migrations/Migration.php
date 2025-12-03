<?php
/**
 * Migration
 *
 * @package Tutor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.8.0
 */

namespace Tutor\Migrations;

/**
 * Class Migration
 */
class Migration {
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->schedule_migrations();
	}

	/**
	 * Schedule migrations.
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	public function schedule_migrations() {
		$migrators = array(
			QuizAttemptMigrator::instance(),
		);

		if ( tutor_utils()->has_wc() ) {
			$migrators[] = ProcessByWcMigrator::instance();
		}

		foreach ( $migrators as $migrator ) {
			if ( ! $migrator->is_completed() ) {
				$migrator->schedule();
			}
		}
	}
}
