<?php
/**
 * Coupon Applications Table.
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
 * Class CouponApplicationsTable
 *
 * @since 4.3.0
 */
class CouponApplicationsTable extends Table {

	/**
	 * Base table name.
	 *
	 * @var string
	 */
	protected static $table = 'tutor_coupon_applications';

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
		$table         = static::get_name();
		$coupons_table = CouponsTable::get_name();
		$collate       = static::get_charset_collate();

		$constraints = '';
		if ( $include_constraints ) {
			$fk_name     = static::fk_name( 'fk_tutor_coupon_application_coupon_code' );
			$constraints = ", CONSTRAINT {$fk_name} FOREIGN KEY (coupon_code) REFERENCES {$coupons_table}(coupon_code) ON DELETE CASCADE";
		}

		return "CREATE TABLE {$table} (
			coupon_code VARCHAR(50) NOT NULL,
			reference_id BIGINT(20) UNSIGNED NOT NULL,
			KEY coupon_code (coupon_code),
			KEY reference_id (reference_id){$constraints}
		) {$collate};";
	}
}

