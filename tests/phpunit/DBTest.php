<?php
/**
 * DB Class Unit Test
 *
 * @package Tutor\Test
 * @since 4.2.0
 */

namespace TutorTest;

use Tutor\Core\DB;

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
	 * Test WHERE with associative array conditions.
	 *
	 * @return void
	 */
	public function test_where_with_associative_array() {
		global $wpdb;

		$prefix = $wpdb->prefix;

		// Multiple column-value pairs combined with AND.
		$sql = DB::table( 'tutor_orders' )
			->where(
				array(
					'order_status' => 'completed',
					'user_id'      => 10,
					'total_price'  => 99.5,
				)
			)
			->to_sql();

		$expected = "SELECT * FROM {$prefix}tutor_orders WHERE order_status = 'completed' AND user_id = 10 AND total_price = 99.5";
		$this->assertSame( $expected, $sql );

		// Associative array with null value (auto-converts to IS NULL).
		$sql_null = DB::table( 'tutor_orders' )
			->where(
				array(
					'order_status' => 'completed',
					'deleted_at'   => null,
				)
			)
			->to_sql();

		$expected_null = "SELECT * FROM {$prefix}tutor_orders WHERE order_status = 'completed' AND deleted_at IS NULL";
		$this->assertSame( $expected_null, $sql_null );

		// Chaining associative array with existing single-condition syntax.
		$sql_chained = DB::table( 'tutor_orders' )
			->where(
				array(
					'order_status' => 'completed',
				)
			)
			->where( 'user_id', 10 )
			->where( 'total_price', '>', 50 )
			->to_sql();

		$expected_chained = "SELECT * FROM {$prefix}tutor_orders WHERE order_status = 'completed' AND user_id = 10 AND total_price > 50";
		$this->assertSame( $expected_chained, $sql_chained );
	}

	/**
	 * Test WHERE with numeric array conditions.
	 *
	 * @return void
	 */
	public function test_where_with_numeric_array() {
		global $wpdb;

		$prefix = $wpdb->prefix;

		// 3-element format: [column, operator, value].
		$sql = DB::table( 'tutor_orders' )
			->where(
				array(
					array( 'order_status', '=', 'completed' ),
					array( 'total_price', '>', 50 ),
					array( 'user_id', '!=', 5 ),
				)
			)
			->to_sql();

		$expected = "SELECT * FROM {$prefix}tutor_orders WHERE order_status = 'completed' AND total_price > 50 AND user_id != 5";
		$this->assertSame( $expected, $sql );

		// 2-element format: [column, value] defaulting to '='.
		$sql_two_elem = DB::table( 'tutor_orders' )
			->where(
				array(
					array( 'order_status', 'completed' ),
					array( 'user_id', 10 ),
				)
			)
			->to_sql();

		$expected_two_elem = "SELECT * FROM {$prefix}tutor_orders WHERE order_status = 'completed' AND user_id = 10";
		$this->assertSame( $expected_two_elem, $sql_two_elem );

		// Numeric array with null values.
		$sql_null = DB::table( 'tutor_orders' )
			->where(
				array(
					array( 'deleted_at', '=', null ),
					array( 'completed_at', '!=', null ),
				)
			)
			->to_sql();

		$expected_null = "SELECT * FROM {$prefix}tutor_orders WHERE deleted_at IS NULL AND completed_at IS NOT NULL";
		$this->assertSame( $expected_null, $sql_null );
	}

	/**
	 * Test WHERE array with various supported comparison operators.
	 *
	 * @return void
	 */
	public function test_where_array_supported_operators() {
		global $wpdb;

		$prefix = $wpdb->prefix;

		$sql = DB::table( 'tutor_orders' )
			->where(
				array(
					array( 'total_price', '>=', 20 ),
					array( 'total_price', '<=', 100 ),
					array( 'total_price', '<', 200 ),
					array( 'user_id', '<>', 0 ),
					array( 'order_status', 'LIKE', '%paid%' ),
					array( 'order_status', 'NOT LIKE', '%failed%' ),
				)
			)
			->to_sql();

		$expected = "SELECT * FROM {$prefix}tutor_orders WHERE total_price >= 20 AND total_price <= 100 AND total_price < 200 AND user_id <> 0 AND order_status LIKE '%paid%' AND order_status NOT LIKE '%failed%'";
		$this->assertSame( $expected, $sql );
	}

	/**
	 * Test WHERE with empty arrays and invalid condition formats.
	 *
	 * @return void
	 */
	public function test_where_empty_and_invalid_array_formats() {
		global $wpdb;

		$prefix = $wpdb->prefix;

		// Empty array produces no WHERE clause.
		$sql_empty = DB::table( 'tutor_orders' )
			->where( array() )
			->to_sql();
		$this->assertSame( "SELECT * FROM {$prefix}tutor_orders", $sql_empty );

		// Empty nested array ignored.
		$sql_nested_empty = DB::table( 'tutor_orders' )
			->where( array( array() ) )
			->to_sql();
		$this->assertSame( "SELECT * FROM {$prefix}tutor_orders", $sql_nested_empty );

		// Sub-array with 1 element ignored.
		$sql_one_elem = DB::table( 'tutor_orders' )
			->where( array( array( 'order_status' ) ) )
			->to_sql();
		$this->assertSame( "SELECT * FROM {$prefix}tutor_orders", $sql_one_elem );

		// Sub-array with 4 elements ignored.
		$sql_four_elem = DB::table( 'tutor_orders' )
			->where( array( array( 'order_status', '=', 'completed', 'extra' ) ) )
			->to_sql();
		$this->assertSame( "SELECT * FROM {$prefix}tutor_orders", $sql_four_elem );

		// Non-array scalar item in indexed array ignored.
		$sql_scalar = DB::table( 'tutor_orders' )
			->where( array( 123, 'random_string' ) )
			->to_sql();
		$this->assertSame( "SELECT * FROM {$prefix}tutor_orders", $sql_scalar );

		// Non-string column in sub-array ignored.
		$sql_non_string_col = DB::table( 'tutor_orders' )
			->where( array( array( 123, '=', 'active' ) ) )
			->to_sql();
		$this->assertSame( "SELECT * FROM {$prefix}tutor_orders", $sql_non_string_col );

		// Invalid operator in sub-array ignored.
		$sql_invalid_op = DB::table( 'tutor_orders' )
			->where( array( array( 'order_status', 'INVALID_OP', 'completed' ) ) )
			->to_sql();
		$this->assertSame( "SELECT * FROM {$prefix}tutor_orders", $sql_invalid_op );

		// Valid conditions mixed with invalid condition formats.
		$sql_mixed = DB::table( 'tutor_orders' )
			->where(
				array(
					'order_status' => 'completed',
					array(),
					array( 'invalid_op_col', 'BAD_OPERATOR', 'foo' ),
					array( 'total_price', '>', 50 ),
					999,
				)
			)
			->to_sql();
		$expected_mixed = "SELECT * FROM {$prefix}tutor_orders WHERE order_status = 'completed' AND total_price > 50";
		$this->assertSame( $expected_mixed, $sql_mixed );
	}

	/**
	 * Test SQL injection protection and value escaping in array conditions.
	 *
	 * @return void
	 */
	public function test_where_array_sql_injection_and_escaping() {
		global $wpdb;

		$prefix = $wpdb->prefix;

		// Malicious column attempt in associative key should be rejected.
		$sql_assoc_col = DB::table( 'tutor_orders' )
			->where(
				array(
					'1=1; --'      => 'val',
					'order_status' => 'completed',
				)
			)
			->to_sql();
		$this->assertSame( "SELECT * FROM {$prefix}tutor_orders WHERE order_status = 'completed'", $sql_assoc_col );

		// Malicious column attempt in numeric array should be rejected.
		$sql_num_col = DB::table( 'tutor_orders' )
			->where(
				array(
					array( 'id; DROP TABLE tutor_orders;--', '=', '1' ),
					array( 'order_status', '=', 'completed' ),
				)
			)
			->to_sql();
		$this->assertSame( "SELECT * FROM {$prefix}tutor_orders WHERE order_status = 'completed'", $sql_num_col );

		// Malicious operator attempt in numeric array should be rejected.
		$sql_op = DB::table( 'tutor_orders' )
			->where(
				array(
					array( 'order_status', '= 1; DROP TABLE tutor_orders;--', 'completed' ),
					array( 'user_id', '=', 10 ),
				)
			)
			->to_sql();
		$this->assertSame( "SELECT * FROM {$prefix}tutor_orders WHERE user_id = 10", $sql_op );

		// Value with SQL injection payload is properly escaped.
		$malicious_val = "active' OR '1'='1";
		$sql_val       = DB::table( 'tutor_orders' )
			->where(
				array(
					'order_status' => $malicious_val,
				)
			)
			->to_sql();
		$expected_val  = "SELECT * FROM {$prefix}tutor_orders WHERE order_status = 'active\' OR \'1\'=\'1'";
		$this->assertSame( $expected_val, $sql_val );

		// Numeric array value with SQL injection payload is properly escaped.
		$sql_num_val  = DB::table( 'tutor_orders' )
			->where(
				array(
					array( 'order_status', '=', $malicious_val ),
				)
			)
			->to_sql();
		$this->assertSame( $expected_val, $sql_num_val );

		// Live execution test with associative and numeric array conditions.
		$key1 = 'where_arr_test_1_' . time() . '_' . wp_rand( 1000, 9999 );
		$key2 = 'where_arr_test_2_' . time() . '_' . wp_rand( 1000, 9999 );

		DB::table( 'options' )->insert(
			array(
				'option_name'  => $key1,
				'option_value' => 'val1',
				'autoload'     => 'no',
			)
		);
		DB::table( 'options' )->insert(
			array(
				'option_name'  => $key2,
				'option_value' => 'val2',
				'autoload'     => 'no',
			)
		);

		// Query with associative array.
		$res1 = DB::table( 'options' )
			->where(
				array(
					'option_name' => $key1,
					'autoload'    => 'no',
				)
			)
			->first();
		$this->assertNotNull( $res1 );
		$this->assertSame( 'val1', $res1->option_value );

		// Query with numeric array.
		$res2 = DB::table( 'options' )
			->where(
				array(
					array( 'option_name', '=', $key2 ),
					array( 'autoload', '=', 'no' ),
				)
			)
			->first();
		$this->assertNotNull( $res2 );
		$this->assertSame( 'val2', $res2->option_value );

		// Clean up.
		DB::table( 'options' )->where( 'option_name', $key1 )->delete();
		DB::table( 'options' )->where( 'option_name', $key2 )->delete();
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

	/**
	 * Test column aliases in select() using uppercase AS, lowercase as, and implicit alias.
	 *
	 * @return void
	 */
	public function test_select_column_aliases() {
		global $wpdb;

		$prefix = $wpdb->prefix;
		$sql    = DB::table( 'orders' )
			->select( 'id AS order_id', 'parent_id as parent_order_id', 'name person_name' )
			->to_sql();

		$expected = "SELECT id AS order_id, parent_id AS parent_order_id, name AS person_name FROM {$prefix}orders";
		$this->assertSame( $expected, $sql );
	}

	/**
	 * Test mixing regular columns and aliased columns in select().
	 *
	 * @return void
	 */
	public function test_select_mixed_columns_and_aliases() {
		global $wpdb;

		$prefix = $wpdb->prefix;
		$sql    = DB::table( 'users' )
			->select( 'id', 'user_email AS email', 'display_name' )
			->to_sql();

		$expected = "SELECT id, user_email AS email, display_name FROM {$prefix}users";
		$this->assertSame( $expected, $sql );
	}

	/**
	 * Test column aliases with table prefixes in select().
	 *
	 * @return void
	 */
	public function test_select_aliases_with_table_prefix() {
		global $wpdb;

		$prefix = $wpdb->prefix;
		$sql    = DB::table( 'users' )
			->as( 'u' )
			->select( 'u.id AS user_id', 'u.user_login as username' )
			->to_sql();

		$expected = "SELECT u.id AS user_id, u.user_login AS username FROM {$prefix}users AS u";
		$this->assertSame( $expected, $sql );
	}

	/**
	 * Test that invalid column aliases or injection attempts in select() are rejected.
	 *
	 * @return void
	 */
	public function test_select_invalid_aliases_rejected() {
		global $wpdb;

		$prefix = $wpdb->prefix;
		$sql    = DB::table( 'orders' )
			->select( 'id AS valid_id', 'id AS 123invalid', 'id; DROP TABLE orders; --' )
			->to_sql();

		$expected = "SELECT id AS valid_id FROM {$prefix}orders";
		$this->assertSame( $expected, $sql );
	}

	/**
	 * Test closure-based join with multiple on() conditions.
	 *
	 * @return void
	 */
	public function test_join_with_closure_multiple_conditions() {
		global $wpdb;

		$prefix = $wpdb->prefix;
		$sql    = DB::table( 'users' )
			->join(
				'contacts',
				function ( $join ) {
					$join->on( 'users.id', '=', 'contacts.user_id' )
						->on( 'users.status', '=', 'contacts.status' );
				}
			)
			->select( 'users.*', 'contacts.phone' )
			->to_sql();

		$expected = "SELECT users.*, contacts.phone FROM {$prefix}users INNER JOIN {$prefix}contacts ON users.id = contacts.user_id AND users.status = contacts.status";
		$this->assertSame( $expected, $sql );
	}

	/**
	 * Test closure-based join with two arguments defaulting operator to '='.
	 *
	 * @return void
	 */
	public function test_join_with_closure_two_arguments() {
		global $wpdb;

		$prefix = $wpdb->prefix;
		$sql    = DB::table( 'users' )
			->join(
				'contacts',
				function ( $join ) {
					$join->on( 'users.id', 'contacts.user_id' );
				}
			)
			->to_sql();

		$expected = "SELECT * FROM {$prefix}users INNER JOIN {$prefix}contacts ON users.id = contacts.user_id";
		$this->assertSame( $expected, $sql );
	}

	/**
	 * Test closure-based left_join and right_join.
	 *
	 * @return void
	 */
	public function test_left_and_right_join_with_closure() {
		global $wpdb;

		$prefix = $wpdb->prefix;
		$sql    = DB::table( 'posts' )
			->left_join(
				'postmeta',
				function ( $join ) {
					$join->on( 'posts.ID', '=', 'postmeta.post_id' )
						->on( 'postmeta.meta_key', '=', 'posts.post_type' );
				}
			)
			->right_join(
				'users',
				function ( $join ) {
					$join->on( 'posts.post_author', '=', 'users.ID' );
				}
			)
			->to_sql();

		$expected = "SELECT * FROM {$prefix}posts LEFT JOIN {$prefix}postmeta ON posts.ID = postmeta.post_id AND postmeta.meta_key = posts.post_type RIGHT JOIN {$prefix}users ON posts.post_author = users.ID";
		$this->assertSame( $expected, $sql );
	}

	/**
	 * Test closure-based join with or_on() conditions.
	 *
	 * @return void
	 */
	public function test_join_closure_with_or_on() {
		global $wpdb;

		$prefix = $wpdb->prefix;
		$sql    = DB::table( 'users' )
			->join(
				'contacts',
				function ( $join ) {
					$join->on( 'users.id', '=', 'contacts.user_id' )
						->or_on( 'users.email', '=', 'contacts.email' );
				}
			)
			->to_sql();

		$expected = "SELECT * FROM {$prefix}users INNER JOIN {$prefix}contacts ON users.id = contacts.user_id OR users.email = contacts.email";
		$this->assertSame( $expected, $sql );
	}

	/**
	 * Test closure-based join with table alias syntax.
	 *
	 * @return void
	 */
	public function test_join_closure_with_table_alias() {
		global $wpdb;

		$prefix = $wpdb->prefix;
		$sql    = DB::table( 'users' )
			->join(
				'contacts AS c',
				function ( $join ) {
					$join->on( 'users.id', '=', 'c.user_id' );
				}
			)
			->to_sql();

		$expected = "SELECT * FROM {$prefix}users INNER JOIN {$prefix}contacts AS c ON users.id = c.user_id";
		$this->assertSame( $expected, $sql );
	}

	/**
	 * Test that invalid ON conditions within a join closure are safely ignored.
	 *
	 * @return void
	 */
	public function test_join_closure_invalid_conditions_rejected() {
		global $wpdb;

		$prefix = $wpdb->prefix;
		$sql    = DB::table( 'users' )
			->join(
				'contacts',
				function ( $join ) {
					$join->on( 'users.id', '=', 'contacts.user_id' )
						->on( '1=1; DROP TABLE users; --', '=', 'contacts.status' );
				}
			)
			->to_sql();

		$expected = "SELECT * FROM {$prefix}users INNER JOIN {$prefix}contacts ON users.id = contacts.user_id";
		$this->assertSame( $expected, $sql );
	}

	/**
	 * Test that distinct() handles count() correctly.
	 *
	 * @return void
	 */
	public function test_distinct_count() {
		global $wpdb;

		$prefix = $wpdb->prefix;

		// 1. With explicit column passed to count().
		$builder = DB::table( 'users' )->distinct();
		// Test live count.
		$count1 = $builder->count( 'user_status' );
		$this->assertIsInt( $count1 );

		// 2. With column specified via select() and count() without args.
		$count2 = DB::table( 'users' )
			->distinct()
			->select( 'user_status' )
			->count();
		$this->assertIsInt( $count2 );
	}

	/**
	 * Test that string column matching a global PHP function is not treated as a closure in join.
	 *
	 * @return void
	 */
	public function test_join_with_string_callable_not_treated_as_closure() {
		global $wpdb;

		$prefix = $wpdb->prefix;
		$sql    = DB::table( 'users' )
			->join( 'contacts', 'trim', '=', 'contacts.id' )
			->to_sql();

		$expected = "SELECT * FROM {$prefix}users INNER JOIN {$prefix}contacts ON trim = contacts.id";
		$this->assertSame( $expected, $sql );
	}

	/**
	 * Test that write operations preserve column casing and reject invalid columns.
	 *
	 * @return void
	 */
	public function test_write_operations_preserve_casing_and_reject_invalid() {
		$unique_key = 'qb_test_case_' . time() . '_' . wp_rand( 1000, 9999 );

		// insert_multiple with mixed casing and invalid column.
		$rows = array(
			array(
				'option_name'            => $unique_key,
				'option_value'           => 'val1',
				'autoload'               => 'no',
				'invalid; DROP TABLE;--' => 'injected',
			),
		);

		$result = DB::table( 'options' )->insert_multiple( $rows );
		$this->assertTrue( $result );

		// Verify row was inserted.
		$val = DB::table( 'options' )->where( 'option_name', $unique_key )->value( 'option_value' );
		$this->assertSame( 'val1', $val );

		// update with invalid column ignored.
		$update_result = DB::table( 'options' )
			->where( 'option_name', $unique_key )
			->update(
				array(
					'option_value'           => 'val2',
					'invalid; DROP TABLE;--' => 'ignored',
				)
			);
		$this->assertTrue( $update_result );

		$val2 = DB::table( 'options' )->where( 'option_name', $unique_key )->value( 'option_value' );
		$this->assertSame( 'val2', $val2 );

		// Clean up.
		DB::table( 'options' )->where( 'option_name', $unique_key )->delete();
	}

	/**
	 * Test that CRUD operations do not throw an exception when wpdb has a stale last_error.
	 *
	 * @return void
	 */
	public function test_crud_operations_ignore_stale_last_error() {
		global $wpdb;

		// Simulate a previous query failure that left last_error populated.
		$wpdb->last_error = 'Table dummy_table does not exist';

		$unique_key = 'qb_stale_err_' . time() . '_' . wp_rand( 1000, 9999 );

		// insert should not throw.
		$insert_id = DB::table( 'options' )->insert(
			array(
				'option_name'  => $unique_key,
				'option_value' => 'clean',
				'autoload'     => 'no',
			)
		);
		$this->assertGreaterThan( 0, $insert_id );

		// update should not throw.
		$wpdb->last_error = 'Stale update error';
		$updated          = DB::table( 'options' )
			->where( 'option_name', $unique_key )
			->update( array( 'option_value' => 'clean_updated' ) );
		$this->assertTrue( $updated );

		// delete should not throw.
		$wpdb->last_error = 'Stale delete error';
		$deleted          = DB::table( 'options' )
			->where( 'option_name', $unique_key )
			->delete();
		$this->assertSame( 1, $deleted );

		// Reset last error.
		$wpdb->last_error = '';
	}

	/**
	 * Clean up after each test.
	 *
	 * @return void
	 */
	public function tear_down() {
		parent::tear_down();
		DB::reset_transaction_state();
	}

	/**
	 * Test has_table with existing tables (prefixed and unprefixed).
	 *
	 * @return void
	 */
	public function test_has_table_positive() {
		global $wpdb;

		// Without prefix.
		$this->assertTrue( DB::has_table( 'users' ) );
		$this->assertTrue( DB::has_table( 'posts' ) );
		$this->assertTrue( DB::has_table( 'options' ) );

		// With prefix.
		$this->assertTrue( DB::has_table( $wpdb->prefix . 'users' ) );
		$this->assertTrue( DB::has_table( $wpdb->prefix . 'posts' ) );
	}

	/**
	 * Test has_table with non-existing tables.
	 *
	 * @return void
	 */
	public function test_has_table_negative() {
		global $wpdb;

		$this->assertFalse( DB::has_table( 'non_existent_table_xyz' ) );
		$this->assertFalse( DB::has_table( $wpdb->prefix . 'non_existent_table_xyz' ) );
	}

	/**
	 * Test has_table with invalid inputs and SQL injection attempts.
	 *
	 * @return void
	 */
	public function test_has_table_invalid_inputs() {
		// Empty and whitespace strings.
		$this->assertFalse( DB::has_table( '' ) );
		$this->assertFalse( DB::has_table( '   ' ) );

		// SQL injection / invalid identifier strings.
		$this->assertFalse( DB::has_table( 'users; DROP TABLE users;--' ) );
		$this->assertFalse( DB::has_table( "users' OR 1=1--" ) );
		$this->assertFalse( DB::has_table( 'users table' ) );
	}

	/**
	 * Test has_column with existing and non-existing columns.
	 *
	 * @return void
	 */
	public function test_has_column_positive_and_negative() {
		global $wpdb;

		// Existing columns.
		$this->assertTrue( DB::has_column( 'users', 'user_email' ) );
		$this->assertTrue( DB::has_column( $wpdb->prefix . 'users', 'user_login' ) );
		$this->assertTrue( DB::has_column( 'options', 'option_name' ) );

		// Case-insensitivity check (e.g. ID vs id, USER_EMAIL vs user_email).
		$this->assertTrue( DB::has_column( 'users', 'USER_EMAIL' ) );
		$this->assertTrue( DB::has_column( 'users', 'id' ) );
		$this->assertTrue( DB::has_column( 'users', 'ID' ) );

		// Non-existing columns.
		$this->assertFalse( DB::has_column( 'users', 'non_existing_col_xyz' ) );
		$this->assertFalse( DB::has_column( 'options', 'random_col' ) );

		// Non-existing table.
		$this->assertFalse( DB::has_column( 'non_existing_table_xyz', 'id' ) );
	}

	/**
	 * Test has_column with invalid inputs.
	 *
	 * @return void
	 */
	public function test_has_column_invalid_inputs() {
		// Invalid column inputs.
		$this->assertFalse( DB::has_column( 'users', 123 ) );
		$this->assertFalse( DB::has_column( 'users', '' ) );
		$this->assertFalse( DB::has_column( 'users', '   ' ) );
		$this->assertFalse( DB::has_column( 'users', 'user_email; DROP TABLE users' ) );
		$this->assertFalse( DB::has_column( 'users', "email' OR 1=1" ) );

		// Invalid table inputs.
		$this->assertFalse( DB::has_column( '', 'user_email' ) );
		$this->assertFalse( DB::has_column( 'users; DROP TABLE', 'user_email' ) );
	}

	/**
	 * Test has_columns with multiple columns.
	 *
	 * @return void
	 */
	public function test_has_columns_multiple() {
		// All exist.
		$this->assertTrue( DB::has_columns( 'users', array( 'id', 'user_login', 'user_email' ) ) );
		$this->assertTrue( DB::has_columns( 'options', array( 'option_id', 'option_name', 'option_value' ) ) );

		// Partial exist.
		$this->assertFalse( DB::has_columns( 'users', array( 'id', 'non_existent_column' ) ) );

		// None exist.
		$this->assertFalse( DB::has_columns( 'users', array( 'foo_col', 'bar_col' ) ) );

		// Non-existing table.
		$this->assertFalse( DB::has_columns( 'non_existing_table_xyz', array( 'id', 'user_login' ) ) );
	}

	/**
	 * Test has_columns with empty arrays and invalid inputs.
	 *
	 * @return void
	 */
	public function test_has_columns_empty_and_invalid_inputs() {
		// Empty column list.
		$this->assertFalse( DB::has_columns( 'users', array() ) );

		// Array with invalid elements.
		$this->assertFalse( DB::has_columns( 'users', array( 'id', null ) ) );
		$this->assertFalse( DB::has_columns( 'users', array( 'id', '' ) ) );
		$this->assertFalse( DB::has_columns( 'users', array( 'id', 123 ) ) );
		$this->assertFalse( DB::has_columns( 'users', array( 'id', 'col; DROP TABLE' ) ) );

		// Invalid table with valid columns array.
		$this->assertFalse( DB::has_columns( '', array( 'id' ) ) );
	}

	/**
	 * Test lock_for_update SQL generation.
	 *
	 * @return void
	 */
	public function test_lock_for_update_sql_generation() {
		global $wpdb;

		$prefix = $wpdb->prefix;

		$builder = DB::table( 'users' )
			->where( 'id', 1 )
			->lock_for_update();

		$this->assertTrue( $builder->is_locked() );
		$this->assertSame( "SELECT * FROM {$prefix}users WHERE id = 1 FOR UPDATE", $builder->to_sql() );

		// With limit.
		$builder_limit = DB::table( 'users' )
			->where( 'id', 1 )
			->limit( 5 )
			->lock_for_update();

		$this->assertSame( "SELECT * FROM {$prefix}users WHERE id = 1 LIMIT 5 OFFSET 0 FOR UPDATE", $builder_limit->to_sql() );
	}

	/**
	 * Test lock_for_update execution within an active transaction.
	 *
	 * @return void
	 */
	public function test_lock_for_update_query_execution_in_transaction() {
		$this->assertTrue( DB::begin_transaction() );
		$this->assertTrue( DB::in_transaction() );

		$user = DB::table( 'users' )
			->lock_for_update()
			->first();

		$this->assertTrue( is_object( $user ) || is_null( $user ) );
		$this->assertTrue( DB::commit() );
		$this->assertFalse( DB::in_transaction() );
	}

	/**
	 * Test lock_for_update query execution outside a transaction.
	 *
	 * @return void
	 */
	public function test_lock_for_update_query_execution_outside_transaction() {
		$this->assertFalse( DB::in_transaction() );

		$user = DB::table( 'users' )
			->lock_for_update()
			->first();

		$this->assertTrue( is_object( $user ) || is_null( $user ) );
	}

	/**
	 * Test manual transaction begin and commit.
	 *
	 * @return void
	 */
	public function test_manual_transaction_commit() {
		$unique_key = 'tx_commit_' . time() . '_' . wp_rand( 1000, 9999 );

		$this->assertFalse( DB::in_transaction() );
		$this->assertSame( 0, DB::transaction_level() );

		$this->assertTrue( DB::begin_transaction() );
		$this->assertTrue( DB::in_transaction() );
		$this->assertSame( 1, DB::transaction_level() );

		DB::table( 'options' )->insert(
			array(
				'option_name'  => $unique_key,
				'option_value' => 'committed_val',
				'autoload'     => 'no',
			)
		);

		$this->assertTrue( DB::commit() );
		$this->assertFalse( DB::in_transaction() );
		$this->assertSame( 0, DB::transaction_level() );

		// Verify option persisted.
		$val = DB::table( 'options' )->where( 'option_name', $unique_key )->value( 'option_value' );
		$this->assertSame( 'committed_val', $val );

		// Cleanup.
		DB::table( 'options' )->where( 'option_name', $unique_key )->delete();
	}

	/**
	 * Test manual transaction begin and rollback.
	 *
	 * @return void
	 */
	public function test_manual_transaction_rollback() {
		$unique_key = 'tx_rollback_' . time() . '_' . wp_rand( 1000, 9999 );

		$this->assertTrue( DB::begin_transaction() );
		$this->assertTrue( DB::in_transaction() );

		DB::table( 'options' )->insert(
			array(
				'option_name'  => $unique_key,
				'option_value' => 'rolled_back_val',
				'autoload'     => 'no',
			)
		);

		$this->assertTrue( DB::rollback() );
		$this->assertFalse( DB::in_transaction() );
		$this->assertSame( 0, DB::transaction_level() );

		// Verify option was NOT saved.
		$val = DB::table( 'options' )->where( 'option_name', $unique_key )->value( 'option_value' );
		$this->assertNull( $val );
	}

	/**
	 * Test commit and rollback when no transaction is active.
	 *
	 * @return void
	 */
	public function test_transaction_inactive_states() {
		$this->assertFalse( DB::in_transaction() );
		$this->assertSame( 0, DB::transaction_level() );

		// Commit when no transaction is active returns false.
		$this->assertFalse( DB::commit() );
		$this->assertSame( 0, DB::transaction_level() );

		// Rollback when no transaction is active returns false.
		$this->assertFalse( DB::rollback() );
		$this->assertSame( 0, DB::transaction_level() );
	}

	/**
	 * Test nested manual transactions using savepoints.
	 *
	 * @return void
	 */
	public function test_nested_manual_transactions_with_savepoints() {
		$key_outer = 'tx_nested_outer_' . time() . '_' . wp_rand( 1000, 9999 );
		$key_inner = 'tx_nested_inner_' . time() . '_' . wp_rand( 1000, 9999 );

		// Outer transaction.
		$this->assertTrue( DB::begin_transaction() );
		$this->assertSame( 1, DB::transaction_level() );

		DB::table( 'options' )->insert(
			array(
				'option_name'  => $key_outer,
				'option_value' => 'outer_val',
				'autoload'     => 'no',
			)
		);

		// Inner transaction (creates SAVEPOINT).
		$this->assertTrue( DB::begin_transaction() );
		$this->assertSame( 2, DB::transaction_level() );

		DB::table( 'options' )->insert(
			array(
				'option_name'  => $key_inner,
				'option_value' => 'inner_val',
				'autoload'     => 'no',
			)
		);

		// Roll back inner transaction (ROLLBACK TO SAVEPOINT).
		$this->assertTrue( DB::rollback() );
		$this->assertSame( 1, DB::transaction_level() );
		$this->assertTrue( DB::in_transaction() );

		// Commit outer transaction.
		$this->assertTrue( DB::commit() );
		$this->assertSame( 0, DB::transaction_level() );
		$this->assertFalse( DB::in_transaction() );

		// Outer value should exist, inner value should have been rolled back.
		$outer_val = DB::table( 'options' )->where( 'option_name', $key_outer )->value( 'option_value' );
		$inner_val = DB::table( 'options' )->where( 'option_name', $key_inner )->value( 'option_value' );

		$this->assertSame( 'outer_val', $outer_val );
		$this->assertNull( $inner_val );

		// Cleanup.
		DB::table( 'options' )->where( 'option_name', $key_outer )->delete();
	}

	/**
	 * Test automatic transaction commit on success and callback return value.
	 *
	 * @return void
	 */
	public function test_automatic_transaction_commit_on_success() {
		$unique_key = 'tx_auto_ok_' . time() . '_' . wp_rand( 1000, 9999 );

		$result = DB::transaction(
			function () use ( $unique_key ) {
				DB::table( 'options' )->insert(
					array(
						'option_name'  => $unique_key,
						'option_value' => 'auto_val',
						'autoload'     => 'no',
					)
				);

				return 'success_payload';
			}
		);

		$this->assertSame( 'success_payload', $result );
		$this->assertFalse( DB::in_transaction() );
		$this->assertSame( 0, DB::transaction_level() );

		// Verify data was committed.
		$val = DB::table( 'options' )->where( 'option_name', $unique_key )->value( 'option_value' );
		$this->assertSame( 'auto_val', $val );

		// Cleanup.
		DB::table( 'options' )->where( 'option_name', $unique_key )->delete();
	}

	/**
	 * Test automatic transaction rollback on exception and exception propagation.
	 *
	 * @return void
	 */
	public function test_automatic_transaction_rollback_on_exception() {
		$unique_key       = 'tx_auto_err_' . time() . '_' . wp_rand( 1000, 9999 );
		$exception_thrown = false;

		try {
			DB::transaction(
				function () use ( $unique_key ) {
					DB::table( 'options' )->insert(
						array(
							'option_name'  => $unique_key,
							'option_value' => 'should_rollback',
							'autoload'     => 'no',
						)
					);

					throw new \RuntimeException( 'Simulated transaction failure' );
				}
			);
		} catch ( \RuntimeException $e ) {
			$exception_thrown = true;
			$this->assertSame( 'Simulated transaction failure', $e->getMessage() );
		}

		$this->assertTrue( $exception_thrown );
		$this->assertFalse( DB::in_transaction() );
		$this->assertSame( 0, DB::transaction_level() );

		// Verify data was rolled back.
		$val = DB::table( 'options' )->where( 'option_name', $unique_key )->value( 'option_value' );
		$this->assertNull( $val );
	}

	/**
	 * Test nested automatic transactions.
	 *
	 * @return void
	 */
	public function test_nested_automatic_transactions() {
		$key1 = 'tx_auto_nested_1_' . time() . '_' . wp_rand( 1000, 9999 );
		$key2 = 'tx_auto_nested_2_' . time() . '_' . wp_rand( 1000, 9999 );

		$result = DB::transaction(
			function () use ( $key1, $key2 ) {
				DB::table( 'options' )->insert(
					array(
						'option_name'  => $key1,
						'option_value' => 'v1',
						'autoload'     => 'no',
					)
				);

				$inner_res = DB::transaction(
					function () use ( $key2 ) {
						DB::table( 'options' )->insert(
							array(
								'option_name'  => $key2,
								'option_value' => 'v2',
								'autoload'     => 'no',
							)
						);

						return 'inner_done';
					}
				);

				return $inner_res . '_outer_done';
			}
		);

		$this->assertSame( 'inner_done_outer_done', $result );
		$this->assertFalse( DB::in_transaction() );
		$this->assertSame( 0, DB::transaction_level() );

		// Both values should exist.
		$this->assertSame( 'v1', DB::table( 'options' )->where( 'option_name', $key1 )->value( 'option_value' ) );
		$this->assertSame( 'v2', DB::table( 'options' )->where( 'option_name', $key2 )->value( 'option_value' ) );

		// Cleanup.
		DB::table( 'options' )->where( 'option_name', $key1 )->delete();
		DB::table( 'options' )->where( 'option_name', $key2 )->delete();
	}

	/**
	 * Test automatic transaction rolls back on PHP Error (Throwable).
	 *
	 * @return void
	 */
	public function test_automatic_transaction_rollback_on_error() {
		$unique_key   = 'tx_auto_err_throwable_' . time() . '_' . wp_rand( 1000, 9999 );
		$error_caught = false;

		try {
			DB::transaction(
				function () use ( $unique_key ) {
					DB::table( 'options' )->insert(
						array(
							'option_name'  => $unique_key,
							'option_value' => 'throwable_test',
							'autoload'     => 'no',
						)
					);

					// Trigger an Error.
					$null_obj = null;
					$null_obj->non_existent_method();
				}
			);
		} catch ( \Error $e ) {
			$error_caught = true;
		}

		$this->assertTrue( $error_caught );
		$this->assertFalse( DB::in_transaction() );
		$this->assertSame( 0, DB::transaction_level() );

		// Value should not exist.
		$this->assertNull( DB::table( 'options' )->where( 'option_name', $unique_key )->value( 'option_value' ) );
	}

	/**
	 * Test lock_for_update with exists, value, and count terminals.
	 *
	 * @return void
	 */
	public function test_lock_for_update_with_terminal_methods() {
		// exists() with lock_for_update.
		$exists = DB::table( 'users' )->lock_for_update()->exists();
		$this->assertIsBool( $exists );

		// value() with lock_for_update.
		$val = DB::table( 'users' )->lock_for_update()->value( 'user_login' );
		$this->assertTrue( is_string( $val ) || is_null( $val ) );

		// count() resets lock and restores it.
		$builder = DB::table( 'users' )->lock_for_update();
		$this->assertTrue( $builder->is_locked() );
		$count = $builder->count();
		$this->assertIsInt( $count );
		$this->assertTrue( $builder->is_locked() );
	}

	/**
	 * Test DB set_db and fallback to global $wpdb.
	 *
	 * @return void
	 */
	public function test_db_set_and_get() {
		global $wpdb;

		// Default falls back to $wpdb.
		DB::set_db( null );
		$ref    = new \ReflectionClass( DB::class );
		$method = $ref->getMethod( 'get_db' );
		$method->setAccessible( true );

		$this->assertSame( $wpdb, $method->invoke( null ) );

		// Can override with custom instance.
		$custom = clone $wpdb;
		DB::set_db( $custom );
		$this->assertSame( $custom, $method->invoke( null ) );

		// Reset back to null.
		DB::set_db( null );
		$this->assertSame( $wpdb, $method->invoke( null ) );
	}

	// ─── prepare_table_name & prefixing ─────────────────────────────────────

	/**
	 * Test DB::prepare_table_name in single site environment.
	 *
	 * @since 4.2.1
	 *
	 * @return void
	 */
	public function test_prepare_table_name_single_site() {
		global $wpdb;

		// Unprefixed site tables.
		$this->assertSame( $wpdb->prefix . 'posts', DB::prepare_table_name( 'posts' ) );
		$this->assertSame( $wpdb->prefix . 'tutor_orders', DB::prepare_table_name( 'tutor_orders' ) );

		// Already prefixed site tables (no double-prefix).
		$this->assertSame( $wpdb->prefix . 'posts', DB::prepare_table_name( $wpdb->prefix . 'posts' ) );
		$this->assertSame( $wpdb->prefix . 'tutor_orders', DB::prepare_table_name( $wpdb->prefix . 'tutor_orders' ) );

		// Global tables in single site.
		$this->assertSame( $wpdb->base_prefix . 'users', DB::prepare_table_name( 'users' ) );
		$this->assertSame( $wpdb->base_prefix . 'usermeta', DB::prepare_table_name( 'usermeta' ) );
		$this->assertSame( $wpdb->base_prefix . 'users', DB::prepare_table_name( $wpdb->base_prefix . 'users' ) );
		$this->assertSame( $wpdb->base_prefix . 'usermeta', DB::prepare_table_name( $wpdb->base_prefix . 'usermeta' ) );
	}

	/**
	 * Test DB::prepare_table_name for site-specific tables in multisite.
	 *
	 * @since 4.2.1
	 *
	 * @return void
	 */
	public function test_prepare_table_name_multisite_site_specific_tables() {
		global $wpdb;

		$orig_prefix      = $wpdb->prefix;
		$orig_base_prefix = $wpdb->base_prefix;

		try {
			// Simulate multisite subsite (e.g. blog_id = 2).
			$wpdb->base_prefix = 'wp_';
			$wpdb->prefix      = 'wp_2_';

			// Unprefixed site-specific tables get site prefix.
			$this->assertSame( 'wp_2_posts', DB::prepare_table_name( 'posts' ) );
			$this->assertSame( 'wp_2_tutor_orders', DB::prepare_table_name( 'tutor_orders' ) );

			// Already site-prefixed table does not double prefix.
			$this->assertSame( 'wp_2_posts', DB::prepare_table_name( 'wp_2_posts' ) );
			$this->assertSame( 'wp_2_tutor_orders', DB::prepare_table_name( 'wp_2_tutor_orders' ) );

			// Site prefix precedence: 'wp_2_' is stripped first, not 'wp_' leaving '2_posts'.
			$this->assertSame( 'wp_2_posts', DB::prepare_table_name( 'wp_2_posts' ) );
			$this->assertSame( 'wp_2_custom_table', DB::prepare_table_name( 'wp_2_custom_table' ) );
		} finally {
			$wpdb->prefix      = $orig_prefix;
			$wpdb->base_prefix = $orig_base_prefix;
		}
	}

	/**
	 * Test DB::prepare_table_name for global tables in multisite.
	 *
	 * @since 4.2.1
	 *
	 * @return void
	 */
	public function test_prepare_table_name_multisite_global_tables() {
		global $wpdb;

		$orig_prefix      = $wpdb->prefix;
		$orig_base_prefix = $wpdb->base_prefix;

		try {
			// Simulate multisite subsite (e.g. blog_id = 2).
			$wpdb->base_prefix = 'wp_';
			$wpdb->prefix      = 'wp_2_';

			// Global tables must use base_prefix, never subsite prefix.
			$this->assertSame( 'wp_users', DB::prepare_table_name( 'users' ) );
			$this->assertSame( 'wp_usermeta', DB::prepare_table_name( 'usermeta' ) );

			// Already base-prefixed global tables remain base-prefixed.
			$this->assertSame( 'wp_users', DB::prepare_table_name( 'wp_users' ) );
			$this->assertSame( 'wp_usermeta', DB::prepare_table_name( 'wp_usermeta' ) );

			// If incorrectly given subsite prefix, global table is normalized to base prefix.
			$this->assertSame( 'wp_users', DB::prepare_table_name( 'wp_2_users' ) );
			$this->assertSame( 'wp_usermeta', DB::prepare_table_name( 'wp_2_usermeta' ) );

			// Verify all registered global tables dynamically.
			$global_tables = $wpdb->tables( 'global', false );
			foreach ( $global_tables as $global_table ) {
				$this->assertSame(
					'wp_' . $global_table,
					DB::prepare_table_name( $global_table ),
					"Global table {$global_table} should have base_prefix."
				);
				$this->assertSame(
					'wp_' . $global_table,
					DB::prepare_table_name( 'wp_2_' . $global_table ),
					"Subsite-prefixed global table wp_2_{$global_table} should normalize to base_prefix."
				);
			}
		} finally {
			$wpdb->prefix      = $orig_prefix;
			$wpdb->base_prefix = $orig_base_prefix;
		}
	}

	/**
	 * Test DB::prepare_table_name for custom tables starting with global table names.
	 *
	 * @since 4.2.1
	 *
	 * @return void
	 */
	public function test_prepare_table_name_custom_table_similar_to_global_table() {
		global $wpdb;

		$orig_prefix      = $wpdb->prefix;
		$orig_base_prefix = $wpdb->base_prefix;

		try {
			$wpdb->base_prefix = 'wp_';
			$wpdb->prefix      = 'wp_2_';

			// Tables like 'users_archive' or 'usermeta_backup' are NOT global tables.
			$this->assertSame( 'wp_2_users_archive', DB::prepare_table_name( 'users_archive' ) );
			$this->assertSame( 'wp_2_users_archive', DB::prepare_table_name( 'wp_2_users_archive' ) );
			$this->assertSame( 'wp_2_usermeta_backup', DB::prepare_table_name( 'usermeta_backup' ) );
			$this->assertSame( 'wp_2_usermeta_backup', DB::prepare_table_name( 'wp_2_usermeta_backup' ) );
		} finally {
			$wpdb->prefix      = $orig_prefix;
			$wpdb->base_prefix = $orig_base_prefix;
		}
	}

	/**
	 * Test DB::table() query generation under multisite conditions.
	 *
	 * @since 4.2.1
	 *
	 * @return void
	 */
	public function test_table_multisite_prefixing() {
		global $wpdb;

		$orig_prefix      = $wpdb->prefix;
		$orig_base_prefix = $wpdb->base_prefix;

		try {
			$wpdb->base_prefix = 'wp_';
			$wpdb->prefix      = 'wp_2_';

			// Site table.
			$this->assertSame( 'SELECT * FROM wp_2_posts', DB::table( 'posts' )->to_sql() );
			$this->assertSame( 'SELECT * FROM wp_2_posts', DB::table( 'wp_2_posts' )->to_sql() );

			// Global table.
			$this->assertSame( 'SELECT * FROM wp_users', DB::table( 'users' )->to_sql() );
			$this->assertSame( 'SELECT * FROM wp_users', DB::table( 'wp_users' )->to_sql() );
			$this->assertSame( 'SELECT * FROM wp_users', DB::table( 'wp_2_users' )->to_sql() );
		} finally {
			$wpdb->prefix      = $orig_prefix;
			$wpdb->base_prefix = $orig_base_prefix;
		}
	}

	/**
	 * Test DB::get_table_prefix.
	 *
	 * @since 4.2.1
	 *
	 * @return void
	 */
	public function test_get_table_prefix() {
		global $wpdb;

		$this->assertSame( $wpdb->prefix, DB::get_table_prefix() );

		$orig_prefix = $wpdb->prefix;
		try {
			$wpdb->prefix = 'wp_custom_';
			$this->assertSame( 'wp_custom_', DB::get_table_prefix() );
		} finally {
			$wpdb->prefix = $orig_prefix;
		}
	}

	// ─── Subquery Tests ─────────────────────────────────────────────────────

	/**
	 * Test WHERE comparison operators with subqueries.
	 *
	 * @since 4.2.0
	 *
	 * @return void
	 */
	public function test_where_subquery_comparison() {
		global $wpdb;

		$prefix = $wpdb->prefix;

		// 3-argument syntax with closure.
		$sql = DB::table( 'tutor_orders' )
			->where(
				'total_price',
				'>',
				function ( $q ) {
					$q->from( 'tutor_orders' )->select_raw( 'AVG(total_price)' );
				}
			)
			->to_sql();

		$expected = "SELECT * FROM {$prefix}tutor_orders WHERE total_price > (SELECT AVG(total_price) FROM {$prefix}tutor_orders)";
		$this->assertSame( $expected, $sql );

		// 3-argument syntax with DB instance.
		$sub = DB::table( 'tutor_orders' )->select_raw( 'AVG(total_price)' );
		$sql = DB::table( 'tutor_orders' )
			->where( 'total_price', '>=', $sub )
			->to_sql();

		$expected = "SELECT * FROM {$prefix}tutor_orders WHERE total_price >= (SELECT AVG(total_price) FROM {$prefix}tutor_orders)";
		$this->assertSame( $expected, $sql );

		// 2-argument syntax with closure (defaults operator to '=').
		$sql = DB::table( 'users' )
			->where(
				'id',
				function ( $q ) {
					$q->from( 'posts' )->select( 'post_author' )->where( 'id', 1 );
				}
			)
			->to_sql();

		$expected = "SELECT * FROM {$prefix}users WHERE id = (SELECT post_author FROM {$prefix}posts WHERE id = 1)";
		$this->assertSame( $expected, $sql );

		// or_where with subquery.
		$sql = DB::table( 'tutor_orders' )
			->where( 'total_price', '>', $sub )
			->or_where(
				'total_price',
				'<',
				function ( $q ) {
					$q->from( 'tutor_orders' )->select_raw( 'MIN(total_price)' );
				}
			)
			->to_sql();

		$expected = "SELECT * FROM {$prefix}tutor_orders WHERE total_price > (SELECT AVG(total_price) FROM {$prefix}tutor_orders) OR total_price < (SELECT MIN(total_price) FROM {$prefix}tutor_orders)";
		$this->assertSame( $expected, $sql );

		// Array conditions with subquery.
		$sql = DB::table( 'tutor_orders' )
			->where(
				array(
					'total_price' => $sub,
					array( 'total_price', '>=', $sub ),
				)
			)
			->to_sql();

		$expected = "SELECT * FROM {$prefix}tutor_orders WHERE total_price = (SELECT AVG(total_price) FROM {$prefix}tutor_orders) AND total_price >= (SELECT AVG(total_price) FROM {$prefix}tutor_orders)";
		$this->assertSame( $expected, $sql );
	}

	/**
	 * Test WHERE IN and WHERE NOT IN with subqueries.
	 *
	 * @since 4.2.0
	 *
	 * @return void
	 */
	public function test_where_in_and_not_in_subquery() {
		global $wpdb;

		$prefix = $wpdb->prefix;

		// where_in with closure.
		$sql = DB::table( 'users' )
			->where_in(
				'id',
				function ( $q ) {
					$q->from( 'tutor_orders' )
						->select( 'user_id' )
						->where( 'order_status', 'completed' );
				}
			)
			->to_sql();

		$expected = "SELECT * FROM {$prefix}users WHERE id IN (SELECT user_id FROM {$prefix}tutor_orders WHERE order_status = 'completed')";
		$this->assertSame( $expected, $sql );

		// where_in with DB instance.
		$sub = DB::table( 'tutor_orders' )
			->select( 'user_id' )
			->where( 'order_status', 'completed' );

		$sql = DB::table( 'users' )
			->where_in( 'id', $sub )
			->to_sql();

		$this->assertSame( $expected, $sql );

		// where_not_in with closure.
		$sql = DB::table( 'users' )
			->where_not_in(
				'id',
				function ( $q ) {
					$q->from( 'tutor_orders' )->select( 'user_id' );
				}
			)
			->to_sql();

		$expected = "SELECT * FROM {$prefix}users WHERE id NOT IN (SELECT user_id FROM {$prefix}tutor_orders)";
		$this->assertSame( $expected, $sql );

		// or_where_in and or_where_not_in.
		$sql = DB::table( 'users' )
			->where( 'user_status', 0 )
			->or_where_in( 'id', $sub )
			->or_where_not_in(
				'id',
				function ( $q ) {
					$q->from( 'posts' )->select( 'post_author' );
				}
			)
			->to_sql();

		$expected = "SELECT * FROM {$prefix}users WHERE user_status = 0 OR id IN (SELECT user_id FROM {$prefix}tutor_orders WHERE order_status = 'completed') OR id NOT IN (SELECT post_author FROM {$prefix}posts)";
		$this->assertSame( $expected, $sql );
	}

	/**
	 * Test WHERE EXISTS and WHERE NOT EXISTS subqueries.
	 *
	 * @since 4.2.0
	 *
	 * @return void
	 */
	public function test_where_exists_and_not_exists() {
		global $wpdb;

		$prefix = $wpdb->prefix;

		// where_exists with closure.
		$sql = DB::table( 'users' )
			->as( 'u' )
			->where_exists(
				function ( $q ) {
					$q->from( 'tutor_orders', 'o' )
						->where_column( 'o.user_id', '=', 'u.id' );
				}
			)
			->to_sql();

		$expected = "SELECT * FROM {$prefix}users AS u WHERE EXISTS (SELECT * FROM {$prefix}tutor_orders AS o WHERE o.user_id = u.id)";
		$this->assertSame( $expected, $sql );

		// where_not_exists with DB instance.
		$sub = DB::table( 'tutor_orders', 'o' )
			->where_column( 'o.user_id', '=', 'u.id' );

		$sql = DB::table( 'users' )
			->as( 'u' )
			->where_not_exists( $sub )
			->to_sql();

		$expected = "SELECT * FROM {$prefix}users AS u WHERE NOT EXISTS (SELECT * FROM {$prefix}tutor_orders AS o WHERE o.user_id = u.id)";
		$this->assertSame( $expected, $sql );

		// or_where_exists and or_where_not_exists.
		$sql = DB::table( 'users' )
			->as( 'u' )
			->where( 'u.status', 'active' )
			->or_where_exists( $sub )
			->or_where_not_exists(
				function ( $q ) {
					$q->from( 'posts' )->where_column( 'post_author', '=', 'u.id' );
				}
			)
			->to_sql();

		$expected = "SELECT * FROM {$prefix}users AS u WHERE u.status = 'active' OR EXISTS (SELECT * FROM {$prefix}tutor_orders AS o WHERE o.user_id = u.id) OR NOT EXISTS (SELECT * FROM {$prefix}posts WHERE post_author = u.id)";
		$this->assertSame( $expected, $sql );
	}

	/**
	 * Test SELECT subquery via select_sub().
	 *
	 * @since 4.2.0
	 *
	 * @return void
	 */
	public function test_select_subquery() {
		global $wpdb;

		$prefix = $wpdb->prefix;

		// select_sub with closure.
		$sql = DB::table( 'users' )
			->as( 'u' )
			->select( 'u.id', 'u.user_login' )
			->select_sub(
				function ( $q ) {
					$q->from( 'tutor_orders', 'o' )
						->select_raw( 'COUNT(*)' )
						->where_column( 'o.user_id', '=', 'u.id' );
				},
				'order_count'
			)
			->to_sql();

		$expected = "SELECT u.id, u.user_login, (SELECT COUNT(*) FROM {$prefix}tutor_orders AS o WHERE o.user_id = u.id) AS order_count FROM {$prefix}users AS u";
		$this->assertSame( $expected, $sql );

		// select_sub with DB instance.
		$sub = DB::table( 'tutor_orders' )->select_raw( 'MAX(total_price)' );
		$sql = DB::table( 'users' )
			->select( 'id' )
			->select_sub( $sub, 'max_spent' )
			->to_sql();

		$expected = "SELECT id, (SELECT MAX(total_price) FROM {$prefix}tutor_orders) AS max_spent FROM {$prefix}users";
		$this->assertSame( $expected, $sql );

		// Invalid alias rejection.
		$sql = DB::table( 'users' )
			->select( 'id' )
			->select_sub( $sub, 'invalid alias;--' )
			->to_sql();

		$this->assertSame( "SELECT id FROM {$prefix}users", $sql );
	}

	/**
	 * Test FROM derived table subquery.
	 *
	 * @since 4.2.0
	 *
	 * @return void
	 */
	public function test_from_subquery() {
		global $wpdb;

		$prefix = $wpdb->prefix;

		// DB::table( Closure, alias )
		$sql = DB::table(
			function ( $q ) {
				$q->from( 'tutor_orders' )->where( 'order_status', 'completed' );
			},
			'completed_orders'
		)
			->select( 'id', 'total_price' )
			->to_sql();

		$expected = "SELECT id, total_price FROM (SELECT * FROM {$prefix}tutor_orders WHERE order_status = 'completed') AS completed_orders";
		$this->assertSame( $expected, $sql );

		// DB::table( DB, alias )
		$sub = DB::table( 'users' )->where( 'user_status', 0 );
		$sql = DB::table( $sub, 'active_users' )
			->select( 'id', 'user_email' )
			->to_sql();

		$expected = "SELECT id, user_email FROM (SELECT * FROM {$prefix}users WHERE user_status = 0) AS active_users";
		$this->assertSame( $expected, $sql );

		// from_sub() method on existing query.
		$sql = DB::table( 'dummy' )
			->from_sub( $sub, 'active_users' )
			->to_sql();

		$expected = "SELECT * FROM (SELECT * FROM {$prefix}users WHERE user_status = 0) AS active_users";
		$this->assertSame( $expected, $sql );

		// Invalid alias rejection.
		$sql = DB::table( 'users' )
			->from_sub( $sub, 'invalid alias;--' )
			->to_sql();

		$this->assertSame( "SELECT * FROM {$prefix}users", $sql );
	}

	/**
	 * Test JOIN subqueries (join_sub, left_join_sub, right_join_sub).
	 *
	 * @since 4.2.0
	 *
	 * @return void
	 */
	public function test_join_subquery() {
		global $wpdb;

		$prefix = $wpdb->prefix;

		$sub = DB::table( 'tutor_orders' )
			->select( 'user_id' )
			->select_raw( 'SUM(total_price) as total_spent' )
			->group_by( 'user_id' );

		// INNER JOIN subquery with 4 arguments.
		$sql = DB::table( 'users' )
			->as( 'u' )
			->select( 'u.id', 's.total_spent' )
			->join_sub( $sub, 's', 'u.id', '=', 's.user_id' )
			->to_sql();

		$expected = "SELECT u.id, s.total_spent FROM {$prefix}users AS u INNER JOIN (SELECT user_id, SUM(total_price) as total_spent FROM {$prefix}tutor_orders GROUP BY user_id) AS s ON u.id = s.user_id";
		$this->assertSame( $expected, $sql );

		// LEFT JOIN subquery with 4 arguments.
		$sql = DB::table( 'users' )
			->as( 'u' )
			->select( 'u.id', 's.total_spent' )
			->left_join_sub( $sub, 's', 'u.id', '=', 's.user_id' )
			->to_sql();

		$expected = "SELECT u.id, s.total_spent FROM {$prefix}users AS u LEFT JOIN (SELECT user_id, SUM(total_price) as total_spent FROM {$prefix}tutor_orders GROUP BY user_id) AS s ON u.id = s.user_id";
		$this->assertSame( $expected, $sql );

		// RIGHT JOIN subquery with 4 arguments.
		$sql = DB::table( 'users' )
			->as( 'u' )
			->select( 'u.id', 's.total_spent' )
			->right_join_sub( $sub, 's', 'u.id', '=', 's.user_id' )
			->to_sql();

		$expected = "SELECT u.id, s.total_spent FROM {$prefix}users AS u RIGHT JOIN (SELECT user_id, SUM(total_price) as total_spent FROM {$prefix}tutor_orders GROUP BY user_id) AS s ON u.id = s.user_id";
		$this->assertSame( $expected, $sql );

		// JOIN subquery with closure ON condition.
		$sql = DB::table( 'users' )
			->as( 'u' )
			->left_join_sub(
				$sub,
				's',
				function ( $join ) {
					$join->on( 'u.id', '=', 's.user_id' )
						->on( 's.total_spent', '>', 'u.id' );
				}
			)
			->to_sql();

		$expected = "SELECT * FROM {$prefix}users AS u LEFT JOIN (SELECT user_id, SUM(total_price) as total_spent FROM {$prefix}tutor_orders GROUP BY user_id) AS s ON u.id = s.user_id AND s.total_spent > u.id";
		$this->assertSame( $expected, $sql );

		// Invalid alias rejection.
		$sql = DB::table( 'users' )
			->join_sub( $sub, 'invalid alias;--', 'u.id', '=', 's.user_id' )
			->to_sql();

		$this->assertSame( "SELECT * FROM {$prefix}users", $sql );
	}

	/**
	 * Test nested subqueries and live database execution with subqueries.
	 *
	 * @since 4.2.0
	 *
	 * @return void
	 */
	public function test_nested_subqueries_and_live_execution() {
		global $wpdb;

		$prefix = $wpdb->prefix;

		// Nested subquery: WHERE IN (subquery with WHERE IN (subquery)).
		$inner  = DB::table( 'tutor_orders' )->select( 'user_id' )->where( 'total_price', '>', 50 );
		$middle = DB::table( 'users' )->select( 'id' )->where_in( 'id', $inner );
		$outer  = DB::table( 'posts' )->where_in( 'post_author', $middle )->to_sql();

		$expected = "SELECT * FROM {$prefix}posts WHERE post_author IN (SELECT id FROM {$prefix}users WHERE id IN (SELECT user_id FROM {$prefix}tutor_orders WHERE total_price > 50))";
		$this->assertSame( $expected, $outer );

		// Live execution test with options table.
		$key1 = 'subquery_test_key_1_' . time() . '_' . wp_rand( 1000, 9999 );
		$key2 = 'subquery_test_key_2_' . time() . '_' . wp_rand( 1000, 9999 );

		DB::table( 'options' )->insert(
			array(
				'option_name'  => $key1,
				'option_value' => 'active_val',
				'autoload'     => 'no',
			)
		);
		DB::table( 'options' )->insert(
			array(
				'option_name'  => $key2,
				'option_value' => 'inactive_val',
				'autoload'     => 'no',
			)
		);

		// Query using subquery.
		$result = DB::table( 'options' )
			->where_in(
				'option_name',
				function ( $q ) use ( $key1 ) {
					$q->from( 'options' )
						->select( 'option_name' )
						->where( 'option_name', $key1 );
				}
			)
			->first();

		$this->assertNotNull( $result );
		$this->assertSame( $key1, $result->option_name );
		$this->assertSame( 'active_val', $result->option_value );

		// Clean up.
		DB::table( 'options' )->where( 'option_name', $key1 )->delete();
		DB::table( 'options' )->where( 'option_name', $key2 )->delete();
	}

	/**
	 * Test empty, uninitialized, and invalid subquery inputs and operators.
	 *
	 * @since 4.2.0
	 *
	 * @return void
	 */
	public function test_subquery_empty_and_invalid_inputs() {
		global $wpdb;

		$prefix = $wpdb->prefix;
		$empty_closure = function ( $q ) {
			// Intentionally left empty.
		};
		$empty_db = new DB();

		// Empty closure and uninitialized DB instance should be ignored.
		$sql = DB::table( 'users' )
			->where_in( 'id', $empty_closure )
			->where_not_in( 'id', $empty_db )
			->where_exists( $empty_closure )
			->where_not_exists( $empty_db )
			->select_sub( $empty_closure, 'empty_col' )
			->join_sub( $empty_closure, 's', 'users.id', '=', 's.id' )
			->to_sql();

		$this->assertSame( "SELECT * FROM {$prefix}users", $sql );

		// Non-Closure / non-DB values passed to subquery methods should be ignored.
		$sql = DB::table( 'users' )
			->where_exists( 'SELECT 1' )
			->select_sub( 123, 'num_col' )
			->from_sub( 'invalid', 'sub' )
			->join_sub( 'invalid', 's', 'users.id', '=', 's.id' )
			->to_sql();

		$this->assertSame( "SELECT * FROM {$prefix}users", $sql );

		// Invalid operators for subquery comparison in where() should be rejected.
		$valid_sub = DB::table( 'tutor_orders' )->select( 'id' );
		$sql       = DB::table( 'users' )
			->where( 'id', 'BETWEEN', $valid_sub )
			->where( 'id', 'IS', $valid_sub )
			->where( 'id', 'INVALID_OP', $valid_sub )
			->to_sql();

		$this->assertSame( "SELECT * FROM {$prefix}users", $sql );
	}

	/**
	 * Test from(), DB::table() with subquery without alias, and scalar subqueries.
	 *
	 * @since 4.2.0
	 *
	 * @return void
	 */
	public function test_from_and_table_subquery_chaining() {
		global $wpdb;

		$prefix = $wpdb->prefix;
		$sub    = DB::table( 'users' )->where( 'user_status', 0 );

		// DB::table( $sub ) chained with ->as( 'alias' ).
		$sql = DB::table( $sub )->as( 'active_users' )->select( 'id' )->to_sql();
		$this->assertSame( "SELECT id FROM (SELECT * FROM {$prefix}users WHERE user_status = 0) AS active_users", $sql );

		// Direct from() with table and alias.
		$sql = ( new DB() )->from( 'tutor_orders', 'o' )->select( 'o.id' )->to_sql();
		$this->assertSame( "SELECT o.id FROM {$prefix}tutor_orders AS o", $sql );

		// Non-string table safely ignored.
		$sql = DB::table( new \stdClass() )->to_sql();
		$this->assertSame( 'SELECT *', $sql );

		// Scalar subquery without FROM clause.
		$sql = DB::table( 'users' )
			->select_sub(
				function ( $q ) {
					$q->select_raw( '1' );
				},
				'one_val'
			)
			->to_sql();

		$this->assertSame( "SELECT (SELECT 1) AS one_val FROM {$prefix}users", $sql );
	}

	/**
	 * Test where_column() and or_where_column().
	 *
	 * @since 4.2.0
	 *
	 * @return void
	 */
	public function test_where_column_and_or_where_column() {
		global $wpdb;

		$prefix = $wpdb->prefix;

		// 2-arg and 3-arg where_column and or_where_column.
		$sql = DB::table( 'posts' )
			->where_column( 'post_modified', 'post_date' )
			->where_column( 'post_modified_gmt', '>', 'post_date_gmt' )
			->or_where_column( 'post_author', 'post_parent' )
			->or_where_column( 'ID', '!=', 'post_parent' )
			->to_sql();

		$expected = "SELECT * FROM {$prefix}posts WHERE post_modified = post_date AND post_modified_gmt > post_date_gmt OR post_author = post_parent OR ID != post_parent";
		$this->assertSame( $expected, $sql );

		// Invalid columns and non-comparison operators should be rejected.
		$sql = DB::table( 'posts' )
			->where_column( '1=1; --', '=', 'post_date' )
			->where_column( 'post_date', '=', '1=1; --' )
			->where_column( 'post_date', 'BETWEEN', 'post_modified' )
			->where_column( 'post_date', 'IN', 'post_modified' )
			->to_sql();

		$this->assertSame( "SELECT * FROM {$prefix}posts", $sql );
	}

	/**
	 * Test subquery value escaping, or_where array support, and DELETE with subquery.
	 *
	 * @since 4.2.0
	 *
	 * @return void
	 */
	public function test_subquery_escaping_and_mutations() {
		global $wpdb;

		$prefix = $wpdb->prefix;

		// Subquery with SQL injection payload and LIKE inside where_in().
		$sql = DB::table( 'users' )
			->where_in(
				'user_login',
				function ( $q ) {
					$q->from( 'usermeta' )
						->select( 'meta_key' )
						->where( 'meta_value', "val' OR '1'='1" );
				}
			)
			->to_sql();

		$this->assertSame(
			"SELECT * FROM {$prefix}users WHERE user_login IN (SELECT meta_key FROM {$prefix}usermeta WHERE meta_value = 'val\' OR \'1\'=\'1')",
			$sql
		);

		// or_where() with array conditions.
		$sql = DB::table( 'users' )
			->where( 'user_status', 0 )
			->or_where(
				array(
					'user_login' => 'admin',
					array( 'ID', '>', 5 ),
				)
			)
			->to_sql();

		$this->assertSame(
			"SELECT * FROM {$prefix}users WHERE user_status = 0 OR user_login = 'admin' OR ID > 5",
			$sql
		);

		// Live DELETE with subquery.
		$key = 'subquery_del_test_' . time() . '_' . wp_rand( 1000, 9999 );
		DB::table( 'options' )->insert(
			array(
				'option_name'  => $key,
				'option_value' => 'to_delete',
				'autoload'     => 'no',
			)
		);

		$deleted = DB::table( 'options' )
			->where_in(
				'option_name',
				function ( $q ) use ( $key ) {
					$q->from(
						function ( $inner ) use ( $key ) {
							$inner->from( 'options' )
								->select( 'option_name' )
								->where( 'option_name', $key );
						},
						'sub'
					)->select( 'option_name' );
				}
			)
			->delete();

		$this->assertSame( 1, $deleted );
		$this->assertNull( DB::table( 'options' )->where( 'option_name', $key )->first() );
	}

	/**
	 * Test left-hand-side subqueries in where() and or_where().
	 *
	 * @since 4.2.0
	 *
	 * @return void
	 */
	public function test_where_lhs_subquery() {
		global $wpdb;

		$prefix = $wpdb->prefix;

		// 3-arg LHS subquery with Closure.
		$sql = DB::table( 'users' )
			->as( 'u' )
			->where(
				function ( $q ) {
					$q->from( 'tutor_orders', 'o' )
						->select_raw( 'COUNT(*)' )
						->where_column( 'o.user_id', '=', 'u.id' );
				},
				'>',
				5
			)
			->to_sql();

		$expected = "SELECT * FROM {$prefix}users AS u WHERE (SELECT COUNT(*) FROM {$prefix}tutor_orders AS o WHERE o.user_id = u.id) > 5";
		$this->assertSame( $expected, $sql );

		// 2-arg LHS subquery with DB instance (defaults to '=') and or_where.
		$sub = DB::table( 'tutor_orders', 'o' )
			->select( 'o.order_status' )
			->where_column( 'o.user_id', 'u.id' )
			->limit( 1 );

		$sql = DB::table( 'users' )
			->as( 'u' )
			->where( $sub, 'completed' )
			->or_where( $sub, '!=', 'cancelled' )
			->to_sql();

		$expected = "SELECT * FROM {$prefix}users AS u WHERE (SELECT o.order_status FROM {$prefix}tutor_orders AS o WHERE o.user_id = u.id LIMIT 1 OFFSET 0) = 'completed' OR (SELECT o.order_status FROM {$prefix}tutor_orders AS o WHERE o.user_id = u.id LIMIT 1 OFFSET 0) != 'cancelled'";
		$this->assertSame( $expected, $sql );

		// 1-arg Closure or empty LHS subquery should be safely ignored.
		$sql = DB::table( 'users' )
			->where(
				function ( $q ) {
					$q->from( 'tutor_orders' );
				}
			)
			->where(
				function ( $q ) {
					// Empty subquery.
				},
				'=',
				1
			)
			->to_sql();

		$this->assertSame( "SELECT * FROM {$prefix}users", $sql );
	}

	/**
	 * Test select() array shorthand for columns and subqueries, and cross_join_sub() / join_sub() $type.
	 *
	 * @since 4.2.0
	 *
	 * @return void
	 */
	public function test_select_array_subquery_and_cross_join_sub() {
		global $wpdb;

		$prefix = $wpdb->prefix;
		$sub    = DB::table( 'tutor_orders' )->select_raw( 'MAX(total_price)' );

		// select() with array of columns and alias => subquery pairs.
		$sql = DB::table( 'users' )
			->as( 'u' )
			->select(
				array( 'u.id', 'u.user_login' ),
				array(
					'order_count' => function ( $q ) {
						$q->from( 'tutor_orders', 'o' )
							->select_raw( 'COUNT(*)' )
							->where_column( 'o.user_id', '=', 'u.id' );
					},
					'max_spent'   => $sub,
					'bad alias;'  => $sub,
				)
			)
			->to_sql();

		$expected = "SELECT u.id, u.user_login, (SELECT COUNT(*) FROM {$prefix}tutor_orders AS o WHERE o.user_id = u.id) AS order_count, (SELECT MAX(total_price) FROM {$prefix}tutor_orders) AS max_spent FROM {$prefix}users AS u";
		$this->assertSame( $expected, $sql );

		// join_sub() with explicit $type parameter and cross_join_sub().
		$agg = DB::table( 'tutor_orders' )
			->select( 'user_id' )
			->select_raw( 'SUM(total_price) AS total_spent' )
			->group_by( 'user_id' );

		$sql = DB::table( 'users' )
			->as( 'u' )
			->join_sub( $agg, 's', 'u.id', '=', 's.user_id', 'LEFT' )
			->cross_join_sub( $sub, 'm' )
			->cross_join_sub( $sub, 'invalid alias;' )
			->to_sql();

		$expected = "SELECT * FROM {$prefix}users AS u LEFT JOIN (SELECT user_id, SUM(total_price) AS total_spent FROM {$prefix}tutor_orders GROUP BY user_id) AS s ON u.id = s.user_id CROSS JOIN (SELECT MAX(total_price) FROM {$prefix}tutor_orders) AS m";
		$this->assertSame( $expected, $sql );
	}
}
