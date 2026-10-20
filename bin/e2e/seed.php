<?php
/**
 * Minimal, idempotent seed for the Playwright E2E environment.
 *
 * Creates only users, Tutor pages, and settings. Courses, lessons, enrollments,
 * and orders are created per spec by bin/e2e/factories.php.
 *
 * Run with: wp eval-file wp-content/plugins/<slug>/bin/e2e/seed.php
 *
 * @package Tutor
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 1 );
}

/**
 * Fail the seed loudly instead of leaving a half-configured site behind.
 *
 * @param bool   $condition Condition that must be true.
 * @param string $message Error message.
 */
function e2e_assert( $condition, $message ) {
	if ( ! $condition ) {
		WP_CLI::error( $message );
	}
}

e2e_assert( function_exists( 'tutor_utils' ), 'Tutor is not active.' );

// Ensure Anyone Can Register is on so admin notices are suppressed.
update_option( 'users_can_register', 1 );

$users = array(
	'admin'      => array(
		'role'         => 'administrator',
		'email'        => 'admin@example.test',
		'display_name' => 'E2E Admin',
	),
	'instructor' => array(
		'role'         => 'tutor_instructor',
		'email'        => 'instructor@example.test',
		'display_name' => 'E2E Instructor',
	),
	'student'    => array(
		'role'         => 'subscriber',
		'email'        => 'student@example.test',
		'display_name' => 'E2E Student',
	),
	'student2'   => array(
		'role'         => 'subscriber',
		'email'        => 'student2@example.test',
		'display_name' => 'E2E Student Two',
	),
);

// Rebuild the role on every seed so the publish capability is present even when
// the environment is reused without a database reset.
update_tutor_option( 'instructor_can_publish_course', 'on' );
\TUTOR\Tutor::manage_tutor_roles_and_permissions();

$instructor_role = get_role( tutor()->instructor_role );
if ( $instructor_role ) {
	$instructor_role->add_cap( 'manage_tutor_instructor' );
	$instructor_role->add_cap( 'publish_tutor_courses' );
}

foreach ( $users as $login => $data ) {
	$user = get_user_by( 'login', $login );

	if ( $user ) {
		$user_id = wp_update_user(
			array(
				'ID'           => $user->ID,
				'user_pass'    => 'password',
				'user_email'   => $data['email'],
				'display_name' => $data['display_name'],
				'role'         => $data['role'],
			)
		);
	} else {
		$user_id = wp_insert_user(
			array(
				'user_login'   => $login,
				'user_pass'    => 'password',
				'user_email'   => $data['email'],
				'display_name' => $data['display_name'],
				'role'         => $data['role'],
			)
		);
	}

	e2e_assert( ! is_wp_error( $user_id ), sprintf( 'Could not create user %s: %s', $login, is_wp_error( $user_id ) ? $user_id->get_error_message() : '' ) );

	if ( 'tutor_instructor' === $data['role'] ) {
		update_user_meta( $user_id, '_is_tutor_instructor', tutor_time() );
		update_user_meta( $user_id, '_tutor_instructor_status', 'approved' );
		update_user_meta( $user_id, '_tutor_instructor_approved', tutor_time() );
	}
}

update_tutor_option( 'monetize_by', 'tutor' );
update_tutor_option( 'is_enable_buy_now', 'on' );
update_tutor_option( 'is_enable_guest_checkout', 'off' );
update_tutor_option( 'enable_tutor_native_login', 'on' );
update_tutor_option( 'instructor_can_publish_course', 'on' );
update_tutor_option( 'hide_admin_bar_for_users', 'off' );
update_tutor_option( 'is_coupon_applicable', 'off' );
update_tutor_option( 'is_tax_applicable', 'off' );
update_tutor_option( 'currency_code', 'USD' );
update_tutor_option( 'currency_position', 'left' );
update_tutor_option( 'thousand_separator', ',' );
update_tutor_option( 'decimal_separator', '.' );
update_tutor_option( 'number_of_decimals', '2' );

$page_contents = array(
	'tutor_dashboard_page_id'  => '[tutor_dashboard]',
	'instructor_register_page' => '[tutor_instructor_registration_form]',
	'student_register_page'    => '[tutor_student_registration_form]',
	'tutor_cart_page_id'       => '[tutor_cart]',
	'tutor_checkout_page_id'   => '[tutor_checkout]',
);

foreach ( tutor_utils()->tutor_pages() as $tutor_page ) {
	$option_key = $tutor_page['option_key'];
	$page_id    = (int) $tutor_page['page_id'];
	$content    = $page_contents[ $option_key ] ?? '';

	if ( $page_id ) {
		wp_update_post(
			array(
				'ID'           => $page_id,
				'post_title'   => $tutor_page['page_name'],
				'post_content' => $content,
				'post_status'  => 'publish',
			)
		);
	} else {
		$page_id = wp_insert_post(
			array(
				'post_title'   => $tutor_page['page_name'],
				'post_content' => $content,
				'post_type'    => 'page',
				'post_status'  => 'publish',
			)
		);
		e2e_assert( $page_id && ! is_wp_error( $page_id ), sprintf( 'Could not create page %s.', $tutor_page['page_name'] ) );
		update_tutor_option( $option_key, $page_id );
	}
}

$payment_settings = array(
	'payment_methods' => array(
		array(
			'name'                 => 'bank-transfer',
			'label'                => 'Bank Transfer',
			'is_active'            => true,
			'icon'                 => '',
			'support_subscription' => false,
			'update_available'     => false,
			'is_manual'            => true,
			'fields'               => array(
				array(
					'name'  => 'method_name',
					'value' => 'Bank Transfer',
				),
				array(
					'name'  => 'icon',
					'value' => '',
				),
				array(
					'name'  => 'payment_instructions',
					'value' => '<p>Pay by bank transfer.</p>',
				),
			),
		),
	),
);

update_tutor_option( 'payment_settings', wp_json_encode( $payment_settings ) );

e2e_assert( tutor_utils()->is_monetize_by_tutor(), 'Native Tutor ecommerce is not enabled.' );
e2e_assert( (int) get_tutor_option( 'tutor_dashboard_page_id' ) > 0, 'Tutor dashboard page is missing.' );
e2e_assert( (int) get_tutor_option( 'tutor_checkout_page_id' ) > 0, 'Tutor checkout page is missing.' );

$settings = \Tutor\Ecommerce\Settings::get_payment_settings();
e2e_assert( ! empty( $settings['payment_methods'] ), 'Manual payment settings were not stored.' );

WP_CLI::success( 'E2E seed complete: users, Tutor pages, native checkout, and manual payment are ready.' );
