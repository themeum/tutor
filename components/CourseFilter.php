<?php
/**
 * CourseFilter Component Class.
 *
 * Responsible for rendering a course filtering dropdown
 * with a search box to filter the list.
 *
 * @package Tutor\Components
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Components;

use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Models\CourseModel;

defined( 'ABSPATH' ) || exit;

/**
 * Class CourseFilter
 *
 * Example Usage:
 * ```
 * CourseFilter::make()
 *     ->courses( $courses )
 *     ->count( $total_announcements )
 *     ->render();
 * ```
 *
 * @since 4.0.0
 */
class CourseFilter extends DropdownFilter {

	/**
	 * Constructor.
	 *
	 * Set default values for course filter.
	 */
	public function __construct() {
		$this->query_param( 'course-id' );
		$this->variant( Variant::PRIMARY_SOFT );
		$this->size( Size::X_SMALL );
		$this->popover_size( Size::MEDIUM );
		$this->placeholder( __( 'Search Course', 'tutor' ) );
		$this->search( true );
	}

	/**
	 * Set courses list
	 *
	 * @param array|null $courses List of courses.
	 *
	 * @return self
	 */
	public function courses( $courses = null ): self {
		$options = self::get_course_filter_options( $courses );
		$this->options( $options );
		return $this;
	}

	/**
	 * Get component content.
	 *
	 * @return string
	 */
	public function get(): string {
		if ( empty( $this->options ) ) {
			$this->courses();
		}

		return parent::get();
	}

	/**
	 * Get course filter options for DropdownFilter component.
	 *
	 * @since 4.0.0
	 *
	 * @param array|null $courses Optional. Array of course objects.
	 *                   If not provided, courses will be fetched based on user permissions.
	 *
	 * @return array
	 */
	public static function get_course_filter_options( $courses = null ) {
		$course_options = array(
			array(
				'label' => __( 'All Courses', 'tutor' ),
				'value' => '',
			),
		);

		if ( null === $courses ) {
			$courses = current_user_can( 'administrator' ) ? CourseModel::get_courses() : CourseModel::get_courses_by_instructor();
		}

		if ( ! empty( $courses ) ) {
			foreach ( $courses as $course ) {
				$course_options[] = array(
					'label' => $course->post_title,
					'value' => $course->ID,
				);
			}
		}

		return $course_options;
	}
}
