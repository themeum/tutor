
<div class="tutor-quiz-question-answers-form">

	<?php
	if ($question_type === 'true_false'){
		?>
		<div class="tutor-quiz-builder-form-row">
			<label><?php _e('Select option which is correct', 'tutor'); ?></label>
			<div class="quiz-modal-field-wrap">
				<label> <input type="radio" name="true_false" value="false" checked="checked"> <?php _e('True', 'tutor'); ?> </label>
				<label> <input type="radio" name="true_false" value="true"> <?php _e('False', 'tutor'); ?> </label>
			</div>
		</div>

		<?php
	}elseif($question_type === 'multiple_choice' || $question_type === 'single_choice' ){
		?>
		<div class="tutor-quiz-builder-form-row">
			<label><?php _e('Answer title', 'tutor'); ?></label>
			<div class="quiz-modal-field-wrap">
				<input type="text" name="answer_title" value="">
			</div>
		</div>

		<div class="tutor-quiz-builder-form-row">
			<div class="quiz-modal-field-wrap">
				<div class="quiz-modal-switch-field">
					<label class="btn-switch">
						<input type="checkbox" value="1" name="is_correct_answer"  />
						<div class="btn-slider btn-round"></div>
						<p class="switch-btn-title"><?php _e('Is this correct answer?', 'tutor'); ?></p>
					</label>
				</div>
			</div>
		</div>

		<div class="tutor-quiz-builder-form-row">
			<label><?php _e('Answer option view format', 'tutor'); ?></label>
			<div class="quiz-modal-field-wrap">
				<label> <input type="radio" name="answer_view_format" value="text"> <?php _e('Only text', 'tutor'); ?> </label>
				<label> <input type="radio" name="answer_view_format" value="image"> <?php _e('Only Image', 'tutor'); ?> </label>
				<label> <input type="radio" name="answer_view_format" value="text_image"> <?php _e('Text &amp; Image both', 'tutor'); ?> </label>
			</div>
		</div>

		<?php
	}elseif($question_type === 'fill_in_the_blank'){
		?>
		<div class="tutor-quiz-builder-form-row">
			<label><?php _e('Fill in the gap hidden answer', 'tutor'); ?></label>
			<div class="quiz-modal-field-wrap">
				<input type="text" name="gape_answer" value="">
			</div>
		</div>
		<?php
	}
	?>

	<button type="button" id="quiz-answer-save-btn" class="button button-primary"><i class="tutor-icon-add-line"></i> <?php _e('Save Answer Option', 'tutor'); ?></button>

</div>