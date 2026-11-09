<?php
/**
 * Schema Manager for Tutor LMS.
 *
 * Orchestrates table creation, migration, and safe table dropping
 * with WordPress Multisite awareness.
 *
 * @package Tutor\Database
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.3.0
 */

namespace Tutor\Database;

use Tutor\Core\DB;

defined( 'ABSPATH' ) || exit;

/**
 * Class SchemaManager
 *
 * @since 4.3.0
 */
class TableManager {
	/**
	 * Core tables created on initial installation or new subsite provisioning.
	 *
	 * @var array<string, string> Key => table base name
	 */
	const CORE_TABLES = array(
		'quiz_attempts'         => 'tutor_quiz_attempts',
		'quiz_attempt_answers'  => 'tutor_quiz_attempt_answers',
		'quiz_questions'        => 'tutor_quiz_questions',
		'quiz_question_answers' => 'tutor_quiz_question_answers',
		'earnings'              => 'tutor_earnings',
		'withdraws'             => 'tutor_withdraws',
		'orders'                => 'tutor_orders',
		'ordermeta'             => 'tutor_ordermeta',
		'order_items'           => 'tutor_order_items',
		'order_itemmeta'        => 'tutor_order_itemmeta',
		'coupons'               => 'tutor_coupons',
		'coupon_applications'   => 'tutor_coupon_applications',
		'coupon_usages'         => 'tutor_coupon_usages',
		'carts'                 => 'tutor_carts',
		'cart_items'            => 'tutor_cart_items',
		'customers'             => 'tutor_customers',
		'scheduler'             => 'tutor_scheduler',
	);

	/**
	 * Get all known tables (Core + Addons + Pro via filter).
	 * Used for drop/cleanup operations across single-site and multisite.
	 *
	 * @since 4.3.0
	 *
	 * @return array<string, string> Key => base name
	 */
	public static function get_tables(): array {
		/**
		 * Filter to register additional addon tables from Pro and third-party addons.
		 *
		 * @since 4.3.0
		 *
		 * @param array<string, string> $all_tables Array of key => base table name.
		 */
		return apply_filters( 'tutor_database_tables', self::CORE_TABLES );
	}

	/**
	 * Create or update a single table using dbDelta.
	 * Reused across core tables, upgrade routines, and addon installers.
	 *
	 * @since 4.3.0
	 *
	 * @param string $schema CREATE TABLE SQL statement.
	 *
	 * @return void
	 */
	public static function create_table( string $schema ): void {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $schema );
	}

	/**
	 * Create only the Core tables for the current active blog context.
	 * Called on plugin activation and new subsite provisioning.
	 *
	 * @since 4.3.0
	 *
	 * @param bool $include_constraints Whether to include foreign key constraints.
	 *
	 * @return void
	 */
	public static function create_core_tables( bool $include_constraints = true ): void {
		$schemas = Schema::get_core_schemas( $include_constraints );

		foreach ( $schemas as $sql ) {
			self::create_table( $sql );
		}

		/**
		 * Action fired after all Tutor Core tables have been created for the current blog.
		 *
		 * @since 4.3.0
		 *
		 * @param bool $include_constraints Whether foreign key constraints were included.
		 */
		do_action( 'tutor_database_core_tables_created', $include_constraints );
	}

	/**
	 * Create all tutor and tutor-pro (filtered by) table.
	 *
	 * @since 4.3.0
	 *
	 * @param bool $include_constraints Whether to include foreign key constraints.
	 *
	 * @return void
	 */
	public static function create_tables( bool $include_constraints = true ):void {
		$schemas = Schema::get_all_schema( $include_constraints );
		foreach ( $schemas as $sql ) {
			self::create_table( $sql );
		}

		do_action( 'tutor_database_tables_created', $include_constraints );
	}

	/**
	 * Safely drop all known Tutor tables for the current active blog context.
	 * Drops both Core and Addon tables using SET FOREIGN_KEY_CHECKS = 0.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public static function drop_tables(): void {
		global $wpdb;

		$tables = self::get_tables();

		// Temporarily disable FK checks to cleanly drop without constraint order issues.
		$wpdb->query( 'SET FOREIGN_KEY_CHECKS = 0' );

		foreach ( $tables as $base_name ) {
			$table_name = DB::prepare_table_name( $base_name );
			$wpdb->query( "DROP TABLE IF EXISTS `{$table_name}`" ); //phpcs:ignore
		}

		$wpdb->query( 'SET FOREIGN_KEY_CHECKS = 1' );

		/**
		 * Action fired after Tutor tables have been dropped for the current blog.
		 *
		 * @since 4.3.0
		 */
		do_action( 'tutor_database_tables_dropped' );
	}
}

