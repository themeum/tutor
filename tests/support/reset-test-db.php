<?php
/**
 * Test Database Reset and Seeder using WordPress APIs.
 *
 * Drops every table in the dedicated test database, installs WordPress, then
 * hands off to tests/support/seed.php. Never touches the live database.
 *
 * Usage:
 *   php tests/support/reset-test-db.php
 *
 * @package Tutor\Tests
 */

//@phpcs:ignoreFile

// Tell wp-config.php to connect to the test database.
putenv( 'WP_TEST_ENV=1' );
defined( 'WP_INSTALLING' ) || define( 'WP_INSTALLING', true );

// Resolve the target before booting WordPress: booting an unrouted
// wp-config.php would read the live database before the guard below runs.
$env_file = dirname( __DIR__, 2 ) . '/.env';
$env      = is_readable( $env_file ) ? (array) parse_ini_file( $env_file, false, INI_SCANNER_RAW ) : array();
$expected = getenv( 'WP_TEST_DB_NAME' );
$expected = ( false !== $expected && '' !== $expected ) ? trim( $expected ) : trim( (string) ( $env['WP_TEST_DB_NAME'] ?? '' ) );

$wp_root       = dirname( __DIR__, 5 );
$wp_config     = $wp_root . '/wp-config.php';
$wp_load_path  = $wp_root . '/wp-load.php';
$config_source = is_readable( $wp_config ) ? (string) file_get_contents( $wp_config ) : '';

if ( '' === $expected ) {
	fwrite( STDERR, "\n[FATAL ERROR] WP_TEST_DB_NAME is missing. Run: php tests/support/install.php --yes\n" );
	exit( 1 );
}

if ( ! preg_match( '/_test$/i', $expected ) ) {
	fwrite( STDERR, "\n[FATAL ERROR] Refusing to reset '{$expected}': the name must end in _test.\n" );
	exit( 1 );
}

if ( false === strpos( $config_source, 'test-config.php' ) ) {
	fwrite( STDERR, "\n[FATAL ERROR] wp-config.php is not routed through tests/support/test-config.php. Run: php tests/support/install.php --yes\n" );
	exit( 1 );
}

// Suppress extraneous output during bootstrap.
ob_start();

if ( ! file_exists( $wp_load_path ) ) {
	ob_end_clean();
	fwrite( STDERR, "[FATAL ERROR] Could not find wp-load.php in parent directories!\n" );
	exit( 1 );
}

require_once $wp_load_path;

if ( ! isset( $GLOBALS['wpdb'] ) ) {
	require_once ABSPATH . 'wp-settings.php';
}
ob_end_clean();

global $wpdb;

// =========================================================================
// SAFEGUARDS: the target must be explicit and must never be the live database.
// =========================================================================
$live = class_exists( 'TutorTestConfig' ) ? TutorTestConfig::live_db_name() : '';

if ( $expected !== $wpdb->dbname ) {
	fwrite( STDERR, "\n[FATAL ERROR] Connected to '{$wpdb->dbname}' but WP_TEST_DB_NAME is '{$expected}'. Routing is broken.\n" );
	exit( 1 );
}

if ( '' === $live || $expected === $live ) {
	fwrite( STDERR, "\n[FATAL ERROR] Refusing to reset '{$expected}': it equals the live database.\n" );
	exit( 1 );
}

$start_time = microtime( true );
echo "==> Connected to WordPress test database: {$wpdb->dbname}\n";

// 1. Drop all existing tables in the test database.
echo "==> Cleaning test database tables...\n";
$tables = $wpdb->get_col( 'SHOW TABLES' );
if ( ! empty( $tables ) ) {
	$wpdb->query( 'SET FOREIGN_KEY_CHECKS = 0' );
	foreach ( $tables as $table ) {
		$wpdb->query( "DROP TABLE IF EXISTS `{$table}`" );
	}
	$wpdb->query( 'SET FOREIGN_KEY_CHECKS = 1' );
}

// 2. Install fresh WordPress core.
echo "==> Installing fresh WordPress core...\n";
require_once ABSPATH . 'wp-admin/includes/upgrade.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';

$admin_user = 'admin';
$admin_pass = getenv( 'E2E_PASSWORD' ) ?: ( $env['E2E_PASSWORD'] ?? 'password' );
$site_title = 'Tutor LMS Test Site';
$site_url   = getenv( 'SITE_URL' ) ?: ( $env['SITE_URL'] ?? ( getenv( 'E2E_BASE_URL' ) ?: 'http://localhost:8888' ) );

wp_install( $site_title, $admin_user, 'admin@example.org', true, '', $admin_pass );

update_option( 'siteurl', $site_url );
update_option( 'home', $site_url );
update_option( 'permalink_structure', '/%postname%/' );

// 3. Activate Tutor, WooCommerce and Pro when their files are present.
$requested = getenv( 'WP_TEST_ACTIVE_PLUGINS' ) ?: ( $env['WP_TEST_ACTIVE_PLUGINS'] ?? '' );
$plugins   = array();

if ( '' !== trim( (string) $requested ) ) {
	foreach ( explode( ',', (string) $requested ) as $plugin ) {
		$plugin = trim( $plugin );
		if ( '' === $plugin ) {
			continue;
		}
		$plugins[] = false === strpos( $plugin, '/' ) ? "{$plugin}/{$plugin}.php" : $plugin;
	}
} else {
	$plugins[] = 'tutor/tutor.php';
	if ( file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' ) ) {
		$plugins[] = 'woocommerce/woocommerce.php';
	}
	if ( file_exists( WP_PLUGIN_DIR . '/tutor-pro/tutor-pro.php' ) ) {
		$plugins[] = 'tutor-pro/tutor-pro.php';
	}
	if ( file_exists( WP_PLUGIN_DIR . '/h5p/h5p.php' ) ) {
		$plugins[] = 'h5p/h5p.php';
	}
}

// Tutor's activated_plugin hook redirects to the setup wizard and exits.
// Mark the wizard complete before activation so this CLI reset can finish.
update_option( 'tutor_wizard', true );
update_option( 'tutor_welcome_page_visited', true );

echo '==> Activating plugins: ' . implode( ', ', $plugins ) . "\n";
foreach ( $plugins as $plugin ) {
	if ( ! file_exists( WP_PLUGIN_DIR . '/' . $plugin ) ) {
		fwrite( STDERR, "[FATAL ERROR] Plugin file not found: {$plugin}\n" );
		exit( 1 );
	}

	$result = activate_plugin( $plugin, '', false, false );
	if ( is_wp_error( $result ) ) {
		fwrite( STDERR, "[FATAL ERROR] Could not activate {$plugin}: {$result->get_error_message()}\n" );
		exit( 1 );
	}
}
// WordPress sorts active plugins alphabetically; keep that order so Pro
// (tutor-pro) loads before Tutor and can hook the `tutor_loaded` action.
$plugins = array_values( array_unique( $plugins ) );
sort( $plugins );
update_option( 'active_plugins', $plugins );

// Plugins were activated after wp-settings.php already ran, so fire their
// lifecycle hooks now to register post types, taxonomies, and addons.
do_action( 'plugins_loaded' );
do_action( 'init' );

if ( ! function_exists( 'tutor' ) ) {
	require_once dirname( __DIR__, 2 ) . '/tutor.php';
}

// 4. Create Tutor custom tables, default options, roles, and initial pages.
// Activation already installed the defaults and pages; re-running save_data()
// would create duplicate dashboard pages and repoint the option at the copy.
echo "==> Initializing Tutor LMS tables, default options, and pages...\n";
if ( class_exists( 'TUTOR\Tutor' ) ) {
	TUTOR\Tutor::create_database();
	TUTOR\Tutor::manage_tutor_roles_and_permissions();
	if ( ! get_option( 'tutor_option' ) ) {
		update_option( 'tutor_option', TUTOR\Tutor::default_options() );
	}
	update_option( 'tutor_welcome_page_visited', true );
	update_option( 'tutor_wizard', true );
	update_option( 'tutor-new-feature', defined( 'TUTOR_VERSION' ) ? TUTOR_VERSION : '4.2.0' );
	update_option( 'tutor_first_activation_time', time() );
}

// 5. Seed users, pages, settings, and fixtures.
echo "==> Seeding fixtures...\n";
require_once __DIR__ . '/seed.php';

// Rebuild dashboard routes now that the canonical pages and add-ons are known.
flush_rewrite_rules( false );

// 6. Marker for the preflight and manual checks.
update_option( 'tutor_e2e_test_db', 1 );

$elapsed = round( microtime( true ) - $start_time, 2 );
echo "==> [SUCCESS] Fresh test database '{$wpdb->dbname}' initialized in {$elapsed}s!\n";
echo "    - WordPress core installed\n";
echo "    - Tutor LMS tables & initial pages created\n";
echo "    - Test accounts: admin ({$admin_user}/{$admin_pass}), instructor, student, student2, manager\n";
