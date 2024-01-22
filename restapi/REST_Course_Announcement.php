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

use WP_REST_Request;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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

		global $wpdb;

		$table = $wpdb->prefix . 'posts';

		$result = $wpdb->get_results(
			$wpdb->prepare( "SELECT ID, post_title, post_content, post_name FROM $table WHERE post_type = %s AND post_parent = %d", $this->post_type, $this->post_parent )
		);

		if ( count( $result ) > 0 ) {
			$response = array(
				'status_code' => 'success',
				'message'     => __( 'Announcement retrieved successfully', 'tutor' ),
				'data'        => $result,
			);

			return self::send( $response );
		}

		$response = array(
			'status_code' => 'not_found',
			'message'     => __( 'Announcement not found for given ID', 'tutor' ),
			'data'        => array(),
		);

		return self::send( $response );
	}
}
