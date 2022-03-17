<?php
/**
 * Quiz Attempts, I attempted to courses
 *
 * @since v.1.1.2
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.6.4
 */


if ( isset( $_GET['view_quiz_attempt_id'] ) ) {
	// Load single attempt details if ID provided
	include __DIR__ . '/my-quiz-attempts/attempts-details.php';
	return;
}
$item_per_page = tutor_utils()->get_option( 'pagination_per_page' );
$current_page  = max( 1, tutor_utils()->array_get( 'current_page', $_GET ) );
$offset        = ( $current_page - 1 ) * $item_per_page;

// Filter params.
$course_filter = isset( $_GET['course-id'] ) ? sanitize_text_field( $_GET['course-id'] ) : '';
$order_filter  = isset( $_GET['order'] ) ? $_GET['order'] : 'DESC';
$date_filter   = isset( $_GET['date'] ) ? $_GET['date'] : '';
$course_id   = isset( $course_id ) ? $course_id : array();

$quiz_attempts = tutor_utils()->get_quiz_attempts_by_course_ids( $offset, $item_per_page, $course_id, '', $course_filter, $date_filter, $order_filter, get_current_user_id() );

?>

<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-24"><?php _e( 'My Quiz Attempts', 'tutor' ); ?></div>
<?php
$quiz_attempts_count = tutor_utils()->get_quiz_attempts_by_course_ids( $offset, $item_per_page, $course_id, '', $course_filter, $date_filter, $order_filter, get_current_user_id(), true );

tutor_load_template_from_custom_path(
	tutor()->path . '/views/quiz/attempt-table.php',
	array(
		'attempt_list' => $quiz_attempts,
		'context'      => 'frontend-dashboard-my-attempts',
	)
);
$pagination_data              = array(
    'total_items' => $quiz_attempts_count,
    'per_page'    => $item_per_page,
    'paged'       => $current_page,
);
$pagination_template_frontend = tutor()->path . 'templates/dashboard/elements/pagination.php';
tutor_load_template_from_custom_path( $pagination_template_frontend, $pagination_data );
?>
