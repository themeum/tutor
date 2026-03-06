<?php
/**
 * Frontend Student's Quiz Review
 *
 * @package Tutor\Templates
 * @subpackage Dashboard\Quiz_Attempts
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.0
 */

use TUTOR\Input;

$attempt_id   = Input::get( 'attempt_id', 0, Input::TYPE_INT );
$attempt_data = tutor_utils()->get_attempt( $attempt_id );
$user_id      = tutor_utils()->avalue_dot( 'user_id', $attempt_data );
$quiz_id      = (int) tutor_utils()->avalue_dot( 'quiz_id', $attempt_data );
$back_url     = remove_query_arg( 'attempt_id' );
?>

<div class="wrap">
	<div class="tutor-quiz-attempt-details-wrapper ">
		<?php
		if ( is_admin() ) {
			tutor_load_template_from_custom_path(
				tutor()->path . '/views/quiz/attempt-details.php',
				array(
					'attempt_id'   => $attempt_id,
					'attempt_data' => $attempt_data,
					'user_id'      => (int) $user_id,
					'context'      => 'frontend-dashboard-students-attempts',
				)
			);
		} else {
			tutor_load_template(
				'shared.components.quiz.attempt-details',
				array(
					'attempt_id'   => $attempt_id,
					'attempt_data' => $attempt_data,
					'quiz_id'      => $quiz_id,
					'user_id'      => (int) $user_id,
					'back_url'     => $back_url,
				)
			);
		}
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
