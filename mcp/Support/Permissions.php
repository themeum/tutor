<?php
/**
 * MCP course permission checks.
 *
 * @package Tutor\Mcp
 * @author  Themeum <support@themeum.com>
 * @link    https://themeum.com
 * @since   4.3.0
 */

namespace Tutor\Mcp\Support;

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
	 * Course view permission (admin/manage_tutor or can_user_edit_course).
	 *
	 * @since 4.3.0
	 *
	 * @param int $course_id Course ID.
	 * @return bool
	 */
	public static function can_view_course( int $course_id ): bool {
		if ( ! $course_id ) {
			return false;
		}
		if ( ! current_user_can( 'edit_tutor_courses' ) && ! current_user_can( 'manage_tutor' ) && ! User::is_admin() ) {
			return false;
		}
		return self::can_edit_course( $course_id ) || User::is_admin() || current_user_can( 'manage_tutor' );
	}

	/**
	 * Course edit ownership.
	 *
	 * @since 4.3.0
	 *
	 * @param int $course_id Course ID.
	 * @return bool
	 */
	public static function can_edit_course( int $course_id ): bool {
		return User::is_admin()
			|| current_user_can( 'manage_tutor' )
			|| (bool) tutor_utils()->can_user_edit_course( get_current_user_id(), $course_id );
	}

	/**
	 * Whether the current user can manage Tutor courses broadly.
	 *
	 * @since 4.3.0
	 *
	 * @return bool
	 */
	public static function can_manage_courses(): bool {
		return current_user_can( 'edit_tutor_courses' ) || current_user_can( 'manage_tutor' ) || User::is_admin();
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
