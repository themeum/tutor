<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package Tutor
 */

session_id() || session_start();

define( 'WP_RUNNING_TESTS', true );
// wp-phpunit spawns its installer as a child process; pass the flag through
// the environment so it selects WP_PHPUNIT_DB_NAME, not the E2E database.
putenv( 'WP_RUNNING_TESTS=1' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
define( 'WP_TESTS_CONFIG_FILE_PATH', dirname( __DIR__, 4 ) . '/wp-config.php' );

$_plugin_dir = dirname( __DIR__ );
$_tests_dir  = "{$_plugin_dir}/vendor/wp-phpunit/wp-phpunit";

if ( ! is_file( "{$_tests_dir}/includes/functions.php" ) ) {
	exit( "WordPress test suite not found. Run: composer require --dev wp-phpunit/wp-phpunit\n" );
}

getenv( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH' )
	|| define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', "{$_plugin_dir}/vendor/yoast/phpunit-polyfills" );

require_once "{$_tests_dir}/includes/functions.php";

tests_add_filter( 'muplugins_loaded', fn() => require "{$_plugin_dir}/tutor.php" );

require "{$_tests_dir}/includes/bootstrap.php";

// Initialize Tutor roles for testing environment.
class_exists( 'TUTOR\Tutor' ) && TUTOR\Tutor::manage_tutor_roles_and_permissions();
