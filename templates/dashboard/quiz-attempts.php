<?php
/**
 * Students Quiz Attempts Frontend
 *
 * @since v.1.4.0
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.6.4
 */


if ( isset( $_GET['view_quiz_attempt_id'] ) ) {
	// Load single attempt details if ID provided
	include __DIR__ . '/quiz-attempts/quiz-reviews.php';
	return;
}

$item_per_page = 20;
$current_page  = max( 1, tutor_utils()->array_get( 'current_page', $_GET ) );
$offset        = ( $current_page - 1 ) * $item_per_page;
?>

<h3><?php esc_html_e( 'Quiz Attempts', 'tutor' ); ?></h3>
<?php
$course_id           = tutor_utils()->get_assigned_courses_ids_by_instructors();
$quiz_attempts       = tutor_utils()->get_quiz_attempts_by_course_ids( $offset, $item_per_page, $course_id );
$quiz_attempts_count = tutor_utils()->get_total_quiz_attempts_by_course_ids( $course_id );

add_action(
	'tutor_quiz/table/after/course_title',
	function( $attempt ) {
		?>
	<span><?php esc_html_e( 'Student', 'tutor' ); ?>: </span> <strong title="<?php echo esc_attr( $attempt->user_email ); ?>"><?php echo esc_attr( $attempt->display_name ); ?></strong>
		<?php
	}
);

tutor_load_template_from_custom_path(
	tutor()->path . '/views/quiz/attempt-table.php',
	array(
		'attempt_list' => $quiz_attempts,
		'context'      => 'frontend-dashboard-students-attempts',
	)
);

?>
<div class="tutor-pagination">
	<?php
	echo paginate_links(
		array(
			'format'  => '?current_page=%#%',
			'current' => $current_page,
			'total'   => ceil( $quiz_attempts_count / $item_per_page ),
		)
	);
	?>
</div>
