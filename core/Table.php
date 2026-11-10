<?php
/**
 * Base Table Definition for Tutor LMS.
 *
 * Provides a standardized contract and consistent API for database table names,
 * schema generation, and execution.
 *
 * @package Tutor\Core
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.3.0
 */

namespace Tutor\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Abstract Class Table
 *
 * @since 4.3.0
 */
abstract class Table {

	/**
	 * Base table name without prefix (e.g. 'tutor_orders').
	 *
	 * @since 4.3.0
	 *
	 * @var string
	 */
	protected static $table = '';

	/**
	 * Get the un-prefixed base table name (e.g. 'tutor_orders').
	 *
	 * @since 4.3.0
	 *
	 * @return string
	 */
	public static function get_base_name(): string {
		return static::$table;
	}

	/**
	 * Get the prefixed table name for the active blog context.
	 * Resolves global tables to base_prefix and subsite tables to blog prefix.
	 *
	 * @since 4.3.0
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return DB::prepare_table_name( static::$table );
	}

	/**
	 * Get the database charset collate.
	 *
	 * @since 4.3.0
	 *
	 * @return string
	 */
	public static function get_charset_collate(): string {
		global $wpdb;
		return $wpdb->get_charset_collate();
	}

	/**
	 * Get CREATE TABLE SQL DDL schema.
	 *
	 * @since 4.3.0
	 *
	 * @param bool $include_constraints Whether to include foreign key constraints.
	 * @return string
	 */
	abstract public static function get_schema( bool $include_constraints = true ): string;

	/**
	 * Helper to generate a unique foreign key constraint name per blog prefix (<= 64 chars).
	 *
	 * @since 4.3.0
	 *
	 * @param string $name Constraint base name.
	 * @return string
	 */
	public static function fk_name( string $name ): string {
		global $wpdb;
		$fk = $wpdb->prefix . $name;
		return strlen( $fk ) <= 64 ? $fk : 'fk_' . substr( md5( $fk ), 0, 32 );
	}

	/**
	 * Create or update this table using dbDelta.
	 *
	 * @since 4.3.0
	 *
	 * @param bool $include_constraints Whether to include foreign key constraints.
	 * @return void
	 */
	public static function create( bool $include_constraints = true ): void {
		if ( ! function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		dbDelta( static::get_schema( $include_constraints ) );
	}

	/**
	 * Drop this table if it exists.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public static function drop(): void {
		global $wpdb;
		$table_name = static::get_name();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "DROP TABLE IF EXISTS `{$table_name}`" );
	}

	/**
	 * Check if this table exists in the database.
	 *
	 * @since 4.3.0
	 *
	 * @return bool
	 */
	public static function exists(): bool {
		return DB::has_table( static::get_name() );
	}
}

