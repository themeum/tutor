<?php
/**
 * Start the test web server with the WordPress root auto-detected.
 *
 * Usage:
 *   php bin/e2e/serve.php
 *   php bin/e2e/serve.php --check
 *
 * Environment:
 *   WP_ROOT       WordPress root. Auto-detected when unset.
 *   E2E_BASE_URL  Base URL; its port is used for the server. Falls back to SITE_URL.
 *   WP_CLI_BIN    WP-CLI binary. Defaults to `wp`.
 *   PHP_CLI_SERVER_WORKERS  Worker count passed through to PHP. Unset by default.
 *
 * @package Tutor\Tests
 */

// @phpcs:ignoreFile

$plugin_dir = dirname( __DIR__, 2 );
$env_file   = $plugin_dir . '/.env';
$env        = is_readable( $env_file ) ? (array) parse_ini_file( $env_file, false, INI_SCANNER_RAW ) : array();

/**
 * Find the WordPress root by walking up from a starting directory.
 *
 * @param string $start Starting directory.
 * @return string|null
 */
function tutor_e2e_find_wp_root( string $start ): ?string {
	$dir = realpath( $start );

	for ( $i = 0; $dir && $i < 12; $i++ ) {
		if ( is_file( $dir . '/wp-load.php' ) ) {
			return $dir;
		}

		$parent = dirname( $dir );
		if ( $parent === $dir ) {
			break;
		}
		$dir = $parent;
	}

	return null;
}

$requested_root = getenv( 'WP_ROOT' );
if ( false === $requested_root || '' === $requested_root ) {
	$requested_root = isset( $env['WP_ROOT'] ) ? (string) $env['WP_ROOT'] : '';
}

if ( '' !== $requested_root ) {
	$wp_root = realpath( $requested_root );
	if ( false === $wp_root || ! is_file( $wp_root . '/wp-load.php' ) ) {
		fwrite( STDERR, "WP_ROOT does not contain wp-load.php: {$requested_root}\n" );
		exit( 1 );
	}
} else {
	$wp_root = null;
	foreach ( array( (string) getcwd(), dirname( $plugin_dir, 3 ) ) as $candidate ) {
		if ( '' === $candidate ) {
			continue;
		}

		$wp_root = tutor_e2e_find_wp_root( $candidate );
		if ( null !== $wp_root ) {
			break;
		}
	}

	if ( null === $wp_root ) {
		fwrite( STDERR, "Could not find WordPress. Set WP_ROOT to the directory containing wp-load.php.\n" );
		exit( 1 );
	}
}

$base_url = getenv( 'E2E_BASE_URL' );
if ( false === $base_url || '' === $base_url ) {
	$base_url = isset( $env['E2E_BASE_URL'] ) ? (string) $env['E2E_BASE_URL'] : '';
}
if ( '' === $base_url ) {
	$base_url = getenv( 'SITE_URL' );
}
if ( false === $base_url || '' === $base_url ) {
	$base_url = isset( $env['SITE_URL'] ) ? (string) $env['SITE_URL'] : '';
}
if ( '' === $base_url ) {
	$base_url = 'http://localhost:8888';
}

$port = parse_url( $base_url, PHP_URL_PORT );
if ( ! $port ) {
	$port = 'https' === parse_url( $base_url, PHP_URL_SCHEME ) ? 443 : 80;
}

$host   = '127.0.0.1';
$wp_bin = getenv( 'WP_CLI_BIN' );
if ( false === $wp_bin || '' === $wp_bin ) {
	$wp_bin = 'wp';
}

$workers = getenv( 'PHP_CLI_SERVER_WORKERS' );

$command = 'WP_TEST_ENV=1';
if ( false !== $workers && '' !== $workers ) {
	$command .= ' PHP_CLI_SERVER_WORKERS=' . escapeshellarg( $workers );
}
$command .= ' ' . escapeshellarg( $wp_bin ) . ' server'
	. ' --host=' . escapeshellarg( $host )
	. ' --port=' . escapeshellarg( (string) $port )
	. ' --path=' . escapeshellarg( $wp_root );

$check = in_array( '--check', array_slice( $argv, 1 ), true );

echo "WordPress root: {$wp_root}\n";
echo "Test server: http://{$host}:{$port}\n";
echo "Command: {$command}\n";

passthru( escapeshellarg( PHP_BINARY ) . ' ' . escapeshellarg( $plugin_dir . '/tests/support/preflight.php' ), $status );
if ( 0 !== $status ) {
	exit( $status );
}

if ( $check ) {
	exit( 0 );
}

echo "Starting test server...\n";
passthru( $command, $status );

if ( 127 === $status ) {
	fwrite( STDERR, "WP-CLI failed to start. Install WP-CLI or set WP_CLI_BIN.\n" );
}

exit( $status );
