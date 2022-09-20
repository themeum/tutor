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
}
