<div class="quiz-meta-info d-flex justify-content-between">
	<div class="quiz-meta-info-left d-flex">
		<?php
			$total_questions = tutor_utils()->total_questions_for_student_by_quiz( get_the_ID() );

		if ( $total_questions ) {
			?>
				<div class="quiz-qno d-flex">
					<span class="text-regular-body tutor-color-text-hints tutor-mr-10">
					<?php esc_html_e( 'Questions No', 'tutor' ); ?>:
					</span>
					<span class="text-bold-body tutor-color-text-title tutor-quiz-question-counter">
						<span>1</span>/<?php echo $total_questions; ?>
					</span>
				</div>
				<?php
		}
		?>
		<div class="quiz-total-attempt d-flex d-xs-none">
			<span class="text-regular-body tutor-color-text-hints tutor-mr-10">Total <?php esc_html_e( 'Attempted', 'tutor' ); ?>:</span>
			<span class="text-bold-body tutor-color-text-title">
			<?php
			if ( 0 != $attempts_allowed ) {
				if ( $attempted_count ) {
					echo esc_html( $attempted_count ) . '/';
				}
			}
				echo 0 == $attempts_allowed ? esc_html__( 'No limit', 'tutor' ) : esc_html( $attempts_allowed );
			?>
			</span>
		</div>
	</div>
	<?php if ( ! $hide_quiz_time_display ) : ?>
		<div class="quiz-meta-info-right">
			<div class="quiz-time-remaining d-flex">
				<?php if ( $remaining_time_secs > 0 ) : ?>
					<div class="quiz-time-remaining-progress-circle">
						<svg viewBox="0 0 50 50" width="50" height="50" style="--quizeProgress: 0;">
							<circle cx="0" cy="0" r="7"></circle>
							<circle cx="0" cy="0" r="7"></circle>
						</svg>
					</div>
				<?php endif; ?>

				<?php if ( $remaining_time_secs < 0 ) : ?>
					<div class="quiz-time-remaining-expired-circle">
						<svg viewBox="0 0 50 50" width="50" height="50">
							<circle cx="0" cy="0" r="8"></circle>
						</svg>
					</div>
				<?php endif; ?>

				<p class="text-regular-body tutor-color-text-hints tutor-mr-10">
					<?php esc_html_e( 'Time remaining: ', 'tutor' ); ?>
				</p>
				<span id="tutor-quiz-time-update" class="text-medium-body 
				<?php
				if ( $remaining_time_secs < 0 ) {
					echo 'color-text-error';
				}
				?>
				" data-attempt-settings="<?php echo esc_attr( json_encode( $is_started_quiz ) ); ?>" data-attempt-meta="<?php echo esc_attr( json_encode( $quiz_attempt_info ) ); ?>" data-quiz-duration="<?php echo esc_attr( tutor_utils()->quiz_time_duration_in_seconds( $quiz_time_type, $quiz_time_value ) ); ?>">

				</span>
			</div>
		</div>
	<?php endif; ?>
</div>

<div class="quiz-flash-message">
	<div id="tutor-quiz-time-expire-wrapper" class="tutor-quiz-warning-box time-remaining-warning d-flex align-items-center justify-content-between" data-attempt-allowed="<?php echo esc_attr( $attempts_allowed ); ?>" data-attempt-remaining="<?php echo esc_attr( $attempt_remaining ); ?>">
		<div class="flash-info d-flex align-items-center">
			<span class="ttr-warning-outline-circle-filled tutor-color-design-warning tutor-mr-7"></span>
			<span class="text-regular-caption tutor-color-text-title tutor-quiz-alert-text">
			</span>
		</div>
		<div class="flash-action">
			<form id="tutor-start-quiz" method="post">
				<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>

				<input type="hidden" value="<?php echo $quiz_id; ?>" name="quiz_id"/>
				<input type="hidden" value="tutor_start_quiz" name="tutor_action"/>

				<button type="submit" class="tutor-btn tutor-btn-md reattempt-btn" name="start_quiz_btn" value="start_quiz">
					<?php esc_html_e( 'Reattempt', 'tutor' ); ?>
				</button>
			</form>
		</div>
	</div>	
</div>
