<?php
/**
 * Previous attempts
 *
 * @package Tutor\Templates
 * @subpackage Single\Quiz
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.6.4
 */

use TUTOR\Input;

$previous_attempts = tutor_utils()->quiz_attempts();
$attempted_count   = is_array( $previous_attempts ) ? count( $previous_attempts ) : 0;
$attempts_allowed  = tutor_utils()->get_quiz_option( $quiz_id, 'attempts_allowed', 0 );
$attempt_remaining = (int) $attempts_allowed - (int) $attempted_count;
$feedback_mode     = tutor_utils()->get_quiz_option( $quiz_id, 'feedback_mode', 0 );

if ( Input::has( 'view_quiz_attempt_id' ) ) {
	// Load single attempt details if ID provided.
	$attempt_id = Input::get( 'view_quiz_attempt_id', 0, Input::TYPE_INT );
	if ( $attempt_id ) {
		$user_id      = get_current_user_id();
		$attempt_data = tutils()->get_attempt( $attempt_id );
		tutor_load_template_from_custom_path(
			tutor()->path . '/views/quiz/attempt-details.php',
			array(
				'attempt_id'   => $attempt_id,
				'attempt_data' => $attempt_data,
				'user_id'      => $user_id,
				'context'      => 'course-single-previous-attempts',
			)
		);
		return;
	}
}

tutor_load_template_from_custom_path(
	tutor()->path . '/views/quiz/attempt-table.php',
	array(
		'attempt_list' => $previous_attempts,
		'context'      => 'course-single-previous-attempts',
	)
);

if ( 'retry' == $feedback_mode && ( ( $attempt_remaining > 0 || 0 == $attempts_allowed ) && $previous_attempts ) ) {
	do_action( 'tutor_quiz/start_form/before', $quiz_id );
	?>
	<div class="tutor-quiz-btn-grp tutor-mt-32">
		<form id="tutor-start-quiz" method="post">
			<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>

			<input type="hidden" value="<?php echo esc_attr( $quiz_id ); ?>" name="quiz_id"/>
			<input type="hidden" value="tutor_start_quiz" name="tutor_action"/>

			<button type="submit" class="tutor-btn tutor-btn-primary tutor-btn-md start-quiz-btn" name="start_quiz_btn" value="start_quiz">
				<?php esc_html_e( 'Start Quiz', 'tutor' ); ?>
			</button>
		</form>
	</div>
<?php } ?>
