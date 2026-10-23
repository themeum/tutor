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

	if ( ! empty( $input['course_meta'] ) && is_array( $input['course_meta'] ) ) {
		foreach ( $input['course_meta'] as $meta_key => $meta_value ) {
			update_post_meta( $course_id, (string) $meta_key, $meta_value );
		}
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

	if ( ! empty( $input['lesson_meta'] ) && is_array( $input['lesson_meta'] ) ) {
		foreach ( $input['lesson_meta'] as $meta_key => $meta_value ) {
			update_post_meta( $lesson_id, (string) $meta_key, $meta_value );
		}
	}

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
		$plan_id    = $plan_model->create_subscription_plan( $course_id, $plan_data );
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
 * Create a lesson under an existing course/topic.
 *
 * @param array $input Factory input.
 * @return array
 */
function e2e_factory_lesson( array $input ): array {
	$course_id = (int) ( $input['course_id'] ?? 0 );
	$topic_id  = (int) ( $input['topic_id'] ?? 0 );
	$course    = get_post( $course_id );

	if ( ! $course || tutor()->course_post_type !== $course->post_type ) {
		WP_CLI::error( sprintf( 'Course ID %d not found.', $course_id ) );
	}

	if ( ! $topic_id ) {
		$topic_id = (int) get_post_meta( $course_id, '_tutor_course_topic_id', true );
	}

	if ( ! $topic_id ) {
		WP_CLI::error( 'A topic_id is required to create a lesson.' );
	}

	$title     = (string) ( $input['title'] ?? ( 'E2E Lesson ' . wp_generate_password( 6, false ) ) );
	$lesson_id = wp_insert_post(
		array(
			'post_title'   => $title,
			'post_content' => (string) ( $input['content'] ?? 'Lesson content for ' . $title ),
			'post_type'    => tutor()->lesson_post_type,
			'post_status'  => (string) ( $input['status'] ?? 'publish' ),
			'post_author'  => (int) $course->post_author,
			'post_parent'  => $topic_id,
		)
	);

	if ( is_wp_error( $lesson_id ) || ! $lesson_id ) {
		WP_CLI::error( 'Could not create lesson.' );
	}

	update_post_meta( $lesson_id, '_tutor_course_id_for_lesson', $course_id );
	update_post_meta( $lesson_id, '_tutor_topic_id_for_lesson', $topic_id );

	if ( ! empty( $input['meta'] ) && is_array( $input['meta'] ) ) {
		foreach ( $input['meta'] as $meta_key => $meta_value ) {
			update_post_meta( $lesson_id, (string) $meta_key, $meta_value );
		}
	}

	return array(
		'lessonId'  => (int) $lesson_id,
		'lessonUrl' => get_permalink( $lesson_id ),
		'title'     => $title,
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

/**
 * Enable a Tutor Pro add-on in tutor_addons_config option.
 *
 * @param array $input Factory input.
 * @return array
 */
function e2e_factory_enable_addon( array $input ): array {
	$addon_name    = (string) ( $input['name'] ?? '' );
	$addons_config = maybe_unserialize( get_option( 'tutor_addons_config', array() ) );
	if ( ! is_array( $addons_config ) ) {
		$addons_config = array();
	}
	$key                   = "tutor-pro/addons/{$addon_name}/{$addon_name}.php";
	$addons_config[ $key ] = array( 'is_enable' => 1 );
	update_option( 'tutor_addons_config', $addons_config );
	return array( 'success' => true );
}

/**
 * Return the whole add-on state map keyed by add-on basename.
 *
 * @param array $input Factory input.
 * @return array
 */
function e2e_factory_get_addon_states( array $input ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
	$config = maybe_unserialize( get_option( 'tutor_addons_config', array() ) );
	if ( ! is_array( $config ) ) {
		$config = array();
	}

	$states = array();
	foreach ( $config as $basename => $value ) {
		$states[ $basename ] = ! empty( $value['is_enable'] );
	}

	return array(
		'config' => $config,
		'states' => $states,
		'count'  => count( $config ),
	);
}

/**
 * Set one add-on state without firing enable/disable actions.
 *
 * This is for setup/cleanup and read-only role tests. The canonical lifecycle
 * tests must use the real Add-ons screen/AJAX path instead.
 *
 * @param array $input Factory input.
 * @return array
 */
function e2e_factory_set_addon_state( array $input ): array {
	$name    = (string) ( $input['name'] ?? $input['slug'] ?? '' );
	$enabled = ! empty( $input['enabled'] );

	if ( ! preg_match( '/^[a-z0-9-]+$/', $name ) ) {
		WP_CLI::error( 'Invalid add-on slug.' );
	}

	$key    = "tutor-pro/addons/{$name}/{$name}.php";
	$config = maybe_unserialize( get_option( 'tutor_addons_config', array() ) );
	if ( ! is_array( $config ) ) {
		$config = array();
	}

	$previous       = isset( $config[ $key ]['is_enable'] ) ? (int) $config[ $key ]['is_enable'] : null;
	$config[ $key ] = array_merge(
		is_array( $config[ $key ] ?? null ) ? $config[ $key ] : array(),
		array( 'is_enable' => $enabled ? 1 : 0 )
	);
	update_option( 'tutor_addons_config', $config );

	// Direct setup writes bypass the enable hooks that refresh rewrite rules.
	flush_rewrite_rules();

	return array(
		'name'     => $name,
		'key'      => $key,
		'previous' => $previous,
		'enabled'  => $enabled ? 1 : 0,
		'config'   => $config,
		'count'    => count( $config ),
	);
}

/**
 * Set a baseline for a list of add-ons, preserving unrelated keys.
 *
 * @param array $input Factory input.
 * @return array
 */
function e2e_factory_set_addon_baseline( array $input ): array {
	$enabled   = ! empty( $input['enabled'] );
	$basenames = $input['basenames'] ?? array();
	$except    = array_flip( $input['except'] ?? array() );

	if ( ! is_array( $basenames ) ) {
		WP_CLI::error( 'basenames must be an array.' );
	}

	$config = maybe_unserialize( get_option( 'tutor_addons_config', array() ) );
	if ( ! is_array( $config ) ) {
		$config = array();
	}

	foreach ( $basenames as $basename ) {
		if ( ! is_string( $basename ) || ! preg_match( '#^tutor-pro/addons/[a-z0-9-]+/[a-z0-9-]+\.php$#', $basename ) ) {
			WP_CLI::error( 'Invalid add-on basename.' );
		}
		if ( isset( $except[ $basename ] ) ) {
			continue;
		}
		$config[ $basename ] = array_merge(
			is_array( $config[ $basename ] ?? null ) ? $config[ $basename ] : array(),
			array( 'is_enable' => $enabled ? 1 : 0 )
		);
	}

	update_option( 'tutor_addons_config', $config );
	flush_rewrite_rules();

	return array(
		'success' => true,
		'count'   => count( $config ),
	);
}

/**
 * Capture post/comment/table/option counts used by the disable contract.
 *
 * @param array $input Factory input.
 * @return array
 */
function e2e_factory_snapshot( array $input ): array {
	global $wpdb;

	$post_types    = $input['postTypes'] ?? array();
	$tables        = $input['tables'] ?? array();
	$comment_types = $input['commentTypes'] ?? array();
	$option_names  = $input['options'] ?? array();
	$snapshot      = array(
		'postTypes'    => array(),
		'tables'       => array(),
		'commentTypes' => array(),
		'options'      => array(),
	);

	foreach ( (array) $post_types as $post_type ) {
		if ( ! preg_match( '/^[a-z0-9_-]+$/', (string) $post_type ) ) {
			continue;
		}
		// Count raw rows so a disabled add-on's unregistered post type still reports its data.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$snapshot['postTypes'][ $post_type ] = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s", $post_type )
		);
	}

	foreach ( (array) $tables as $table ) {
		if ( ! preg_match( '/^[a-z0-9_]+$/', (string) $table ) ) {
			continue;
		}
		$table_name = $wpdb->prefix . $table;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$snapshot['tables'][ $table ] = $exists ? (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" ) : null;
	}

	foreach ( (array) $comment_types as $comment_type ) {
		$snapshot['commentTypes'][ $comment_type ] = (int) get_comments(
			array(
				'type'   => $comment_type,
				'count'  => true,
				'status' => 'all',
			)
		);
	}

	foreach ( (array) $option_names as $option_name ) {
		$snapshot['options'][ $option_name ] = get_option( $option_name );
	}

	return $snapshot;
}

/**
 * Read the tail of debug.log so a spec can assert no new add-on warnings.
 *
 * @param array $input Factory input.
 * @return array
 */
function e2e_factory_get_debug_log( array $input ): array {
	$limit = max( 1, min( 2000, (int) ( $input['lines'] ?? 200 ) ) );
	$path  = WP_CONTENT_DIR . '/debug.log';

	if ( ! file_exists( $path ) ) {
		return array(
			'path'  => $path,
			'lines' => array(),
			'size'  => 0,
		);
	}

	$lines = file( $path, FILE_IGNORE_NEW_LINES ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file
	$lines = is_array( $lines ) ? array_slice( $lines, -$limit ) : array();

	return array(
		'path'  => $path,
		'lines' => $lines,
		'size'  => (int) filesize( $path ),
	);
}

/**
 * Truncate debug.log before a lifecycle test.
 *
 * @param array $input Factory input.
 * @return array
 */
function e2e_factory_clear_debug_log( array $input ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
	$path = WP_CONTENT_DIR . '/debug.log';
	if ( file_exists( $path ) ) {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		file_put_contents( $path, '' );
	}
	return array( 'success' => true );
}

/**
 * Read or clear the E2E mail log written by the mu-plugin.
 *
 * @param array $input Factory input.
 * @return array
 */
function e2e_factory_mail_log( array $input ): array {
	$action = (string) ( $input['action'] ?? 'get' );

	if ( 'clear' === $action ) {
		delete_option( 'e2e_mail_log' );
		return array(
			'success'  => true,
			'messages' => array(),
		);
	}

	$messages = get_option( 'e2e_mail_log', array() );
	return array(
		'success'  => true,
		'messages' => is_array( $messages ) ? $messages : array(),
	);
}

/**
 * Configure a server-side HTTP stub for an external provider.
 *
 * @param array $input Factory input.
 * @return array
 */
function e2e_factory_set_http_stub( array $input ): array {
	$stubs = $input['stubs'] ?? array();
	if ( ! is_array( $stubs ) ) {
		WP_CLI::error( 'stubs must be an array.' );
	}
	update_option( 'e2e_http_stub', $stubs );
	return array(
		'success' => true,
		'stubs'   => $stubs,
	);
}

/**
 * Return installed and active plugin basenames.
 *
 * @param array $input Factory input.
 * @return array
 */
function e2e_factory_get_plugins( array $input ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
	return array(
		'active'    => array_values( (array) get_option( 'active_plugins', array() ) ),
		'installed' => array_keys( get_plugins() ),
	);
}

/**
 * Add a capability to a seeded user for the screen/toggle capability split.
 *
 * @param array $input Factory input.
 * @return array
 */
function e2e_factory_add_capability( array $input ): array {
	$user = get_user_by( 'login', (string) ( $input['user'] ?? 'student2' ) );
	$cap  = (string) ( $input['cap'] ?? 'manage_tutor' );

	if ( ! $user ) {
		WP_CLI::error( 'User not found.' );
	}
	$user->add_cap( $cap );
	return array(
		'success' => true,
		'user'    => $user->user_login,
		'cap'     => $cap,
	);
}

/**
 * Remove a capability added by add_capability.
 *
 * @param array $input Factory input.
 * @return array
 */
function e2e_factory_remove_capability( array $input ): array {
	$user = get_user_by( 'login', (string) ( $input['user'] ?? 'student2' ) );
	$cap  = (string) ( $input['cap'] ?? 'manage_tutor' );

	if ( ! $user ) {
		WP_CLI::error( 'User not found.' );
	}
	$user->remove_cap( $cap );
	return array(
		'success' => true,
		'user'    => $user->user_login,
		'cap'     => $cap,
	);
}

/**
 * Return cron hooks, optionally filtered by a substring.
 *
 * @param array $input Factory input.
 * @return array
 */
function e2e_factory_get_cron( array $input ): array {
	$needle = (string) ( $input['contains'] ?? '' );
	$cron   = _get_cron_array();
	$hooks  = array();

	foreach ( (array) $cron as $timestamp => $entries ) {
		foreach ( (array) $entries as $hook => $events ) {
			if ( $needle && false === strpos( $hook, $needle ) ) {
				continue;
			}
			$hooks[] = array(
				'hook'      => $hook,
				'timestamp' => (int) $timestamp,
				'count'     => count( (array) $events ),
			);
		}
	}

	return array( 'hooks' => $hooks );
}

/**
 * Set a post meta value.
 *
 * @param array $input Factory input.
 * @return array
 */
function e2e_factory_post_meta( array $input ): array {
	$post_id = (int) ( $input['post_id'] ?? 0 );
	$key     = (string) ( $input['key'] ?? '' );

	if ( ! $post_id || ! get_post( $post_id ) || '' === $key ) {
		WP_CLI::error( 'A valid post_id and key are required.' );
	}

	$previous = get_post_meta( $post_id, $key, true );
	$value    = $input['value'] ?? '';

	if ( ! empty( $input['delete'] ) ) {
		delete_post_meta( $post_id, $key );
	} elseif ( ! empty( $input['add'] ) ) {
		add_post_meta( $post_id, $key, $value );
	} else {
		update_post_meta( $post_id, $key, $value );
	}

	return array(
		'postId'   => $post_id,
		'key'      => $key,
		'previous' => $previous,
		'value'    => get_post_meta( $post_id, $key, true ),
	);
}

/**
 * Set a user meta value.
 *
 * @param array $input Factory input.
 * @return array
 */
function e2e_factory_user_meta( array $input ): array {
	$user = get_user_by( 'login', (string) ( $input['user'] ?? '' ) );
	$key  = (string) ( $input['key'] ?? '' );

	if ( ! $user || '' === $key ) {
		WP_CLI::error( 'A valid user and key are required.' );
	}

	$previous = get_user_meta( $user->ID, $key, true );
	$value    = $input['value'] ?? '';

	if ( ! empty( $input['delete'] ) ) {
		delete_user_meta( $user->ID, $key );
	} elseif ( ! empty( $input['add'] ) ) {
		add_user_meta( $user->ID, $key, $value );
	} else {
		update_user_meta( $user->ID, $key, $value );
	}

	return array(
		'userId'   => (int) $user->ID,
		'key'      => $key,
		'previous' => $previous,
		'value'    => get_user_meta( $user->ID, $key, true ),
	);
}

/**
 * Create a user, or return the existing one with that login.
 *
 * @param array $input Factory input.
 * @return array
 */
function e2e_factory_user( array $input ): array {
	$login = (string) ( $input['user'] ?? $input['login'] ?? '' );
	$role  = (string) ( $input['role'] ?? 'subscriber' );

	if ( ! preg_match( '/^[a-z0-9._-]+$/i', $login ) ) {
		WP_CLI::error( 'A valid user login is required.' );
	}

	$existing = get_user_by( 'login', $login );
	if ( $existing ) {
		return array(
			'userId'   => (int) $existing->ID,
			'user'     => $existing->user_login,
			'email'    => $existing->user_email,
			'role'     => $role,
			'existing' => true,
		);
	}

	$user_id = wp_insert_user(
		array(
			'user_login'   => $login,
			'user_pass'    => (string) ( $input['password'] ?? 'password' ),
			'user_email'   => (string) ( $input['email'] ?? $login . '@example.com' ),
			'display_name' => (string) ( $input['name'] ?? ucfirst( $login ) ),
			'role'         => $role,
		)
	);

	if ( is_wp_error( $user_id ) ) {
		WP_CLI::error( 'Could not create user: ' . $user_id->get_error_message() );
	}

	return array(
		'userId'   => (int) $user_id,
		'user'     => $login,
		'email'    => (string) ( $input['email'] ?? $login . '@example.com' ),
		'role'     => $role,
		'existing' => false,
	);
}

/**
 * Mark a course completed for a user.
 *
 * @param array $input Factory input.
 * @return array
 */
function e2e_factory_course_complete( array $input ): array {
	$course_id = (int) ( $input['course_id'] ?? 0 );
	$user      = get_user_by( 'login', (string) ( $input['user'] ?? 'student' ) );

	if ( ! $course_id || ! $user ) {
		WP_CLI::error( 'A valid course_id and user are required.' );
	}

	\Tutor\Models\CourseModel::mark_course_as_completed( $course_id, $user->ID );

	return array(
		'courseId'  => $course_id,
		'userId'    => (int) $user->ID,
		'completed' => (bool) tutor_utils()->is_completed_course( $course_id, $user->ID, false ),
	);
}

/**
 * Insert one notifications row for a user.
 *
 * @param array $input Factory input.
 * @return array
 */
function e2e_factory_notification( array $input ): array {
	$user = get_user_by( 'login', (string) ( $input['user'] ?? 'student' ) );

	if ( ! $user ) {
		WP_CLI::error( 'User not found for notification.' );
	}

	if ( ! class_exists( '\TUTOR_NOTIFICATIONS\Utils' ) ) {
		WP_CLI::error( 'Notifications add-on is not active.' );
	}

	if ( 'delete' === ( $input['action'] ?? '' ) ) {
		global $wpdb;
		$notification_id = (int) ( $input['notification_id'] ?? 0 );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$deleted = $wpdb->delete( $wpdb->prefix . 'tutor_notifications', array( 'ID' => $notification_id ) );
		return array(
			'notificationId' => $notification_id,
			'deleted'        => (bool) $deleted,
		);
	}

	$notification_id = \TUTOR_NOTIFICATIONS\Utils::save_notification_data(
		array(
			'type'        => (string) ( $input['type'] ?? 'announcement' ),
			'title'       => (string) ( $input['title'] ?? 'E2E notification' ),
			'content'     => (string) ( $input['content'] ?? 'E2E notification body' ),
			'status'      => 'UNREAD',
			'receiver_id' => (int) $user->ID,
			'post_id'     => (int) ( $input['post_id'] ?? 0 ),
			'topic_url'   => (string) ( $input['topic_url'] ?? '' ),
		)
	);

	return array(
		'notificationId' => (int) $notification_id,
		'userId'         => (int) $user->ID,
	);
}

/**
 * Set an option or update an item within the tutor_option array.
 *
 * @param array $input Factory input.
 * @return array
 */
function e2e_factory_set_option( array $input ): array {
	$option_name = (string) ( $input['option_name'] ?? 'tutor_option' );
	$key         = (string) ( $input['key'] ?? '' );
	$value       = $input['value'] ?? null;

	if ( 'tutor_option' === $option_name && $key ) {
		$options         = get_option( 'tutor_option', array() );
		$previous_value  = $options[ $key ] ?? null;
		$options[ $key ] = $value;
		update_option( 'tutor_option', $options );
		return array(
			'success'        => true,
			'previous_value' => $previous_value,
		);
	}

	$previous_value = get_option( $option_name );
	update_option( $option_name, $value );
	return array(
		'success'        => true,
		'previous_value' => $previous_value,
	);
}

$result = array();

switch ( $factory ) {
	case 'course':
		$result = e2e_factory_course( $payload );
		break;
	case 'lesson':
		$result = e2e_factory_lesson( $payload );
		break;
	case 'enrollment':
		$result = e2e_factory_enrollment( $payload );
		break;
	case 'course_complete':
		$result = e2e_factory_course_complete( $payload );
		break;
	case 'notification':
		$result = e2e_factory_notification( $payload );
		break;
	case 'post_meta':
		$result = e2e_factory_post_meta( $payload );
		break;
	case 'user_meta':
		$result = e2e_factory_user_meta( $payload );
		break;
	case 'user':
		$result = e2e_factory_user( $payload );
		break;
	case 'wc_product':
		$result = e2e_factory_wc_product( $payload );
		break;
	case 'set_option':
		$result = e2e_factory_set_option( $payload );
		break;
	case 'enable_addon':
		$result = e2e_factory_enable_addon( $payload );
		break;
	case 'get_addon_states':
		$result = e2e_factory_get_addon_states( $payload );
		break;
	case 'set_addon_state':
		$result = e2e_factory_set_addon_state( $payload );
		break;
	case 'set_addon_baseline':
		$result = e2e_factory_set_addon_baseline( $payload );
		break;
	case 'snapshot':
		$result = e2e_factory_snapshot( $payload );
		break;
	case 'get_debug_log':
		$result = e2e_factory_get_debug_log( $payload );
		break;
	case 'clear_debug_log':
		$result = e2e_factory_clear_debug_log( $payload );
		break;
	case 'mail_log':
		$result = e2e_factory_mail_log( $payload );
		break;
	case 'set_http_stub':
		$result = e2e_factory_set_http_stub( $payload );
		break;
	case 'get_plugins':
		$result = e2e_factory_get_plugins( $payload );
		break;
	case 'add_capability':
		$result = e2e_factory_add_capability( $payload );
		break;
	case 'remove_capability':
		$result = e2e_factory_remove_capability( $payload );
		break;
	case 'get_cron':
		$result = e2e_factory_get_cron( $payload );
		break;
	default:
		WP_CLI::error( sprintf( 'Unknown factory "%s".', $factory ) );
}

$result_dir = WP_CONTENT_DIR . '/e2e';
wp_mkdir_p( $result_dir );
// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
file_put_contents( $result_dir . '/' . $result_file, wp_json_encode( $result ) );
WP_CLI::success( sprintf( 'Factory %s completed.', $factory ) );
