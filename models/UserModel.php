<?php
/**
 * User Model
 *
 * @package Tutor\Models
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\Models;

use TUTOR\Course;
use Tutor\Helpers\QueryHelper;

/**
 * Coupon model class
 */
class UserModel {

	/**
	 * Get user list
	 *
	 * @since 3.0.0
	 *
	 * @see https://developer.wordpress.org/reference/classes/wp_user_query/
	 *
	 * @return \WP_User_Query
	 */
	public function get_users_list( array $args ) {
		$default_args = array(
			'orderby' => 'ID',
			'order'   => 'DESC',
			'number'  => 10,
			'offset'  => 0,
			'role'    => '',
			'fields'  => array( 'ID', 'user_login', 'display_name', 'user_email', 'user_url', 'registered' ),
		);

		$args = wp_parse_args( $args, $default_args );

		return new \WP_User_Query( $args );
	}
}
