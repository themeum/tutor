<?php
/**
 * Per-spec data factories for the Playwright E2E suite.
 *
 * The Playwright support layer calls this through `wp eval-file` and reads a
 * JSON result from the `.e2e` mapping, which avoids parsing wp-env output.
 *
 * @package Tutor
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	fwrite( STDERR, "This script must run through WP-CLI.\n" );
	exit( 1 );
}

if ( false ) {
	/**
	 * Stub for static analysis when WP-CLI is not present in autoload.
	 */
	class WP_CLI {
		/**
		 * @param string $message Error message.
		 * @return void
		 */
		public static function error( $message ) {}

		/**
		 * @param string $message Success message.
		 * @return void
		 */
		public static function success( $message ) {}

		/**
		 * @param string $message Line message.
		 * @return void
		 */
		public static function line( $message ) {}
	}
}

$args    = $args ?? array();
$payload = json_decode( base64_decode( $args[0] ?? '' ), true );

if ( ! is_array( $payload ) ) {
	WP_CLI::error( 'Invalid factory payload.' );
}

$factory     = $payload['factory'] ?? '';
$result_file = $payload['result_file'] ?? '';

if ( ! preg_match( '/^[a-f0-9-]{36}\.json$/', $result_file ) ) {
	WP_CLI::error( 'Invalid factory result file.' );
}

/**
 * Create a published course with one topic and one lesson.
 *
 * @param array $input Factory input.
 * @return array
 */
function e2e_factory_course( array $input ): array {
	$title       = $input['title'] ?? ( 'E2E Course ' . wp_generate_password( 6, false ) );
	$price_type  = $input['price_type'] ?? 'free';
	$price       = $input['price'] ?? 0;
	$author_user = $input['author'] ?? 'instructor';

	$author = get_user_by( 'login', $author_user );
	if ( ! $author ) {
		WP_CLI::error( sprintf( 'Author user "%s" not found.', $author_user ) );
	}

	$course_id = wp_insert_post(
		array(
			'post_title'   => $title,
			'post_content' => 'Course description for ' . $title,
			'post_type'    => tutor()->course_post_type,
			'post_status'  => 'publish',
			'post_author'  => $author->ID,
		)
	);

	if ( is_wp_error( $course_id ) || ! $course_id ) {
		WP_CLI::error( 'Could not create course: ' . ( is_wp_error( $course_id ) ? $course_id->get_error_message() : '' ) );
	}

	update_post_meta( $course_id, '_tutor_course_price_type', $price_type );
	if ( 'paid' === $price_type ) {
		update_post_meta( $course_id, '_tutor_course_price', (float) $price );
	}

	$topic_id = wp_insert_post(
		array(
			'post_title'   => 'First Topic',
			'post_type'    => 'topics',
			'post_status'  => 'publish',
			'post_author'  => $author->ID,
			'post_parent'  => $course_id,
		)
	);

	if ( is_wp_error( $topic_id ) || ! $topic_id ) {
		WP_CLI::error( 'Could not create topic.' );
	}

	$lesson_id = wp_insert_post(
		array(
			'post_title'   => 'First Lesson',
			'post_content' => '<p>Secret lesson text for ' . $title . '</p>',
			'post_type'    => tutor()->lesson_post_type,
			'post_status'  => 'publish',
			'post_author'  => $author->ID,
			'post_parent'  => $topic_id,
		)
	);

	if ( is_wp_error( $lesson_id ) || ! $lesson_id ) {
		WP_CLI::error( 'Could not create lesson.' );
	}

	update_post_meta( $lesson_id, '_tutor_course_id_for_lesson', $course_id );
	update_post_meta( $lesson_id, '_tutor_topic_id_for_lesson', $topic_id );

	return array(
		'courseId'   => (int) $course_id,
		'url'        => get_permalink( $course_id ),
		'courseUrl'  => get_permalink( $course_id ),
		'courseSlug' => get_post_field( 'post_name', $course_id ),
		'topicId'    => (int) $topic_id,
		'lessonId'   => (int) $lesson_id,
		'lessonUrl'  => get_permalink( $lesson_id ),
		'title'      => $title,
	);
}

/**
 * Enroll a user into a course using Tutor's native enrollment API.
 *
 * @param array $input Factory input.
 * @return array
 */
function e2e_factory_enrollment( array $input ): array {
	$course_id  = (int) ( $input['course_id'] ?? 0 );
	$user_login = $input['user'] ?? 'student';

	$user = get_user_by( 'login', $user_login );
	if ( ! $user ) {
		WP_CLI::error( sprintf( 'User "%s" not found for enrollment.', $user_login ) );
	}

	$course = get_post( $course_id );
	if ( ! $course || $course->post_type !== tutor()->course_post_type ) {
		WP_CLI::error( sprintf( 'Course ID %d not found.', $course_id ) );
	}

	$enrollment_id = \Tutor\Models\EnrollmentModel::do_enroll( $course_id, 0, $user->ID );
	if ( ! $enrollment_id ) {
		WP_CLI::error( sprintf( 'Could not enroll user %s into course %d.', $user_login, $course_id ) );
	}

	return array(
		'enrollmentId' => (int) $enrollment_id,
		'userId'       => (int) $user->ID,
		'courseId'     => $course_id,
	);
}

$result = array();

switch ( $factory ) {
	case 'course':
		$result = e2e_factory_course( $payload );
		break;
	case 'enrollment':
		$result = e2e_factory_enrollment( $payload );
		break;
	default:
		WP_CLI::error( sprintf( 'Unknown factory "%s".', $factory ) );
}

$result_dir = WP_CONTENT_DIR . '/e2e';
wp_mkdir_p( $result_dir );

$written = file_put_contents( $result_dir . '/' . $result_file, wp_json_encode( $result ) );
if ( false === $written ) {
	WP_CLI::error( 'Could not write the factory result.' );
}

WP_CLI::success( sprintf( 'Factory %s completed.', $factory ) );
