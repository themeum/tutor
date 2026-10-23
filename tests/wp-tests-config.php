<?php
/**
 * WordPress PHPUnit Test Configuration.
 * Reuses the existing Local WordPress installation.
 *
 * @package Tutor\Tests
 */

// Path to the existing WordPress core installation in Local Sites.
$existing_wp_dir = dirname( __DIR__, 4 );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', rtrim( $existing_wp_dir, '/\\' ) . '/' );
}

// Load optional overrides from .env.
$env_file = dirname( __DIR__ ) . '/.env';
$env      = file_exists( $env_file ) ? parse_ini_file( $env_file ) : array();

// Dedicated test database settings - shared with Playwright E2E tests.
$test_db_name     = ! empty( $env['WP_TEST_DB_NAME'] ) ? $env['WP_TEST_DB_NAME'] : ( ! empty( $env['DB_NAME'] ) ? $env['DB_NAME'] : getenv( 'WP_TEST_DB_NAME' ) );
$test_db_user     = ! empty( $env['WP_TEST_DB_USER'] ) ? $env['WP_TEST_DB_USER'] : ( ! empty( $env['DB_USER'] ) ? $env['DB_USER'] : getenv( 'WP_TEST_DB_USER' ) );
$test_db_password = ! empty( $env['WP_TEST_DB_PASS'] ) ? $env['WP_TEST_DB_PASS'] : ( ! empty( $env['DB_PASSWORD'] ) ? $env['DB_PASSWORD'] : getenv( 'WP_TEST_DB_PASS' ) );
$test_db_host     = ! empty( $env['WP_TEST_DB_HOST'] ) ? $env['WP_TEST_DB_HOST'] : ( ! empty( $env['DB_HOST'] ) ? $env['DB_HOST'] : getenv( 'WP_TEST_DB_HOST' ) );

define( 'DB_NAME', false !== $test_db_name && null !== $test_db_name ? $test_db_name : 'local_test' );
define( 'DB_USER', false !== $test_db_user && null !== $test_db_user ? $test_db_user : 'root' );
define( 'DB_PASSWORD', false !== $test_db_password && null !== $test_db_password ? $test_db_password : 'root' );
define( 'DB_HOST', false !== $test_db_host && null !== $test_db_host ? $test_db_host : '127.0.0.1:10003' );

define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

// =========================================================================
// SAFEGUARDS: Ensure tests NEVER run against development or production DB!
// =========================================================================
if ( 'local' === DB_NAME || 'production' === DB_NAME || ! preg_match( '/(test|testing|phpunit)/i', DB_NAME ) ) {
	fwrite(
		STDERR,
		"\n[FATAL ERROR] Testing aborted: Refusing to run tests against '" . DB_NAME . "'.\n" .
		"A dedicated test database ending in '_test' is required to prevent accidental data loss.\n\n"
	);
	exit( 1 );
}

// Table prefix for PHPUnit tests.
$table_prefix = 'wptests_';//phpcs:ignore

$site_url    = $env['SITE_URL'] ?? getenv( 'SITE_URL' );
$parsed_host = parse_url( $site_url, PHP_URL_HOST );
$test_domain = $parsed_host ? $parsed_host : 'tutor-dev.local';

define( 'WP_TESTS_DOMAIN', $test_domain );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Tutor LMS Test Site' );
define( 'WP_PHP_BINARY', 'php' );
define( 'WPLANG', '' );
define( 'WP_DEBUG', true );

// Key salts for testing.
define( 'AUTH_KEY', 'tutor-test-auth-key-salt-phrase' );
define( 'SECURE_AUTH_KEY', 'tutor-test-secure-auth-key-salt-phrase' );
define( 'LOGGED_IN_KEY', 'tutor-test-logged-in-key-salt-phrase' );
define( 'NONCE_KEY', 'tutor-test-nonce-key-salt-phrase' );
define( 'AUTH_SALT', 'tutor-test-auth-salt-phrase' );
define( 'SECURE_AUTH_SALT', 'tutor-test-secure-auth-salt-phrase' );
define( 'LOGGED_IN_SALT', 'tutor-test-logged-in-salt-phrase' );
define( 'NONCE_SALT', 'tutor-test-nonce-salt-phrase' );
