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

use Tutor\Core\Table;
use Tutor\Database\Tables\QuizAttemptsTable;
use Tutor\Database\Tables\QuizAttemptAnswersTable;
use Tutor\Database\Tables\QuizQuestionsTable;
use Tutor\Database\Tables\QuizQuestionAnswersTable;
use Tutor\Database\Tables\EarningsTable;
use Tutor\Database\Tables\WithdrawsTable;
use Tutor\Database\Tables\OrdersTable;
use Tutor\Database\Tables\OrdermetaTable;
use Tutor\Database\Tables\OrderItemsTable;
use Tutor\Database\Tables\OrderItemmetaTable;
use Tutor\Database\Tables\CouponsTable;
use Tutor\Database\Tables\CouponApplicationsTable;
use Tutor\Database\Tables\CouponUsagesTable;
use Tutor\Database\Tables\CartsTable;
use Tutor\Database\Tables\CartItemsTable;
use Tutor\Database\Tables\CustomersTable;
use Tutor\Database\Tables\SchedulerTable;

defined( 'ABSPATH' ) || exit;

/**
 * Class TableManager
 *
 * @since 4.3.0
 */
class TableManager {

	/**
	 * Core table classes created on initial installation or new subsite provisioning.
	 *
	 * @var array<class-string<\Tutor\Core\Table>>
	 */
	const CORE_TABLE_CLASSES = array(
		QuizAttemptsTable::class,
		QuizAttemptAnswersTable::class,
		QuizQuestionsTable::class,
		QuizQuestionAnswersTable::class,
		EarningsTable::class,
		WithdrawsTable::class,
		OrdersTable::class,
		OrdermetaTable::class,
		OrderItemsTable::class,
		OrderItemmetaTable::class,
		CouponsTable::class,
		CouponApplicationsTable::class,
		CouponUsagesTable::class,
		CartsTable::class,
		CartItemsTable::class,
		CustomersTable::class,
		SchedulerTable::class,
	);

	/**
	 * Get all table classes (Core + Addons + Pro via filter).
	 *
	 * @since 4.3.0
	 *
	 * @return array<class-string<\Tutor\Core\Table>> Array of table classes.
	 */
	public static function get_table_classes(): array {
		$core_classes = self::CORE_TABLE_CLASSES;

		/**
		 * Filter to register additional addon tables from Pro and third-party addons.
		 *
		 * @since 4.3.0
		 *
		 * @param array<class-string<\Tutor\Core\Table>> $all_classes Array of table classes.
		 */
		$all_classes = apply_filters( 'tutor_database_table_classes', $core_classes );

		// Filter is instance of Table.
		$all_classes = array_filter( $all_classes, fn( $class ) => $class instanceof Table );

		return $all_classes;
	}

	/**
	 * Get all known tables (Core + Addons + Pro via filter).
	 * Used for drop/cleanup operations across single-site and multisite.
	 *
	 * @since 4.3.0
	 *
	 * @return array<string> Array of base table names.
	 */
	public static function get_table_names(): array {
		$table_names = array_map(
			fn( $class ) => $class::get_name(),
			static::get_table_classes()
		);

		/**
		 * Filter to register additional addon tables from Pro and third-party addons.
		 *
		 * @since 4.3.0
		 *
		 * @param array<string> $all_tables Array of base table names.
		 */
		return apply_filters( 'tutor_database_tables', $table_names );
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
		if ( ! function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

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
		foreach ( self::CORE_TABLE_CLASSES as $table_class ) {
			$table_class::create( $include_constraints );
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
	 * Safely drop all known Tutor tables for the current active blog context.
	 * Drops both Core and Addon tables using SET FOREIGN_KEY_CHECKS = 0.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public static function drop_tables(): void {
		global $wpdb;
		$table_names = self::get_table_names();

		// Temporarily disable FK checks to cleanly drop without constraint order issues.
		$wpdb->query( 'SET FOREIGN_KEY_CHECKS = 0' );

		foreach ( $table_names as $table_name ) {
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
