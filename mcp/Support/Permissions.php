<?php
/**
 * MCP course permission checks.
 *
 * Mirrors Tutor plugin access: Course::check_access(), can_user_edit_course(),
 * instructor_can_publish_course / instructor_can_delete_course options.
 *
 * @package Tutor\Mcp
 * @author  Themeum <support@themeum.com>
 * @link    https://themeum.com
 * @since   4.3.0
 */

namespace Tutor\Mcp\Support;

use Tutor\Models\CourseModel;
use Tutor\Mcp\Registrar;
use TUTOR\User;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Course view/edit permission helpers for MCP abilities.
 *
 * @since 4.3.0
 */
class Permissions {

	/**
	 * Deny writes when Tools → MCP read-only is enabled.
	 *
	 * @since 4.3.0
	 *
	 * @return true|\WP_Error
	 */
	public static function allow_write() {
		if ( Registrar::is_read_only() ) {
			return new \WP_Error( 'tutor_mcp_read_only', __( 'MCP is in read-only mode.', 'tutor' ) );
		}

		return true;
	}

	/**
	 * Whether a user may connect to Tutor MCP (bool only for the adapter).
	 *
	 * Site admins always can. Instructors / co-instructors can when an admin
	 * enables Tools → MCP → Allow instructors. Individual tools still enforce
	 * each user's Tutor capabilities.
	 *
	 * @since 4.3.0
	 *
	 * @param int $user_id User ID (0 = current).
	 * @return bool
	 */
	public static function can_connect( int $user_id = 0 ): bool {
		$user_id = $user_id ? $user_id : get_current_user_id();
		if ( ! $user_id ) {
			return false;
		}

		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}

		if ( ! Registrar::is_instructor_access_enabled() ) {
			return false;
		}

		return (bool) User::is_instructor( $user_id, true );
	}

	/**
	 * Whether the current user is a Tutor admin (WP admin or manage_tutor).
	 *
	 * @since 4.3.0
	 *
	 * @return bool
	 */
	public static function is_tutor_admin(): bool {
		return User::is_admin() || current_user_can( 'manage_tutor' );
	}

	/**
	 * Create course access — same as Course::check_access() without a course_id.
	 *
	 * @since 4.3.0
	 *
	 * @return bool
	 */
	public static function can_create_course(): bool {
		return User::is_admin() || User::is_instructor();
	}

	/**
	 * Default post status for MCP course create (never publish).
	 *
	 * Matches Course create/update publish option behavior.
	 *
	 * @since 4.3.0
	 *
	 * @return string
	 */
	public static function default_course_status(): string {
		if ( self::is_tutor_admin() ) {
			return CourseModel::STATUS_DRAFT;
		}

		$can_publish = (bool) tutor_utils()->get_option( 'instructor_can_publish_course', false );
		return $can_publish ? CourseModel::STATUS_DRAFT : CourseModel::STATUS_PENDING;
	}

	/**
	 * Course manage/view access for MCP tools (instructor dashboard scope).
	 *
	 * Same ownership check as Course::check_access( $course_id ):
	 * admin or instructor/co-instructor of the course.
	 *
	 * @since 4.3.0
	 *
	 * @param int $course_id Course ID.
	 * @return bool
	 */
	public static function can_view_course( int $course_id ): bool {
		return self::can_edit_course( $course_id );
	}

	/**
	 * Course edit ownership — same as Course::check_access( $course_id ).
	 *
	 * @since 4.3.0
	 *
	 * @param int $course_id Course ID.
	 * @return bool
	 */
	public static function can_edit_course( int $course_id ): bool {
		if ( ! $course_id ) {
			return false;
		}

		if ( self::is_tutor_admin() ) {
			return true;
		}

		return (bool) tutor_utils()->can_user_edit_course( get_current_user_id(), $course_id );
	}

	/**
	 * Trash course — matches Course_List / course update trash rules.
	 *
	 * Admin always; instructors only when instructor_can_delete_course is on.
	 *
	 * @since 4.3.0
	 *
	 * @param int $course_id Course ID.
	 * @return bool
	 */
	public static function can_trash_course( int $course_id ): bool {
		if ( ! self::can_edit_course( $course_id ) ) {
			return false;
		}

		if ( self::is_tutor_admin() ) {
			return true;
		}

		return (bool) tutor_utils()->get_option( 'instructor_can_delete_course', false )
			&& current_user_can( 'edit_tutor_courses' );
	}

	/**
	 * Whether the current user can use instructor-level Tutor tools at all.
	 *
	 * Entry gate only — list handlers must still scope to owned courses.
	 *
	 * @since 4.3.0
	 *
	 * @return bool
	 */
	public static function can_manage_courses(): bool {
		return current_user_can( 'edit_tutor_courses' ) || self::is_tutor_admin();
	}

	/**
	 * Whether the current user can manage Tools → MCP settings.
	 *
	 * Admin-only: site administrators (manage_options). Instructors and other
	 * roles must not view or change MCP enablement, tools, or agent options.
	 *
	 * @since 4.3.0
	 *
	 * @return bool
	 */
	public static function can_manage_settings(): bool {
		return current_user_can( 'manage_options' );
	}
}
