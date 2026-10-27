<?php
/**
 * Dynamic Page URLs Resolver for E2E Tests.
 *
 * Bootstraps the WordPress test environment (local_test) and returns dynamic page
 * URLs for frontend and backend pages without hardcoding slugs in .env.
 *
 * Usage:
 *   php tests/support/page-urls.php          # Outputs JSON of all URLs
 *   php tests/support/page-urls.php checkout # Outputs single checkout URL
 *
 * @package Tutor\Tests
 */

// Tell wp-config.php to connect to local_test.
putenv( 'WP_TEST_ENV=1' );
defined( 'WP_INSTALLING' ) || define( 'WP_INSTALLING', true );

// Suppress extraneous output and warnings during bootstrap.
ob_start();

$wp_load_path = dirname( __DIR__, 5 ) . '/wp-load.php';
if ( ! file_exists( $wp_load_path ) ) {
	ob_end_clean();
	fwrite( STDERR, "[FATAL ERROR] Could not find wp-load.php at {$wp_load_path}!\n" );
	exit( 1 );
}

require_once $wp_load_path;

if ( ! isset( $GLOBALS['wpdb'] ) ) {
	require_once ABSPATH . 'wp-settings.php';
}

if ( ! function_exists( 'tutor' ) ) {
	$tutor_bootstrap = dirname( __DIR__, 2 ) . '/tutor.php';
	if ( file_exists( $tutor_bootstrap ) ) {
		require_once $tutor_bootstrap;
	}
}

// Ensure rewrite rules are initialized so permalinks format cleanly.
if ( isset( $GLOBALS['wp_rewrite'] ) && is_object( $GLOBALS['wp_rewrite'] ) ) {
	$GLOBALS['wp_rewrite']->init();
}

// Ensure cart and checkout pages exist.
if ( class_exists( '\Tutor\Ecommerce\CartController' ) ) {
	\Tutor\Ecommerce\CartController::create_cart_page();
}
if ( class_exists( '\Tutor\Ecommerce\CheckoutController' ) ) {
	\Tutor\Ecommerce\CheckoutController::create_checkout_page();
}

$checkout_id = (int) tutor_utils()->get_option( 'tutor_checkout_page_id' );
$cart_id     = (int) tutor_utils()->get_option( 'tutor_cart_page_id' );
$dash_id     = (int) tutor_utils()->get_option( 'tutor_dashboard_page_id' );

$cart_url     = $cart_id ? get_permalink( $cart_id ) : \Tutor\Ecommerce\CartController::get_page_url();
$checkout_url = $checkout_id ? get_permalink( $checkout_id ) : \Tutor\Ecommerce\CheckoutController::get_page_url();
$dash_url     = $dash_id ? get_permalink( $dash_id ) : tutor_utils()->tutor_dashboard_url();
$courses_url  = get_post_type_archive_link( tutor()->course_post_type );
if ( ! $courses_url ) {
	$courses_url = site_url( '/courses/' );
}

$urls = array(
	'site_url'               => site_url(),
	'cart'                   => $cart_url,
	'checkout'               => $checkout_url,
	'dashboard'              => $dash_url,
	'courses'                => $courses_url,
	'student_register'       => tutor_utils()->student_register_url(),
	'instructor_register'    => tutor_utils()->instructor_register_url(),
	'login'                  => wp_login_url(),
	'admin_orders'           => admin_url( 'admin.php?page=tutor_orders' ),
	'admin_settings'         => admin_url( 'admin.php?page=tutor_settings' ),
	'admin_monetization'     => admin_url( 'admin.php?page=tutor_settings&tab_page=monetization' ),
	'admin_payment_settings' => admin_url( 'admin.php?page=tutor_settings&tab_page=ecommerce_payment' ),
);

// Clear bootstrap buffer.
ob_end_clean();

// Check if a single key was requested via CLI argument.
$requested_key = isset( $argv[1] ) ? trim( $argv[1] ) : '';
if ( '' !== $requested_key ) {
	if ( isset( $urls[ $requested_key ] ) ) {
		echo $urls[ $requested_key ] . "\n";
		exit( 0 );
	}
	fwrite( STDERR, "[ERROR] URL key '{$requested_key}' not found!\n" );
	exit( 1 );
}

// Output entire array as JSON.
echo json_encode( $urls, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . "\n";
