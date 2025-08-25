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
		QuizAttemptMigrator::instance()->is_completed() ? null : QuizAttemptMigrator::instance()->schedule();
	}
}
