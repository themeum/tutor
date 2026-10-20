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
	exit( 1 );
}

$args    = $args ?? array();
$payload = json_decode( base64_decode( $args[0] ?? '' ), true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode

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
	$title          = $input['title'] ?? ( 'E2E Course ' . wp_generate_password( 6, false ) );
	$price          = $input['price'] ?? 0;
	$price_type     = $input['price_type'] ?? ( ! empty( $price ) ? 'paid' : 'free' );
	$author_user    = $input['author'] ?? 'instructor';
	$lesson_title   = $input['lessonTitle'] ?? ( $input['lesson_title'] ?? 'First Lesson' );
	$lesson_content = $input['lessonContent'] ?? ( $input['lesson_content'] ?? ( '<p>Secret lesson text for ' . $title . '</p>' ) );

	$author = get_user_by( 'login', $author_user );
	if ( ! $author ) {
		WP_CLI::error( sprintf( 'Author user "%s" not found.', $author_user ) );
	}

	$post_data = array(
		'post_title'   => $title,
		'post_content' => 'Course description for ' . $title,
		'post_type'    => tutor()->course_post_type,
		'post_status'  => $input['status'] ?? 'publish',
		'post_author'  => $author->ID,
	);

	if ( ! empty( $input['password'] ) ) {
		$post_data['post_password'] = (string) $input['password'];
	}

	$course_id = wp_insert_post( $post_data );

	if ( is_wp_error( $course_id ) || ! $course_id ) {
		WP_CLI::error( 'Could not create course: ' . ( is_wp_error( $course_id ) ? $course_id->get_error_message() : '' ) );
	}

	update_post_meta( $course_id, '_tutor_course_price_type', $price_type );
	if ( 'paid' === $price_type ) {
		update_post_meta( $course_id, 'tutor_course_price', (float) $price );
		update_post_meta( $course_id, '_tutor_course_price', (float) $price );
	}

	if ( ! empty( $input['is_public'] ) ) {
		update_post_meta( $course_id, '_tutor_is_public_course', 'yes' );
	}

	if ( ! empty( $input['product_id'] ) ) {
		update_post_meta( $course_id, '_tutor_course_product_id', (int) $input['product_id'] );
	}

	if ( ! empty( $input['prerequisites'] ) && is_array( $input['prerequisites'] ) ) {
		update_post_meta( $course_id, '_tutor_course_prerequisites_ids', array_map( 'intval', $input['prerequisites'] ) );
	}

	if ( ! empty( $input['selling_option'] ) ) {
		update_post_meta( $course_id, '_tutor_course_selling_option', (string) $input['selling_option'] );
		update_post_meta( $course_id, 'tutor_course_selling_option', (string) $input['selling_option'] );
	}

	$topic_id = wp_insert_post(
		array(
			'post_title'  => 'First Topic',
			'post_type'   => 'topics',
			'post_status' => 'publish',
			'post_author' => $author->ID,
			'post_parent' => $course_id,
		)
	);

	if ( is_wp_error( $topic_id ) || ! $topic_id ) {
		WP_CLI::error( 'Could not create topic.' );
	}

	$lesson_id = wp_insert_post(
		array(
			'post_title'   => $lesson_title,
			'post_content' => $lesson_content,
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

	if ( ! empty( $input['content_drip'] ) ) {
		$course_settings = array(
			'enable_content_drip' => 1,
			'content_drip_type'   => $input['content_drip_type'] ?? 'unlock_by_date',
		);
		update_post_meta( $course_id, '_tutor_course_settings', $course_settings );
		update_post_meta( $lesson_id, '_content_drip_settings', $input['content_drip'] );
	}

	$quiz_id = 0;
	if ( ! empty( $input['add_quiz'] ) ) {
		$quiz_id = wp_insert_post(
			array(
				'post_title'   => $input['quiz_title'] ?? 'Sample Quiz',
				'post_content' => 'Quiz description for ' . $title,
				'post_type'    => tutor()->quiz_post_type,
				'post_status'  => 'publish',
				'post_author'  => $author->ID,
				'post_parent'  => $topic_id,
			)
		);
		if ( ! is_wp_error( $quiz_id ) && $quiz_id ) {
			update_post_meta( $quiz_id, '_tutor_course_id_for_quiz', $course_id );
			update_post_meta( $quiz_id, '_tutor_topic_id_for_quiz', $topic_id );
		}
	}

	$assignment_id = 0;
	if ( ! empty( $input['add_assignment'] ) ) {
		$assignment_id = wp_insert_post(
			array(
				'post_title'   => $input['assignment_title'] ?? 'Sample Assignment',
				'post_content' => 'Assignment description for ' . $title,
				'post_type'    => 'tutor_assignments',
				'post_status'  => 'publish',
				'post_author'  => $author->ID,
				'post_parent'  => $topic_id,
			)
		);
		if ( ! is_wp_error( $assignment_id ) && $assignment_id ) {
			update_post_meta( $assignment_id, '_tutor_course_id_for_assignment', $course_id );
			update_post_meta( $assignment_id, '_tutor_topic_id_for_assignment', $topic_id );
			update_post_meta( $assignment_id, 'total_mark', 100 );
			update_post_meta( $assignment_id, 'pass_mark', 50 );
		}
	}

	$plan_id = 0;
	if ( ! empty( $input['subscription_plan'] ) && class_exists( 'TutorPro\Subscription\Models\PlanModel' ) ) {
		$addons = get_option( 'tutor_addons_config', array() );
		$addons['tutor-pro/addons/subscription/subscription.php'] = array( 'is_enable' => 1 );
		update_option( 'tutor_addons_config', $addons );

		$plan_model = new \TutorPro\Subscription\Models\PlanModel();
		$plan_opts  = is_array( $input['subscription_plan'] ) ? $input['subscription_plan'] : array();
		$plan_data  = array_merge(
			array(
				'plan_name'          => 'Pro Monthly Plan ' . $title,
				'payment_type'       => 'recurring',
				'plan_type'          => 'course',
				'regular_price'      => 29.00,
				'recurring_value'    => 1,
				'recurring_interval' => 'month',
				'is_enabled'         => 1,
			),
			$plan_opts
		);
		$plan_id = $plan_model->create_subscription_plan( $course_id, $plan_data );
		update_post_meta( $course_id, 'tutor_course_selling_option', 'subscription' );
		update_post_meta( $course_id, '_tutor_course_selling_option', 'subscription' );
		update_post_meta( $course_id, '_tutor_course_price_type', 'paid' );
		update_post_meta( $course_id, '_tutor_course_price', (float) $plan_data['regular_price'] );
		update_post_meta( $course_id, 'tutor_course_price', (float) $plan_data['regular_price'] );
	}

	return array(
		'courseId'      => (int) $course_id,
		'url'           => get_permalink( $course_id ),
		'courseUrl'     => get_permalink( $course_id ),
		'courseSlug'    => get_post_field( 'post_name', $course_id ),
		'topicId'       => (int) $topic_id,
		'lessonId'      => (int) $lesson_id,
		'lessonUrl'     => get_permalink( $lesson_id ),
		'quizId'        => (int) $quiz_id,
		'quizUrl'       => $quiz_id ? get_permalink( $quiz_id ) : '',
		'assignmentId'  => (int) $assignment_id,
		'assignmentUrl' => $assignment_id ? get_permalink( $assignment_id ) : '',
		'planId'        => (int) $plan_id,
		'title'         => $title,
	);
}

/**
 * Enroll a user into a course using Tutor's native enrollment API.
 *
 * @param array $input Factory input.
 * @return array
 */
function e2e_factory_enrollment( array $input ): array {
	$course_id  = (int) ( $input['course_id'] ?? $input['courseId'] ?? 0 );
	$user_login = $input['user'] ?? 'student';

	$user = get_user_by( 'login', $user_login );
	if ( ! $user ) {
		WP_CLI::error( sprintf( 'User "%s" not found for enrollment.', $user_login ) );
	}

	$course = get_post( $course_id );
	if ( ! $course || tutor()->course_post_type !== $course->post_type ) {
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

/**
 * Create a simple WooCommerce product for courses.
 *
 * @param array $input Factory input.
 * @return array
 */
function e2e_factory_wc_product( array $input ): array {
	if ( ! class_exists( 'WC_Product_Simple' ) ) {
		WP_CLI::error( 'WooCommerce is not active.' );
	}

	$product = new \WC_Product_Simple();
	$product->set_name( $input['title'] ?? ( 'E2E Course Product ' . wp_generate_password( 4, false ) ) );
	$product->set_status( 'publish' );
	$product->set_regular_price( (string) ( $input['price'] ?? 59 ) );
	$product_id = $product->save();

	if ( ! $product_id ) {
		WP_CLI::error( 'Could not create WooCommerce product.' );
	}

	return array(
		'productId' => (int) $product_id,
		'title'     => $product->get_name(),
		'price'     => (float) $product->get_price(),
	);
}

function e2e_factory_set_option( array $input ): array {
	$option_name = (string) ( $input['option_name'] ?? 'tutor_option' );
	$key         = (string) ( $input['key'] ?? '' );
	$value       = $input['value'] ?? null;

	if ( 'tutor_option' === $option_name && $key ) {
		$options         = get_option( 'tutor_option', array() );
		$previous_value  = $options[ $key ] ?? null;
		$options[ $key ] = $value;
		update_option( 'tutor_option', $options );
		return array( 'success' => true, 'previous_value' => $previous_value );
	}

	$previous_value = get_option( $option_name );
	update_option( $option_name, $value );
	return array( 'success' => true, 'previous_value' => $previous_value );
}

$result = array();

switch ( $factory ) {
	case 'course':
		$result = e2e_factory_course( $payload );
		break;
	case 'enrollment':
		$result = e2e_factory_enrollment( $payload );
		break;
	case 'wc_product':
		$result = e2e_factory_wc_product( $payload );
		break;
	case 'set_option':
		$result = e2e_factory_set_option( $payload );
		break;
	default:
		WP_CLI::error( sprintf( 'Unknown factory "%s".', $factory ) );
}

$result_dir = WP_CONTENT_DIR . '/e2e';
wp_mkdir_p( $result_dir );
file_put_contents( $result_dir . '/' . $result_file, wp_json_encode( $result ) );
WP_CLI::success( sprintf( 'Factory %s completed.', $factory ) );
