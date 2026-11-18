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
	 * User meta / capability key for MCP allowlist.
	 *
	 * @since 4.3.0
	 *
	 * @var string
	 */
	const META_ALLOWED = 'tutor_mcp_allowed';

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
	 * Admins always; others need tutor_mcp_allowed meta/cap.
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
		return (bool) get_user_meta( $user_id, self::META_ALLOWED, true ) || user_can( $user_id, self::META_ALLOWED );
	}

	/**
	 * Allow or revoke MCP access for a user.
	 *
	 * @since 4.3.0
	 *
	 * @param int  $user_id User ID.
	 * @param bool $allow   True to allow, false to revoke.
	 * @return void
	 */
	public static function set_allowed( int $user_id, bool $allow ): void {
		$user = get_userdata( $user_id );
		if ( ! $user || user_can( $user_id, 'manage_options' ) ) {
			return;
		}
		if ( $allow ) {
			update_user_meta( $user_id, self::META_ALLOWED, 1 );
			$user->add_cap( self::META_ALLOWED );
			return;
		}
		delete_user_meta( $user_id, self::META_ALLOWED );
		$user->remove_cap( self::META_ALLOWED );
	}

	/**
	 * Default post status for MCP course create (never publish).
	 *
	 * @since 4.3.0
	 *
	 * @return string
	 */
	public static function default_course_status(): string {
		if ( User::is_admin() || current_user_can( 'manage_tutor' ) ) {
			return CourseModel::STATUS_DRAFT;
		}

		$can_publish = (bool) tutor_utils()->get_option( 'instructor_can_publish_course', false );
		return $can_publish ? CourseModel::STATUS_DRAFT : CourseModel::STATUS_PENDING;
	}

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
