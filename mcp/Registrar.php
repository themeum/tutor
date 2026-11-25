<?php
/**
 * Tutor MCP server + Free (core) feature abilities.
 *
 * Pro-only tools (assignments, enrollments) register from Tutor Pro.
 *
 * @package Tutor\Mcp
 * @author  Themeum <support@themeum.com>
 * @link    https://themeum.com
 * @since   4.3.0
 */

namespace Tutor\Mcp;

use Tutor\Mcp\Abilities\CommerceAbilities;
use Tutor\Mcp\Abilities\CourseAbilities;
use Tutor\Mcp\Abilities\CurriculumAbilities;
use Tutor\Mcp\Abilities\EngagementAbilities;
use Tutor\Mcp\Abilities\FinanceAbilities;
use Tutor\Mcp\Abilities\HealthAbility;
use Tutor\Mcp\Abilities\QuizAttemptAbilities;
use Tutor\Mcp\Abilities\SchemaResources;
use Tutor\Mcp\Abilities\StudentAbilities;
use Tutor\Mcp\Support\Permissions;
use WP\MCP\Core\McpAdapter;
use WP\MCP\Infrastructure\ErrorHandling\ErrorLogMcpErrorHandler;
use WP\MCP\Transport\HttpTransport;

defined( 'ABSPATH' ) || exit;

/**
 * Registers tutor-mcp and Free plugin feature abilities (read + write).
 *
 * @since 4.3.0
 */
class Registrar {

	const OPTION                   = 'tutor_mcp_enabled';
	const OPTION_READ_ONLY         = 'tutor_mcp_read_only';
	const OPTION_ENABLED_TOOLS     = 'tutor_mcp_enabled_tools';
	const OPTION_INSTRUCTOR_ACCESS = 'tutor_mcp_instructor_access';
	const SERVER_ID                = 'tutor-mcp';
	const AGENT_USER               = 'tutor-mcp-agent';
	const ROLE                     = 'tutor_mcp_agent';
	const ADAPTER_PLUGIN_FILE      = 'mcp-adapter/mcp-adapter.php';
	const ADAPTER_GITHUB_URL       = 'https://github.com/WordPress/mcp-adapter';

	/**
	 * Tool ability IDs.
	 *
	 * @var string[]
	 */
	const TOOLS = array(
		'tutor-mcp/health-check',
		// Courses.
		'tutor-mcp/get-course',
		'tutor-mcp/search-courses',
		'tutor-mcp/get-course-contents',
		'tutor-mcp/create-course',
		'tutor-mcp/update-course',
		'tutor-mcp/set-course-status',
		'tutor-mcp/trash-course',
		// Curriculum / lessons / quizzes (reorder intentionally omitted).
		'tutor-mcp/create-topic',
		'tutor-mcp/update-topic',
		'tutor-mcp/delete-topic',
		'tutor-mcp/get-lesson',
		'tutor-mcp/create-lesson',
		'tutor-mcp/update-lesson',
		'tutor-mcp/attach-lesson-video',
		'tutor-mcp/delete-lesson',
		'tutor-mcp/list-quizzes',
		'tutor-mcp/get-quiz',
		'tutor-mcp/create-quiz',
		'tutor-mcp/update-quiz',
		'tutor-mcp/add-quiz-question',
		'tutor-mcp/update-quiz-question',
		'tutor-mcp/delete-quiz-question',
		'tutor-mcp/delete-quiz',
		// Quiz attempts.
		'tutor-mcp/list-quiz-attempts',
		'tutor-mcp/get-quiz-attempt',
		'tutor-mcp/review-quiz-attempt',
		'tutor-mcp/delete-quiz-attempt',
		// Students / progress.
		'tutor-mcp/list-students',
		'tutor-mcp/get-student',
		'tutor-mcp/get-course-progress',
		'tutor-mcp/unenroll-student',
		// Finance reports.
		'tutor-mcp/get-earnings',
		'tutor-mcp/list-withdrawals',
		// Engagement.
		'tutor-mcp/list-announcements',
		'tutor-mcp/create-announcement',
		'tutor-mcp/delete-announcement',
		'tutor-mcp/list-questions',
		'tutor-mcp/reply-to-question',
		'tutor-mcp/set-question-flags',
		'tutor-mcp/list-reviews',
		// Native commerce.
		'tutor-mcp/list-orders',
		'tutor-mcp/get-order',
		'tutor-mcp/mark-order-paid',
		'tutor-mcp/list-coupons',
		'tutor-mcp/create-coupon',
		'tutor-mcp/update-coupon',
		'tutor-mcp/expire-coupon',
		'tutor-mcp/get-course-price',
		'tutor-mcp/set-course-price',
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
	 * Quiz attempt abilities.
	 *
	 * @var QuizAttemptAbilities
	 */
	private QuizAttemptAbilities $quiz_attempts;

	/**
	 * Student abilities.
	 *
	 * @var StudentAbilities
	 */
	private StudentAbilities $students;

	/**
	 * Finance abilities.
	 *
	 * @var FinanceAbilities
	 */
	private FinanceAbilities $finance;

	/**
	 * Engagement abilities.
	 *
	 * @var EngagementAbilities
	 */
	private EngagementAbilities $engagement;

	/**
	 * Commerce abilities.
	 *
	 * @var CommerceAbilities
	 */
	private CommerceAbilities $commerce;

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
		$this->health        = new HealthAbility();
		$this->courses       = new CourseAbilities();
		$this->curriculum    = new CurriculumAbilities();
		$this->quiz_attempts = new QuizAttemptAbilities();
		$this->students      = new StudentAbilities();
		$this->finance       = new FinanceAbilities();
		$this->engagement    = new EngagementAbilities();
		$this->commerce      = new CommerceAbilities();
		$this->schema        = new SchemaResources( $this->courses );
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
		if ( ! self::is_enabled() || ! class_exists( McpAdapter::class ) ) {
			return;
		}

		McpAdapter::instance();
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
	 * Whether instructors / co-instructors may connect to Tutor MCP.
	 *
	 * Off by default — only site admins can connect until an admin enables this.
	 *
	 * @since 4.3.0
	 *
	 * @return bool
	 */
	public static function is_instructor_access_enabled(): bool {
		return (bool) get_option( self::OPTION_INSTRUCTOR_ACCESS, false );
	}

	/**
	 * Canonical tool ability IDs (core + Pro via filter).
	 *
	 * @since 4.3.0
	 *
	 * @return string[]
	 */
	public static function known_tools(): array {
		$tools = apply_filters( 'tutor_mcp_known_tools', self::TOOLS );
		if ( ! is_array( $tools ) ) {
			$tools = self::TOOLS;
		}

		$normalized = array();
		foreach ( $tools as $tool_id ) {
			$tool_id = sanitize_text_field( (string) $tool_id );
			if ( '' === $tool_id ) {
				continue;
			}
			$normalized[] = $tool_id;
		}

		return array_values( array_unique( $normalized ) );
	}

	/**
	 * Resource ability IDs (core + Pro via filter).
	 *
	 * @since 4.3.0
	 *
	 * @return string[]
	 */
	public static function known_resources(): array {
		$resources = apply_filters( 'tutor_mcp_resource_ids', self::RESOURCES );
		if ( ! is_array( $resources ) ) {
			$resources = self::RESOURCES;
		}

		$normalized = array();
		foreach ( $resources as $resource_id ) {
			$resource_id = sanitize_text_field( (string) $resource_id );
			if ( '' === $resource_id ) {
				continue;
			}
			$normalized[] = $resource_id;
		}

		return array_values( array_unique( $normalized ) );
	}

	/**
	 * Tool ability IDs currently enabled for MCP exposure.
	 *
	 * @since 4.3.0
	 *
	 * @return string[]
	 */
	public static function enabled_tools(): array {
		$known  = self::known_tools();
		$stored = get_option( self::OPTION_ENABLED_TOOLS, null );
		if ( ! is_array( $stored ) ) {
			return $known;
		}

		return array_values( array_intersect( $known, array_map( 'strval', $stored ) ) );
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
		$t = static function ( string $id, string $label, string $risk ): array {
			return array(
				'n' => $id,
				'l' => $label,
				'r' => $risk,
			);
		};

		$catalog = array(
			array(
				'id'        => 'health',
				'name'      => __( 'Health', 'tutor' ),
				'abilities' => array(
					array(
						'id'    => 'health_check',
						'name'  => __( 'Site Health', 'tutor' ),
						'desc'  => __( 'WP + Tutor versions and addon flags. No secrets.', 'tutor' ),
						'tools' => array(
							$t( 'tutor-mcp/health-check', __( 'Health check', 'tutor' ), 'read' ),
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
						'desc'  => __( 'Search, create, update, publish, and trash courses.', 'tutor' ),
						'tools' => array(
							$t( 'tutor-mcp/get-course', __( 'Get course details', 'tutor' ), 'read' ),
							$t( 'tutor-mcp/search-courses', __( 'Search courses', 'tutor' ), 'read' ),
							$t( 'tutor-mcp/get-course-contents', __( 'Get course contents', 'tutor' ), 'read' ),
							$t( 'tutor-mcp/create-course', __( 'Create course', 'tutor' ), 'write' ),
							$t( 'tutor-mcp/update-course', __( 'Update course', 'tutor' ), 'write' ),
							$t( 'tutor-mcp/set-course-status', __( 'Set course status', 'tutor' ), 'write' ),
							$t( 'tutor-mcp/trash-course', __( 'Trash course', 'tutor' ), 'destructive' ),
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
						'name'  => __( 'Topic, Lesson & Quiz', 'tutor' ),
						'desc'  => __( 'Build curriculum; reorder is intentionally disabled.', 'tutor' ),
						'tools' => array(
							$t( 'tutor-mcp/create-topic', __( 'Create topic', 'tutor' ), 'write' ),
							$t( 'tutor-mcp/update-topic', __( 'Update topic', 'tutor' ), 'write' ),
							$t( 'tutor-mcp/delete-topic', __( 'Delete topic', 'tutor' ), 'destructive' ),
							$t( 'tutor-mcp/get-lesson', __( 'Get lesson', 'tutor' ), 'read' ),
							$t( 'tutor-mcp/create-lesson', __( 'Create lesson', 'tutor' ), 'write' ),
							$t( 'tutor-mcp/update-lesson', __( 'Update lesson', 'tutor' ), 'write' ),
							$t( 'tutor-mcp/attach-lesson-video', __( 'Attach lesson video', 'tutor' ), 'write' ),
							$t( 'tutor-mcp/delete-lesson', __( 'Delete lesson', 'tutor' ), 'destructive' ),
							$t( 'tutor-mcp/list-quizzes', __( 'List quizzes', 'tutor' ), 'read' ),
							$t( 'tutor-mcp/get-quiz', __( 'Get quiz', 'tutor' ), 'read' ),
							$t( 'tutor-mcp/create-quiz', __( 'Create quiz', 'tutor' ), 'write' ),
							$t( 'tutor-mcp/update-quiz', __( 'Update quiz', 'tutor' ), 'write' ),
							$t( 'tutor-mcp/add-quiz-question', __( 'Add quiz question', 'tutor' ), 'write' ),
							$t( 'tutor-mcp/update-quiz-question', __( 'Update quiz question', 'tutor' ), 'write' ),
							$t( 'tutor-mcp/delete-quiz-question', __( 'Delete quiz question', 'tutor' ), 'destructive' ),
							$t( 'tutor-mcp/delete-quiz', __( 'Delete quiz', 'tutor' ), 'destructive' ),
						),
					),
				),
			),
			array(
				'id'        => 'quiz_attempts',
				'name'      => __( 'Quiz Attempts', 'tutor' ),
				'abilities' => array(
					array(
						'id'    => 'quiz_attempt_tools',
						'name'  => __( 'Attempt Tools', 'tutor' ),
						'desc'  => __( 'List, review, and delete quiz attempts.', 'tutor' ),
						'tools' => array(
							$t( 'tutor-mcp/list-quiz-attempts', __( 'List attempts', 'tutor' ), 'read' ),
							$t( 'tutor-mcp/get-quiz-attempt', __( 'Get attempt', 'tutor' ), 'read' ),
							$t( 'tutor-mcp/review-quiz-attempt', __( 'Review attempt', 'tutor' ), 'write' ),
							$t( 'tutor-mcp/delete-quiz-attempt', __( 'Delete attempt', 'tutor' ), 'destructive' ),
						),
					),
				),
			),
			array(
				'id'        => 'students',
				'name'      => __( 'Students', 'tutor' ),
				'abilities' => array(
					array(
						'id'    => 'student_tools',
						'name'  => __( 'Student Tools', 'tutor' ),
						'desc'  => __( 'Student directory, progress, and unenroll.', 'tutor' ),
						'tools' => array(
							$t( 'tutor-mcp/list-students', __( 'List students', 'tutor' ), 'read' ),
							$t( 'tutor-mcp/get-student', __( 'Get student', 'tutor' ), 'read' ),
							$t( 'tutor-mcp/get-course-progress', __( 'Get course progress', 'tutor' ), 'read' ),
							$t( 'tutor-mcp/unenroll-student', __( 'Unenroll student', 'tutor' ), 'destructive' ),
						),
					),
				),
			),
			array(
				'id'        => 'finance',
				'name'      => __( 'Finance', 'tutor' ),
				'abilities' => array(
					array(
						'id'    => 'finance_tools',
						'name'  => __( 'Earnings & Withdrawals', 'tutor' ),
						'desc'  => __( 'Read-only earnings aggregates and withdrawal list (no approve/reject).', 'tutor' ),
						'tools' => array(
							$t( 'tutor-mcp/get-earnings', __( 'Get earnings', 'tutor' ), 'read' ),
							$t( 'tutor-mcp/list-withdrawals', __( 'List withdrawals', 'tutor' ), 'read' ),
						),
					),
				),
			),
			array(
				'id'        => 'engagement',
				'name'      => __( 'Engagement', 'tutor' ),
				'abilities' => array(
					array(
						'id'    => 'engagement_tools',
						'name'  => __( 'Announcements, Q&A, Reviews', 'tutor' ),
						'desc'  => __( 'Announcements + Q&A flags/reply; reviews are list-only.', 'tutor' ),
						'tools' => array(
							$t( 'tutor-mcp/list-announcements', __( 'List announcements', 'tutor' ), 'read' ),
							$t( 'tutor-mcp/create-announcement', __( 'Create announcement', 'tutor' ), 'write' ),
							$t( 'tutor-mcp/delete-announcement', __( 'Delete announcement', 'tutor' ), 'destructive' ),
							$t( 'tutor-mcp/list-questions', __( 'List questions', 'tutor' ), 'read' ),
							$t( 'tutor-mcp/reply-to-question', __( 'Reply to question', 'tutor' ), 'write' ),
							$t( 'tutor-mcp/set-question-flags', __( 'Set question flags', 'tutor' ), 'write' ),
							$t( 'tutor-mcp/list-reviews', __( 'List reviews', 'tutor' ), 'read' ),
						),
					),
				),
			),
			array(
				'id'        => 'commerce',
				'name'      => __( 'Commerce', 'tutor' ),
				'abilities' => array(
					array(
						'id'    => 'commerce_tools',
						'name'  => __( 'Orders, Coupons & Price', 'tutor' ),
						'desc'  => __( 'Tutor native ecommerce only. No refunds.', 'tutor' ),
						'tools' => array(
							$t( 'tutor-mcp/list-orders', __( 'List orders', 'tutor' ), 'read' ),
							$t( 'tutor-mcp/get-order', __( 'Get order', 'tutor' ), 'read' ),
							$t( 'tutor-mcp/mark-order-paid', __( 'Mark order paid', 'tutor' ), 'write' ),
							$t( 'tutor-mcp/list-coupons', __( 'List coupons', 'tutor' ), 'read' ),
							$t( 'tutor-mcp/create-coupon', __( 'Create coupon', 'tutor' ), 'write' ),
							$t( 'tutor-mcp/update-coupon', __( 'Update coupon', 'tutor' ), 'write' ),
							$t( 'tutor-mcp/expire-coupon', __( 'Expire coupon', 'tutor' ), 'destructive' ),
							$t( 'tutor-mcp/get-course-price', __( 'Get course price', 'tutor' ), 'read' ),
							$t( 'tutor-mcp/set-course-price', __( 'Set course price', 'tutor' ), 'write' ),
						),
					),
				),
			),
		);

		/**
		 * Filter the Tools → MCP abilities catalog (core + Pro groups).
		 *
		 * @since 4.3.0
		 *
		 * @param array $catalog Catalog groups.
		 */
		$filtered = apply_filters( 'tutor_mcp_tool_catalog', $catalog );

		return is_array( $filtered ) ? $filtered : $catalog;
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
		if ( function_exists( 'is_plugin_active' ) ) {
			return is_plugin_active( self::ADAPTER_PLUGIN_FILE );
		}

		$active_plugins = (array) get_option( 'active_plugins', array() );
		if ( in_array( self::ADAPTER_PLUGIN_FILE, $active_plugins, true ) ) {
			return true;
		}

		if ( is_multisite() ) {
			$network_plugins = (array) get_site_option( 'active_sitewide_plugins', array() );
			return isset( $network_plugins[ self::ADAPTER_PLUGIN_FILE ] );
		}

		return false;
	}

	/**
	 * Whether the MCP Adapter plugin files are present (active or not).
	 *
	 * @since 4.3.0
	 *
	 * @return bool
	 */
	public static function is_adapter_plugin_installed(): bool {
		return file_exists( WP_PLUGIN_DIR . '/' . self::ADAPTER_PLUGIN_FILE );
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
			'tutor-mcp-health'        => __( 'Tutor MCP Health', 'tutor' ),
			'tutor-mcp-schema'        => __( 'Tutor MCP Schema', 'tutor' ),
			'tutor-mcp-courses'       => __( 'Tutor MCP Courses', 'tutor' ),
			'tutor-mcp-curriculum'    => __( 'Tutor MCP Curriculum', 'tutor' ),
			'tutor-mcp-quiz-attempts' => __( 'Tutor MCP Quiz Attempts', 'tutor' ),
			'tutor-mcp-students'      => __( 'Tutor MCP Students', 'tutor' ),
			'tutor-mcp-finance'       => __( 'Tutor MCP Finance', 'tutor' ),
			'tutor-mcp-engagement'    => __( 'Tutor MCP Engagement', 'tutor' ),
			'tutor-mcp-commerce'      => __( 'Tutor MCP Commerce', 'tutor' ),
		) as $slug => $label ) {
			wp_register_ability_category(
				$slug,
				array(
					'label'       => $label,
					'description' => $label,
				)
			);
		}

		/**
		 * Register additional MCP ability categories (e.g. Tutor Pro).
		 *
		 * @since 4.3.0
		 */
		do_action( 'tutor_mcp_register_categories' );
	}

	/**
	 * Register Free feature abilities (+ Pro via action).
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
		$this->quiz_attempts->register();
		$this->students->register();
		$this->finance->register();
		$this->engagement->register();
		$this->commerce->register();
		$this->schema->register();

		/**
		 * Register additional MCP abilities (e.g. Tutor Pro tools).
		 *
		 * @since 4.3.0
		 */
		do_action( 'tutor_mcp_register_abilities' );
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
		if ( ! class_exists( HttpTransport::class ) ) {
			return;
		}

		$error = class_exists( ErrorLogMcpErrorHandler::class )
			? ErrorLogMcpErrorHandler::class
			: null;

		$tools = apply_filters( 'tutor_mcp_ability_ids', self::enabled_tools() );
		if ( ! is_array( $tools ) ) {
			$tools = self::enabled_tools();
		}
		$tools = array_values( array_intersect( self::known_tools(), array_map( 'strval', $tools ) ) );

		$adapter->create_server(
			self::SERVER_ID,
			'mcp',
			self::SERVER_ID,
			__( 'Tutor MCP Server', 'tutor' ),
			__( 'Tutor LMS MCP tools. New courses are always draft/pending — never publish unless the user explicitly asks, then use set-course-status with confirm=true. After creating a course, tell the user it is in draft mode.', 'tutor' ),
			defined( 'TUTOR_VERSION' ) ? TUTOR_VERSION : '1.0.0',
			array( HttpTransport::class ),
			$error,
			null,
			$tools,
			self::known_resources(),
			array(),
			static function (): bool {
				// Bool only — WP_Error falls through to is_user_logged_in() in the adapter.
				return Permissions::can_connect();
			}
		);
	}
}
