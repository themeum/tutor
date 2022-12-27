<?php
/**
 * Frontend Students Quiz Attempts
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.0
 */

use TUTOR\Input;
use Tutor\Models\QuizModel;

if ( isset( $_GET['view_quiz_attempt_id'] ) ) {
	// Load single attempt details if ID provided.
	include __DIR__ . '/quiz-attempts/quiz-reviews.php';
	return;
}

$item_per_page = tutor_utils()->get_option( 'pagination_per_page' );
$current_page  = max( 1, Input::get( 'current_page', 1, Input::TYPE_INT ) );
$offset        = ( $current_page - 1 ) * $item_per_page;

// Filter params.
$course_filter = Input::get( 'course-id', '' );
$order_filter  = Input::get( 'order', 'DESC' );
$date_filter   = Input::get( 'date', '' );
?>

<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-24 tutor-capitalize-text"><?php esc_html_e( 'Quiz Attempts', 'tutor' ); ?></div>
<?php
// Load filter template.
tutor_load_template_from_custom_path( tutor()->path . 'templates/dashboard/elements/filters.php' );

$course_id           = tutor_utils()->get_assigned_courses_ids_by_instructors();
$quiz_attempts       = QuizModel::get_quiz_attempts( $offset, $item_per_page, '', $course_filter, $date_filter, $order_filter, null, false, true );
$quiz_attempts_count = QuizModel::get_quiz_attempts( $offset, $item_per_page, '', $course_filter, $date_filter, $order_filter, null, true, true );

tutor_load_template_from_custom_path(
	tutor()->path . '/views/quiz/attempt-table.php',
	array(
		'attempt_list' => $quiz_attempts,
		'context'      => 'frontend-dashboard-students-attempts',
	)
);

$pagination_data = array(
	'total_items' => $quiz_attempts_count,
	'per_page'    => $item_per_page,
	'paged'       => $current_page,
);
if ( $quiz_attempts_count > $item_per_page ) {
	$pagination_template_frontend = tutor()->path . 'templates/dashboard/elements/pagination.php';
	tutor_load_template_from_custom_path( $pagination_template_frontend, $pagination_data );
}
?>
