<?php
/**
 * MCP permission helpers.
 *
 * Prefer calling Tutor helpers directly (User::is_admin(), can_user_edit_course(),
 * current_user_can()). This class only holds MCP-specific gates and a few
 * Course::check_access / Course_List equivalents used repeatedly.
 *
 * @package Tutor\Mcp
 * @author  Themeum <support@themeum.com>
 * @link    https://themeum.com
 * @since   4.3.0
 */

namespace Tutor\Mcp\Support;

use Tutor\Mcp\Registrar;
use TUTOR\User;

defined( 'ABSPATH' ) || exit;

/**
 * MCP permission helpers.
 *
 * @since 4.3.0
 */
class Permissions {

	/**
	 * Single write gate for Tools → MCP read-only mode.
	 *
	 * Call from execute handlers only. Do not also check is_read_only() in
	 * permission callbacks — those return bool and hide this WP_Error message.
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

		if ( User::is_admin( $user_id ) ) {
			return true;
		}

		if ( ! Registrar::is_instructor_access_enabled() ) {
			return false;
		}

		return (bool) User::is_instructor( $user_id, true );
	}

	/**
	 * Course edit ownership — same as Course::check_access( $course_id ).
	 *
	 * Rejects empty course_id so admins do not pass on missing IDs
	 * (can_user_edit_course short-circuits true for admins).
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

		return (bool) tutor_utils()->can_user_edit_course( get_current_user_id(), $course_id );
	}

	/**
	 * Trash course — matches Course_List trash rules.
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

		if ( User::is_admin() ) {
			return true;
		}

		return (bool) tutor_utils()->get_option( 'instructor_can_delete_course', false )
			&& current_user_can( 'edit_tutor_courses' );
	}

	/**
	 * Instructor-level entry gate — same as Course::check_access() without a course_id.
	 *
	 * List handlers must still scope to owned courses.
	 *
	 * @since 4.3.0
	 *
	 * @return bool
	 */
	public static function can_manage_courses(): bool {
		return User::is_admin() || User::is_instructor();
	}
}
