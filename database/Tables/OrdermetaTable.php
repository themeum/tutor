<?php
/**
 * Order Meta Table.
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
 * Class OrdermetaTable
 *
 * @since 4.3.0
 */
class OrdermetaTable extends Table {

	/**
	 * Base table name.
	 *
	 * @var string
	 */
	protected static $table = 'tutor_ordermeta';

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
		$table        = static::get_name();
		$orders_table = OrdersTable::get_name();
		$collate      = static::get_charset_collate();

		$constraints = '';
		if ( $include_constraints ) {
			$fk_name     = static::fk_name( 'fk_tutor_ordermeta_order_id' );
			$constraints = ", CONSTRAINT {$fk_name} FOREIGN KEY (order_id) REFERENCES {$orders_table}(id) ON DELETE CASCADE";
		}

		return "CREATE TABLE {$table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			order_id BIGINT(20) UNSIGNED NOT NULL,
			meta_key VARCHAR(255) NOT NULL,
			meta_value LONGTEXT NOT NULL,
			created_at_gmt DATETIME NOT NULL,
			created_by BIGINT(20) UNSIGNED NOT NULL,
			updated_at_gmt DATETIME,
			updated_by BIGINT(20) UNSIGNED NOT NULL,
			PRIMARY KEY (id),
			KEY order_id (order_id),
			KEY meta_key (meta_key){$constraints}
		) {$collate};";
	}
}

