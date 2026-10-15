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
		$expect  = 'SET id = 1';

		// Multiple elements.
		$array2  = array(
			'id'     => 1,
			'title'  => 'title',
			'status' => 'publish',
		);
		$actual2 = QueryHelper::prepare_set_clause( $array2 );
		$expect2 = "SET id = 1,title = 'title',status = 'publish'";

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
		$expect3 = "SET id = 1,title = 'title',status = 'publish'";

		$array4  = array();
		$actual4 = QueryHelper::prepare_set_clause( $array4 );
		$expect4 = '';

		$this->assertSame( $expect, trim( $actual1 ) );
		$this->assertSame( $expect2, trim( $actual2 ) );
		$this->assertSame( $expect3, trim( $actual3 ) );
		$this->assertSame( $expect4, trim( $actual4 ) );
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
		$expect1 = "age = 18 AND height >= '5feet7inch' AND hobbies IN ('coding','biking','swimming')";
		$actual1 = QueryHelper::prepare_where_clause( $test1 );

		$test2 = array(
			'salary' => array( 'BETWEEN', array( 10, 20 ) ),
			'name'   => array( 'NOT BETWEEN', array( 'b', 'c' ) ),
		);

		$expect2 = "salary BETWEEN 10 AND 20 AND name NOT BETWEEN 'b' AND 'c'";
		$actual2 = QueryHelper::prepare_where_clause( $test2 );

		$test3 = array(
			'name' => array( 'LIKE', 'test' ),
			'age'  => array( 'NOT IN', array( 18, 19, 20 ) ),
		);

		$expect3 = "name LIKE 'test' AND age NOT IN (18,19,20)";
		$actual3 = QueryHelper::prepare_where_clause( $test3 );

		$test4 = array(
			'name' => 'NULL',
			'age'  => array( 'IS NOT', 'NULL' ),
		);

		$expect4 = 'name IS NULL AND age IS NOT NULL';
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
		$expect_3 = 'id BETWEEN 10 AND 20 AND DATE(value) = CAST(value as date) AND id = 10';

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
}
