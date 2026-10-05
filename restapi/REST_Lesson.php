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
	 * Guests / non-enrolled users on public paid courses receive an outline
	 * (title + duration) matching the single-course curriculum UI.
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

		$topic_id      = absint( $this->post_parent );
		$course_id     = (int) tutor_utils()->get_course_id_by( 'topic', $topic_id );
		$reveal_bodies = RestAuth::can_reveal_learning_payload( $course_id );

		$args = array(
			'post_type'      => $this->post_type,
			'post_parent'    => $topic_id,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		);

		$lessons_query = new WP_Query( $args );

		$data = array();

		if ( $lessons_query->have_posts() ) {
			$posts = $lessons_query->get_posts();
			foreach ( $posts as $post ) {
				if ( $reveal_bodies ) {
					$data[] = self::to_full_lesson_dto( $post, $topic_id );
				} else {
					$data[] = REST_Posts::to_outline_post_dto( $post );
				}
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

	/**
	 * Full lesson payload (enrolled / public-free / instructor).
	 *
	 * @since 4.2.0
	 *
	 * @param \WP_Post $post     Lesson post.
	 * @param int      $topic_id Topic id.
	 *
	 * @return object
	 */
	private static function to_full_lesson_dto( $post, $topic_id ) {
		$lesson = new \stdClass();

		$lesson->ID           = $post->ID;
		$lesson->post_title   = $post->post_title;
		$lesson->post_content = $post->post_content;
		$lesson->post_name    = $post->post_name;
		$lesson->topic_id     = absint( $topic_id );

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

		return $lesson;
	}
}
