<?php
	! is_array( $answers ) ? $answers = array() : 0;

	$stat = array(
		'text'       => false,
		'image'      => false,
		'text_image' => false,
	);

	foreach ( $answers as $answer ) {
		$answer->answer_view_format == 'image' ? $stat['image']           = true : false;
		$answer->answer_view_format == 'text_image' ? $stat['text_image'] = true : false;
		( $answer->answer_view_format !== 'image' && $answer->answer_view_format !== 'text_image' ) ? $stat['text'] = true : 0;
	}

	$class = '';
	$id    = '';

	if ( $stat['text'] && ! $stat['image'] && ! $stat['text_image'] ) {
		// Only text
		$id = 'tutor-quiz-single-multiple-choice';

	} elseif ( ! $stat['text'] && $stat['image'] && ! $stat['text_image'] ) {
		// Only image
		$id = 'tutor-quiz-image-multiple-choice';

	} elseif ( ! $stat['text'] && ! $stat['image'] && $stat['text_image'] ) {
		// Only image qith text
		$id = 'tutor-quiz-image-multiple-choice';

	} else {
		// Multi variation
		$id    = 'tutor-quiz-image-multiple-choice';
		$class = 'tutor-quiz-multiple-variation';
	}
	?>

<div class="quiz-question-ans-choice-area tutor-mt-40 question-type-<?php echo $question_type; ?> <?php echo $answer_required ? 'quiz-answer-required' : ''; ?> "">
	<!-- <div id="tutor-quiz-image-multiple-choice" class="tutor-quiz-multiple-variation tutor-quiz-wrap"> -->
	<div id="<?php echo $id; ?>" class="<?php echo $class; ?> tutor-quiz-wrap">
		<div class="tutor-image-checkbox">
			<?php
			if ( count( $answers ) ) {
				foreach ( $answers as $answer ) {
					$answer_title                         = stripslashes( $answer->answer_title );
					$answer->is_correct ? $quiz_answers[] = $answer->answer_id : 0;

					if ( $answer->answer_view_format !== 'image' && $answer->answer_view_format !== 'text_image' ) {
						?>
							<div class="quiz-question-ans-choice">
								<label for="<?php echo $answer->answer_id; ?>">
									<input class="tutor-form-check-input" id="<?php echo $answer->answer_id; ?>" name="attempt[<?php echo $is_started_quiz->attempt_id; ?>][quiz_question][<?php echo $question->question_id; ?>]<?php echo esc_html( 'multiple_choice' === $question_type ? '[]' : '' ); ?>" type="<?php echo $choice_type; ?>" value="<?php echo $answer->answer_id; ?>">
									<span class="tutor-fs-6 tutor-color-black">
									<?php
										echo $answer_title;
									?>
									</span>
								</label>
							</div>
							<?php
					} else {
						?>
							<label for="<?php echo $answer->answer_id; ?>" class="tutor-form-check-input">
								<input type="<?php echo $choice_type; ?>" class="tutor-form-check-input" id="<?php echo $answer->answer_id; ?>" name="attempt[<?php echo $is_started_quiz->attempt_id; ?>][quiz_question][<?php echo $question->question_id; ?>]" value="<?php echo $answer->answer_id; ?>" />
								<div class="tutor-multiple-checkbox">
									<img src="<?php echo wp_get_attachment_image_url( $answer->image_id, 'full' ); ?>" />
								<?php
								if ( $answer->answer_view_format == 'text_image' ) {
									?>
											<div class="tutor-fs-6 tutor-color-black tutor-px-16 tutor-py-12 ">
										<?php
											echo $answer_title;
										?>
											</div>
										<?php
								}
								?>
									<span class="tutor-icon-checkbox"></span>
								</div>
							</label>
							<?php
					}
				}
			}
			?>
		</div>
	</div>
</div>
