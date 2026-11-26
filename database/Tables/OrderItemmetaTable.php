<?php
/**
 * Order Item Meta Table.
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
 * Class OrderItemmetaTable
 *
 * @since 4.3.0
 */
class OrderItemmetaTable extends Table {

	/**
	 * Base table name.
	 *
	 * @since 4.3.0
	 *
	 * @var string
	 */
	protected static $table = 'tutor_order_itemmeta';

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
		$table             = static::get_name();
		$order_items_table = OrderItemsTable::get_name();
		$collate           = static::get_charset_collate();

		$constraints = '';
		if ( $include_constraints ) {
			$fk_name     = static::fk_name( 'fk_tutor_itemmeta' );
			$constraints = ", CONSTRAINT {$fk_name} FOREIGN KEY (item_id) REFERENCES {$order_items_table}(id) ON DELETE CASCADE";
		}

		return "CREATE TABLE {$table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			item_id BIGINT(20) UNSIGNED NOT NULL,
			meta_key VARCHAR(255) NOT NULL,
			meta_value LONGTEXT NOT NULL,
			PRIMARY KEY (id),
			KEY item_id (item_id),
			KEY meta_key (meta_key){$constraints}
		) {$collate};";
	}
}

