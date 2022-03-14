<?php
/**
 * Student's Quiz Review Frontend
 *
 * @since v.1.4.0
 *
 * @author Themeum
 * @url https://themeum.com
 * @package Tutor
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

	$attempt_id   = (int) sanitize_text_field( $_GET['view_quiz_attempt_id'] );
	$attempt_data = tutor_utils()->get_attempt( $attempt_id );
	$user_id      = tutor_utils()->avalue_dot( 'user_id', $attempt_data );
	$quiz_id      = $attempt_data->quiz_id;
?>

<div class="wrap">
	<div class="tutor-quiz-attempt-details-wrapper ">
		<?php
			tutor_load_template_from_custom_path(
				tutor()->path . '/views/quiz/attempt-details.php',
				array(
					'attempt_id'   => $attempt_id,
					'attempt_data' => $attempt_data,
					'user_id'      => $user_id,
					'context'      => 'frontend-dashboard-students-attempts',
				)
			);
		?>
	</div>

	<?php
		/**
		 * Load Instructor Feedback template
		 * pass quiz id
		 *
		 * @since v2.0.0
		 */
		tutor_load_template_from_custom_path(
			tutor()->path . 'views/quiz/instructor-feedback.php',
			array( 'attempt_data' => $attempt_data )
		);
	?>
</div>
