<?php
/**
 * Test Database Reset and Seeder using WordPress APIs.
 *
 * Resets the dedicated test database (local_test) to a fresh, clean state
 * with standard users and baseline fixtures using native WordPress functions.
 *
 * Usage:
 *   php tests/support/reset-test-db.php
 *
 * @package Tutor\Tests
 */

//@phpcs:ignoreFile

// Tell wp-config.php to connect to local_test.
putenv( 'WP_TEST_ENV=1' );
defined( 'WP_INSTALLING' ) || define( 'WP_INSTALLING', true );

// Suppress extraneous output during bootstrap.
ob_start();

// Dynamically find wp-load.php by walking up directory tree.
$wp_load_path = dirname( __DIR__, 5 ) . '/wp-load.php';
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
// SAFEGUARDS: Ensure we NEVER touch development or production databases!
// =========================================================================
if ( 'local' === $wpdb->dbname || 'production' === $wpdb->dbname ) {
	fwrite( STDERR, "\n[FATAL ERROR] Safeguard triggered: Cannot run on '{$wpdb->dbname}'!\n" );
	exit( 1 );
}

if ( ! preg_match( '/(test|testing|phpunit)/i', $wpdb->dbname ) ) {
	fwrite( STDERR, "\n[FATAL ERROR] Safeguard triggered: Database name must contain 'test'. Current: '{$wpdb->dbname}'\n" );
	exit( 1 );
}

$start_time = microtime( true );
echo "==> Connected to WordPress test database: {$wpdb->dbname}\n";

// 1. Drop all existing tables in test database
echo "==> Cleaning test database tables...\n";
$tables = $wpdb->get_col( 'SHOW TABLES' );
if ( ! empty( $tables ) ) {
	$wpdb->query( 'SET FOREIGN_KEY_CHECKS = 0' );
	foreach ( $tables as $table ) {
		$wpdb->query( "DROP TABLE IF EXISTS `{$table}`" );
	}
	$wpdb->query( 'SET FOREIGN_KEY_CHECKS = 1' );
}

// 2. Install fresh WordPress core
echo "==> Installing fresh WordPress core...\n";
require_once ABSPATH . 'wp-admin/includes/upgrade.php';

$env_file = dirname( __DIR__, 2 ) . '/.env';
$env      = file_exists( $env_file ) ? parse_ini_file( $env_file ) : array();

$admin_user = ! empty( $env['ADMIN_USERNAME'] ) ? trim( $env['ADMIN_USERNAME'] ) : ( getenv( 'ADMIN_USERNAME' ) ?: 'admin' );
$admin_pass = ! empty( $env['ADMIN_PASSWORD'] ) ? trim( $env['ADMIN_PASSWORD'] ) : ( getenv( 'ADMIN_PASSWORD' ) ?: '12345' );
$site_title = 'Tutor LMS Test Site';
$site_url   = ! empty( $env['SITE_URL'] ) ? trim( $env['SITE_URL'] ) : ( getenv( 'SITE_URL' ) ?: ( getenv( 'WP_BASE_URL' ) ?: 'https://tutor-dev.local' ) );

wp_install( $site_title, $admin_user, 'admin@example.org', true, '', $admin_pass );

// Ensure correct siteurl, home, permalinks, and active plugins.
update_option( 'siteurl', $site_url );
update_option( 'home', $site_url );
update_option( 'permalink_structure', '/%postname%/' );
$active_plugins = array( 'tutor/tutor.php' );
$pro_plugin_path = dirname( __DIR__, 3 ) . '/tutor-pro/tutor-pro.php';
if ( file_exists( $pro_plugin_path ) ) {
	$active_plugins[] = 'tutor-pro/tutor-pro.php';
}
update_option( 'active_plugins', $active_plugins );
update_option( 'users_can_register', 1 );

if ( ! function_exists( 'tutor' ) ) {
	require_once dirname( __DIR__, 2 ) . '/tutor.php';
}
if ( file_exists( $pro_plugin_path ) && ! function_exists( 'tutor_pro' ) ) {
	require_once $pro_plugin_path;
}

// 3. Create Tutor custom tables, default options, roles, and initial pages
echo "==> Initializing Tutor LMS tables, default options, and pages...\n";
if ( class_exists( 'TUTOR\Tutor' ) ) {
	TUTOR\Tutor::create_database();
	TUTOR\Tutor::manage_tutor_roles_and_permissions();
	TUTOR\Tutor::save_data();

	// Configure default options with native monetization, preserving pages created by save_data().
	$existing_options = (array) get_option( 'tutor_option', array() );
	$default_options  = array_merge( TUTOR\Tutor::default_options(), $existing_options );
	$default_options['monetize_by']              = 'tutor';
	$default_options['is_enable_buy_now']        = 'off';
	$default_options['is_enable_guest_checkout'] = 'on';
	$default_options['is_coupon_applicable']     = 'on';

	// Seed default active manual payment method (Bank Transfer).
	$manual_method = array(
		'name'                 => 'bank-transfer',
		'label'                => 'Bank Transfer',
		'is_active'            => true,
		'icon'                 => '',
		'support_subscription' => false,
		'is_manual'            => true,
		'fields'               => array(
			array( 'name' => 'method_name', 'value' => 'Bank Transfer' ),
			array( 'name' => 'icon', 'value' => '' ),
			array( 'name' => 'payment_instructions', 'value' => 'Please transfer total payment to Bank Account: 1234-5678-9012 (SWIFT: TUTORBANK).' ),
		),
	);
	$default_options['payment_settings'] = json_encode( array( 'payment_methods' => array( $manual_method ) ) );

	update_option( 'tutor_option', $default_options );
	update_option( 'tutor_welcome_page_visited', true );
	update_option( 'tutor_wizard', true );
	update_option( 'tutor-new-feature', defined( 'TUTOR_VERSION' ) ? TUTOR_VERSION : '4.2.0' );
	update_option( 'tutor_first_activation_time', time() );

	// Ensure Native Ecommerce Cart and Checkout pages are created.
	if ( class_exists( '\Tutor\Ecommerce\CartController' ) ) {
		\Tutor\Ecommerce\CartController::create_cart_page();
	}
	if ( class_exists( '\Tutor\Ecommerce\CheckoutController' ) ) {
		\Tutor\Ecommerce\CheckoutController::create_checkout_page();
	}

	// Apply any schema upgrades if available.
	if ( class_exists( '\TUTOR\Upgrader' ) ) {
		( new \TUTOR\Upgrader() )->init_upgrader();
	}
}

// 4. Seed standard test users.
echo "==> Seeding test users (instructor, student)...\n";
// Create instructor.
$instructor_id = wp_create_user( 'instructor', '12345', 'instructor@example.org' );
if ( ! is_wp_error( $instructor_id ) ) {
	$instructor = new WP_User( $instructor_id );
	$instructor->set_role( 'tutor_instructor' );
}

// Create student
$student_id = wp_create_user( 'student', '12345', 'student@example.org' );
if ( ! is_wp_error( $student_id ) ) {
	$student = new WP_User( $student_id );
	$student->set_role( 'subscriber' );
}

// 5. Seed baseline course fixtures (free + 3 paid courses).
echo "==> Seeding baseline course fixtures (free + 3 paid courses)...\n";
$admin     = get_user_by( 'login', $admin_user );
$author_id = $admin ? $admin->ID : 1;

$courses_to_seed = array(
	array(
		'title'        => 'Sample Test Course',
		'slug'         => 'sample-test-course',
		'content'      => 'Sample test course description for E2E testing.',
		'price_type'   => 'free',
		'price'        => 0,
		'sale_price'   => 0,
	),
	array(
		'title'        => 'Mastering React 19',
		'slug'         => 'mastering-react-19',
		'content'      => 'Master modern frontend development with React 19 and Next.js.',
		'price_type'   => 'paid',
		'price'        => 50.00,
		'sale_price'   => 40.00,
	),
	array(
		'title'        => 'Advanced TypeScript Guide',
		'slug'         => 'advanced-typescript-guide',
		'content'      => 'In-depth TypeScript techniques, utility types, and modern patterns.',
		'price_type'   => 'paid',
		'price'        => 60.00,
		'sale_price'   => 45.00,
	),
	array(
		'title'        => 'WordPress Playwright E2E Course',
		'slug'         => 'wordpress-playwright-e2e-course',
		'content'      => 'Automate full-stack testing for WordPress LMS with Playwright.',
		'price_type'   => 'paid',
		'price'        => 75.00,
		'sale_price'   => 0,
	),
);

foreach ( $courses_to_seed as $course_data ) {
	$course_id = wp_insert_post(
		array(
			'post_title'   => $course_data['title'],
			'post_name'    => $course_data['slug'],
			'post_content' => $course_data['content'],
			'post_status'  => 'publish',
			'post_type'    => tutor()->course_post_type,
			'post_author'  => $author_id,
		)
	);

	if ( $course_id && ! is_wp_error( $course_id ) ) {
		// Pricing metadata.
		update_post_meta( $course_id, \TUTOR\Course::COURSE_PRICE_TYPE_META, $course_data['price_type'] );
		if ( 'paid' === $course_data['price_type'] ) {
			update_post_meta( $course_id, \TUTOR\Course::COURSE_PRICE_META, $course_data['price'] );
			if ( $course_data['sale_price'] > 0 ) {
				update_post_meta( $course_id, \TUTOR\Course::COURSE_SALE_PRICE_META, $course_data['sale_price'] );
			}
			update_post_meta( $course_id, \TUTOR\Course::COURSE_SELLING_OPTION_META, \TUTOR\Course::SELLING_OPTION_ONE_TIME );
		}

		// Add a topic.
		$topic_id = wp_insert_post(
			array(
				'post_title'  => $course_data['title'] . ' - Introduction',
				'post_status' => 'publish',
				'post_type'   => tutor()->topics_post_type,
				'post_parent' => $course_id,
				'post_author' => $author_id,
			)
		);

		// Add a lesson under topic.
		if ( $topic_id && ! is_wp_error( $topic_id ) ) {
			wp_insert_post(
				array(
					'post_title'  => 'Lesson 1: Overview',
					'post_status' => 'publish',
					'post_type'   => tutor()->lesson_post_type,
					'post_parent' => $topic_id,
					'post_author' => $author_id,
				)
			);
		}
	}
}

// 6. Initialize rewrite rules for clean permalinks.
do_action( 'init' );
if ( isset( $GLOBALS['wp_rewrite'] ) && is_object( $GLOBALS['wp_rewrite'] ) ) {
	$GLOBALS['wp_rewrite']->init();
	$GLOBALS['wp_rewrite']->flush_rules();
}

$elapsed = round( microtime( true ) - $start_time, 2 );
echo "==> [SUCCESS] Fresh test database '{$wpdb->dbname}' initialized in {$elapsed}s!\n";
echo "    - WordPress core installed\n";
echo "    - Tutor LMS tables, initial pages (including Cart & Checkout) created\n";
echo "    - Native Monetization enabled with Manual Payment (Bank Transfer)\n";
echo "    - Test accounts: admin ({$admin_user}/{$admin_pass}), instructor (instructor/12345), student (student/12345)\n";
echo "    - Fixtures: 4 published courses (1 free, 3 paid: Mastering React 19, Advanced TypeScript Guide, WordPress Playwright E2E Course)\n";

