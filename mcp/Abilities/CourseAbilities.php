<?php
/**
 * Tutor MCP course tools.
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
use TUTOR\REST_Course;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Course tool registration and execute handlers.
 *
 * @since 4.3.0
 */
class CourseAbilities {

	/**
	 * Register course abilities.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public function register(): void {
		AbilityFactory::tool(
			'tutor-mcp/get-course',
			'tutor-mcp-courses',
			__( 'Get Course', 'tutor' ),
			__( 'Fetch course additional info by ID (same as REST course detail).', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'course_id' ),
				'properties' => array(
					'course_id' => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
				),
			),
			static function ( array $input ): bool {
				return Permissions::can_view_course( absint( $input['course_id'] ?? 0 ) );
			},
			array( $this, 'get_course' )
		);

		AbilityFactory::tool(
			'tutor-mcp/search-courses',
			'tutor-mcp-courses',
			__( 'Search Courses', 'tutor' ),
			__( 'Paginated published course list (same as REST /courses).', 'tutor' ),
			array(
				'type'       => 'object',
				'properties' => array(
					'page'       => array(
						'type'    => 'integer',
						'minimum' => 1,
						'default' => 1,
					),
					'order'      => array( 'type' => 'string' ),
					'orderby'    => array( 'type' => 'string' ),
					'categories' => array( 'type' => 'string' ),
					'tags'       => array( 'type' => 'string' ),
				),
			),
			static function (): bool {
				return Permissions::can_manage_courses();
			},
			array( $this, 'search_courses' )
		);

		AbilityFactory::tool(
			'tutor-mcp/get-course-contents',
			'tutor-mcp-courses',
			__( 'Get Course Contents', 'tutor' ),
			__( 'Topics and nested lessons/quizzes.', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'course_id' ),
				'properties' => array(
					'course_id' => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
				),
			),
			static function ( array $input ): bool {
				return Permissions::can_view_course( absint( $input['course_id'] ?? 0 ) );
			},
			array( $this, 'get_course_contents' )
		);
	}

	/**
	 * Get course.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function get_course( array $input ) {
		$course_id = absint( $input['course_id'] ?? 0 );
		$request   = new \WP_REST_Request( 'GET' );
		$request->set_param( 'id', $course_id );

		$data = RestBridge::unwrap_call(
			static function () use ( $request ) {
				return ( new REST_Course() )->course_detail( $request );
			}
		);
		if ( is_wp_error( $data ) ) {
			return new \WP_Error( 'tutor_mcp_not_found', __( 'Course not found.', 'tutor' ) );
		}

		return $data;
	}

	/**
	 * Search courses.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array
	 */
	public function search_courses( array $input ): array {
		$request = new \WP_REST_Request( 'GET' );
		$request->set_param( 'paged', max( 1, absint( $input['page'] ?? 1 ) ) );

		foreach ( array( 'order', 'orderby', 'categories', 'tags' ) as $param ) {
			if ( ! empty( $input[ $param ] ) ) {
				$request->set_param( $param, sanitize_text_field( (string) $input[ $param ] ) );
			}
		}

		$data = RestBridge::unwrap_call(
			static function () use ( $request ) {
				return ( new REST_Course() )->course( $request );
			}
		);
		if ( is_wp_error( $data ) ) {
			return array(
				'posts'        => array(),
				'total_course' => 0,
				'total_page'   => 0,
			);
		}

		return $data;
	}

	/**
	 * Course contents tree.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function get_course_contents( array $input ) {
		$course_id = absint( $input['course_id'] ?? 0 );
		$request   = new \WP_REST_Request( 'GET' );
		$request->set_param( 'id', $course_id );

		$topics = RestBridge::unwrap_call(
			static function () use ( $request ) {
				return ( new REST_Course() )->course_contents( $request );
			}
		);
		if ( is_wp_error( $topics ) ) {
			return new \WP_Error( 'tutor_mcp_not_found', __( 'Course not found.', 'tutor' ) );
		}

		return array(
			'course_id' => $course_id,
			'topics'    => $topics,
		);
	}
}
