<?php
/**
 * Customers Table.
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
 * Class CustomersTable
 *
 * @since 4.3.0
 */
class CustomersTable extends Table {

	/**
	 * Base table name.
	 *
	 * @since 4.3.0
	 *
	 * @var string
	 */
	protected static $table = 'tutor_customers';

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

		return "CREATE TABLE {$table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT(20) UNSIGNED DEFAULT NULL,
			billing_first_name VARCHAR(255) NOT NULL,
			billing_last_name VARCHAR(255) NOT NULL,
			billing_email VARCHAR(255) NOT NULL,
			billing_phone VARCHAR(20) NOT NULL,
			billing_zip_code VARCHAR(20) NOT NULL,
			billing_address TEXT NOT NULL,
			billing_country VARCHAR(100) NOT NULL,
			billing_state VARCHAR(100) NOT NULL,
			billing_city VARCHAR(100) NOT NULL,
			PRIMARY KEY (id),
			KEY user_id (user_id),
			KEY billing_email (billing_email)
		) {$collate};";
	}
}

