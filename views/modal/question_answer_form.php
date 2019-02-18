<?php
if ($question_type === 'open_ended'){
	echo '<p style="color: #ff0000;">No need any options</p>';
    return '';
}
?>

<div class="tutor-quiz-question-answers-form">

	<?php
	if ($question_type === 'true_false'){
		?>
		<div class="tutor-quiz-builder-form-row">
			<label><?php _e('Select option which is correct', 'tutor'); ?></label>
			<div class="quiz-modal-field-wrap">
				<label> <input type="radio" name="quiz_answer[<?php echo $question_id; ?>][true_false]" value="true" checked="checked"> <?php _e('True', 'tutor');
				?> </label>
				<label> <input type="radio" name="quiz_answer[<?php echo $question_id; ?>][true_false]" value="false"> <?php _e('False', 'tutor'); ?>
                </label>
			</div>
		</div>

		<?php
	}elseif($question_type === 'multiple_choice' || $question_type === 'single_choice' || $question_type === 'ordering' ){

	    if ($question_type === 'ordering'){
	        echo '<p class="quiz-modal-form-help">Make sure you are saving answer in right sorting in answers lists, student should ordering should match with this answer order.</p>';
	        echo '<p class="quiz-modal-form-help">You can re-order it from above answer list.</p>';
        }

		?>
		<div class="tutor-quiz-builder-form-row">
			<label><?php _e('Answer title', 'tutor'); ?></label>
			<div class="quiz-modal-field-wrap">
				<input type="text" name="quiz_answer[<?php echo $question_id; ?>][answer_title]" value="">
			</div>
		</div>

        <?php
        if ($question_type !== 'ordering'){
        ?>
		<!--<div class="tutor-quiz-builder-form-row">
			<div class="quiz-modal-field-wrap">
				<div class="quiz-modal-switch-field">
					<label class="btn-switch">
						<input type="checkbox" value="1" name="quiz_answer[<?php /*echo $question_id; */?>][is_correct_answer]"  />
						<div class="btn-slider btn-round"></div>
						<p class="switch-btn-title"><?php /*_e('Is this correct answer?', 'tutor'); */?></p>
					</label>
				</div>
			</div>
		</div>-->
        <?php } ?>


        <div class="tutor-quiz-builder-form-cols-row">

            <div class="quiz-form-field-col">

                <label><?php _e('Upload Image', 'tutor'); ?></label>


                <div class="tutor-media-upload-wrap">
                    <input type="hidden" name="quiz_answer[<?php echo $question_id; ?>][image_id]" value="">

                    <div class="tutor-media-preview">
                        <a href="javascript:;" class="tutor-media-upload-btn"><i class="tutor-icon-image1"></i></a>
                    </div>

                    <div class="tutor-media-upload-trash-wrap">
                        <a href="javascript:;" class="tutor-media-upload-trash">&times;</a>
                    </div>
                </div>


                <!--
                <div class="option-media-wrap tutor-quiz-answer-media">
                    <div class="option-media-preview"></div>
                    <input type="hidden" name="quiz_answer[<?php /*echo $question_id; */?>][image_id]" value="">
                    <button class="button button-cancel tutor-option-media-upload-btn">
                        <i class="dashicons dashicons-upload"></i>
			            <?php /*_e('Upload an Image'); */?>
                    </button>
                </div>
                -->


            </div>



            <div class="quiz-form-field-col">

                <label><?php _e('Answer option view format', 'tutor'); ?></label>
                <div class="quiz-modal-field-wrap label-inline">
                    <label> <input type="radio" name="quiz_answer[<?php echo $question_id; ?>][answer_view_format]" value="text" checked="checked"> <?php _e('Only text', 'tutor'); ?> </label>
                    <label> <input type="radio" name="quiz_answer[<?php echo $question_id; ?>][answer_view_format]" value="image"> <?php _e('Only Image', 'tutor'); ?> </label>
                    <label> <input type="radio" name="quiz_answer[<?php echo $question_id; ?>][answer_view_format]" value="text_image"> <?php _e('Text &amp; Image both', 'tutor'); ?> </label>
                </div>

            </div>



        </div>






		<?php
	}elseif($question_type === 'fill_in_the_blank'){
		?>
		<div class="tutor-quiz-builder-form-row">
            <p class="quiz-modal-form-help"><?php _e('Please make sure that <b>{dash}</b> variable contains in your question title to show dash, so student can write an answer here', 'tutor'); ?></p>

			<label><?php _e('Fill in the gap hidden answer', 'tutor'); ?></label>
			<div class="quiz-modal-field-wrap">
				<input type="text" name="quiz_answer[<?php echo $question_id; ?>][gape_answer]" value="">
			</div>
		</div>
		<?php
	}
	?>

    <div class="tutor-quiz-answers-form-footer  tutor-quiz-builder-form-row">
        <button type="button" id="quiz-answer-save-btn" class="tutor-answer-save-btn"><i class="tutor-icon-add-line"></i> <?php _e('Save Answer', 'tutor'); ?></button>

    </div>

</div>