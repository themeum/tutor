<?php
/**
 * Orders Table.
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
 * Class OrdersTable
 *
 * @since 4.3.0
 */
class OrdersTable extends Table {

	/**
	 * Base table name.
	 *
	 * @var string
	 */
	protected static $table = 'tutor_orders';

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
			id BIGINT(20) UNSIGNED AUTO_INCREMENT,
			parent_id BIGINT(20) UNSIGNED DEFAULT 0,
			transaction_id VARCHAR(255),
			user_id BIGINT(20) UNSIGNED NOT NULL,
			order_type VARCHAR(50) NOT NULL,
			order_status VARCHAR(50) NOT NULL,
			payment_status VARCHAR(50) NOT NULL,
			subtotal_price DECIMAL(13, 2) NOT NULL,
			pre_tax_price DECIMAL(13, 2) NOT NULL,
			tax_type VARCHAR(50),
			tax_rate DECIMAL(13, 2),
			tax_amount DECIMAL(13, 2),
			total_price DECIMAL(13, 2) NOT NULL,
			net_payment DECIMAL(13, 2) NOT NULL,
			coupon_code VARCHAR(255),
			coupon_amount DECIMAL(13, 2),
			discount_type ENUM('percentage', 'flat') DEFAULT NULL,
			discount_amount DECIMAL(13, 2),
			discount_reason TEXT,
			fees DECIMAL(13, 2),
			earnings DECIMAL(13, 2),
			refund_amount DECIMAL(13, 2),
			payment_method VARCHAR(255),
			payment_payloads LONGTEXT,
			note TEXT,
			created_at_gmt DATETIME NOT NULL,
			created_by BIGINT(20) UNSIGNED NOT NULL,
			updated_at_gmt DATETIME,
			updated_by BIGINT(20) UNSIGNED NOT NULL,
			PRIMARY KEY (id),
			KEY user_id (user_id),
			KEY order_type (order_type),
			KEY payment_status (payment_status),
			KEY order_status (order_status),
			KEY transaction_id (transaction_id)
		) {$collate};";
	}
}

