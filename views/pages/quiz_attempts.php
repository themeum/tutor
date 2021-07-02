<?php
/**
 * @package @TUTOR
 * @since v.1.0.0
 */

if (isset($_GET['sub_page'])){
    $page = sanitize_text_field($_GET['sub_page']);
    include_once tutor()->path."views/pages/{$page}.php";
    return;
}
/**
 * Quiz attempt filters added
 * 
 * @since 1.9.5
 */
$search_filter	= $_GET['search'] ? sanitize_text_field( $_GET['search'] ) : '';
$course_filter	= $_GET['course-id'] ? sanitize_text_field( $_GET['course-id'] ) : '';
$date_filter	= $_GET['date'] ? sanitize_text_field( $_GET['date'] ) : '';
$order_filter	= $_GET['order'] ? sanitize_text_field( $_GET['order'] ) : '';

$quiz_attempt 	= new \TUTOR\Quiz_Attempts_List();
$quiz_attempt->prepare_items();
?>

<div class="wrap">
	<div class="quiz-attempts-title">
		<?php _e('Quiz Attempts', 'tutor'); ?>
	</div>

	<form id="quiz_attempts-filter" method="get">
		<input type="hidden" name="page" value="<?php echo \TUTOR\Quiz_Attempts_List::QUIZ_ATTEMPT_PAGE; ?>" />
		<?php $quiz_attempt->display($enable_sorting_field_with_bulk_action = true); ?>
	</form>
</div>