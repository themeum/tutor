<?php
/**
 * QueryHelper Class Unit Test
 *
 * @package Tutor\Test
 * @since 2.1.9
 */

namespace TutorTest;

use Tutor\Helpers\QueryHelper;

/**
 * QueryHelper utility methods testing
 *
 * Run test by: vendor/bin/phpunit --filter=QueryHelperTest
 */
class QueryHelperTest extends \WP_UnitTestCase {

	/**
	 * Test  QueryHelper::prepare_set_clause
	 *
	 * @return void
	 */
	public function test_prepare_set_clause() {
		// Single elements.
		$array1  = array( 'id' => 1 );
		$actual1 = QueryHelper::prepare_set_clause( $array1 );
		$expect  = 'SET `id` = 1';

		// Multiple elements.
		$array2  = array(
			'id'     => 1,
			'title'  => 'title',
			'status' => 'publish',
		);
		$actual2 = QueryHelper::prepare_set_clause( $array2 );
		$expect2 = "SET `id` = 1,`title` = 'title',`status` = 'publish'";

		// Multi-dimension array.
		$array3  = array(
			'id'          => 1,
			'title'       => 'title',
			'status'      => 'publish',
			'post_parent' => array(
				1,
				2,
				3,
			),
		);
		$actual3 = QueryHelper::prepare_set_clause( $array3 );

		// Since multi-dimension is not allowed post_parent will be omitted.
		$expect3 = "SET `id` = 1,`title` = 'title',`status` = 'publish'";

		$array4  = array();
		$actual4 = QueryHelper::prepare_set_clause( $array4 );
		$expect4 = '';

		$this->assertSame( $expect, trim( $actual1 ) );
		$this->assertSame( $expect2, trim( $actual2 ) );
		$this->assertSame( $expect3, trim( $actual3 ) );
		$this->assertSame( $expect4, trim( $actual4 ) );
	}

	/**
	 * Test prepare_set_clause with null values.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function test_prepare_set_clause_null_value() {
		$data   = array( 'deleted_at' => null );
		$actual = QueryHelper::prepare_set_clause( $data );
		$this->assertSame( 'SET `deleted_at` = null', trim( $actual ) );
	}

	/**
	 * Test that prepare_set_clause escapes string values properly
	 * to prevent SQL injection.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function test_prepare_set_clause_escapes_special_characters() {
		$data   = array( 'name' => "O'Brien" );
		$actual = QueryHelper::prepare_set_clause( $data );

		// The value must be properly escaped — no raw single quote breakout.
		$this->assertStringNotContainsString( "O'Brien", $actual );
		$this->assertStringContainsString( '`name`', $actual );
	}

	/**
	 * Test QueryHelper::prepare_where_clause()
	 *
	 * @since 3.6.0
	 *
	 * @return void
	 */
	public function test_prepare_where_clause() {

		$test1   = array(
			'age'     => 18,
			'height'  => array( '>=', '5feet7inch' ),
			'hobbies' => array( 'coding', 'biking', 'swimming' ),
		);
		$expect1 = "`age` = 18 AND `height` >= '5feet7inch' AND `hobbies` IN ('coding','biking','swimming')";
		$actual1 = QueryHelper::prepare_where_clause( $test1 );

		$test2 = array(
			'salary' => array( 'BETWEEN', array( 10, 20 ) ),
			'name'   => array( 'NOT BETWEEN', array( 'b', 'c' ) ),
		);

		$expect2 = "`salary` BETWEEN 10 AND 20 AND `name` NOT BETWEEN 'b' AND 'c'";
		$actual2 = QueryHelper::prepare_where_clause( $test2 );

		$test3 = array(
			'name' => array( 'LIKE', 'test' ),
			'age'  => array( 'NOT IN', array( 18, 19, 20 ) ),
		);

		$expect3 = "`name` LIKE 'test' AND `age` NOT IN (18,19,20)";
		$actual3 = QueryHelper::prepare_where_clause( $test3 );

		$test4 = array(
			'name' => 'NULL',
			'age'  => array( 'IS NOT', 'NULL' ),
		);

		$expect4 = '`name` IS NULL AND `age` IS NOT NULL';
		$actual4 = QueryHelper::prepare_where_clause( $test4 );

		$this->assertEquals( $expect1, trim( $actual1 ) );
		$this->assertEquals( $expect2, trim( $actual2 ) );
		$this->assertEquals( $expect3, trim( $actual3 ) );
		$this->assertEquals( $expect4, trim( $actual4 ) );
	}

	/**
	 * Test QueryHelper Raw query.
	 *
	 * @since 3.6.0
	 *
	 * @return void
	 */
	public function test_prepare_raw_query() {
		$case_1 = array(
			"username = '%s'" => array(
				'RAW',
				array(
					'admin',
				),
			),
		);

		$case_2 = array(
			'age >= %d' => array(
				'RAW',
				array(
					20,
				),
			),
		);

		$case_3 = array(
			'id' => array(
				'BETWEEN',
				array( 10, 20 ),
			),
			'DATE(value) = CAST(value as date) AND id = %d' => array(
				'RAW',
				array( 10 ),
			),
		);

		$expect_1 = "username = 'admin'";
		$expect_2 = 'age >= 20';
		$expect_3 = '`id` BETWEEN 10 AND 20 AND DATE(value) = CAST(value as date) AND id = 10';

		$this->assertEquals( $expect_1, QueryHelper::prepare_where_clause( $case_1 ) );
		$this->assertEquals( $expect_2, QueryHelper::prepare_where_clause( $case_2 ) );
		$this->assertEquals( $expect_3, QueryHelper::prepare_where_clause( $case_3 ) );
	}

	/**
	 * Test QueryHelper::prepare_in_clause
	 *
	 * @return void
	 * @since 2.1.1
	 */
	public function test_prepare_in_clause() {
		$expected_query = "SELECT * from abc WHERE id IN(3,4,5,'hello')";
		$raw_sql_query  = 'SELECT * from abc WHERE id IN(' . QueryHelper::prepare_in_clause( array( 3, 4, 5, 'hello' ) ) . ')';
		$this->assertEquals( $expected_query, $raw_sql_query );

		$this->assertEquals( "'A','B','C'", QueryHelper::prepare_in_clause( array( 'A', 'B', 'C' ) ) );
		$this->assertEquals( '10,20,40', QueryHelper::prepare_in_clause( array( 10, 20, 40 ) ) );
		$this->assertEquals( "'jhon',3,4.55,'adam'", QueryHelper::prepare_in_clause( array( 'jhon', 3, 4.55, 'adam' ) ) );
		$this->assertEquals( '2,3,5.996', QueryHelper::prepare_in_clause( array( 2, 3, 5.996 ) ) );
	}

	/**
	 * Test prepare_in_clause with an empty array.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function test_prepare_in_clause_empty_array() {
		$this->assertSame( '', QueryHelper::prepare_in_clause( array() ) );
	}

	/**
	 * Test prepare_in_clause escapes SQL injection strings.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function test_prepare_in_clause_escapes_injection() {
		$result = QueryHelper::prepare_in_clause( array( "'; DROP TABLE users; --" ) );
		// The single quote must be escaped (backslash-escaped by $wpdb->prepare).
		$this->assertStringContainsString( "\\'", $result );
		// The result must be a single quoted string, not a breakout.
		$this->assertStringStartsWith( "'", $result );
		$this->assertStringEndsWith( "'", $result );
	}

	// ─── is_valid_column_name ───────────────────────────────────────────────

	/**
	 * Test is_valid_column_name with valid column names.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function test_is_valid_column_name_valid() {
		$this->assertTrue( QueryHelper::is_valid_column_name( 'id' ) );
		$this->assertTrue( QueryHelper::is_valid_column_name( 'post_title' ) );
		$this->assertTrue( QueryHelper::is_valid_column_name( '_private' ) );
		$this->assertTrue( QueryHelper::is_valid_column_name( 'u.ID' ) );
		$this->assertTrue( QueryHelper::is_valid_column_name( 'table_name.column_name' ) );
		$this->assertTrue( QueryHelper::is_valid_column_name( 'A' ) );
	}

	/**
	 * Test is_valid_column_name with invalid column names.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function test_is_valid_column_name_invalid() {
		$this->assertFalse( QueryHelper::is_valid_column_name( '' ) );
		$this->assertFalse( QueryHelper::is_valid_column_name( '1column' ) );
		$this->assertFalse( QueryHelper::is_valid_column_name( 'col name' ) );
		$this->assertFalse( QueryHelper::is_valid_column_name( "col'; DROP TABLE" ) );
		$this->assertFalse( QueryHelper::is_valid_column_name( 'col--comment' ) );
		$this->assertFalse( QueryHelper::is_valid_column_name( 'a.b.c' ) ); // Too many dots.
		$this->assertFalse( QueryHelper::is_valid_column_name( '.col' ) );
		$this->assertFalse( QueryHelper::is_valid_column_name( 'col.' ) );
	}

	// ─── prepare_identifier ─────────────────────────────────────────────────

	/**
	 * Test prepare_identifier with valid identifiers.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function test_prepare_identifier_valid() {
		$this->assertSame( 'id', QueryHelper::prepare_identifier( 'id' ) );
		$this->assertSame( 'post_title', QueryHelper::prepare_identifier( 'post_title' ) );
		$this->assertSame( '_meta', QueryHelper::prepare_identifier( '_meta' ) );
		$this->assertSame( 'Col123', QueryHelper::prepare_identifier( 'Col123' ) );
	}

	/**
	 * Test prepare_identifier with invalid identifiers.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function test_prepare_identifier_invalid() {
		$this->assertSame( '', QueryHelper::prepare_identifier( '' ) );
		$this->assertSame( '', QueryHelper::prepare_identifier( '123col' ) );
		$this->assertSame( '', QueryHelper::prepare_identifier( 'col name' ) );
		$this->assertSame( '', QueryHelper::prepare_identifier( 'col;DROP' ) );
		$this->assertSame( '', QueryHelper::prepare_identifier( 'table.col' ) ); // Dots not allowed in single identifier.
		$this->assertSame( '', QueryHelper::prepare_identifier( "col'" ) );
	}

	// ─── quote_sql_identifier ───────────────────────────────────────────────

	/**
	 * Test quote_sql_identifier with valid identifiers.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function test_quote_sql_identifier_valid() {
		$this->assertSame( '`id`', QueryHelper::quote_sql_identifier( 'id' ) );
		$this->assertSame( '`u`.`ID`', QueryHelper::quote_sql_identifier( 'u.ID' ) );
		$this->assertSame( '`tbl`.`col_name`', QueryHelper::quote_sql_identifier( 'tbl.col_name' ) );
	}

	/**
	 * Test quote_sql_identifier with invalid identifiers.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function test_quote_sql_identifier_invalid() {
		$this->assertSame( '', QueryHelper::quote_sql_identifier( '' ) );
		$this->assertSame( '', QueryHelper::quote_sql_identifier( "'; DROP TABLE" ) );
		$this->assertSame( '', QueryHelper::quote_sql_identifier( '1bad' ) );
		$this->assertSame( '', QueryHelper::quote_sql_identifier( '.col' ) );
	}

	// ─── is_support_operator ────────────────────────────────────────────────

	/**
	 * Test is_support_operator accepts all supported operators.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function test_is_support_operator_valid() {
		$supported = array( '=', '!=', '<>', '>', '<', '>=', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'IS', 'IS NOT', 'BETWEEN', 'NOT BETWEEN', 'RAW' );
		foreach ( $supported as $op ) {
			$this->assertTrue( QueryHelper::is_support_operator( $op ), "Operator '{$op}' should be supported" );
		}
		// Case-insensitive.
		$this->assertTrue( QueryHelper::is_support_operator( 'like' ) );
		$this->assertTrue( QueryHelper::is_support_operator( 'Between' ) );
	}

	/**
	 * Test is_support_operator rejects unsupported operators.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function test_is_support_operator_invalid() {
		$this->assertFalse( QueryHelper::is_support_operator( 'DROP' ) );
		$this->assertFalse( QueryHelper::is_support_operator( 'UNION' ) );
		$this->assertFalse( QueryHelper::is_support_operator( '' ) );
		$this->assertFalse( QueryHelper::is_support_operator( '||' ) );
	}

	// ─── make_clause ────────────────────────────────────────────────────────

	/**
	 * Test make_clause with basic equality.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function test_make_clause_equality() {
		$result = QueryHelper::make_clause( array( 'status', '=', 'active' ) );
		$this->assertSame( "`status` = 'active'", $result );
	}

	/**
	 * Test make_clause with IN operator.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function test_make_clause_in() {
		$result = QueryHelper::make_clause( array( 'id', 'IN', array( 1, 2, 3 ) ) );
		$this->assertSame( '`id` IN (1,2,3)', $result );
	}

	/**
	 * Test make_clause with NULL value.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function test_make_clause_null() {
		$result = QueryHelper::make_clause( array( 'deleted_at', 'IS', 'NULL' ) );
		$this->assertSame( '`deleted_at` IS NULL', $result );
	}

	/**
	 * Test make_clause rejects injection in field name.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function test_make_clause_rejects_injection_in_field() {
		$result = QueryHelper::make_clause( array( "id; DROP TABLE users", '=', 1 ) );
		$this->assertSame( '1=0', $result );
	}

	/**
	 * Test make_clause with qualified column name (table.column).
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function test_make_clause_qualified_column() {
		$result = QueryHelper::make_clause( array( 'u.status', '=', 'active' ) );
		$this->assertSame( "`u`.`status` = 'active'", $result );
	}

	// ─── prepare_like_clause ────────────────────────────────────────────────

	/**
	 * Test prepare_like_clause with valid columns.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function test_prepare_like_clause_valid() {
		$result = QueryHelper::prepare_like_clause( array( 'name' => 'test' ) );
		$this->assertStringContainsString( '`name`', $result );
		$this->assertStringContainsString( 'LIKE', $result );
		$this->assertStringContainsString( 'test', $result );
	}

	/**
	 * Test prepare_like_clause skips invalid column names.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function test_prepare_like_clause_skips_invalid_columns() {
		$result = QueryHelper::prepare_like_clause( array( "invalid; DROP" => 'test' ) );
		$this->assertSame( '', $result );
	}

	/**
	 * Test prepare_like_clause with multiple columns.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function test_prepare_like_clause_multiple_columns() {
		$result = QueryHelper::prepare_like_clause(
			array(
				'name'  => 'john',
				'email' => 'john',
			)
		);
		$this->assertStringContainsString( '`name`', $result );
		$this->assertStringContainsString( '`email`', $result );
		$this->assertStringContainsString( ' OR ', $result );
	}

	// ─── prepare_order_clause ───────────────────────────────────────────────

	/**
	 * Test prepare_order_clause with valid inputs.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function test_prepare_order_clause_valid() {
		$this->assertSame( 'ORDER BY id DESC', QueryHelper::prepare_order_clause( 'id', 'DESC' ) );
		$this->assertSame( 'ORDER BY created_at ASC', QueryHelper::prepare_order_clause( 'created_at', 'ASC' ) );
		$this->assertSame( 'ORDER BY u.name DESC', QueryHelper::prepare_order_clause( 'u.name', 'DESC' ) );
	}

	/**
	 * Test prepare_order_clause returns empty string for empty or invalid input.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function test_prepare_order_clause_empty_and_invalid() {
		$this->assertSame( '', QueryHelper::prepare_order_clause( '' ) );
		$this->assertSame( '', QueryHelper::prepare_order_clause( 'id; DROP TABLE' ) );
		$this->assertSame( '', QueryHelper::prepare_order_clause( "col'bad" ) );
	}

	/**
	 * Test prepare_order_clause defaults invalid order to DESC.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function test_prepare_order_clause_defaults_order() {
		$this->assertSame( 'ORDER BY id DESC', QueryHelper::prepare_order_clause( 'id', 'INVALID' ) );
		$this->assertSame( 'ORDER BY id DESC', QueryHelper::prepare_order_clause( 'id', 'desc' ) );
	}

	// ─── prepare_limit_clause ───────────────────────────────────────────────

	/**
	 * Test prepare_limit_clause with valid inputs.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function test_prepare_limit_clause_valid() {
		$this->assertSame( 'LIMIT 10 OFFSET 0', QueryHelper::prepare_limit_clause( 10, 0 ) );
		$this->assertSame( 'LIMIT 25 OFFSET 50', QueryHelper::prepare_limit_clause( 25, 50 ) );
	}

	/**
	 * Test prepare_limit_clause returns empty for invalid values.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function test_prepare_limit_clause_invalid() {
		$this->assertSame( '', QueryHelper::prepare_limit_clause( 0, 0 ) );
		$this->assertSame( '', QueryHelper::prepare_limit_clause( -1, 0 ) );
		$this->assertSame( '', QueryHelper::prepare_limit_clause( 10, -1 ) );
	}

	// ─── prepare_value ──────────────────────────────────────────────────────

	/**
	 * Test prepare_value with integer values.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function test_prepare_value_integer() {
		$this->assertEquals( '42', QueryHelper::prepare_value( 42 ) );
		$this->assertEquals( '0', QueryHelper::prepare_value( 0 ) );
		$this->assertEquals( '-5', QueryHelper::prepare_value( -5 ) );
	}

	/**
	 * Test prepare_value with float values.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function test_prepare_value_float() {
		$this->assertEquals( '3.14', QueryHelper::prepare_value( 3.14 ) );
		$this->assertEquals( '0.5', QueryHelper::prepare_value( 0.5 ) );
		$this->assertEquals( '1.25', QueryHelper::prepare_value( 1.25 ) );
	}

	/**
	 * Test prepare_value with string values.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function test_prepare_value_string() {
		$result = QueryHelper::prepare_value( 'hello' );
		$this->assertSame( "'hello'", $result );
	}

	/**
	 * Test prepare_value escapes SQL-special characters.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function test_prepare_value_escapes_special() {
		$result = QueryHelper::prepare_value( "test'value" );
		// Must not contain an unescaped single quote.
		$this->assertStringNotContainsString( "test'value", $result );
	}

	// ─── prepare_raw_query ──────────────────────────────────────────────────

	/**
	 * Test prepare_raw_query rejects unsafe SQL characters.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function test_prepare_raw_query_rejects_unsafe() {
		// Semicolons are not allowed.
		$this->assertSame( '', QueryHelper::prepare_raw_query( 'id = 1; DROP TABLE users', array() ) );

		// Backticks are not allowed.
		$this->assertSame( '', QueryHelper::prepare_raw_query( '`id` = 1', array() ) );

		// Block comment syntax is not allowed.
		$this->assertSame( '', QueryHelper::prepare_raw_query( 'id = 1 /* comment */', array() ) );
	}

	/**
	 * Test prepare_raw_query without parameters returns raw query as-is.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function test_prepare_raw_query_no_params() {
		$this->assertSame( 'age = 18', QueryHelper::prepare_raw_query( 'age = 18', array() ) );
	}

	// ─── get_period_clause ──────────────────────────────────────────────────

	/**
	 * Test get_period_clause with valid column and periods.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function test_get_period_clause_valid() {
		$result = QueryHelper::get_period_clause( 'o.created_at', 'today' );
		$this->assertStringContainsString( '`o`.`created_at`', $result );
		$this->assertStringContainsString( 'CURDATE()', $result );

		$result = QueryHelper::get_period_clause( 'created_at', 'monthly' );
		$this->assertStringContainsString( 'MONTH(`created_at`)', $result );
		$this->assertStringContainsString( 'YEAR(`created_at`)', $result );

		$result = QueryHelper::get_period_clause( 'created_at', 'yearly' );
		$this->assertStringContainsString( 'YEAR(`created_at`)', $result );

		$result = QueryHelper::get_period_clause( 'created_at', 'last30days' );
		$this->assertStringContainsString( 'INTERVAL 30 DAY', $result );

		$result = QueryHelper::get_period_clause( 'created_at', 'last90days' );
		$this->assertStringContainsString( 'INTERVAL 90 DAY', $result );

		$result = QueryHelper::get_period_clause( 'created_at', 'last365days' );
		$this->assertStringContainsString( 'INTERVAL 365 DAY', $result );
	}

	/**
	 * Test get_period_clause returns empty for unknown period.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function test_get_period_clause_unknown_period() {
		$this->assertSame( '', QueryHelper::get_period_clause( 'created_at', 'unknown' ) );
		$this->assertSame( '', QueryHelper::get_period_clause( 'created_at', '' ) );
	}

	/**
	 * Test get_period_clause rejects invalid column names.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function test_get_period_clause_invalid_column() {
		$this->assertSame( '', QueryHelper::get_period_clause( "col; DROP TABLE", 'today' ) );
		$this->assertSame( '', QueryHelper::get_period_clause( '', 'today' ) );
	}

	// ─── prepare_table_name ─────────────────────────────────────────────────

	/**
	 * Test prepare_table_name adds prefix when missing.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function test_prepare_table_name_adds_prefix() {
		global $wpdb;
		$result = QueryHelper::prepare_table_name( 'my_table' );
		$this->assertSame( $wpdb->prefix . 'my_table', $result );
	}

	/**
	 * Test prepare_table_name does not double-prefix.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function test_prepare_table_name_no_double_prefix() {
		global $wpdb;
		$already_prefixed = $wpdb->prefix . 'my_table';
		$result           = QueryHelper::prepare_table_name( $already_prefixed );
		$this->assertSame( $already_prefixed, $result );
	}

	// ─── get_valid_sort_order ───────────────────────────────────────────────

	/**
	 * Test get_valid_sort_order.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function test_get_valid_sort_order() {
		$this->assertSame( 'ASC', QueryHelper::get_valid_sort_order( 'ASC' ) );
		$this->assertSame( 'ASC', QueryHelper::get_valid_sort_order( 'asc' ) );
		$this->assertSame( 'DESC', QueryHelper::get_valid_sort_order( 'DESC' ) );
		$this->assertSame( 'DESC', QueryHelper::get_valid_sort_order( 'desc' ) );
		$this->assertSame( 'DESC', QueryHelper::get_valid_sort_order( 'INVALID' ) );
		$this->assertSame( 'DESC', QueryHelper::get_valid_sort_order( '' ) );
	}

	// ─── where clause injection safety ──────────────────────────────────────

	/**
	 * Test prepare_where_clause neutralizes injection in column keys.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function test_prepare_where_clause_injection_in_key() {
		$where  = array( "id = 1; DROP TABLE users --" => 'value' );
		$result = QueryHelper::prepare_where_clause( $where );
		// The injection key must be rejected, producing the safe fallback.
		$this->assertStringContainsString( '1=0', $result );
		$this->assertStringNotContainsString( 'DROP', $result );
	}
}
