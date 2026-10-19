<?php
/**
 * REST API for course announcements.
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
 * Class REST_Course_Announcement
 *
 * @package Tutor
 * @since 1.0.0
 */
class REST_Course_Announcement {

	use REST_Response;

	/**
	 * Post parent ID.
	 *
	 * @var int $post_parent The ID of the post parent.
	 */
	private $post_parent;

	/**
	 * Post type.
	 *
	 * @var string $post_type The post type for announcements.
	 */
	private $post_type = 'tutor_announcements';

	/**
	 * Retrieve announcements by course ID via REST API.
	 *
	 * @since 1.7.1
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return mixed
	 */
	public function course_announcement( WP_REST_Request $request ) {
		$this->post_parent = $request->get_param( 'id' );
		$course_id         = absint( $this->post_parent );

		if ( ! CourseModel::get_post_types( $course_id ) ) {
			return $this->response(
				'tutor_read_announcement',
				__( 'Announcement not found for given ID', 'tutor' ),
				array(),
				HttpHelper::STATUS_NOT_FOUND
			);
		}

		$result = REST_Posts::get_published_child_posts(
			$this->post_type,
			$this->post_parent,
			array(
				'orderby' => 'date',
				'order'   => 'DESC',
			)
		);

		if ( count( $result ) > 0 ) {
			return $this->response(
				'tutor_read_announcement',
				__( 'Announcement retrieved successfully', 'tutor' ),
				$result,
				HttpHelper::STATUS_OK
			);
		}

		return $this->response(
			'tutor_read_announcement',
			__( 'Announcement not found for given ID', 'tutor' ),
			array(),
			HttpHelper::STATUS_NOT_FOUND
		);
	}
}
