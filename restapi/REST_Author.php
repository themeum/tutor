<?php
/**
 * REST API for author details.
 *
 * @package Tutor\RestAPI
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.7.1
 */

namespace TUTOR;

use WP_REST_Request;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class REST_Author
 *
 * @package Tutor
 * @since 1.0.0
 */
class REST_Author {

	use REST_Response;

	/**
	 * User ID.
	 *
	 * @var int $user_id The ID of the user.
	 */
	private $user_id;

	/**
	 * Retrieve author details via REST API.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return mixed
	 */
	public function author_detail( WP_REST_Request $request ) {
		$this->user_id = absint( $request->get_param( 'id' ) );

		$user_data = get_userdata( $this->user_id );

		if ( ! is_a( $user_data, 'WP_User' ) ) {
			$response = array(
				'code'    => 'invalid_id',
				'message' => __( 'Author not found', 'tutor' ),
				'data'    => array(),
			);

			return static::send( $response );
		}

		$author = (object) array(
			'ID'            => $user_data->ID,
			'display_name'  => $user_data->display_name,
			'user_nicename' => $user_data->user_nicename,
			'courses'       => get_user_meta( $this->user_id, '_tutor_instructor_course_id', false ),
		);

		if ( RestAuth::can_view_user_private_fields( $this->user_id ) ) {
			$author->user_login      = $user_data->user_login;
			$author->user_email      = $user_data->user_email;
			$author->user_registered = $user_data->user_registered;
			$author->user_url        = $user_data->user_url;
		}

		$response = array(
			'code'    => 'success',
			'message' => __( 'Author details retrieved successfully', 'tutor' ),
			'data'    => $author,
		);

		return static::send( $response );
	}
}
