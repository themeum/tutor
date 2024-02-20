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
		$this->user_id = $request->get_param( 'id' );

		$user_data = get_userdata( $this->user_id );

		// Author object.
		$author = is_a( $user_data, 'WP_User' ) ? $user_data->data : false;

		if ( $author ) {
			// Unset user pass & key.
			unset( $author->user_pass );
			unset( $author->user_activation_key );

			// Get author course ID.
			$author->courses = get_user_meta( $this->user_id, '_tutor_instructor_course_id', false );

			$response = array(
				'status_code' => 'success',
				'message'     => __( 'Author details retrieved successfully', 'tutor' ),
				'data'        => $author,
			);

			return self::send( $response );
		}

		$response = array(
			'status_code' => 'invalid_id',
			'message'     => __( 'Author not found', 'tutor' ),
			'data'        => array(),
		);

		return self::send( $response );
	}
}
