<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package Tutor
 */

! session_id() ? session_start() : 0;

$test_env_key = 'WP_TESTS_DIR';
$_tests_dir   = getenv( $test_env_key );

/**
 * Local .env file support
 * Put a .env file in root of project.
 */
$local_env = __DIR__ . '/../.env';
if ( file_exists( $local_env ) ) {
	$env = parse_ini_file( $local_env );
	if ( is_array( $env ) && array_key_exists( $test_env_key, $env ) ) {
		$_tests_dir = trim( $env[ $test_env_key ] );
	}
}

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

// Forward custom PHPUnit Polyfills configuration to PHPUnit bootstrap file.
$_phpunit_polyfills_path = getenv( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH' );
if ( false !== $_phpunit_polyfills_path ) {
	define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', $_phpunit_polyfills_path );
}

if ( ! file_exists( "{$_tests_dir}/includes/functions.php" ) ) {
	echo "Could not find {$_tests_dir}/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once "{$_tests_dir}/includes/functions.php";

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/tutor.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require "{$_tests_dir}/includes/bootstrap.php";
