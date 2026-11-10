<?php
/**
 * Earnings Table.
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
 * Class EarningsTable
 *
 * @since 4.3.0
 */
class EarningsTable extends Table {

	/**
	 * Base table name.
	 *
	 * @var string
	 */
	protected static $table = 'tutor_earnings';

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

		return "CREATE TABLE IF NOT EXISTS {$table} (
			earning_id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) DEFAULT NULL,
			course_id bigint(20) DEFAULT NULL,
			order_id bigint(20) DEFAULT NULL,
			order_status varchar(50) DEFAULT NULL,
			course_price_total decimal(16,2) DEFAULT NULL,
			course_price_grand_total decimal(16,2) DEFAULT NULL,
			instructor_amount decimal(16,2) DEFAULT NULL,
			instructor_rate decimal(16,2) DEFAULT NULL,
			admin_amount decimal(16,2) DEFAULT NULL,
			admin_rate decimal(16,2) DEFAULT NULL,
			commission_type varchar(20) DEFAULT NULL,
			deduct_fees_amount decimal(16,2) DEFAULT NULL,
			deduct_fees_name varchar(250) DEFAULT NULL,
			deduct_fees_type varchar(20) DEFAULT NULL,
			process_by varchar(20) DEFAULT NULL,
			created_at datetime DEFAULT NULL,
			PRIMARY KEY (earning_id),
			INDEX (user_id),
			INDEX (course_id),
			INDEX (order_id),
			INDEX (process_by)
		) {$collate};";
	}
}

