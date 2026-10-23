<?php
/**
 * Test Database Reset and Seeder using WordPress APIs.
 *
 * Resets the dedicated test database (local_test) to a fresh, clean state
 * with standard users and baseline fixtures using native WordPress functions.
 *
 * Usage:
 *   php tests/bin/reset-test-db.php
 *
 * @package Tutor\Tests
 */

// Tell wp-config.php to connect to local_test
putenv( 'WP_TEST_ENV=1' );
if ( ! defined( 'WP_RUNNING_TESTS' ) ) {
	define( 'WP_RUNNING_TESTS', true );
}

// Suppress extraneous output during bootstrap
ob_start();

// Dynamically find wp-load.php by walking up directory tree
$wp_load_path = null;
$search_dir   = __DIR__;
while ( $search_dir && '/' !== $search_dir ) {
	if ( file_exists( $search_dir . '/wp-load.php' ) ) {
		$wp_load_path = $search_dir . '/wp-load.php';
		break;
	}
	$search_dir = dirname( $search_dir );
}

if ( ! $wp_load_path ) {
	ob_end_clean();
	fwrite( STDERR, "[FATAL ERROR] Could not find wp-load.php in parent directories!\n" );
	exit( 1 );
}

require_once $wp_load_path;
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
		$wpdb->query( "DROP TABLE IF EXISTS `{$table}`" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}
	$wpdb->query( 'SET FOREIGN_KEY_CHECKS = 1' );
}

// 2. Install fresh WordPress core
echo "==> Installing fresh WordPress core...\n";
require_once ABSPATH . 'wp-admin/includes/upgrade.php';

$admin_user = getenv( 'ADMIN_USERNAME' ) ?: 'admin';
$admin_pass = getenv( 'ADMIN_PASSWORD' ) ?: '12345';
$site_title = 'Tutor LMS Test Site';
$site_url   = getenv( 'WP_BASE_URL' ) ?: ( getenv( 'CYPRESS_base_url' ) ?: 'https://tutor-dev.local' );

wp_install( $site_title, $admin_user, 'admin@example.org', true, '', $admin_pass );

// Ensure correct siteurl, home, permalinks, and active plugins
update_option( 'siteurl', $site_url );
update_option( 'home', $site_url );
update_option( 'permalink_structure', '/%postname%/' );
update_option( 'active_plugins', array( 'tutor/tutor.php' ) );

// 3. Create Tutor custom tables, default options, roles, and initial pages
echo "==> Initializing Tutor LMS tables, default options, and pages...\n";
if ( class_exists( 'TUTOR\Tutor' ) ) {
	TUTOR\Tutor::create_database();
	TUTOR\Tutor::manage_tutor_roles_and_permissions();
	TUTOR\Tutor::save_data();
	update_option( 'tutor_option', TUTOR\Tutor::default_options() );
	update_option( 'tutor_welcome_page_visited', true );
	update_option( 'tutor_wizard', true );
	update_option( 'tutor-new-feature', defined( 'TUTOR_VERSION' ) ? TUTOR_VERSION : '4.2.0' );
	update_option( 'tutor_first_activation_time', time() );
}

// 4. Seed standard test users
echo "==> Seeding test users (instructor, student)...\n";
// Create instructor
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

// 5. Seed baseline course fixture
echo "==> Seeding baseline course fixture...\n";
$admin     = get_user_by( 'login', $admin_user );
$author_id = $admin ? $admin->ID : 1;

$course_id = wp_insert_post(
	array(
		'post_title'   => 'Sample Test Course',
		'post_content' => 'Sample test course description for E2E testing.',
		'post_status'  => 'publish',
		'post_type'    => tutor()->course_post_type,
		'post_author'  => $author_id,
	)
);

if ( $course_id && ! is_wp_error( $course_id ) ) {
	// Add a topic
	$topic_id = wp_insert_post(
		array(
			'post_title'  => 'Introduction Topic',
			'post_status' => 'publish',
			'post_type'   => tutor()->topics_post_type,
			'post_parent' => $course_id,
			'post_author' => $author_id,
		)
	);

	// Add a lesson under topic
	if ( $topic_id && ! is_wp_error( $topic_id ) ) {
		wp_insert_post(
			array(
				'post_title'  => 'Lesson 1: Getting Started',
				'post_status' => 'publish',
				'post_type'   => tutor()->lesson_post_type,
				'post_parent' => $topic_id,
				'post_author' => $author_id,
			)
		);
	}
}

$elapsed = round( microtime( true ) - $start_time, 2 );
echo "==> [SUCCESS] Fresh test database '{$wpdb->dbname}' initialized in {$elapsed}s!\n";
echo "    - WordPress core installed\n";
echo "    - Tutor LMS tables & initial pages created\n";
echo "    - Test accounts: admin ({$admin_user}/{$admin_pass}), instructor (instructor/12345), student (student/12345)\n";
echo "    - Fixtures: 1 published course with topic and lesson\n";
