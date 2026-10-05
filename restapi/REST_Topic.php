<?php
/**
 * REST API for course topics.
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
 * Class REST_Topic
 *
 * @package Tutor
 *
 * @since 1.7.1
 */
class REST_Topic {
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
	 * @var string $post_type The post type for topics.
	 */
	private $post_type = 'topics';

	/**
	 * Retrieve topics by course ID via REST API.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @since 1.7.1
	 *
	 * @return mixed
	 */
	public function course_topic( WP_REST_Request $request ) {
		$this->post_parent = $request->get_param( 'course_id' );

		if ( ! isset( $this->post_parent ) ) {
			$response = array(
				'code'    => 'get_topic',
				'message' => __( 'course_id is required', 'tutor' ),
				'data'    => array(),
			);
			return self::send( $response );
		}

		$course_id     = absint( $this->post_parent );
		$reveal_bodies = RestAuth::can_reveal_learning_payload( $course_id );

		$result = REST_Posts::get_published_child_posts( $this->post_type, $this->post_parent );

		if ( ! $reveal_bodies ) {
			$result = array_map(
				static function ( $topic ) {
					return (object) array(
						'ID'         => (int) $topic->ID,
						'post_title' => $topic->post_title,
						'post_name'  => $topic->post_name,
					);
				},
				$result
			);
		}

		if ( count( $result ) > 0 ) {
			$response = array(
				'code'    => 'get_topic',
				'message' => __( 'Topic retrieved successfully', 'tutor' ),
				'data'    => $result,
			);

			return self::send( $response );
		}
		$response = array(
			'code'    => 'not_found',
			'message' => __( 'Topic not found for given course ID', 'tutor' ),
			'data'    => array(),
		);

		return self::send( $response );
	}
}
