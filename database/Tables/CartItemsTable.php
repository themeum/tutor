<?php
/**
 * Cart Items Table.
 *
 * @package Tutor\Database\Tables
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.3.0
 */

namespace Tutor\Database\Tables;

defined( 'ABSPATH' ) || exit;

use Tutor\Core\DB;
use Tutor\Core\Table;

/**
 * Class CartItemsTable
 *
 * @since 4.3.0
 */
class CartItemsTable extends Table {

	/**
	 * Base table name.
	 *
	 * @since 4.3.0
	 *
	 * @var string
	 */
	protected static $table = 'tutor_cart_items';

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
		$table       = static::get_name();
		$carts_table = CartsTable::get_name();
		$posts_table = DB::prepare_table_name( 'posts' );
		$collate     = static::get_charset_collate();

		$constraints = '';
		if ( $include_constraints ) {
			$fk_cart     = static::fk_name( 'fk_tutor_cart_item_cart_id' );
			$fk_post     = static::fk_name( 'fk_tutor_cart_item_course_id' );
			$constraints = ",
			CONSTRAINT {$fk_cart} FOREIGN KEY (cart_id) REFERENCES {$carts_table}(id) ON DELETE CASCADE,
			CONSTRAINT {$fk_post} FOREIGN KEY (course_id) REFERENCES {$posts_table}(ID) ON DELETE CASCADE";
		}

		return "CREATE TABLE {$table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			cart_id BIGINT(20) UNSIGNED NOT NULL,
			course_id BIGINT(20) UNSIGNED NOT NULL,
			item_type VARCHAR(255),
			item_details JSON,
			PRIMARY KEY (id),
			KEY cart_id (cart_id),
			KEY course_id (course_id){$constraints}
		) {$collate};";
	}
}

