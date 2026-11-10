<?php
/**
 * Order Items Table.
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
 * Class OrderItemsTable
 *
 * @since 4.3.0
 */
class OrderItemsTable extends Table {

	/**
	 * Base table name.
	 *
	 * @var string
	 */
	protected static $table = 'tutor_order_items';

	/**
	 * Get CREATE TABLE SQL DDL schema.
	 *
	 * @since 4.3.0
	 *
	 * @param bool $include_constraints Whether to include foreign key constraints.
	 * @return string
	 */
	public static function get_schema( bool $include_constraints = true ): string {
		$table        = static::get_name();
		$orders_table = OrdersTable::get_name();
		$collate      = static::get_charset_collate();

		$constraints = '';
		if ( $include_constraints ) {
			$fk_name     = static::fk_name( 'fk_tutor_order_item_order_id' );
			$constraints = ", CONSTRAINT {$fk_name} FOREIGN KEY (order_id) REFERENCES {$orders_table}(id) ON DELETE CASCADE";
		}

		return "CREATE TABLE {$table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			order_id BIGINT(20) UNSIGNED NOT NULL,
			item_id BIGINT(20) UNSIGNED NOT NULL,
			regular_price DECIMAL(13, 2) NOT NULL,
			sale_price VARCHAR(13) DEFAULT NULL,
			discount_price VARCHAR(13) DEFAULT NULL,
			coupon_code VARCHAR(255) DEFAULT NULL,
			PRIMARY KEY (id),
			KEY order_id (order_id),
			KEY item_id (item_id){$constraints}
		) {$collate};";
	}
}

