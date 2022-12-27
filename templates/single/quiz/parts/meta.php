<?php
/**
 * Meta
 *
 * @package Tutor\Templates
 * @subpackage Single\Quiz\Parts
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @version 1.0.0
 */

?>
<div class="quiz-meta-info tutor-d-flex tutor-justify-between">
	<div class="quiz-meta-info-left tutor-d-flex">
		<?php
			$total_questions = tutor_utils()->total_questions_for_student_by_quiz( get_the_ID() );

		if ( $total_questions ) {
			?>
				<div class="quiz-qno tutor-d-flex">
					<span class="tutor-fs-6 tutor-color-muted tutor-mr-12">
					<?php esc_html_e( 'Questions No', 'tutor' ); ?>:
					</span>
					<span class="tutor-fs-6 tutor-fw-bold tutor-color-secondary tutor-quiz-question-counter">
						<span>1</span>/<?php echo esc_html( $total_questions ); ?>
					</span>
				</div>
				<?php
		}
		?>
		<div class="quiz-total-attempt tutor-d-flex d-xs-none">
		<?php if ( 'retry' === $feedback_mode ) : ?>
			<span class="tutor-fs-6 tutor-color-muted tutor-mr-12"><?php esc_html_e( 'Total Attempted', 'tutor' ); ?>:</span>
			<span class="tutor-fs-6 tutor-fw-bold tutor-color-secondary">
			<?php
			if ( 0 != $attempts_allowed ) {
				if ( $attempted_count ) {
					echo esc_html( $attempted_count ) . '/';
				}
			}
				echo 0 == $attempts_allowed ? esc_html__( 'No limit', 'tutor' ) : esc_html( $attempts_allowed );
			?>
			</span>
		<?php else : ?>
			<span class="tutor-fs-6 tutor-color-muted tutor-mr-12"><?php esc_html_e( 'Attempts Allowed', 'tutor' ); ?>:</span>
			<span class="tutor-fs-6 tutor-fw-bold tutor-color-secondary">1</span>
		<?php endif; ?>
		</div>
	</div>
	<?php if ( ! $hide_quiz_time_display ) : ?>
		<div class="quiz-meta-info-right">
			<div class="quiz-time-remaining tutor-d-flex">
				<?php if ( $remaining_time_secs > 0 ) : ?>
					<div class="quiz-time-remaining-progress-circle">
						<svg viewBox="0 0 50 50" width="50" height="50">
							<circle cx="0" cy="0" r="7"></circle>
							<circle cx="0" cy="0" r="7"></circle>
						</svg>
					</div>
				<?php endif; ?>

				<?php if ( $remaining_time_secs < 0 ) : ?>
					<div class="quiz-time-remaining-expired-circle">
						<svg viewBox="0 0 50 50" width="50" height="50">
							<circle cx="0" cy="0" r="11"></circle>
						</svg>
					</div>
				<?php endif; ?>

				<div class="tutor-fs-6 tutor-color-muted tutor-text-nowrap tutor-mr-12">
					<?php esc_html_e( 'Time remaining: ', 'tutor' ); ?>
				</div>
				
				<span id="tutor-quiz-time-update" 
					class="tutor-fs-6 tutor-fw-medium tutor-text-nowrap <?php $remaining_time_secs < 0 ? 'color-text-error' : ''; ?>" 
					data-attempt-settings="<?php echo esc_attr( json_encode( $is_started_quiz ) ); ?>" 
					data-attempt-meta="<?php echo esc_attr( json_encode( $quiz_attempt_info ) ); ?>" 
					data-quiz-duration="<?php echo esc_attr( tutor_utils()->quiz_time_duration_in_seconds( $quiz_time_type, $quiz_time_value ) ); ?>">
				
				</span>
			</div>
		</div>
	<?php endif; ?>
</div>

<?php
	$feedback_mode = tutor_utils()->get_quiz_option( $quiz_id, 'feedback_mode', 0 );
?>

<?php if ( 'retry' == $feedback_mode ) : ?>
	<div class="quiz-flash-message">
		<div id="tutor-quiz-time-expire-wrapper" class="tutor-mt-20 tutor-quiz-warning-box time-remaining-warning tutor-align-center tutor-justify-between" data-attempt-allowed="<?php echo esc_attr( $attempts_allowed ); ?>" data-attempt-remaining="<?php echo esc_attr( $attempt_remaining ); ?>">
			<div class="flash-info tutor-d-flex tutor-align-center">
				<span class="tutor-icon-circle-warning-outline tutor-color-warning tutor-mr-8"></span>
				<span class="tutor-fs-7 tutor-color-secondary tutor-quiz-alert-text">
				</span>
			</div>
			<div class="flash-action">
				<form id="tutor-start-quiz" method="post">
					<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>

					<input type="hidden" value="<?php echo esc_attr( $quiz_id ); ?>" name="quiz_id"/>
					<input type="hidden" value="tutor_start_quiz" name="tutor_action"/>

					<button type="submit" class="tutor-btn tutor-btn-md reattempt-btn" name="start_quiz_btn" value="start_quiz">
						<?php esc_html_e( 'Reattempt', 'tutor' ); ?>
					</button>
				</form>
			</div>
		</div>	
	</div>
<?php endif; ?>
