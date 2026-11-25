<?php
/**
 * Tutor MCP student tools.
 *
 * @package Tutor\Mcp
 * @author  Themeum <support@themeum.com>
 * @link    https://themeum.com
 * @since   4.3.0
 */

namespace Tutor\Mcp\Abilities;

use Tutor\Models\CourseModel;
use Tutor\Models\EnrollmentModel;
use Tutor\Mcp\Registrar;
use Tutor\Mcp\Support\AbilityFactory;
use Tutor\Mcp\Support\Pagination;
use Tutor\Mcp\Support\Permissions;
use TUTOR\User;

defined( 'ABSPATH' ) || exit;

/**
 * Student tool registration and execute handlers.
 *
 * @since 4.3.0
 */
class StudentAbilities {

	/**
	 * Register student abilities.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public function register(): void {
		AbilityFactory::tool(
			'tutor-mcp/list-students',
			'tutor-mcp-students',
			__( 'List Students', 'tutor' ),
			__( 'Paginated list of enrolled students. Optional course_id filter.', 'tutor' ),
			array(
				'type'       => 'object',
				'properties' => array(
					'course_id' => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'search'    => array( 'type' => 'string' ),
					'page'      => array(
						'type'    => 'integer',
						'minimum' => 1,
						'default' => 1,
					),
					'per_page'  => array(
						'type'    => 'integer',
						'minimum' => 1,
						'maximum' => 50,
						'default' => 20,
					),
				),
			),
			static function ( array $input ): bool {
				if ( ! Permissions::can_manage_courses() ) {
					return false;
				}
				$course_id = absint( $input['course_id'] ?? 0 );
				if ( $course_id ) {
					return Permissions::can_edit_course( $course_id );
				}
				return true;
			},
			array( $this, 'list_students' )
		);

		AbilityFactory::tool(
			'tutor-mcp/get-student',
			'tutor-mcp-students',
			__( 'Get Student', 'tutor' ),
			__( 'Get student info and enrolled courses (instructors: only shared courses).', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'student_id' ),
				'properties' => array(
					'student_id' => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
				),
			),
			static function (): bool {
				return Permissions::can_manage_courses();
			},
			array( $this, 'get_student' )
		);

		AbilityFactory::tool(
			'tutor-mcp/get-course-progress',
			'tutor-mcp-students',
			__( 'Get Course Progress', 'tutor' ),
			__( 'Get a student\'s progress details for a course.', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'course_id', 'student_id' ),
				'properties' => array(
					'course_id'  => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'student_id' => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
				),
			),
			static function ( array $input ): bool {
				return Permissions::can_edit_course( absint( $input['course_id'] ?? 0 ) );
			},
			array( $this, 'get_course_progress' )
		);

		AbilityFactory::tool(
			'tutor-mcp/unenroll-student',
			'tutor-mcp-students',
			__( 'Unenroll Student', 'tutor' ),
			__( 'Unenroll a student from a course and clear progress. Same as Students_List admin delete — administrator only. Requires confirm=true.', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'course_id', 'student_id', 'confirm' ),
				'properties' => array(
					'course_id'  => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'student_id' => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'confirm'    => array(
						'type'    => 'boolean',
						'default' => false,
					),
				),
			),
			static function (): bool {
				if ( Registrar::is_read_only() ) {
					return false;
				}
				return User::is_admin();
			},
			array( $this, 'unenroll_student' ),
			AbilityFactory::DESTRUCTIVE_HINTS
		);
	}

	/**
	 * List students.
	 *
	 * Admin: all students (Students_List). Instructor: own course students
	 * via get_students_by_instructor (same as Pro report students).
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function list_students( array $input ) {
		$bounds    = Pagination::bounds( $input );
		$search    = sanitize_text_field( (string) ( $input['search'] ?? '' ) );
		$course_id = ! empty( $input['course_id'] ) ? absint( $input['course_id'] ) : 0;

		if ( $course_id && ! Permissions::can_edit_course( $course_id ) ) {
			return new \WP_Error( 'tutor_mcp_forbidden', __( 'Access Denied', 'tutor' ) );
		}

		$show_pii = User::is_admin();
		$rows     = array();

		if ( User::is_admin() ) {
			$students = tutor_utils()->get_students(
				$bounds['offset'],
				$bounds['per_page'],
				$search,
				$course_id ? (string) $course_id : ''
			);
		} else {
			$result   = tutor_utils()->get_students_by_instructor(
				get_current_user_id(),
				$bounds['offset'],
				$bounds['per_page'],
				$search,
				$course_id ? (string) $course_id : '',
				'',
				'',
				'DESC',
				array( 'publish', 'private', 'draft', 'pending' )
			);
			$students = is_array( $result ) ? ( $result['students'] ?? array() ) : array();
		}

		if ( is_array( $students ) ) {
			foreach ( $students as $student ) {
				$row = array(
					'ID'           => (int) $student->ID,
					'display_name' => $student->display_name,
				);
				if ( $show_pii ) {
					$row['user_email'] = $student->user_email;
				}
				$rows[] = $row;
			}
		}

		return array(
			'students' => $rows,
			'page'     => $bounds['page'],
			'per_page' => $bounds['per_page'],
		);
	}

	/**
	 * Get student details.
	 *
	 * Instructors only see enrollments in courses they instruct
	 * (get_courses_by_student_instructor_id).
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function get_student( array $input ) {
		$student_id = absint( $input['student_id'] ?? 0 );
		$user       = get_userdata( $student_id );
		if ( ! $user ) {
			return new \WP_Error( 'tutor_mcp_not_found', __( 'Student not found.', 'tutor' ) );
		}

		$show_pii = User::is_admin();
		$data     = array(
			'ID'           => (int) $user->ID,
			'display_name' => $user->display_name,
			'registered'   => $user->user_registered,
		);
		if ( $show_pii ) {
			$data['user_email'] = $user->user_email;
		}

		$courses = array();

		if ( User::is_admin() ) {
			$enrolled = EnrollmentModel::get_enrolled_data( $student_id );
			if ( ! empty( $enrolled ) ) {
				foreach ( $enrolled as $enrollment ) {
					$courses[] = array(
						'course_id'   => (int) $enrollment->post_parent,
						'status'      => $enrollment->post_status,
						'enrolled_at' => $enrollment->post_date,
					);
				}
			}
		} else {
			$shared = tutor_utils()->get_courses_by_student_instructor_id(
				$student_id,
				get_current_user_id(),
				array( 'publish', 'private', 'draft', 'pending' )
			);
			if ( empty( $shared ) ) {
				return new \WP_Error( 'tutor_mcp_forbidden', __( 'Access Denied', 'tutor' ) );
			}
			foreach ( $shared as $course ) {
				$courses[] = array(
					'course_id'   => (int) $course->ID,
					'status'      => $course->post_status,
					'enrolled_at' => null,
				);
			}
		}

		$data['enrolled_courses'] = $courses;

		return $data;
	}

	/**
	 * Get course progress for a student.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function get_course_progress( array $input ) {
		$course_id  = absint( $input['course_id'] ?? 0 );
		$student_id = absint( $input['student_id'] ?? 0 );

		if ( tutor()->course_post_type !== get_post_type( $course_id ) ) {
			return new \WP_Error( 'tutor_mcp_not_found', __( 'Course not found.', 'tutor' ) );
		}

		$completion = tutor_utils()->get_course_completed_percent( $course_id, $student_id, true );
		$details    = CourseModel::get_course_progress_details( $course_id, $student_id );

		return array(
			'course_id'            => $course_id,
			'student_id'           => $student_id,
			'completion_percent'   => is_array( $completion ) ? ( $completion['percentage'] ?? 0 ) : $completion,
			'completion_stats'     => is_array( $completion ) ? $completion : array(),
			'topics'               => $details,
		);
	}

	/**
	 * Unenroll a student from a course.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function unenroll_student( array $input ) {
		$deny = Permissions::allow_write();
		if ( is_wp_error( $deny ) ) {
			return $deny;
		}

		if ( empty( $input['confirm'] ) ) {
			return new \WP_Error( 'tutor_mcp_confirm_required', __( 'Unenrolling requires confirm=true.', 'tutor' ) );
		}

		$course_id  = absint( $input['course_id'] ?? 0 );
		$student_id = absint( $input['student_id'] ?? 0 );

		if ( tutor()->course_post_type !== get_post_type( $course_id ) ) {
			return new \WP_Error( 'tutor_mcp_not_found', __( 'Course not found.', 'tutor' ) );
		}
		if ( ! get_userdata( $student_id ) ) {
			return new \WP_Error( 'tutor_mcp_not_found', __( 'Student not found.', 'tutor' ) );
		}

		EnrollmentModel::delete_enrollment_record( $student_id, $course_id );
		tutor_utils()->delete_course_progress( $course_id, $student_id );

		return array(
			'course_id'  => $course_id,
			'student_id' => $student_id,
			'unenrolled' => true,
		);
	}
}
