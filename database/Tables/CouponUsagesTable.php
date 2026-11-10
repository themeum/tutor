<?php
/**
 * Coupon Usages Table.
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
 * Class CouponUsagesTable
 *
 * @since 4.3.0
 */
class CouponUsagesTable extends Table {

	/**
	 * Base table name.
	 *
	 * @var string
	 */
	protected static $table = 'tutor_coupon_usages';

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
		$table           = static::get_name();
		$coupons_table   = CouponsTable::get_name();
		$users_table     = DB::prepare_table_name( 'users' );
		$collate         = static::get_charset_collate();
		$status_consumed = 'consumed';

		$constraints = '';
		if ( $include_constraints ) {
			$fk_coupon   = static::fk_name( 'fk_tutor_coupon_usage_coupon_code' );
			$fk_user     = static::fk_name( 'fk_tutor_coupon_usage_user_id' );
			$constraints = ",
			CONSTRAINT {$fk_coupon} FOREIGN KEY (coupon_code) REFERENCES {$coupons_table}(coupon_code) ON DELETE CASCADE,
			CONSTRAINT {$fk_user} FOREIGN KEY (user_id) REFERENCES {$users_table}(ID) ON DELETE CASCADE";
		}

		return "CREATE TABLE {$table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			coupon_code VARCHAR(50) NOT NULL,
			user_id BIGINT(20) UNSIGNED NOT NULL,
			order_id BIGINT(20) UNSIGNED DEFAULT NULL,
			status VARCHAR(20) NOT NULL DEFAULT '{$status_consumed}',
			expires_at_gmt DATETIME DEFAULT NULL,
			created_at_gmt DATETIME NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY coupon_order (coupon_code, order_id),
			KEY coupon_code (coupon_code),
			KEY user_id (user_id),
			KEY order_id (order_id),
			KEY status (status){$constraints}
		) {$collate};";
	}
}

