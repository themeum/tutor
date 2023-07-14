<?php
/**
 * Question
 *
 * @package Tutor\Templates
 * @subpackage Single\Quiz\Parts
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @version 1.0.0
 */

?>

<div id="tutor-quiz-attempt-questions-wrap" data-question-layout-view="<?php echo esc_attr( $question_layout_view ); ?>">

	<?php
		$choice_contexts      = array(
			'true_false'      => 'radio',
			'single_choice'   => 'radio',
			'multiple_choice' => 'checkbox',
		);
		$show_previous_button = (bool) tutor_utils()->get_option( 'quiz_previous_button_enabled', true );

		if ( 'question_pagination' === $question_layout_view ) {
			$question_i = 0;
			?>
			<div class="tutor-quiz-questions-pagination">
				<ul>
					<?php
					foreach ( $questions as $question ) {
						$question_i++;
						$markup = "<li><a href='#quiz-attempt-single-question-{$question->question_id}' class='tutor-quiz-question-paginate-item'>{$question_i}</a> </li>";
						echo wp_kses(
							$markup,
							array(
								'li' => array(),
								'a'  => array(
									'href'  => true,
									'class' => true,
								),
							)
						);
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
			$style_display     = ( 'question_below_each_other' !== $question_layout_view && 1 == $question_i ) ? 'block' : 'none';
			if ( 'question_below_each_other' === $question_layout_view ) {
				$style_display = 'block';
			}

			$next_question     = isset( $questions[ $question_i ] ) ? $questions[ $question_i ] : false;
			$previous_question = $question_i > 1 ? $questions[ $question_i - 1 ] : false;
			?>
				<div id="quiz-attempt-single-question-<?php echo esc_attr( $question->question_id ); ?>" 
					 class="quiz-attempt-single-question quiz-attempt-single-question-<?php echo esc_attr( $question_i ); ?>" 
					 style="display: <?php echo esc_attr( $style_display ); ?> ;" 
					 <?php echo $next_question ? "data-next-question-id='#quiz-attempt-single-question-" . esc_attr( $next_question->question_id ) . "'" : ''; ?> 
					 data-quiz-feedback-mode="<?php echo esc_attr( $feedback_mode ); ?>"  
					 data-question_index="<?php echo esc_attr( $question_i ); ?>">

					<div class="quiz-question tutor-mt-44 tutor-mr-md-100">
					<?php
						$input_markup = "<input type='hidden' name='attempt[{$is_started_quiz->attempt_id}][quiz_question_ids][]' value='{$question->question_id}' />";
						echo wp_kses(
							$input_markup,
							array(
								'input' => array(
									'type'  => true,
									'name'  => true,
									'value' => true,
								),
							)
						);

						$question_type = $question->question_type;

						$rand_choice = false;
					if ( 'single_choice' == $question_type || 'multiple_choice' == $question_type ) {
						$choice = maybe_unserialize( $question->question_settings );
						if ( isset( $choice['randomize_question'] ) ) {
							$rand_choice = 1 == $choice['randomize_question'] ? true : false;
						}
					}

					$answers            = \Tutor\Models\QuizModel::get_answers_by_quiz_question( $question->question_id, $rand_choice );
					$show_question_mark = (bool) tutor_utils()->avalue_dot( 'show_question_mark', $question_settings );
					$answer_required    = (bool) tutor_utils()->array_get( 'answer_required', $question_settings );
					echo wp_kses(
						'<div class="quiz-question-title tutor-fs-4 tutor-fw-medium tutor-color-black tutor-mb-20">',
						array(
							'div' => array( 'class' => true ),
						)
					);

					if ( ! $hide_question_number_overview ) {
						echo esc_html( $question_i . '. ' );
					}
						echo esc_html( stripslashes( $question->question_title ) );
						echo '</div>';

					if ( $show_question_mark ) {
						echo wp_kses(
							'<p class="question-marks"> ' . __( 'Marks : ', 'tutor' ) . $question->question_mark . ' </p>',
							array(
								'p' => array( 'class' => true ),
							)
						);
					}

					$question_description = wp_unslash( $question->question_description );
					if ( $question_description ) {
						$markup = "<div class='matching-quiz-question-desc'><span class='tutor-fs-7 tutor-color-secondary'>{$question_description}</span></div>";
						if ( tutor()->has_pro ) {
							do_action( 'tutor_quiz_question_desc_render', $markup, $question );
						} else {
							echo wp_kses_post( $markup );
						}
					}
					?>
					</div>
					<!-- Quiz Answer -->
					<?php
					if ( array_key_exists( $question_type, $choice_contexts ) ) {
						// Only checkbox and radio type content will be loaded here.
						$choice_type = $choice_contexts[ $question_type ];
						require 'choice-box.php';
					}

					// Fill In The Blank.
					if ( 'fill_in_the_blank' === $question_type ) {
						require 'fill-in-the-blank.php';
					}

					// Ordering.
					if ( 'ordering' === $question_type ) {
						require 'ordering.php';
					}


					// Matching.
					if ( 'matching' === $question_type ) {
						require 'matching.php';
					}

					// Image Matching.
					if ( 'image_matching' === $question_type ) {
						require 'image-matching.php';
					}

					// Image Answer.
					if ( 'image_answering' === $question_type ) {
						require 'image-answer.php';
					}

					// Open Ended.
					if ( 'open_ended' === $question_type ) {
						require 'open-ended.php';
					}

					// Short Answer.
					if ( 'short_answer' === $question_type ) {
						require 'short-answer.php';
					}
					?>

					<div class="answer-help-block"></div>
					
					<?php if ( 'question_below_each_other' !== $question_layout_view ) : ?>
						<div class="tutor-quiz-btn-group tutor-mt-60 tutor-d-flex">
							<?php
							if ( $show_previous_button && $previous_question ) {
								?>
										<button type="button" class="tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-quiz-answer-previous-btn tutor-mr-20">
											<span class="tutor-icon-previous tutor-mr-8" area-hidden="true"></span> <?php esc_html_e( 'Back', 'tutor' ); ?>
										</button>
									<?php
							}
							?>
							<button disabled="disabled" type="submit" class="tutor-btn tutor-btn-primary tutor-btn-md start-quiz-btn tutor-quiz-next-btn-all <?php echo $next_question ? 'tutor-quiz-answer-next-btn' : 'tutor-quiz-submit-btn'; ?>">
								<?php $next_question ? esc_html_e( 'Submit &amp; Next', 'tutor' ) : esc_html_e( 'Submit Quiz', 'tutor' ); ?>
							</button>
							<?php if ( ! isset( $question_settings['answer_required'] ) ) : ?>
								<span class="tutor-ml-32 tutor-btn tutor-btn-ghost tutor-btn-md tutor-next-btn <?php echo $next_question ? 'tutor-quiz-answer-next-btn' : 'tutor-quiz-submit-btn'; ?> tutor-ml-auto">
									<?php esc_html_e( 'Skip Question', 'tutor' ); ?>
								</span>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php
		}

		if ( 'question_below_each_other' === $question_layout_view ) {
			?>
				<div class="quiz-answer-footer-bar tutor-mt-60">
					<div class="quiz-footer-button">
						<button type="submit" name="quiz_answer_submit_btn" value="quiz_answer_submit" class="tutor-btn tutor-btn-primary">
							<?php esc_html_e( 'Submit Quiz', 'tutor' ); ?>
						</button>
					</div>
				</div>
				<?php
		}
		?>
	</form>
</div>
