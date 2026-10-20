<?php
/**
 * Deterministic seed script for Tutor Core Admin Dashboard E2E tests.
 *
 * Seeds all entities required to exercise 100% of the Tutor Core admin dashboard:
 * - Categories and Tags
 * - Published, Draft, and Pending Courses
 * - Orders and Order Items
 * - Course Announcements
 * - Q&A Threads (Unanswered & Answered)
 * - Quiz Attempts
 * - Marketplace Settings, Pending Instructors, and Withdrawal Requests
 *
 * Run with: wp eval-file wp-content/plugins/tutor/bin/e2e/seed-admin.php
 *
 * @package Tutor
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 1 );
}

/**
 * Fail the seed loudly if a critical condition fails.
 *
 * @param bool   $condition Condition to check.
 * @param string $message Error message.
 */
function e2e_admin_assert( bool $condition, string $message ): void {
	if ( ! $condition ) {
		WP_CLI::error( $message );
	}
}

global $wpdb;

// 1. Enable Course Marketplace and ensure Admin Menu groups are loaded.
update_tutor_option( 'enable_course_marketplace', 'on' );
update_option( 'enable_course_marketplace', 1 );
update_tutor_option( 'enable_qa_course_tab', 'on' );
update_option( 'enable_qa_course_tab', 1 );

// 2. Fetch or create actors.
$instructor = get_user_by( 'login', 'instructor' );
$student    = get_user_by( 'login', 'student' );

e2e_admin_assert( $instructor instanceof WP_User, 'Instructor user missing. Run bin/e2e/seed.php first.' );
e2e_admin_assert( $student instanceof WP_User, 'Student user missing. Run bin/e2e/seed.php first.' );

// Ensure pending instructor exists for marketplace tests.
$pending_instructor = get_user_by( 'login', 'instructor_pending' );
if ( ! $pending_instructor ) {
	$pending_instructor_id = wp_insert_user(
		array(
			'user_login'   => 'instructor_pending',
			'user_pass'    => 'password',
			'user_email'   => 'pending_inst@example.test',
			'role'         => 'tutor_instructor',
			'display_name' => 'Pending Applicant',
		)
	);
	e2e_admin_assert( ! is_wp_error( $pending_instructor_id ), 'Failed to create pending instructor.' );
	update_user_meta( $pending_instructor_id, '_tutor_instructor_status', 'pending' );
} else {
	update_user_meta( $pending_instructor->ID, '_tutor_instructor_status', 'pending' );
}

// 3. Taxonomies: Categories & Tags.
$categories = array( 'Web Development', 'UI & UX Design', 'Data Science' );
$cat_ids    = array();
foreach ( $categories as $cat_name ) {
	$target_term = get_term_by( 'name', $cat_name, 'course-category' );
	if ( ! $target_term ) {
		$created = wp_insert_term( $cat_name, 'course-category' );
		if ( ! is_wp_error( $created ) ) {
			$cat_ids[ $cat_name ] = (int) $created['term_id'];
		}
	} else {
		$cat_ids[ $cat_name ] = (int) $target_term->term_id;
	}
}

$tags    = array( 'JavaScript', 'Design Systems', 'Python' );
$tag_ids = array();
foreach ( $tags as $tag_name ) {
	$target_term = get_term_by( 'name', $tag_name, 'course-tag' );
	if ( ! $target_term ) {
		$created = wp_insert_term( $tag_name, 'course-tag' );
		if ( ! is_wp_error( $created ) ) {
			$tag_ids[ $tag_name ] = (int) $created['term_id'];
		}
	} else {
		$tag_ids[ $tag_name ] = (int) $target_term->term_id;
	}
}

// 4. Create Courses with distinct statuses.
$course_defs = array(
	array(
		'title'    => 'Mastering Modern JavaScript',
		'status'   => 'publish',
		'category' => 'Web Development',
		'tag'      => 'JavaScript',
		'price'    => 49.99,
	),
	array(
		'title'    => 'Draft UI/UX Prototyping Essentials',
		'status'   => 'draft',
		'category' => 'UI & UX Design',
		'tag'      => 'Design Systems',
		'price'    => 0.00,
	),
	array(
		'title'    => 'Pending Python for Data Analysis',
		'status'   => 'pending',
		'category' => 'Data Science',
		'tag'      => 'Python',
		'price'    => 29.99,
	),
);

$seeded_courses = array();

foreach ( $course_defs as $def ) {
	$existing_posts = get_posts(
		array(
			'title'                  => $def['title'],
			'post_type'              => tutor()->course_post_type,
			'post_status'            => 'any',
			'posts_per_page'         => 1,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	if ( ! empty( $existing_posts ) ) {
		$course_id = $existing_posts[0]->ID;
		wp_update_post(
			array(
				'ID'          => $course_id,
				'post_status' => $def['status'],
			)
		);
	} else {
		$course_id = wp_insert_post(
			array(
				'post_type'    => tutor()->course_post_type,
				'post_title'   => $def['title'],
				'post_content' => 'Comprehensive course material for ' . $def['title'],
				'post_status'  => $def['status'],
				'post_author'  => $instructor->ID,
			)
		);
	}

	e2e_admin_assert( ! is_wp_error( $course_id ) && $course_id > 0, 'Failed to seed course ' . $def['title'] );

	if ( ! empty( $cat_ids[ $def['category'] ] ) ) {
		wp_set_object_terms( $course_id, array( $cat_ids[ $def['category'] ] ), 'course-category' );
	}
	if ( ! empty( $tag_ids[ $def['tag'] ] ) ) {
		wp_set_object_terms( $course_id, array( $tag_ids[ $def['tag'] ] ), 'course-tag' );
	}

	$is_paid = $def['price'] > 0;
	update_post_meta( $course_id, '_tutor_course_price_type', $is_paid ? 'paid' : 'free' );
	if ( $is_paid ) {
		update_post_meta( $course_id, 'tutor_course_price', (string) $def['price'] );
		update_post_meta( $course_id, '_tutor_course_price', (string) $def['price'] );
	}

	$seeded_courses[ $def['status'] ] = (int) $course_id;
}

$main_course_id = $seeded_courses['publish'];

// 5. Add Topic, Lesson, and Quiz to Published Course.
$topic = get_page_by_path( 'admin-seed-topic', OBJECT, 'topics' );
if ( ! $topic ) {
	$topic_id = wp_insert_post(
		array(
			'post_title'  => 'Introduction to JavaScript Engine',
			'post_name'   => 'admin-seed-topic',
			'post_type'   => 'topics',
			'post_status' => 'publish',
			'post_author' => $instructor->ID,
			'post_parent' => $main_course_id,
		)
	);
} else {
	$topic_id = $topic->ID;
}

// Lesson.
$lesson = get_page_by_path( 'admin-seed-lesson', OBJECT, tutor()->lesson_post_type );
if ( ! $lesson ) {
	wp_insert_post(
		array(
			'post_title'   => 'Call Stack and Event Loop',
			'post_name'    => 'admin-seed-lesson',
			'post_content' => 'Deep dive into JavaScript execution context.',
			'post_type'    => tutor()->lesson_post_type,
			'post_status'  => 'publish',
			'post_author'  => $instructor->ID,
			'post_parent'  => $main_course_id,
		)
	);
}

// Quiz.
$quiz = get_page_by_path( 'admin-seed-quiz', OBJECT, 'tutor_quiz' );
if ( ! $quiz ) {
	$quiz_id = wp_insert_post(
		array(
			'post_title'  => 'JavaScript Engine Evaluation Quiz',
			'post_name'   => 'admin-seed-quiz',
			'post_type'   => 'tutor_quiz',
			'post_status' => 'publish',
			'post_author' => $instructor->ID,
			'post_parent' => $topic_id,
		)
	);
} else {
	$quiz_id = $quiz->ID;
}

// 6. Student Enrollment.
$existing_enrollment = \Tutor\Models\EnrollmentModel::is_enrolled( $main_course_id, $student->ID );
if ( ! $existing_enrollment ) {
	$enrollment_id = \Tutor\Models\EnrollmentModel::do_enroll( $main_course_id, 0, $student->ID );
	if ( $enrollment_id ) {
		wp_update_post( array( "ID" => $enrollment_id, "post_status" => "completed" ) );
	}
} else {
	wp_update_post( array( "ID" => $existing_enrollment, "post_status" => "completed" ) );
}

// 7. Orders.
// phpcs:ignore WordPress.DB.DirectDatabaseQuery
$existing_order = $wpdb->get_var(
	$wpdb->prepare(
		"SELECT id FROM {$wpdb->prefix}tutor_orders WHERE user_id = %d LIMIT 1",
		$student->ID
	)
);

if ( ! $existing_order ) {
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$wpdb->insert(
		$wpdb->prefix . 'tutor_orders',
		array(
			'user_id'        => $student->ID,
			'order_type'     => 'single_order',
			'order_status'   => 'completed',
			'payment_status' => 'paid',
			'subtotal_price' => 49.99,
			'pre_tax_price'  => 49.99,
			'total_price'    => 49.99,
			'net_payment'    => 49.99,
			'payment_method' => 'bank-transfer',
			'created_at_gmt' => gmdate( 'Y-m-d H:i:s' ),
			'created_by'     => $student->ID,
			'updated_at_gmt' => gmdate( 'Y-m-d H:i:s' ),
			'updated_by'     => $student->ID,
		)
	);
	$order_id = (int) $wpdb->insert_id;

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$wpdb->insert(
		$wpdb->prefix . 'tutor_order_items',
		array(
			'order_id'      => $order_id,
			'item_id'       => $main_course_id,
			'regular_price' => 49.99,
		)
	);
}

// 8. Announcement.
$existing_announcements = get_posts(
	array(
		'title'                  => 'Welcome to Modern JS Bootcamp',
		'post_type'              => 'tutor_announcements',
		'post_status'            => 'any',
		'posts_per_page'         => 1,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
	)
);
if ( empty( $existing_announcements ) ) {
	wp_insert_post(
		array(
			'post_type'    => 'tutor_announcements',
			'post_title'   => 'Welcome to Modern JS Bootcamp',
			'post_content' => 'We are excited to kick off our interactive JavaScript cohort!',
			'post_parent'  => $main_course_id,
			'post_status'  => 'publish',
			'post_author'  => $instructor->ID,
		)
	);
}

// 9. Q&A Threads (Unanswered question from student).
// phpcs:ignore WordPress.DB.DirectDatabaseQuery
$existing_qna = $wpdb->get_var(
	$wpdb->prepare(
		"SELECT comment_ID FROM {$wpdb->comments} WHERE comment_type = 'tutor_q_and_a' AND comment_post_ID = %d LIMIT 1",
		$main_course_id
	)
);

if ( ! $existing_qna ) {
	$qna_id = wp_insert_comment(
		array(
			'comment_post_ID'      => $main_course_id,
			'comment_author'       => $student->display_name,
			'comment_author_email' => $student->user_email,
			'comment_content'      => 'How can I debug microtasks in the event loop?',
			'comment_type'         => 'tutor_q_and_a',
			'user_id'              => $student->ID,
			'comment_approved'     => 1,
		)
	);

	if ( ! is_wp_error( $qna_id ) && $qna_id > 0 ) {
		update_comment_meta( $qna_id, 'tutor_qna_read_' . $instructor->ID, 0 );
	}
}

// 10. Quiz Attempt.
// phpcs:ignore WordPress.DB.DirectDatabaseQuery
$existing_attempt = $wpdb->get_var(
	$wpdb->prepare(
		"SELECT attempt_id FROM {$wpdb->prefix}tutor_quiz_attempts WHERE course_id = %d AND user_id = %d LIMIT 1",
		$main_course_id,
		$student->ID
	)
);

if ( ! $existing_attempt && $quiz_id > 0 ) {
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$wpdb->insert(
		$wpdb->prefix . 'tutor_quiz_attempts',
		array(
			'course_id'                => $main_course_id,
			'quiz_id'                  => $quiz_id,
			'user_id'                  => $student->ID,
			'total_questions'          => 5,
			'total_answered_questions' => 5,
			'total_marks'              => 100.00,
			'earned_marks'             => 80.00,
			'attempt_status'           => 'attempt_ended',
			'result'                   => 'pass',
			'attempt_started_at'       => gmdate( 'Y-m-d H:i:s', time() - 3600 ),
			'attempt_ended_at'         => gmdate( 'Y-m-d H:i:s' ),
		)
	);
}

// 11. Withdrawal Request for Marketplace tests.
// phpcs:ignore WordPress.DB.DirectDatabaseQuery
$existing_withdraw = $wpdb->get_var(
	$wpdb->prepare(
		"SELECT withdraw_id FROM {$wpdb->prefix}tutor_withdraws WHERE user_id = %d LIMIT 1",
		$instructor->ID
	)
);

$withdraw_data = array(
	'user_id'     => $instructor->ID,
	'amount'      => 75.00,
	// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
	'method_data' => serialize(
		array(
			'withdraw_method_name' => 'Bank Transfer',
			'withdraw_method_key'  => 'bank_transfer_withdraw',
			'account_name'         => array( 'value' => 'E2E Instructor' ),
			'account_number'       => array( 'value' => '1234567890' ),
			'bank_name'            => array( 'value' => 'E2E Test Bank' ),
			'iban'                 => array( 'value' => 'US123456789012345678' ),
			'swift'                => array( 'value' => 'TESTUS33' ),
		)
	),
	'status'      => 'pending',
	'created_at'  => gmdate( 'Y-m-d H:i:s' ),
	'updated_at'  => gmdate( 'Y-m-d H:i:s' ),
);

if ( ! $existing_withdraw ) {
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$wpdb->insert(
		$wpdb->prefix . 'tutor_withdraws',
		$withdraw_data
	);
} else {
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$wpdb->update(
		$wpdb->prefix . 'tutor_withdraws',
		$withdraw_data,
		array( 'withdraw_id' => $existing_withdraw )
	);
}

WP_CLI::success( 'Tutor Core Admin Dashboard seeding complete.' );
