<?php
/**
 * Withdraws Table.
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
 * Class WithdrawsTable
 *
 * @since 4.3.0
 */
class WithdrawsTable extends Table {

	/**
	 * Base table name.
	 *
	 * @var string
	 */
	protected static $table = 'tutor_withdraws';

	/**
	 * Get CREATE TABLE SQL DDL schema.
	 *
	 * @since 4.3.0
	 *
	 * @param bool $include_constraints Whether to include foreign key constraints.
	 *
	 * @return string
	 */
	public static function get_schema( bool $include_constraints = true ): string {
		$table   = static::get_name();
		$collate = static::get_charset_collate();

		return "CREATE TABLE IF NOT EXISTS {$table} (
			withdraw_id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) DEFAULT NULL,
			amount decimal(16,2) DEFAULT NULL,
			method_data text DEFAULT NULL,
			status varchar(50) DEFAULT NULL,
			updated_at datetime DEFAULT NULL,
			created_at datetime DEFAULT NULL,
			PRIMARY KEY (withdraw_id)
		) {$collate};";
	}
}

