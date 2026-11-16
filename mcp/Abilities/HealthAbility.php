<?php
/**
 * Tutor MCP health-check ability.
 *
 * @package Tutor\Mcp
 * @author  Themeum <support@themeum.com>
 * @link    https://themeum.com
 * @since   4.3.0
 */

namespace Tutor\Mcp\Abilities;

use Tutor\Mcp\Registrar;
use Tutor\Mcp\Support\AbilityFactory;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Health tool registration and execute.
 *
 * @since 4.3.0
 */
class HealthAbility {

	/**
	 * Register health abilities.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public function register(): void {
		AbilityFactory::tool(
			'tutor-mcp/health-check',
			'tutor-mcp-health',
			__( 'Tutor Health Check', 'tutor' ),
			__( 'WP + Tutor versions and addon flags. No secrets.', 'tutor' ),
			array(
				'type'       => 'object',
				'properties' => AbilityFactory::empty_object_schema(),
			),
			static function (): bool {
				return is_user_logged_in();
			},
			array( $this, 'execute' )
		);
	}

	/**
	 * Health check execute.
	 *
	 * @since 4.3.0
	 *
	 * @return array
	 */
	public function execute(): array {
		global $wp_version;

		return array(
			'wordpress_version' => (string) $wp_version,
			'tutor_version'     => defined( 'TUTOR_VERSION' ) ? TUTOR_VERSION : '',
			'tutor_pro'         => (bool) tutor()->has_pro,
			'tutor_pro_version' => defined( 'TUTOR_PRO_VERSION' ) ? TUTOR_PRO_VERSION : null,
			'abilities_api'     => Registrar::has_abilities_api(),
			'mcp_adapter'       => Registrar::has_adapter(),
			'enabled_addons'    => self::enabled_addons(),
		);
	}

	/**
	 * Enabled addon basenames from tutor_addons_option.
	 *
	 * @since 4.3.0
	 *
	 * @return string[]
	 */
	public static function enabled_addons(): array {
		$addons = array();
		$option = get_option( 'tutor_addons_option', array() );
		if ( ! is_array( $option ) ) {
			return array();
		}

		foreach ( $option as $basename => $cfg ) {
			if ( ! empty( $cfg['is_enable'] ) ) {
				$addons[] = sanitize_text_field( (string) $basename );
			}
		}

		return array_values( array_unique( $addons ) );
	}
}
