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
		// Only image with text
		$id = 'tutor-quiz-image-multiple-choice';
	} else {
		// Multi variation
		$id    = 'tutor-quiz-image-multiple-choice';
		$class = 'tutor-quiz-multiple-variation';
	}
?>

<div class="quiz-question-ans-choice-area tutor-mt-40 question-type-<?php echo $question_type; ?> <?php echo $answer_required ? 'quiz-answer-required' : ''; ?>">
	<div id="<?php echo $id; ?>" class="<?php echo $class; ?> tutor-quiz-wrap">
		<div class="tutor-row">
			<?php if ( count( $answers ) ) : ?>
				<?php foreach ( $answers as $answer ) : ?>
					<?php
						$answer_title                         = stripslashes( $answer->answer_title );
						$answer->is_correct ? $quiz_answers[] = $answer->answer_id : 0;
					?>

					<?php if ( $answer->answer_view_format !== 'image' && $answer->answer_view_format !== 'text_image' ) : ?>
						<div class="tutor-col-6 tutor-col-lg-6 tutor-mb-16 tutor-quiz-answer-single">
							<label for="<?php echo $answer->answer_id; ?>" class="tutor-quiz-question-item">
								<div class="tutor-card tutor-px-16 tutor-py-12">
									<div class="tutor-d-flex tutor-align-center">
										<input class="tutor-form-check-input" id="<?php echo $answer->answer_id; ?>" name="attempt[<?php echo $is_started_quiz->attempt_id; ?>][quiz_question][<?php echo $question->question_id; ?>]<?php echo 'multiple_choice' === $question_type ? '[]' : ''; ?>" type="<?php echo $choice_type; ?>" value="<?php echo $answer->answer_id; ?>">
										<span class="tutor-fs-6 tutor-color-black tutor-ml-8">
											<?php echo 'True' === $answer_title || 'False' === $answer_title ? tutor_utils()->translate_dynamic_text( strtolower(  $answer_title ) ) : $answer_title; ?>
										</span>
									</div>
								</div>
							</label>
						</div>
					<?php else: ?>
						<div class="tutor-col-6 tutor-col-lg-6 tutor-mb-16 tutor-quiz-answer-single">
							<label for="<?php echo $answer->answer_id; ?>" class="tutor-quiz-question-item tutor-quiz-question-item-has-media">
								<input type="<?php echo $choice_type; ?>" class="tutor-form-check-input" id="<?php echo $answer->answer_id; ?>" name="attempt[<?php echo $is_started_quiz->attempt_id; ?>][quiz_question][<?php echo $question->question_id; ?>]<?php echo 'multiple_choice' === $question_type ? '[]' : ''; ?>" value="<?php echo $answer->answer_id; ?>" />
								<div class="tutor-card">
									<img class="tutor-card-image<?php echo $answer->answer_view_format == 'text_image' ? '-top' : ''; ?>" src="<?php echo wp_get_attachment_image_url( $answer->image_id, 'full' ); ?>" />
									<?php if ( $answer->answer_view_format == 'text_image' ) : ?>
										<div class="tutor-fs-6 tutor-color-black tutor-px-16 tutor-py-8">
											<?php echo $answer_title; ?>
										</div>
									<?php endif; ?>
								</div>
							</label>
						</div>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>
</div>
