<?php
/**
 * Table Manager for Tutor LMS.
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

defined( 'ABSPATH' ) || exit;

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
	protected static function get_table_classes(): array {
		$classes = apply_filters( 'tutor_database_table_classes', self::CORE_TABLE_CLASSES );

		/**
		 * Validate the filtered list:
		 * 1. Keep only strings that are subclasses of Tutor\Core\Table.
		 * 2. Remove duplicates in case a class is registered more than once.
		 */
		$classes = array_unique(
			array_filter(
				$classes,
				fn( $class ) => is_string( $class ) && is_a( $class, Table::class, true )
			)
		);

		return $classes;
	}

	/**
	 * Get all known tables (Core + Addons + Pro via filter).
	 *
	 * @since 4.3.0
	 *
	 * @return array<string,string> Base table name => prefixed table name.
	 */
	public static function get_table_names(): array {
		$table_names = array();
		foreach ( static::get_table_classes() as $class ) {
			$table_names[ $class::get_base_name() ] = $class::get_name();
		}

		return apply_filters( 'tutor_database_tables', $table_names );
	}
	/**
	 * Create or update a single table using dbDelta.
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
	 * Create only the Core tables.
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

		do_action( 'tutor_database_core_tables_created', $include_constraints );
	}

	/**
	 * Safely drop all known Tutor tables.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public static function drop_tables(): void {
		global $wpdb;
		$table_names = array_values( self::get_table_names() );

		// Temporarily disable FK checks to cleanly drop without constraint order issues.
		$wpdb->query( 'SET FOREIGN_KEY_CHECKS = 0' );

		foreach ( $table_names as $table_name ) {
			$wpdb->query( "DROP TABLE IF EXISTS `{$table_name}`" ); //phpcs:ignore
		}

		$wpdb->query( 'SET FOREIGN_KEY_CHECKS = 1' );

		do_action( 'tutor_database_tables_dropped' );
	}
}
