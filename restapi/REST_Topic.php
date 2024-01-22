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
		$this->post_parent = $request->get_param( 'id' );

		global $wpdb;

		$table = $wpdb->prefix . 'posts';

		$result = $wpdb->get_results(
			$wpdb->prepare( "SELECT ID, post_title, post_content, post_name FROM $table WHERE post_type = %s AND post_parent = %d", $this->post_type, $this->post_parent )
		);

		if ( count( $result ) > 0 ) {
			$response = array(
				'status_code' => 'get_topic',
				'message'     => __( 'Topic retrieved successfully', 'tutor' ),
				'data'        => $result,
			);

			return self::send( $response );
		}
		$response = array(
			'status_code' => 'not_found',
			'message'     => __( 'Topic not found for given ID', 'tutor' ),
			'data'        => array(),
		);

		return self::send( $response );
	}
}
