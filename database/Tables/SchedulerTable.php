<?php
/**
 * Scheduler Table.
 *
 * @package Tutor\Database\Tables
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.3.0
 */

namespace Tutor\Database\Tables;

defined( 'ABSPATH' ) || exit;

use Tutor\Core\Table;

/**
 * Class SchedulerTable
 *
 * @since 4.3.0
 */
class SchedulerTable extends Table {

	/**
	 * Base table name.
	 *
	 * @var string
	 */
	protected static $table = 'tutor_scheduler';

	/**
	 * Get CREATE TABLE SQL DDL schema.
	 *
	 * @since 4.3.0
	 *
	 * @param bool $include_constraints Whether to include foreign key constraints.
	 * @return string
	 */
	public static function get_schema( bool $include_constraints = true ): string {
		$table   = static::get_name();
		$collate = static::get_charset_collate();

		return "CREATE TABLE {$table} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			type VARCHAR(50) NOT NULL COMMENT 'Type of schedule, e.g., gift, email, reminder',
			reference_id VARCHAR(255) NOT NULL COMMENT 'Unique reference id, token, etc',
			scheduled_at_gmt DATETIME NOT NULL COMMENT 'When the action should be executed',
			status VARCHAR(255) NOT NULL DEFAULT 'processing',
			payload LONGTEXT,
			created_at_gmt DATETIME,
			updated_at_gmt DATETIME,
			scheduled_by BIGINT UNSIGNED COMMENT 'User who scheduled the action',
			scheduled_for BIGINT UNSIGNED COMMENT 'Target user of the scheduled action',
			PRIMARY KEY (id),
			KEY idx_context_status (type, status),
			KEY idx_status (status),
			KEY idx_scheduled_at_gmt (scheduled_at_gmt)
		) {$collate};";
	}
}

