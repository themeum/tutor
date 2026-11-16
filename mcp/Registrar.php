<?php
/**
 * Tutor MCP server + Phase 1–3 abilities (minimal surface).
 *
 * @package Tutor\Mcp
 * @author  Themeum <support@themeum.com>
 * @link    https://themeum.com
 * @since   4.3.0
 */

namespace Tutor\Mcp;

use Tutor\Mcp\Abilities\CourseAbilities;
use Tutor\Mcp\Abilities\CurriculumAbilities;
use Tutor\Mcp\Abilities\HealthAbility;
use Tutor\Mcp\Abilities\SchemaResources;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers tutor-mcp and read-only core abilities.
 *
 * @since 4.3.0
 */
class Registrar {

	const OPTION               = 'tutor_mcp_enabled';
	const OPTION_READ_ONLY     = 'tutor_mcp_read_only';
	const OPTION_ENABLED_TOOLS = 'tutor_mcp_enabled_tools';
	const SERVER_ID            = 'tutor-mcp';
	const AGENT_USER           = 'tutor-mcp-agent';
	const ROLE                 = 'tutor_mcp_agent';
	const ADAPTER_PLUGIN_FILE  = 'mcp-adapter/mcp-adapter.php';
	const ADAPTER_GITHUB_URL   = 'https://github.com/WordPress/mcp-adapter';

	/**
	 * Tool ability IDs.
	 *
	 * @var string[]
	 */
	const TOOLS = array(
		'tutor-mcp/health-check',
		'tutor-mcp/get-course',
		'tutor-mcp/search-courses',
		'tutor-mcp/get-course-contents',
		'tutor-mcp/get-lesson',
		'tutor-mcp/get-quiz',
	);

	/**
	 * Resource ability IDs.
	 *
	 * @var string[]
	 */
	const RESOURCES = array(
		'tutor-mcp/resource-post-types',
		'tutor-mcp/resource-taxonomies',
		'tutor-mcp/resource-caps',
		'tutor-mcp/resource-addons',
		'tutor-mcp/resource-course',
	);

	/**
	 * Health abilities.
	 *
	 * @var HealthAbility
	 */
	private HealthAbility $health;

	/**
	 * Course abilities.
	 *
	 * @var CourseAbilities
	 */
	private CourseAbilities $courses;

	/**
	 * Curriculum abilities.
	 *
	 * @var CurriculumAbilities
	 */
	private CurriculumAbilities $curriculum;

	/**
	 * Schema resources.
	 *
	 * @var SchemaResources
	 */
	private SchemaResources $schema;

	/**
	 * Constructor.
	 *
	 * @since 4.3.0
	 */
	public function __construct() {
		$this->health     = new HealthAbility();
		$this->courses    = new CourseAbilities();
		$this->curriculum = new CurriculumAbilities();
		$this->schema     = new SchemaResources( $this->courses );
	}

	/**
	 * Wire hooks.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( 'wp_abilities_api_categories_init', array( $this, 'register_categories' ) );
		add_action( 'wp_abilities_api_init', array( $this, 'register_abilities' ) );
		add_action( 'mcp_adapter_init', array( $this, 'register_server' ) );

		/*
		 * Boot the official MCP Adapter ourselves. WooCommerce only starts it when its
		 * mcp_integration feature flag is on; Tutor MCP must not depend on that.
		 */
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			self::disable_stdio_idle_timeout();
			add_action( 'plugins_loaded', array( $this, 'boot_adapter' ), 20 );
		} else {
			add_action( 'rest_api_init', array( $this, 'boot_adapter' ), 5 );
		}
	}

	/**
	 * Keep STDIO MCP serve alive past PHP's default_socket_timeout (60s).
	 *
	 * The mcp-adapter StdioServerBridge uses fgets( STDIN ); after 60s idle PHP
	 * treats that as failure/EOF and the bridge exits, which breaks Cursor MCP.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	private static function disable_stdio_idle_timeout(): void {
		$argv = isset( $GLOBALS['argv'] ) && is_array( $GLOBALS['argv'] ) ? $GLOBALS['argv'] : array();
		if ( ! in_array( 'mcp-adapter', $argv, true ) || ! in_array( 'serve', $argv, true ) ) {
			return;
		}

		// phpcs:ignore WordPress.PHP.IniSet.Risky
		ini_set( 'default_socket_timeout', '-1' );

		if ( defined( 'STDIN' ) && is_resource( STDIN ) ) {
			// 1 year; -1 is not reliably supported by stream_set_timeout on all builds.
			stream_set_timeout( STDIN, YEAR_IN_SECONDS );
		}
	}

	/**
	 * Ensure McpAdapter::instance() runs so mcp_adapter_init fires.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public function boot_adapter(): void {
		if ( ! self::is_enabled() || ! class_exists( '\WP\MCP\Core\McpAdapter' ) ) {
			return;
		}

		\WP\MCP\Core\McpAdapter::instance();
	}

	/**
	 * Whether MCP is enabled.
	 *
	 * @since 4.3.0
	 *
	 * @return bool
	 */
	public static function is_enabled(): bool {
		return (bool) get_option( self::OPTION, false );
	}

	/**
	 * Whether site-wide read-only mode is on.
	 *
	 * Defaults on. Write/destructive tools (future phases) must gate on this.
	 *
	 * @since 4.3.0
	 *
	 * @return bool
	 */
	public static function is_read_only(): bool {
		$stored = get_option( self::OPTION_READ_ONLY, null );
		if ( null === $stored ) {
			return true;
		}

		return (bool) $stored;
	}

	/**
	 * Tool ability IDs currently enabled for MCP exposure.
	 *
	 * @since 4.3.0
	 *
	 * @return string[]
	 */
	public static function enabled_tools(): array {
		$stored = get_option( self::OPTION_ENABLED_TOOLS, null );
		if ( ! is_array( $stored ) ) {
			return self::TOOLS;
		}

		$allowed = array_values( array_intersect( self::TOOLS, array_map( 'strval', $stored ) ) );
		return $allowed;
	}

	/**
	 * Whether a tool ability is enabled for MCP.
	 *
	 * @since 4.3.0
	 *
	 * @param string $tool_id Ability ID.
	 * @return bool
	 */
	public static function is_tool_enabled( string $tool_id ): bool {
		return in_array( $tool_id, self::enabled_tools(), true );
	}

	/**
	 * Admin UI catalog of current Phase 1–3 tools.
	 *
	 * @since 4.3.0
	 *
	 * @return array<int, array{id:string,name:string,abilities:array<int,array{id:string,name:string,desc:string,tools:array<int,array{n:string,l:string,r:string}>}>}>
	 */
	public static function tool_catalog(): array {
		return array(
			array(
				'id'        => 'health',
				'name'      => __( 'Health', 'tutor' ),
				'abilities' => array(
					array(
						'id'    => 'health_check',
						'name'  => __( 'Site Health', 'tutor' ),
						'desc'  => __( 'WP + Tutor versions and addon flags. No secrets.', 'tutor' ),
						'tools' => array(
							array(
								'n' => 'tutor-mcp/health-check',
								'l' => __( 'Health check', 'tutor' ),
								'r' => 'read',
							),
						),
					),
				),
			),
			array(
				'id'        => 'courses',
				'name'      => __( 'Courses', 'tutor' ),
				'abilities' => array(
					array(
						'id'    => 'course_tools',
						'name'  => __( 'Course Tools', 'tutor' ),
						'desc'  => __( 'Fetch course details, search published courses, and read curriculum trees.', 'tutor' ),
						'tools' => array(
							array(
								'n' => 'tutor-mcp/get-course',
								'l' => __( 'Get course details', 'tutor' ),
								'r' => 'read',
							),
							array(
								'n' => 'tutor-mcp/search-courses',
								'l' => __( 'Search courses', 'tutor' ),
								'r' => 'read',
							),
							array(
								'n' => 'tutor-mcp/get-course-contents',
								'l' => __( 'Get course contents', 'tutor' ),
								'r' => 'read',
							),
						),
					),
				),
			),
			array(
				'id'        => 'curriculum',
				'name'      => __( 'Curriculum', 'tutor' ),
				'abilities' => array(
					array(
						'id'    => 'curriculum_tools',
						'name'  => __( 'Lesson & Quiz', 'tutor' ),
						'desc'  => __( 'Read published lessons and quizzes (quiz answers remain gated).', 'tutor' ),
						'tools' => array(
							array(
								'n' => 'tutor-mcp/get-lesson',
								'l' => __( 'Get lesson', 'tutor' ),
								'r' => 'read',
							),
							array(
								'n' => 'tutor-mcp/get-quiz',
								'l' => __( 'Get quiz', 'tutor' ),
								'r' => 'read',
							),
						),
					),
				),
			),
		);
	}

	/**
	 * STDIO client config for Cursor / Claude Desktop.
	 *
	 * @since 4.3.0
	 *
	 * @return array
	 */
	public static function stdio_config(): array {
		return array(
			'mcpServers' => array(
				'tutor-mcp' => array(
					'command' => 'wp',
					'args'    => array(
						'--path=' . untrailingslashit( ABSPATH ),
						'mcp-adapter',
						'serve',
						'--server=' . self::SERVER_ID,
						'--user=' . self::AGENT_USER,
					),
				),
			),
		);
	}

	/**
	 * HTTP proxy client config (WooCommerce-style remote connection).
	 *
	 * @since 4.3.0
	 *
	 * @return array
	 */
	public static function http_config(): array {
		return array(
			'mcpServers' => array(
				'tutor-mcp-http' => array(
					'command' => 'npx',
					'args'    => array( '-y', '@automattic/mcp-wordpress-remote@latest' ),
					'env'     => array(
						'WP_API_URL'      => self::endpoint(),
						'WP_API_USERNAME' => self::AGENT_USER,
						'WP_API_PASSWORD' => '${TUTOR_MCP_APP_PASSWORD}',
					),
				),
			),
		);
	}

	/**
	 * Whether the official WordPress MCP Adapter plugin is active.
	 *
	 * Do not use class_exists() alone — WooCommerce (and others) may vendor the
	 * same classes without the standalone plugin being installed.
	 *
	 * @since 4.3.0
	 *
	 * @return bool
	 */
	public static function has_adapter(): bool {
		return self::is_adapter_plugin_active();
	}

	/**
	 * Whether the MCP Adapter plugin is active.
	 *
	 * @since 4.3.0
	 *
	 * @return bool
	 */
	public static function is_adapter_plugin_active(): bool {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return is_plugin_active( self::ADAPTER_PLUGIN_FILE );
	}

	/**
	 * Whether the MCP Adapter plugin files are present (active or not).
	 *
	 * @since 4.3.0
	 *
	 * @return bool
	 */
	public static function is_adapter_plugin_installed(): bool {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugins = get_plugins();
		return isset( $plugins[ self::ADAPTER_PLUGIN_FILE ] );
	}

	/**
	 * Admin URL to install or activate the MCP Adapter plugin.
	 *
	 * @since 4.3.0
	 *
	 * @return string
	 */
	public static function adapter_plugin_action_url(): string {
		if ( self::is_adapter_plugin_installed() && ! self::is_adapter_plugin_active() ) {
			return wp_nonce_url(
				self_admin_url( 'plugins.php?action=activate&plugin=' . rawurlencode( self::ADAPTER_PLUGIN_FILE ) ),
				'activate-plugin_' . self::ADAPTER_PLUGIN_FILE
			);
		}

		return self_admin_url( 'plugin-install.php?s=mcp-adapter&tab=search&type=term' );
	}

	/**
	 * Abilities API present?
	 *
	 * @since 4.3.0
	 *
	 * @return bool
	 */
	public static function has_abilities_api(): bool {
		return function_exists( 'wp_register_ability' );
	}

	/**
	 * HTTP endpoint.
	 *
	 * @since 4.3.0
	 *
	 * @return string
	 */
	public static function endpoint(): string {
		return rest_url( 'mcp/' . self::SERVER_ID );
	}

	/**
	 * Register ability categories.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public function register_categories(): void {
		if ( ! self::is_enabled() || ! function_exists( 'wp_register_ability_category' ) ) {
			return;
		}

		foreach ( array(
			'tutor-mcp-health'     => __( 'Tutor MCP Health', 'tutor' ),
			'tutor-mcp-schema'     => __( 'Tutor MCP Schema', 'tutor' ),
			'tutor-mcp-courses'    => __( 'Tutor MCP Courses', 'tutor' ),
			'tutor-mcp-curriculum' => __( 'Tutor MCP Curriculum', 'tutor' ),
		) as $slug => $label ) {
			wp_register_ability_category(
				$slug,
				array(
					'label'       => $label,
					'description' => $label,
				)
			);
		}
	}

	/**
	 * Register Phase 3 abilities.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public function register_abilities(): void {
		if ( ! self::is_enabled() || ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		$this->health->register();
		$this->courses->register();
		$this->curriculum->register();
		$this->schema->register();
	}

	/**
	 * Register MCP Adapter server.
	 *
	 * @since 4.3.0
	 *
	 * @param object $adapter Adapter.
	 * @return void
	 */
	public function register_server( $adapter ): void {
		if ( ! self::is_enabled() || ! is_object( $adapter ) || ! method_exists( $adapter, 'create_server' ) ) {
			return;
		}
		if ( ! class_exists( '\WP\MCP\Transport\HttpTransport' ) ) {
			return;
		}

		$error = class_exists( '\WP\MCP\Infrastructure\ErrorHandling\ErrorLogMcpErrorHandler' )
			? \WP\MCP\Infrastructure\ErrorHandling\ErrorLogMcpErrorHandler::class
			: null;

		$tools = apply_filters( 'tutor_mcp_ability_ids', self::enabled_tools() );
		if ( ! is_array( $tools ) ) {
			$tools = self::enabled_tools();
		}
		$tools = array_values( array_intersect( self::TOOLS, array_map( 'strval', $tools ) ) );

		$adapter->create_server(
			self::SERVER_ID,
			'mcp',
			self::SERVER_ID,
			__( 'Tutor MCP Server', 'tutor' ),
			__( 'Tutor LMS MCP tools.', 'tutor' ),
			defined( 'TUTOR_VERSION' ) ? TUTOR_VERSION : '1.0.0',
			array( \WP\MCP\Transport\HttpTransport::class ),
			$error,
			null,
			$tools,
			self::RESOURCES,
			array(),
			static function (): bool {
				return is_user_logged_in();
			}
		);
	}
}
