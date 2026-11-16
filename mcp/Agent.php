<?php
/**
 * Tutor MCP agent role and user provisioning.
 *
 * @package Tutor\Mcp
 * @author  Themeum <support@themeum.com>
 * @link    https://themeum.com
 * @since   4.3.0
 */

namespace Tutor\Mcp;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Least-privilege agent identity for STDIO/HTTP MCP.
 *
 * @since 4.3.0
 */
class Agent {

	/**
	 * Ensure least-privilege agent role exists.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public static function ensure_role(): void {
		$caps = array(
			'read'                 => true,
			'upload_files'         => true,
			'edit_posts'           => true,
			'edit_published_posts' => true,
			'edit_tutor_course'    => true,
			'read_tutor_course'    => true,
			'edit_tutor_courses'   => true,
			'edit_tutor_lesson'    => true,
			'read_tutor_lesson'    => true,
			'edit_tutor_lessons'   => true,
			'edit_tutor_quiz'      => true,
			'read_tutor_quiz'      => true,
			'edit_tutor_quizzes'   => true,
		);

		$role = get_role( Registrar::ROLE );
		if ( ! $role ) {
			add_role( Registrar::ROLE, __( 'Tutor MCP Agent', 'tutor' ), $caps );
			return;
		}

		foreach ( $caps as $cap => $grant ) {
			if ( $grant ) {
				$role->add_cap( $cap );
			}
		}
	}

	/**
	 * Ensure the dedicated STDIO/HTTP agent user exists.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public static function ensure_agent_user(): void {
		self::ensure_role();

		$user = get_user_by( 'login', Registrar::AGENT_USER );
		if ( $user instanceof \WP_User ) {
			if ( ! in_array( Registrar::ROLE, (array) $user->roles, true ) ) {
				$user->set_role( Registrar::ROLE );
			}
			return;
		}

		$host = wp_parse_url( home_url(), PHP_URL_HOST );
		if ( ! is_string( $host ) || '' === $host ) {
			$host = 'localhost';
		}

		wp_insert_user(
			array(
				'user_login'   => Registrar::AGENT_USER,
				'user_pass'    => wp_generate_password( 32, true, true ),
				'user_email'   => Registrar::AGENT_USER . '@' . $host,
				'display_name' => __( 'Tutor MCP Agent', 'tutor' ),
				'role'         => Registrar::ROLE,
			)
		);
	}
}
