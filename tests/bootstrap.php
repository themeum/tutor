<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package Tutor
 */

if ( ! session_id() ) {
	session_start();
}

define( 'WP_RUNNING_TESTS', true );

// Use our own config (points to a separate test database on the existing WP install).
define( 'WP_TESTS_CONFIG_FILE_PATH', __DIR__ . '/wp-tests-config.php' );

$_plugin_dir = dirname( __DIR__ );
$_tests_dir  = getenv( 'WP_TESTS_DIR' ) ? getenv( 'WP_TESTS_DIR' ) : $_plugin_dir . '/vendor/wp-phpunit/wp-phpunit';

if ( ! file_exists( "{$_tests_dir}/includes/functions.php" ) ) {
	echo "Could not find WordPress test suite at {$_tests_dir}. Run: composer require --dev wp-phpunit/wp-phpunit" . PHP_EOL;//phpcs:ignore
	exit( 1 );
}

// Polyfills (required by WP test suite).
if ( ! getenv( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH' ) ) {
	define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', $_plugin_dir . '/vendor/yoast/phpunit-polyfills' );
}

require_once "{$_tests_dir}/includes/functions.php";

tests_add_filter(
	'muplugins_loaded',
	function () use ( $_plugin_dir ) {
		require $_plugin_dir . '/tutor.php';
	}
);

require "{$_tests_dir}/includes/bootstrap.php";

// Initialize Tutor roles for testing.
if ( class_exists( 'TUTOR\Tutor' ) ) {
	TUTOR\Tutor::manage_tutor_roles_and_permissions();
}
