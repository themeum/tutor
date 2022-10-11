<?php
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

}
