<?php
/**
 * Utils test class
 */

namespace TutorTest;

use Tutor\Utils; 

class UtilsTest extends \WP_UnitTestCase {

	public function test_total_review() {
        $utils = new Utils();
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
}
