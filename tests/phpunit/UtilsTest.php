<?php
/**
 * Utils test class
 */

namespace TutorTest;

use Tutor\Utils;

class UtilsTest extends \WP_UnitTestCase {

	public function test_total_review() {
		$utils  = new Utils();
		$result = $utils->get_total_review();
		$this->assertIsNumeric( $result );
	}

	public function test_get_user_name() {
		$username           = 'admin';
		$user               = new \WP_User();
		$user->user_login   = $username;
		$user->display_name = $username;

		// If someone not set first and last name
		$this->assertEquals( 'admin', tutor_utils()->get_user_name( $user ) );

		// If someone only set first name
		$user->first_name = 'Jhon';
		$this->assertEquals( 'Jhon', tutor_utils()->get_user_name( $user ) );

		// If someone set first and last name both
		$user->last_name = 'Kou';
		$this->assertEquals( 'Jhon Kou', tutor_utils()->get_user_name( $user ) );

		// If someone only set last name
		unset( $user->first_name );
		$this->assertEquals( $username, tutor_utils()->get_user_name( $user ) );
	}

	public function test_add_option_after_an_option_key() {
		$utils = new Utils();

		$arr   = array( 'a' => 'b' );
		$index = $utils->add_option_after( 'foo', $arr, array() );
		$this->assertEquals( null, $index );

		$arr1   = array( array( 'key' => 'a' ), array( 'key' => 'b' ), array( 'key' => 'c' ) );
		$index1 = $utils->add_option_after( 'b', $arr1, array() );
		$this->assertEquals( null, $index1 );

		$arr3     = array( array( 'key' => 'a' ), array( 'key' => 'b' ), array( 'key' => 'd' ) );
		$new_item = array( 'key' => 'c' );
		$index3   = $utils->add_option_after( 'b', $arr3, array( 'key' => 'c' ) );

		$this->assertEquals( 2, $index3 );
		$this->assertEquals( $arr3[ $index3 ]['key'], $new_item['key'] );
	}

	/**
	 * Test the is_assoc function
	 *
	 * @return boolean
	 */
	public function test_is_assoc() {
		$array1 = array( 0, 1, 2 );
		$array2 = array(
			'a' => 'apple',
			'b' => 'banana',
		);

		$this->assertTrue( tutor_utils()->is_assoc( $array2 ) );
		$this->assertFalse( tutor_utils()->is_assoc( $array1 ) );
	}

	/**
	 * Test display name
	 *
	 * @return void
	 */
	public function test_display_name() {
		$args      = array(
			'display_name' => 'john doe',
		);
		$user_id   = Utilities::get_user_id( $args );
		$expected1 = 'john doe';
		$actual1   = tutor_utils()->display_name( $user_id );

		// Test display name.
		$this->assertSame( $expected1, $actual1 );

		// Update user , set display name empty.
		$update_args = array(
			'ID'           => $user_id,
			'display_name' => '',
			'user_login'   => 'abc',
		);
		$user_id = Utilities::get_user_id( $update_args );

		$expected2 = 'abc';
		/**
		 * It should return user_login since display name empty
		 * & first name & last name not been setup
		 */
		$actual2 = tutor_utils()->display_name( $user_id );

		$this->assertSame( $expected2, $actual2 );
	}
}
