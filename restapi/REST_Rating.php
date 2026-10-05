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

use Tutor\Models\CourseModel;
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
	 * @since 4.2.0 Guest payload matches reviews UI; require published course.
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
		$limit  = ! empty( $limit ) ? min( $limit, 100 ) : 10;

		if ( ! CourseModel::get_post_types( $this->post_id ) || 'publish' !== get_post_status( $this->post_id ) ) {
			$response = array(
				'code'    => 'not_found',
				'message' => __( 'Course not found', 'tutor' ),
				'data'    => array(),
			);
			return self::send( $response );
		}

		$ratings = tutor_utils()->get_course_rating( $this->post_id );
		$reviews = tutor_utils()->get_course_reviews( $this->post_id, $offset, $limit, false, array( 'approved' ) );

		$is_guest = ! RestAuth::is_rest_authenticated();

		if ( $is_guest ) {
			$payload = (object) array(
				'rating_count'   => isset( $ratings->rating_count ) ? $ratings->rating_count : 0,
				'rating_avg'     => isset( $ratings->rating_avg ) ? $ratings->rating_avg : 0,
				'count_by_value' => isset( $ratings->count_by_value ) ? $ratings->count_by_value : array(),
				'reviews'        => array(),
			);

			if ( is_array( $reviews ) ) {
				foreach ( $reviews as $review ) {
					$payload->reviews[] = (object) array(
						'display_name'      => isset( $review->display_name ) ? $review->display_name : '',
						'comment_content'   => isset( $review->comment_content ) ? $review->comment_content : '',
						'comment_date_gmt'  => isset( $review->comment_date_gmt ) ? $review->comment_date_gmt : '',
						'rating'            => isset( $review->rating ) ? $review->rating : 0,
					);
				}
			}

			$response = array(
				'code'    => 'success',
				'message' => __( 'Course rating retrieved successfully', 'tutor' ),
				'data'    => $payload,
			);

			return self::send( $response );
		}

		$ratings->reviews = is_array( $reviews ) ? $reviews : array();

		foreach ( $ratings->reviews as $review ) {
			$user_id = isset( $review->user_id ) ? (int) $review->user_id : 0;
			if ( ! RestAuth::can_view_user_private_fields( $user_id ) ) {
				unset( $review->comment_author_email );
			}
		}

		$response = array(
			'code'    => 'success',
			'message' => __( 'Course rating retrieved successfully', 'tutor' ),
			'data'    => $ratings,
		);

		return self::send( $response );
	}
}
