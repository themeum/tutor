<?php
/**
 * Tutor MCP curriculum tools (Free read-only).
 *
 * @package Tutor\Mcp
 * @author  Themeum <support@themeum.com>
 * @link    https://themeum.com
 * @since   4.3.0
 */

namespace Tutor\Mcp\Abilities;

use Tutor\Mcp\Support\AbilityFactory;
use Tutor\Mcp\Support\Permissions;
use Tutor\Mcp\Support\RestBridge;
use TUTOR\REST_Lesson;
use TUTOR\REST_Quiz;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lesson and quiz read tool registration and execute handlers.
 *
 * @since 4.3.0
 */
class CurriculumAbilities {

	/**
	 * Register Free curriculum abilities (read-only).
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public function register(): void {
		AbilityFactory::tool(
			'tutor-mcp/get-lesson',
			'tutor-mcp-curriculum',
			__( 'Get Lesson', 'tutor' ),
			__( 'Fetch a published lesson by ID (same as REST topic lessons).', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'lesson_id' ),
				'properties' => array(
					'lesson_id' => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
				),
			),
			static function ( array $input ): bool {
				$lesson_id = absint( $input['lesson_id'] ?? 0 );
				$course_id = (int) tutor_utils()->get_course_id_by( 'lesson', $lesson_id );
				return $course_id && Permissions::can_view_course( $course_id );
			},
			array( $this, 'get_lesson' )
		);

		AbilityFactory::tool(
			'tutor-mcp/get-quiz',
			'tutor-mcp-curriculum',
			__( 'Get Quiz', 'tutor' ),
			__( 'Fetch a published quiz by ID (same as REST quiz; answers gated).', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'quiz_id' ),
				'properties' => array(
					'quiz_id' => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
				),
			),
			static function ( array $input ): bool {
				$quiz_id   = absint( $input['quiz_id'] ?? 0 );
				$course_id = (int) tutor_utils()->get_course_id_by( 'quiz', $quiz_id );
				return $course_id && Permissions::can_view_course( $course_id );
			},
			array( $this, 'get_quiz' )
		);
	}

	/**
	 * Get lesson.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function get_lesson( array $input ) {
		$lesson_id = absint( $input['lesson_id'] ?? 0 );
		$post      = get_post( $lesson_id );
		if ( ! $post instanceof \WP_Post || tutor()->lesson_post_type !== $post->post_type ) {
			return new \WP_Error( 'tutor_mcp_not_found', __( 'Lesson not found.', 'tutor' ) );
		}

		$request = new \WP_REST_Request( 'GET' );
		$request->set_param( 'topic_id', (int) $post->post_parent );

		$lessons = RestBridge::unwrap_call(
			static function () use ( $request ) {
				return ( new REST_Lesson() )->topic_lesson( $request );
			}
		);
		if ( is_wp_error( $lessons ) ) {
			return new \WP_Error( 'tutor_mcp_not_found', __( 'Lesson not found.', 'tutor' ) );
		}

		foreach ( $lessons as $lesson ) {
			if ( (int) ( $lesson['ID'] ?? 0 ) === $lesson_id ) {
				return $lesson;
			}
		}

		return new \WP_Error( 'tutor_mcp_not_found', __( 'Lesson not found.', 'tutor' ) );
	}

	/**
	 * Get quiz (answer keys gated by REST).
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function get_quiz( array $input ) {
		$quiz_id = absint( $input['quiz_id'] ?? 0 );
		$request = new \WP_REST_Request( 'GET' );
		$request->set_param( 'id', $quiz_id );

		$data = RestBridge::unwrap_call(
			static function () use ( $request ) {
				return ( new REST_Quiz() )->get_quiz( $request );
			}
		);
		if ( is_wp_error( $data ) ) {
			return new \WP_Error( 'tutor_mcp_not_found', __( 'Quiz not found.', 'tutor' ) );
		}

		return $data;
	}
}
