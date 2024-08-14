<?php
/**
 * REST API Lesson
 *
 * @package Tutor\RestAPI
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.7.1
 */

namespace TUTOR;

use WP_Query;
use WP_REST_Request;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class REST_Lesson
 */
class REST_Lesson {

	use REST_Response;

	/**
	 * Post type
	 *
	 * @var string $post_type
	 */
	private $post_type;

	/**
	 * Post parent ID
	 *
	 * @var int $post_parent
	 */
	private $post_parent;

	/**
	 * REST_Lesson constructor.
	 */
	public function __construct() {
		$this->post_type = tutor()->lesson_post_type;
	}

	/**
	 * Get lessons for a specific topic.
	 *
	 * @param WP_REST_Request $request REST request object.
	 *
	 * @return mixed
	 */
	public function topic_lesson( WP_REST_Request $request ) {
		$this->post_parent = $request->get_param( 'topic_id' );

		if ( ! isset( $this->post_parent ) ) {
			$response = array(
				'code'    => 'not_found',
				'message' => __( 'topic_id is required', 'tutor' ),
				'data'    => array(),
			);
			return self::send( $response );
		}

		$args = array(
			'post_type'      => $this->post_type,
			'post_parent'    => $this->post_parent,
			'posts_per_page' => -1,
		);

		$lessons_query = new WP_Query( $args );

		$data = array();

		if ( $lessons_query->have_posts() ) {
			$posts = $lessons_query->get_posts();
			foreach ( $posts as $post ) {

				$lesson = new \stdClass();

				$lesson->ID           = $post->ID;
				$lesson->post_title   = $post->post_title;
				$lesson->post_content = $post->post_content;
				$lesson->post_name    = $post->post_name;
				$lesson->topic_id     = wp_get_post_parent_id( $lesson->ID );

				$attachments    = array();
				$attachments_id = get_post_meta( $lesson->ID, '_tutor_attachments', false );
				if ( is_array( $attachments_id ) && count( $attachments_id ) > 0 ) {
					$attachments_id = $attachments_id[0];

					foreach ( $attachments_id as $id ) {
						$guid = get_the_guid( $id );
						array_push( $attachments, $guid );
					}
				}

				$lesson->attachments = $attachments;
				$lesson->thumbnail   = get_the_post_thumbnail_url( $lesson->ID );
				$lesson->video       = get_post_meta( $lesson->ID, '_video', false );

				array_push( $data, $lesson );
			}

			$response = array(
				'code'    => 'success',
				'message' => __( 'Lesson retrieved successfully', 'tutor' ),
				'data'    => $data,
			);

			return self::send( $response );
		}

		$response = array(
			'code'    => 'not_found',
			'message' => __( 'Lesson not found for the given topic ID', 'tutor' ),
			'data'    => array(),
		);

		return self::send( $response );
	}
}
