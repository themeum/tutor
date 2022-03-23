<div id="tutor-quiz-attempt-questions-wrap" data-question-layout-view="<?php echo $question_layout_view; ?>">

	<?php
		$choice_contexts      = array(
			'true_false'      => 'radio',
			'single_choice'   => 'radio',
			'multiple_choice' => 'checkbox',
		);
		$show_previous_button = (bool) tutor_utils()->get_option( 'quiz_previous_button_enabled', true );

		if ( $question_layout_view === 'question_pagination' ) {
			$question_i = 0;
			?>
			<div class="tutor-quiz-questions-pagination">
				<ul>
					<?php
					foreach ( $questions as $question ) {
						$question_i++;
						echo "<li><a href='#quiz-attempt-single-question-{$question->question_id}' class='tutor-quiz-question-paginate-item'>{$question_i}</a> </li>";
					}
					?>
				</ul>
			</div>
			<?php
		}
		?>

	<form id="tutor-answering-quiz" method="post">
		<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
		<input type="hidden" value="<?php echo esc_attr( $is_started_quiz->attempt_id ); ?>" name="attempt_id"/>
		<input type="hidden" value="tutor_answering_quiz_question" name="tutor_action"/>
		<?php
			$question_i = 0;
		foreach ( $questions as $question ) {
			$question_i++;
			$question_settings = maybe_unserialize( $question->question_settings );
			$style_display = ( $question_layout_view !== 'question_below_each_other' && $question_i == 1 ) ? 'block' : 'none';
			if ( $question_layout_view === 'question_below_each_other' ) {
				$style_display = 'block';
			}

			$next_question     = isset( $questions[ $question_i ] ) ? $questions[ $question_i ] : false;
			$previous_question = $question_i > 1 ? $questions[ $question_i - 1 ] : false;
			?>
				<div id="quiz-attempt-single-question-<?php echo $question->question_id; ?>" class="quiz-attempt-single-question quiz-attempt-single-question-<?php echo $question_i; ?>" style="display: <?php echo esc_attr( $style_display ); ?> ;" <?php echo $next_question ? "data-next-question-id='#quiz-attempt-single-question-{$next_question->question_id}'" : ''; ?> data-quiz-feedback-mode="<?php echo $feedback_mode; ?>"  data-question_index="<?php echo esc_attr( $question_i ); ?>">
					<div class="quiz-question tutor-mt-44 tutor-mr-md-100">
					<?php
						echo "<input type='hidden' name='attempt[{$is_started_quiz->attempt_id}][quiz_question_ids][]' value='{$question->question_id}' />";

						$question_type = $question->question_type;

						$rand_choice = false;
						if ( $question_type == 'single_choice' || $question_type == 'multiple_choice' ) {
							$choice = maybe_unserialize( $question->question_settings );
							if ( isset( $choice['randomize_question'] ) ) {
								$rand_choice = $choice['randomize_question'] == 1 ? true : false;
							}
						}

						$answers            = tutor_utils()->get_answers_by_quiz_question( $question->question_id, $rand_choice );
						$show_question_mark = (bool) tutor_utils()->avalue_dot( 'show_question_mark', $question_settings );
						$answer_required    = (bool) tutor_utils()->array_get( 'answer_required', $question_settings );	
						echo '<div class="quiz-question-title tutor-fs-4 tutor-fw-medium tutor-color-black tutor-mb-20">';
							if ( ! $hide_question_number_overview ) {
								echo $question_i . '. ';
							}
							echo stripslashes( $question->question_title );
						echo '</div>';

						if ( $show_question_mark ) {
							echo '<p class="question-marks"> ' . __( 'Marks : ', 'tutor' ) . $question->question_mark . ' </p>';
						}

						$question_description = nl2br( stripslashes( $question->question_description ) );
						if ( $question_description ) {
							echo "<div class='matching-quiz-question-desc'><span class='tutor-fs-7 tutor-color-black-60'>{$question_description}</span></div>";
						}
					?>
					</div>
					<!-- Quiz Answer -->
					<?php
					if ( array_key_exists( $question_type, $choice_contexts ) ) {
						// Only checkbox and radio type content will be loaded here
						$choice_type = $choice_contexts[ $question_type ];
						require 'choice-box.php';
					}

					// Fill In The Blank
					if ( $question_type === 'fill_in_the_blank' ) {
						require 'fill-in-the-blank.php';
					}

					// Ordering
					if ( $question_type === 'ordering' ) {
						require 'ordering.php';
					}


					// Matching
					if ( $question_type === 'matching' ) {
						require 'matching.php';
					}

					// Image Matching
					if ( $question_type === 'image_matching' ) {
						require 'image-matching.php';
					}

					// Image Answer
					if ( $question_type === 'image_answering' ) {
						require 'image-answer.php';
					}

					// Open Ended
					if ( $question_type === 'open_ended' ) {
						require 'open-ended.php';
					}

					// Short Answer
					if ( $question_type === 'short_answer' ) {
						require 'short-answer.php';
					}
					?>
					
					<?php if ( $question_layout_view !== 'question_below_each_other' ) : ?>
						<div class="tutor-quiz-btn-grp tutor-mt-60 tutor-d-flex">
							<?php
								if ( $show_previous_button && $previous_question ) {
									?>
										<button type="button" class="tutor-btn tutor-btn-tertiary tutor-is-outline tutor-btn-md tutor-quiz-answer-previous-btn tutor-mr-20">
											<?php esc_html_e( 'Back', 'tutor' ); ?>
										</button>
									<?php
								}
							?>
							<button disabled="disabled" type="submit" class="tutor-btn tutor-btn-primary tutor-btn-md start-quiz-btn tutor-quiz-next-btn-all <?php echo $next_question ? 'tutor-quiz-answer-next-btn' : 'tutor-quiz-submit-btn'; ?>">
								<?php $next_question ? esc_html_e( 'Submit &amp; Next', 'tutor' ) : esc_html_e( 'Submit Quiz', 'tutor' ); ?>
							</button>
							<?php if ( ! isset( $question_settings['answer_required'] ) ) : ?>
								<span class="tutor-ml-32 tutor-btn tutor-btn-disable-outline tutor-no-hover tutor-btn-md tutor-next-btn <?php echo $next_question ? 'tutor-quiz-answer-next-btn' : 'tutor-quiz-submit-btn'; ?> " style="border: 0px; padding: 0px; margin-left: auto;">
									<?php esc_html_e( 'Skip Question', 'tutor' ); ?>
								</span>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php
		}

		if ( $question_layout_view === 'question_below_each_other' ) {
			?>
				<div class="quiz-answer-footer-bar tutor-mt-60">
					<div class="quiz-footer-button">
						<button type="submit" name="quiz_answer_submit_btn" value="quiz_answer_submit" class="tutor-btn">
							<?php _e( 'Submit Quiz', 'tutor' ); ?>
						</button>
					</div>
				</div>
				<?php
		}
		?>
	</form>
</div>
