<?php
/**
 * Fail fast when the test environment points at an unsafe database.
 *
 * Usage: php tests/support/preflight.php
 *
 * @package Tutor\Tests
 */

// @phpcs:ignoreFile

$plugin_dir = dirname( __DIR__, 2 );
$wp_root    = dirname( __DIR__, 5 );
$env_file   = $plugin_dir . '/.env';
$wp_config  = $wp_root . '/wp-config.php';

$env = is_readable( $env_file ) ? (array) parse_ini_file( $env_file, false, INI_SCANNER_RAW ) : array();

/**
 * Resolve one setting from the process environment, then .env.
 *
 * @param string $key     Setting name.
 * @param array  $env     Parsed .env values.
 * @param string $default Fallback.
 * @return string
 */
function tutor_preflight_env( string $key, array $env, string $default = '' ): string {
	$value = getenv( $key );
	if ( false !== $value && '' !== $value ) {
		return trim( $value );
	}

	return isset( $env[ $key ] ) ? trim( (string) $env[ $key ] ) : $default;
}

/**
 * Read the live database name from wp-config.php.
 *
 * @param string $file wp-config.php path.
 * @return string
 */
function tutor_preflight_live_db( string $file ): string {
	if ( ! is_readable( $file ) ) {
		return '';
	}

	$content = (string) file_get_contents( $file );
	$patterns = array(
		"/TutorTestConfig::db\s*\(\s*['\"]NAME['\"]\s*,\s*['\"]([^'\"]*)['\"]\s*\)/",
		"/define\s*\(\s*['\"]DB_NAME['\"]\s*,\s*['\"]([^'\"]*)['\"]\s*\)/",
	);

	foreach ( $patterns as $pattern ) {
		if ( preg_match( $pattern, $content, $match ) ) {
			return trim( $match[1] );
		}
	}

	return '';
}

/**
 * Whether a host matches the WP_TEST_HOSTS allowlist.
 *
 * @param string $url      Site URL.
 * @param string $patterns Comma-separated allowlist.
 * @return bool
 */
function tutor_preflight_host_allowed( string $url, string $patterns ): bool {
	$host = strtolower( (string) parse_url( $url, PHP_URL_HOST ) );
	if ( '' === $host ) {
		return false;
	}

	foreach ( array_filter( array_map( 'trim', explode( ',', $patterns ) ) ) as $pattern ) {
		$pattern = strtolower( $pattern );
		if ( $pattern === $host || ( 0 === strpos( $pattern, '*.' ) && str_ends_with( $host, substr( $pattern, 1 ) ) ) ) {
			return true;
		}
	}

	return false;
}

$errors = array();

if ( ! is_readable( $wp_config ) ) {
	$errors[] = "wp-config.php not found at {$wp_config}";
} elseif ( false === strpos( (string) file_get_contents( $wp_config ), 'test-config.php' ) ) {
	$errors[] = 'wp-config.php does not route through tests/support/test-config.php';
}

$live_db    = tutor_preflight_live_db( $wp_config );
$test_db    = tutor_preflight_env( 'WP_TEST_DB_NAME', $env );
$phpunit_db = tutor_preflight_env( 'WP_PHPUNIT_DB_NAME', $env );

if ( '' === $live_db ) {
	$errors[] = 'could not read the live database name from wp-config.php';
}
if ( '' === $test_db ) {
	$errors[] = 'WP_TEST_DB_NAME is missing';
}
if ( '' === $phpunit_db ) {
	$errors[] = 'WP_PHPUNIT_DB_NAME is missing';
}

if ( $live_db && $test_db && $live_db === $test_db ) {
	$errors[] = "WP_TEST_DB_NAME equals the live database '{$live_db}'";
}
if ( $live_db && $phpunit_db && $live_db === $phpunit_db ) {
	$errors[] = "WP_PHPUNIT_DB_NAME equals the live database '{$live_db}'";
}
if ( $test_db && $phpunit_db && $test_db === $phpunit_db ) {
	$errors[] = 'WP_TEST_DB_NAME and WP_PHPUNIT_DB_NAME are the same';
}
if ( '' !== $test_db && ! preg_match( '/_test$/i', $test_db ) ) {
	$errors[] = "WP_TEST_DB_NAME '{$test_db}' must end in _test";
}
if ( '' !== $phpunit_db && ! preg_match( '/(_test|phpunit)/i', $phpunit_db ) ) {
	$errors[] = "WP_PHPUNIT_DB_NAME '{$phpunit_db}' must end in _test or contain phpunit";
}

$hosts    = tutor_preflight_env( 'WP_TEST_HOSTS', $env, 'localhost,127.0.0.1,::1,*.local' );
$site_url = tutor_preflight_env( 'SITE_URL', $env );
$base_url = tutor_preflight_env( 'E2E_BASE_URL', $env );

foreach ( array( 'SITE_URL' => $site_url, 'E2E_BASE_URL' => $base_url ) as $key => $url ) {
	if ( '' !== $url && ! tutor_preflight_host_allowed( $url, $hosts ) ) {
		$errors[] = "{$key} host is not in WP_TEST_HOSTS ({$hosts})";
	}
}

if ( $errors ) {
	fwrite( STDERR, '[preflight] ' . implode( '; ', $errors ) . "\n" );
	exit( 1 );
}

$wp_test_db_host = tutor_preflight_env( 'WP_TEST_DB_HOST', $env, 'localhost' );
echo "[preflight] ok: live={$live_db} e2e={$test_db} phpunit={$phpunit_db} host={$wp_test_db_host}\n";
