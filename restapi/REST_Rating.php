<?php
/**
 * REST API for course ratings.
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
 * Class REST_Rating
 *
 * @package Tutor
 * @since 1.0.0
 */
class REST_Rating {

	/**
	 * Course response trait
	 *
	 * @since 1.7.1
	 */
	use REST_Response;

	/**
	 * Course ID.
	 *
	 * @since 1.7.1
	 *
	 * @var int $post_id The ID of the course.
	 */
	private $post_id;

	/**
	 * Post type for course ratings.
	 *
	 * @since 1.7.1
	 *
	 * @var string $post_type The post type for course ratings.
	 */
	private $post_type = 'tutor_course_rating';

	/**
	 * Retrieve course ratings via REST API.
	 *
	 * @since 1.7.1
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return mixed
	 */
	public function course_rating( WP_REST_Request $request ) {
		$this->post_id = (int) $request->get_param( 'id' );
		$offset        = (int) sanitize_text_field( $request->get_param( 'offset' ) );
		$limit         = (int) sanitize_text_field( $request->get_param( 'limit' ) );

		$offset = ! empty( $offset ) ? $offset : 0;
		$limit  = ! empty( $limit ) ? $limit : 10;

		$ratings          = tutor_utils()->get_course_rating( $this->post_id );
		$ratings->reviews = tutor_utils()->get_course_reviews( $this->post_id, $offset, $limit, false, array( 'approved' ) );

		if ( ! empty( $ratings ) ) {
			$response = array(
				'status_code' => 'success',
				'message'     => __( 'Course rating retrieved successfully', 'tutor' ),
				'data'        => $ratings,
			);

			return self::send( $response );
		}

		$response = array(
			'status_code' => 'not_found',
			'message'     => __( 'Rating not found for given ID', 'tutor' ),
			'data'        => array(),
		);

		return self::send( $response );
	}
}
