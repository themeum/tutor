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
 * UserModel class
 */
class UserModel {

	/**
	 * Get user list
	 *
	 * @since 3.0.0
	 *
	 * @see https://developer.wordpress.org/reference/classes/wp_user_query/
	 *
	 * @param array $args args.
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
		$args = apply_filters( 'tutor_user_list_args', $args );

		return new \WP_User_Query( $args );
	}

	/**
	 * Get unenrolled users of a course/bundle
	 *
	 * @since 3.0.0
	 *
	 * @param int     $object_id Course/Bundle id.
	 * @param array   $search_clause Search condition.
	 * @param integer $limit List limit.
	 * @param integer $offset Offset.
	 *
	 * @return array
	 */
	public function get_unenrolled_users( $object_id, $search_clause = array(), $limit = 10, $offset = 0 ) {
		global $wpdb;

		$primary_table  = "{$wpdb->users} AS u";
		$joining_tables = array(
			array(
				'type'  => 'LEFT',
				'table' => "{$wpdb->posts} p",
				'on'    => "p.post_type = 'tutor_enrolled' AND p.post_parent = {$object_id} AND u.ID = p.post_author",
			),
		);

		$response = QueryHelper::get_joined_data(
			$primary_table,
			$joining_tables,
			array(
				'distinct u.ID',
				'u.user_login',
				'u.user_email',
				'u.display_name',
				'CASE WHEN p.ID IS NOT NULL THEN 1 ELSE 0 END AS is_enrolled',
				'p.post_status',
			),
			array(),
			$search_clause,
			'ID',
			$limit,
			$offset
		);

		foreach ( $response['results'] as $result ) {
			// Typecast `is_enrolled` to int.
			$result->is_enrolled = (int) $result->is_enrolled;

			// Add enrollment status.
			$result->enrollment_status = in_array( $result->post_status, array( 'cancel', 'cancelled', 'canceled' ) ) ? __( 'Cancelled', 'tutor' ) : ( 'completed' === $result->post_status ? __( 'Approved', 'tutor' ) : ( 'pending' === $result->post_status ? __( 'Pending', 'tutor' ) : $result->post_status ) );

			// Add avatar URL for the user.
			$result->avatar_url = get_avatar_url( $result->ID );
		}

		return $response;
	}
}
