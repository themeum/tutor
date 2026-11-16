<?php //phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase
/**
 * MCP bootstrap (Tools → MCP).
 *
 * @package Tutor\API
 * @author  Themeum <support@themeum.com>
 * @link    https://themeum.com
 * @since   4.3.0
 */

namespace TUTOR;

use Tutor\Core\Input;
use Tutor\Mcp\Agent;
use Tutor\Mcp\Registrar;
use Tutor\Mcp\Support\Permissions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Thin MCP entry: hooks + Tools form save.
 *
 * @since 4.3.0
 */
class Mcp {

	/**
	 * Constructor.
	 *
	 * @since 4.3.0
	 */
	public function __construct() {
		( new Registrar() )->hooks();
		add_action( 'admin_init', array( $this, 'save_settings' ) );
		add_action( 'admin_init', array( $this, 'guard_tools_page' ) );
		add_action( 'init', array( $this, 'maybe_bootstrap_role' ), 5 );
		// Outermost tools filter, late — keep MCP hidden even if other filters re-add it.
		add_filter( 'tutor/tools/extend/attr', array( $this, 'filter_tools_pages' ), 999 );
		add_filter( 'pre_update_option', array( $this, 'protect_option_update' ), 10, 3 );
	}

	/**
	 * Option keys that only admins may change.
	 *
	 * @since 4.3.0
	 *
	 * @return string[]
	 */
	private static function protected_options(): array {
		return array(
			Registrar::OPTION,
			Registrar::OPTION_READ_ONLY,
			Registrar::OPTION_ENABLED_TOOLS,
		);
	}

	/**
	 * Create agent role when MCP is on.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public function maybe_bootstrap_role() {
		if ( Registrar::is_enabled() ) {
			Agent::ensure_agent_user();
		}
	}

	/**
	 * Hide the MCP tools tab from non-admins.
	 *
	 * @since 4.3.0
	 *
	 * @param array $pages Tools pages.
	 * @return array
	 */
	public function filter_tools_pages( $pages ) {
		if ( ! is_array( $pages ) ) {
			return $pages;
		}

		if ( ! Permissions::can_manage_settings() ) {
			unset( $pages['tutor_mcp'] );
		}

		return $pages;
	}

	/**
	 * Block direct access to Tools → MCP for non-admins.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public function guard_tools_page() {
		if ( ! is_admin() ) {
			return;
		}

		$page     = Input::get( 'page', '' );
		$sub_page = Input::get( 'sub_page', '' );

		if ( 'tutor-tools' !== $page || 'tutor_mcp' !== $sub_page ) {
			return;
		}

		if ( Permissions::can_manage_settings() ) {
			return;
		}

		wp_die( esc_html__( 'You do not have permission to access this page.', 'tutor' ), 403 );
	}

	/**
	 * Reject MCP option writes from anyone except administrators.
	 *
	 * Allows WP-CLI and unauthenticated bootstrap contexts (no current user)
	 * so installs/tests are not blocked; logged-in non-admins are always denied.
	 *
	 * @since 4.3.0
	 *
	 * @param mixed  $value     New value.
	 * @param string $option    Option name.
	 * @param mixed  $old_value Previous value.
	 * @return mixed
	 */
	public function protect_option_update( $value, $option, $old_value ) {
		if ( ! in_array( $option, self::protected_options(), true ) ) {
			return $value;
		}

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return $value;
		}

		if ( ! is_user_logged_in() ) {
			return $value;
		}

		if ( Permissions::can_manage_settings() ) {
			return $value;
		}

		return $old_value;
	}

	/**
	 * Save Tools → MCP settings.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public function save_settings() {
		if ( 'save_settings' !== Input::post( 'tutor_mcp_action', '' ) ) {
			return;
		}

		if ( ! Permissions::can_manage_settings() ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'tutor' ), 403 );
		}

		check_admin_referer( 'tutor_mcp_tools_save' );

		$enabled   = (bool) Input::post( 'tutor_mcp_enabled', false, Input::TYPE_BOOL );
		$read_only = (bool) Input::post( 'tutor_mcp_read_only', false, Input::TYPE_BOOL );
		$tools_raw = Input::post( 'tutor_mcp_enabled_tools', array(), Input::TYPE_ARRAY );
		if ( ! is_array( $tools_raw ) ) {
			$tools_raw = array();
		}

		// MCP requires the external WordPress MCP Adapter plugin.
		if ( $enabled && ! Registrar::has_adapter() ) {
			$enabled = false;
		}

		$tools = array_values(
			array_intersect(
				Registrar::TOOLS,
				array_map( 'sanitize_text_field', $tools_raw )
			)
		);

		update_option( Registrar::OPTION, $enabled ? 1 : 0, false );
		update_option( Registrar::OPTION_READ_ONLY, $read_only ? 1 : 0, false );
		update_option( Registrar::OPTION_ENABLED_TOOLS, $tools, false );

		if ( $enabled ) {
			Agent::ensure_agent_user();
		}

		$redirect = array(
			'page'     => 'tutor-tools',
			'sub_page' => 'tutor_mcp',
			'updated'  => '1',
		);

		$mcp_view = Input::post( 'tutor_mcp_view', '' );
		if ( 'abilities' === $mcp_view ) {
			$redirect['mcp_view'] = 'abilities';
		}

		wp_safe_redirect( add_query_arg( $redirect, admin_url( 'admin.php' ) ) );
		exit;
	}
}
