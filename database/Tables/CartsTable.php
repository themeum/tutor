<?php
/**
 * Carts Table.
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
 * Class CartsTable
 *
 * @since 4.3.0
 */
class CartsTable extends Table {

	/**
	 * Base table name.
	 *
	 * @var string
	 */
	protected static $table = 'tutor_carts';

	/**
	 * Get CREATE TABLE SQL DDL schema.
	 *
	 * @since 4.3.0
	 *
	 * @param bool $include_constraints Whether to include foreign key constraints.
	 * @return string
	 */
	public static function get_schema( bool $include_constraints = true ): string {
		$table       = static::get_name();
		$users_table = DB::prepare_table_name( 'users' );
		$collate     = static::get_charset_collate();

		$constraints = '';
		if ( $include_constraints ) {
			$fk_user     = static::fk_name( 'fk_tutor_cart_user_id' );
			$constraints = ", CONSTRAINT {$fk_user} FOREIGN KEY (user_id) REFERENCES {$users_table}(ID) ON DELETE CASCADE";
		}

		return "CREATE TABLE {$table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT(20) UNSIGNED DEFAULT NULL,
			coupon_code VARCHAR(50) DEFAULT NULL,
			created_at_gmt DATETIME NOT NULL,
			updated_at_gmt DATETIME,
			PRIMARY KEY (id),
			KEY user_id (user_id),
			KEY coupon_code (coupon_code){$constraints}
		) {$collate};";
	}
}

