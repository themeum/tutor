<?php
/**
 * Coupons Table.
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
 * Class CouponsTable
 *
 * @since 4.3.0
 */
class CouponsTable extends Table {

	/**
	 * Base table name.
	 *
	 * @since 4.3.0
	 *
	 * @var string
	 */
	protected static $table = 'tutor_coupons';

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
			coupon_status VARCHAR(50),
			coupon_type VARCHAR(100) DEFAULT 'code',
			coupon_code VARCHAR(50) NOT NULL,
			coupon_title VARCHAR(255) NOT NULL,
			coupon_description TEXT,
			discount_type ENUM('percentage', 'flat') NOT NULL,
			discount_amount DECIMAL(13, 2) NOT NULL,
			applies_to VARCHAR(100) DEFAULT 'all_courses_and_bundles',
			total_usage_limit INT(10) UNSIGNED DEFAULT NULL,
			per_user_usage_limit TINYINT(4) UNSIGNED DEFAULT NULL,
			purchase_requirement VARCHAR(50) DEFAULT 'no_minimum',
			purchase_requirement_value DECIMAL(13, 2),
			start_date_gmt DATETIME NOT NULL,
			expire_date_gmt DATETIME DEFAULT NULL,
			created_at_gmt DATETIME NOT NULL,
			created_by BIGINT(20) UNSIGNED NOT NULL,
			updated_at_gmt DATETIME,
			updated_by BIGINT(20) UNSIGNED NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY coupon_code (coupon_code),
			KEY start_date_gmt (start_date_gmt),
			KEY expire_date_gmt (expire_date_gmt)
		) {$collate};";
	}
}

