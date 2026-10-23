<?php
/**
 * WordPress PHPUnit Test Configuration.
 * Defines test suite constants and inherits DB settings from root wp-config.php.
 *
 * @package Tutor\Tests
 */

defined( 'WP_TESTS_DOMAIN' ) || define( 'WP_TESTS_DOMAIN', 'tutor-dev.local' );
defined( 'WP_TESTS_EMAIL' ) || define( 'WP_TESTS_EMAIL', 'admin@example.org' );
defined( 'WP_TESTS_TITLE' ) || define( 'WP_TESTS_TITLE', 'Tutor LMS Test Site' );
defined( 'WP_PHP_BINARY' ) || define( 'WP_PHP_BINARY', 'php' );

require_once dirname( __DIR__, 5 ) . '/wp-config.php';
