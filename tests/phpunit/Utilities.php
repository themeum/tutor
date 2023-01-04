<?php
/**
 * Wrapper of WP Factory to provide easy to use
 * helper methods
 *
 * @package Tutor\Test
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.1.6
 */

namespace TutorTest;

/**
 * Contain Utilities method
 */
class Utilities extends  \WP_UnitTestCase {

	/**
	 * Create & get user id
	 *
	 * @param array $args array of args.
	 *
	 * @return integer
	 */
	public static function get_user_id( array $args ): int {
		$default_args = array(
			'user_pass'     => '1234',
			'user_login'    => 'test_user',
			'user_nicename' => 'test_user_nice',
			'user_email'    => 'test@email.com',
			'role'          => 'subscriber',
		);
		$args         = wp_parse_args( $args, $default_args );
		$user_id      = self::factory()->user->create( $args );
		return $user_id;
	}

	/**
	 * Create & get user
	 *
	 * @param array $args array of args.
	 *
	 * @return WP_User|False
	 */
	public static function get_user( array $args ) {
		$default_args = array(
			'user_pass'     => '1234',
			'user_login'    => 'test_user',
			'user_nicename' => 'test_user_nice',
			'user_email'    => 'test@email.com',
			'role'          => 'subscriber',
		);
		$args         = wp_parse_args( $args, $default_args );
		$user         = self::factory()->user->create_and_get( $args );
		return $user;
	}
}
