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

use Tutor\Helpers\HttpHelper;
use Tutor\Models\CourseModel;
use WP_REST_Request;

defined( 'ABSPATH' ) || exit;

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
	 * Mirrors plugin UI reviews access:
	 * - Published courses: public (guest reduced payload).
	 * - Private courses: enrolled / course-content access only.
	 * - Authenticated callers get their own rating (approved or hold).
	 *
	 * @since 1.7.1
	 * @since 4.2.0 Guest payload matches reviews UI; private courses for enrolled viewers; expose my_rating.
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

		if ( ! $this->can_read_course_rating( $this->post_id ) ) {
			return $this->response(
				'tutor_read_rating',
				__( 'Course not found', 'tutor' ),
				array(),
				HttpHelper::STATUS_NOT_FOUND
			);
		}

		$is_guest        = ! RestAuth::is_rest_authenticated();
		$current_user_id = $is_guest ? 0 : (int) tutor_utils()->get_user_id();

		$ratings = tutor_utils()->get_course_rating( $this->post_id );
		$reviews = tutor_utils()->get_course_reviews(
			$this->post_id,
			$offset,
			$limit,
			false,
			array( 'approved' ),
			$current_user_id
		);

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
						'display_name'     => isset( $review->display_name ) ? $review->display_name : '',
						'comment_content'  => isset( $review->comment_content ) ? $review->comment_content : '',
						'comment_date_gmt' => isset( $review->comment_date_gmt ) ? $review->comment_date_gmt : '',
						'rating'           => isset( $review->rating ) ? $review->rating : 0,
					);
				}
			}

			return $this->response(
				'tutor_read_rating',
				__( 'Course rating retrieved successfully', 'tutor' ),
				$payload,
				HttpHelper::STATUS_OK
			);
		}

		$my_rating = tutor_utils()->get_reviews_by_user(
			$current_user_id,
			0,
			1,
			false,
			$this->post_id,
			array( 'approved', 'hold' )
		);

		$ratings->reviews   = is_array( $reviews ) ? $reviews : array();
		$ratings->my_rating = $my_rating ? $my_rating : null;

		foreach ( $ratings->reviews as $review ) {
			$user_id = isset( $review->user_id ) ? (int) $review->user_id : 0;
			if ( ! RestAuth::can_view_user_private_fields( $user_id ) ) {
				unset( $review->comment_author_email );
			}
		}

		if ( $ratings->my_rating && ! RestAuth::can_view_user_private_fields( $current_user_id ) ) {
			unset( $ratings->my_rating->comment_author_email );
		}

		return $this->response(
			'tutor_read_rating',
			__( 'Course rating retrieved successfully', 'tutor' ),
			$ratings,
			HttpHelper::STATUS_OK
		);
	}

	/**
	 * Whether the caller may read ratings for the course.
	 *
	 * Matches UI: publish is public; private requires enrolled / content access.
	 *
	 * @since 4.2.0
	 *
	 * @param int $course_id Course ID.
	 *
	 * @return bool
	 */
	private function can_read_course_rating( $course_id ) {
		$course_id = absint( $course_id );
		if ( ! $course_id || ! CourseModel::get_post_types( $course_id ) ) {
			return false;
		}

		$status = get_post_status( $course_id );

		if ( CourseModel::STATUS_PUBLISH === $status ) {
			return true;
		}

		if ( CourseModel::STATUS_PRIVATE === $status ) {
			return RestAuth::is_rest_authenticated() && RestAuth::can_view_course_content( $course_id );
		}

		return false;
	}
}
