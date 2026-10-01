<?php
/**
 * DB Class Unit Test
 *
 * @package Tutor\Test
 * @since 4.1.2
 */

namespace TutorTest;

use Tutor\Helpers\DB;

/**
 * Unit tests for DB fluent API and SQL compilation.
 *
 * Run test: vendor/bin/phpunit --filter=DBTest
 */
class DBTest extends \WP_UnitTestCase {

	/**
	 * Test table prefixing.
	 *
	 * @return void
	 */
	public function test_table_prefixing() {
		global $wpdb;

		$prefix = $wpdb->prefix;

		// Without prefix.
		$builder = DB::table( 'tutor_orders' );
		$this->assertSame( "SELECT * FROM {$prefix}tutor_orders", $builder->to_sql() );

		// With prefix already included.
		$builder2 = DB::table( "{$prefix}tutor_orders" );
		$this->assertSame( "SELECT * FROM {$prefix}tutor_orders", $builder2->to_sql() );
	}

	/**
	 * Test table alias.
	 *
	 * @return void
	 */
	public function test_table_alias() {
		global $wpdb;

		$prefix = $wpdb->prefix;
		$sql    = DB::table( 'tutor_orders' )
			->as( 'o' )
			->to_sql();

		$this->assertSame( "SELECT * FROM {$prefix}tutor_orders AS o", $sql );
	}

	/**
	 * Test select columns.
	 *
	 * @return void
	 */
	public function test_select_columns() {
		global $wpdb;

		$prefix = $wpdb->prefix;
		$sql    = DB::table( 'tutor_orders' )
			->select( 'id', 'order_status', 'total_price' )
			->to_sql();

		$this->assertSame( "SELECT id, order_status, total_price FROM {$prefix}tutor_orders", $sql );
	}

	/**
	 * Test select raw and distinct.
	 *
	 * @return void
	 */
	public function test_select_raw_and_distinct() {
		global $wpdb;

		$prefix = $wpdb->prefix;
		$sql    = DB::table( 'tutor_orders' )
			->distinct()
			->select( 'order_status' )
			->select_raw( 'COUNT(*) AS total' )
			->to_sql();

		$this->assertSame( "SELECT DISTINCT order_status, COUNT(*) AS total FROM {$prefix}tutor_orders", $sql );
	}

	/**
	 * Test JOIN clauses.
	 *
	 * @return void
	 */
	public function test_joins() {
		global $wpdb;

		$prefix = $wpdb->prefix;
		$sql    = DB::table( 'tutor_orders' )
			->as( 'o' )
			->select( 'o.id', 'u.user_login' )
			->join( "{$wpdb->users} AS u", 'o.user_id', '=', 'u.ID' )
			->left_join( 'posts AS p', 'o.course_id', '=', 'p.ID' )
			->to_sql();

		$expected = "SELECT o.id, u.user_login FROM {$prefix}tutor_orders AS o INNER JOIN {$wpdb->users} AS u ON o.user_id = u.ID LEFT JOIN {$prefix}posts AS p ON o.course_id = p.ID";
		$this->assertSame( $expected, $sql );
	}

	/**
	 * Test basic WHERE conditions and operators.
	 *
	 * @return void
	 */
	public function test_where_conditions() {
		global $wpdb;

		$prefix = $wpdb->prefix;
		$sql    = DB::table( 'tutor_orders' )
			->where( 'order_status', 'completed' )
			->where( 'total_price', '>', 50 )
			->where( 'user_id', '=', 10 )
			->to_sql();

		$expected = "SELECT * FROM {$prefix}tutor_orders WHERE order_status = 'completed' AND total_price > 50 AND user_id = 10";
		$this->assertSame( $expected, $sql );
	}

	/**
	 * Test OR WHERE conditions.
	 *
	 * @return void
	 */
	public function test_or_where_conditions() {
		global $wpdb;

		$prefix = $wpdb->prefix;
		$sql    = DB::table( 'tutor_orders' )
			->where( 'order_status', 'completed' )
			->or_where( 'order_status', 'processing' )
			->to_sql();

		$expected = "SELECT * FROM {$prefix}tutor_orders WHERE order_status = 'completed' OR order_status = 'processing'";
		$this->assertSame( $expected, $sql );
	}

	/**
	 * Test WHERE IN and WHERE NOT IN.
	 *
	 * @return void
	 */
	public function test_where_in_and_not_in() {
		global $wpdb;

		$prefix = $wpdb->prefix;
		$sql    = DB::table( 'tutor_orders' )
			->where_in( 'order_status', array( 'completed', 'pending' ) )
			->where_not_in( 'user_id', array( 1, 2, 3 ) )
			->to_sql();

		$expected = "SELECT * FROM {$prefix}tutor_orders WHERE order_status IN ('completed', 'pending') AND user_id NOT IN (1, 2, 3)";
		$this->assertSame( $expected, $sql );
	}

	/**
	 * Test WHERE NULL and WHERE NOT NULL.
	 *
	 * @return void
	 */
	public function test_where_null_and_not_null() {
		global $wpdb;

		$prefix = $wpdb->prefix;
		$sql    = DB::table( 'tutor_orders' )
			->where_null( 'deleted_at' )
			->where_not_null( 'completed_at' )
			->to_sql();

		$expected = "SELECT * FROM {$prefix}tutor_orders WHERE deleted_at IS NULL AND completed_at IS NOT NULL";
		$this->assertSame( $expected, $sql );
	}

	/**
	 * Test WHERE BETWEEN and NOT BETWEEN.
	 *
	 * @return void
	 */
	public function test_where_between() {
		global $wpdb;

		$prefix = $wpdb->prefix;
		$sql    = DB::table( 'tutor_orders' )
			->where_between( 'created_at', '2024-01-01', '2024-12-31' )
			->where_not_between( 'total_price', 10, 20 )
			->to_sql();

		$expected = "SELECT * FROM {$prefix}tutor_orders WHERE created_at BETWEEN '2024-01-01' AND '2024-12-31' AND total_price NOT BETWEEN 10 AND 20";
		$this->assertSame( $expected, $sql );
	}

	/**
	 * Test WHERE LIKE.
	 *
	 * @return void
	 */
	public function test_where_like() {
		global $wpdb;

		$prefix = $wpdb->prefix;
		$sql    = DB::table( 'posts' )
			->where_like( 'post_title', 'WordPress' )
			->to_sql();

		$expected = "SELECT * FROM {$prefix}posts WHERE post_title LIKE '%WordPress%'";
		$this->assertSame( $expected, $sql );
	}

	/**
	 * Test WHERE RAW with bindings.
	 *
	 * @return void
	 */
	public function test_where_raw() {
		global $wpdb;

		$prefix = $wpdb->prefix;
		$sql    = DB::table( 'tutor_orders' )
			->where_raw( 'DATE(created_at) = %s', array( '2024-05-01' ) )
			->or_where_raw( 'id = %d', array( 99 ) )
			->to_sql();

		$expected = "SELECT * FROM {$prefix}tutor_orders WHERE DATE(created_at) = '2024-05-01' OR id = 99";
		$this->assertSame( $expected, $sql );
	}

	/**
	 * Test GROUP BY, HAVING, and ORDER BY.
	 *
	 * @return void
	 */
	public function test_group_having_order() {
		global $wpdb;

		$prefix = $wpdb->prefix;
		$sql    = DB::table( 'tutor_orders' )
			->select( 'order_status' )
			->select_raw( 'COUNT(*) AS total' )
			->group_by( 'order_status' )
			->having_raw( 'COUNT(*) > %d', array( 5 ) )
			->order_by( 'total', 'DESC' )
			->limit( 10 )
			->offset( 20 )
			->to_sql();

		$expected = "SELECT order_status, COUNT(*) AS total FROM {$prefix}tutor_orders GROUP BY order_status HAVING COUNT(*) > 5 ORDER BY total DESC LIMIT 10 OFFSET 20";
		$this->assertSame( $expected, $sql );
	}

	/**
	 * Test latest and oldest helpers.
	 *
	 * @return void
	 */
	public function test_latest_and_oldest() {
		global $wpdb;

		$prefix = $wpdb->prefix;
		$latest = DB::table( 'posts' )->latest()->to_sql();
		$this->assertSame( "SELECT * FROM {$prefix}posts ORDER BY id DESC", $latest );

		$oldest = DB::table( 'posts' )->oldest( 'post_date' )->to_sql();
		$this->assertSame( "SELECT * FROM {$prefix}posts ORDER BY post_date ASC", $oldest );
	}

	/**
	 * Test when() conditional helper.
	 *
	 * @return void
	 */
	public function test_when_helper() {
		global $wpdb;

		$prefix = $wpdb->prefix;

		$search = 'active';
		$sql    = DB::table( 'tutor_orders' )
			->when(
				$search,
				function ( $query, $value ) {
					$query->where( 'status', $value );
				}
			)
			->when(
				false,
				function ( $query ) {
					$query->where( 'status', 'ignored' );
				}
			)
			->to_sql();

		$this->assertSame( "SELECT * FROM {$prefix}tutor_orders WHERE status = 'active'", $sql );
	}

	/**
	 * Test self-contained value preparation helpers.
	 *
	 * @return void
	 */
	public function test_prepare_value() {
		$this->assertSame( '42', DB::prepare_value( 42 ) );
		$this->assertSame( "'hello'", DB::prepare_value( 'hello' ) );
		$this->assertSame( '1', DB::prepare_value( true ) );
		$this->assertSame( '0', DB::prepare_value( false ) );
		$this->assertSame( 'NULL', DB::prepare_value( null ) );
		$this->assertSame( '19.99', DB::prepare_value( 19.99 ) );
	}

	/**
	 * Test prepare_in_clause helper.
	 *
	 * @return void
	 */
	public function test_prepare_in_clause() {
		$in = DB::prepare_in_clause( array( 'apple', 123, 'banana' ) );
		$this->assertSame( "'apple', 123, 'banana'", $in );
	}

	/**
	 * Test identifier validation prevents injection in column names.
	 *
	 * @return void
	 */
	public function test_identifier_sanitization() {
		global $wpdb;

		$prefix = $wpdb->prefix;

		// Malicious column attempt should be ignored.
		$sql = DB::table( 'tutor_orders' )
			->select( 'id', 'status; DROP TABLE tutor_orders; --' )
			->where( '1=1; --', 'val' )
			->to_sql();

		$this->assertSame( "SELECT id FROM {$prefix}tutor_orders", $sql );
	}

	/**
	 * Test live database execution: insert, find, first, value, pluck, count, exists, update, and delete.
	 *
	 * @return void
	 */
	public function test_database_crud_operations() {
		// 1. Insert single row.
		$unique_key = 'qb_test_' . time() . '_' . wp_rand( 1000, 9999 );
		$insert_id  = DB::table( 'options' )
			->insert(
				array(
					'option_name'  => $unique_key,
					'option_value' => 'initial_value',
					'autoload'     => 'no',
				)
			);

		$this->assertGreaterThan( 0, $insert_id );

		// 2. exists().
		$exists = DB::table( 'options' )
			->where( 'option_name', $unique_key )
			->exists();
		$this->assertTrue( $exists );

		// 3. find() by primary key.
		$row = DB::table( 'options' )->find( $insert_id, 'option_id' );
		$this->assertNotNull( $row );
		$this->assertSame( $unique_key, $row->option_name );
		$this->assertSame( 'initial_value', $row->option_value );

		// 4. first().
		$first_row = DB::table( 'options' )
			->where( 'option_name', $unique_key )
			->first( 'ARRAY_A' );
		$this->assertIsArray( $first_row );
		$this->assertSame( 'initial_value', $first_row['option_value'] );

		// 5. value().
		$val = DB::table( 'options' )
			->where( 'option_name', $unique_key )
			->value( 'option_value' );
		$this->assertSame( 'initial_value', $val );

		// 6. count().
		$count = DB::table( 'options' )
			->where( 'option_name', $unique_key )
			->count();
		$this->assertSame( 1, $count );

		// 7. pluck().
		$plucked = DB::table( 'options' )
			->where( 'option_name', $unique_key )
			->pluck( 'option_value' );
		$this->assertSame( array( 'initial_value' ), $plucked );

		// 8. update().
		$updated = DB::table( 'options' )
			->where( 'option_name', $unique_key )
			->update(
				array(
					'option_value' => 'updated_value',
				)
			);
		$this->assertTrue( $updated );

		$new_val = DB::table( 'options' )
			->where( 'option_name', $unique_key )
			->value( 'option_value' );
		$this->assertSame( 'updated_value', $new_val );

		// 9. delete().
		$deleted = DB::table( 'options' )
			->where( 'option_name', $unique_key )
			->delete();
		$this->assertSame( 1, $deleted );

		$after_delete_exists = DB::table( 'options' )
			->where( 'option_name', $unique_key )
			->exists();
		$this->assertFalse( $after_delete_exists );
	}
}
