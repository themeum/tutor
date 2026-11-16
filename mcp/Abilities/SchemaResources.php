<?php
/**
 * Tutor MCP schema resources.
 *
 * @package Tutor\Mcp
 * @author  Themeum <support@themeum.com>
 * @link    https://themeum.com
 * @since   4.3.0
 */

namespace Tutor\Mcp\Abilities;

use Tutor\Models\CourseModel;
use Tutor\Mcp\Support\AbilityFactory;
use Tutor\Mcp\Support\Permissions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Schema resource registration and execute handlers.
 *
 * @since 4.3.0
 */
class SchemaResources {

	/**
	 * Course abilities (for course resource reuse).
	 *
	 * @var CourseAbilities
	 */
	private CourseAbilities $courses;

	/**
	 * Constructor.
	 *
	 * @since 4.3.0
	 *
	 * @param CourseAbilities $courses Course abilities.
	 */
	public function __construct( CourseAbilities $courses ) {
		$this->courses = $courses;
	}

	/**
	 * Register schema resources.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public function register(): void {
		AbilityFactory::resource(
			'tutor-mcp/resource-post-types',
			'tutor://schema/post-types',
			__( 'Tutor Post Types', 'tutor' ),
			array( $this, 'post_types' )
		);
		AbilityFactory::resource(
			'tutor-mcp/resource-taxonomies',
			'tutor://schema/taxonomies',
			__( 'Tutor Taxonomies', 'tutor' ),
			array( $this, 'taxonomies' )
		);
		AbilityFactory::resource(
			'tutor-mcp/resource-caps',
			'tutor://schema/capabilities',
			__( 'Tutor MCP Capabilities', 'tutor' ),
			array( $this, 'caps' ),
			static function (): bool {
				return is_user_logged_in();
			}
		);
		AbilityFactory::resource(
			'tutor-mcp/resource-addons',
			'tutor://schema/addons',
			__( 'Tutor Addons', 'tutor' ),
			array( $this, 'addons' )
		);
		AbilityFactory::resource(
			'tutor-mcp/resource-course',
			'tutor://course/{id}',
			__( 'Course Resource', 'tutor' ),
			array( $this->courses, 'get_course' ),
			static function ( array $input ): bool {
				return Permissions::can_view_course( absint( $input['course_id'] ?? 0 ) );
			},
			array(
				'type'       => 'object',
				'required'   => array( 'course_id' ),
				'properties' => array(
					'course_id' => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
				),
			)
		);
	}

	/**
	 * Post types resource.
	 *
	 * @since 4.3.0
	 *
	 * @return array
	 */
	public function post_types(): array {
		return array(
			'course'     => tutor()->course_post_type,
			'lesson'     => tutor()->lesson_post_type,
			'quiz'       => tutor()->quiz_post_type,
			'topic'      => tutor()->topics_post_type,
			'assignment' => tutor()->assignment_post_type,
			'enrollment' => tutor()->enrollment_post_type,
			'bundle'     => tutor()->bundle_post_type,
		);
	}

	/**
	 * Taxonomies resource.
	 *
	 * @since 4.3.0
	 *
	 * @return array
	 */
	public function taxonomies(): array {
		return array(
			'course_category' => CourseModel::COURSE_CATEGORY,
			'course_tag'      => CourseModel::COURSE_TAG,
		);
	}

	/**
	 * Caps resource.
	 *
	 * @since 4.3.0
	 *
	 * @return array
	 */
	public function caps(): array {
		$caps = array();
		foreach ( array( 'manage_tutor', 'edit_tutor_courses', 'publish_tutor_courses', 'edit_tutor_lessons', 'edit_tutor_quizzes' ) as $cap ) {
			$caps[ $cap ] = current_user_can( $cap );
		}
		return array(
			'user_id' => get_current_user_id(),
			'roles'   => wp_get_current_user()->roles,
			'caps'    => $caps,
		);
	}

	/**
	 * Addons resource.
	 *
	 * @since 4.3.0
	 *
	 * @return array
	 */
	public function addons(): array {
		return array(
			'has_pro'        => (bool) tutor()->has_pro,
			'enabled_addons' => HealthAbility::enabled_addons(),
		);
	}
}
