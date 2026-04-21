<?php
/**
 * GDPR DB base class.
 *
 * @package Tutor\GDPR\DB
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\GDPR\DB;

defined( 'ABSPATH' ) || exit;

/**
 * Abstract GDPR DB table class.
 */
abstract class DB {

	/**
	 * Create all GDPR tables.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public static function create_tables() {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		foreach ( static::tables() as $table_class ) {
			$sql = $table_class::get_schema();
			if ( ! empty( $sql ) ) {
				dbDelta( $sql );
			}
		}
	}

	/**
	 * Drop all GDPR tables.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public static function drop_tables() {
		global $wpdb;

		foreach ( static::tables() as $table_class ) {
			$table_name = $table_class::get_table_name();
			$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" ); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
	}

	/**
	 * Registered GDPR table classes.
	 *
	 * @since 4.0.0
	 *
	 * @return array
	 */
	protected static function tables() {
		return array(
			LegalConsents::class,
			UserConsents::class,
			Logs::class,
		);
	}

	/**
	 * Get table name.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	abstract public static function get_table_name();

	/**
	 * Get create table SQL.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	abstract public static function get_schema();
}
