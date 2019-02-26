<?php
if ($question_type === 'open_ended' || $question_type === 'short_answer'){
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
        <div class="tutor-quiz-builder-form-cols-row">
            <div class="quiz-form-field-col full-width">
                <label><?php _e('Question Title', 'tutor'); ?></label>
                <div class="quiz-modal-field-wrap">
                    <input type="text" name="quiz_answer[<?php echo $question_id; ?>][answer_title]" value="">
                    <p class="quiz-modal-form-help"><?php _e( 'Please make sure that <b>{dash}</b> variable contains in your question title to show dash, You can use multiple variable', 'tutor' ); ?></p>
                </div>
            </div>

            <div class="quiz-form-field-col full-width">
                <label><?php _e('Gap Answer', 'tutor'); ?></label>
                <div class="quiz-modal-field-wrap">
                    <input type="text" name="quiz_answer[<?php echo $question_id; ?>][answer_two_gap_match]" value="">
                    <p class="quiz-modal-form-help"><?php _e( 'Separate multiple answer by pipe <b>( | )</b> , 1 answer per variable assigned in question', 'tutor' ); ?></p>
                </div>
            </div>
        </div>

		<?php
	}elseif ($question_type === 'answer_sorting'){
		?>
        <div class="tutor-quiz-builder-form-cols-row">
            <div class="quiz-form-field-col full-width">
                <label><?php _e('Answer title', 'tutor'); ?></label>
                <div class="quiz-modal-field-wrap">
                    <input type="text" name="quiz_answer[<?php echo $question_id; ?>][answer_title]" value="">
                </div>
            </div>

            <div class="quiz-form-field-col full-width">
                <label><?php _e('Matched Answer title', 'tutor'); ?></label>
                <div class="quiz-modal-field-wrap">
                    <input type="text" name="quiz_answer[<?php echo $question_id; ?>][matched_answer_title]" value="">
                </div>
            </div>
        </div>

		<?php
	}elseif($question_type === 'matching'){
		?>

        <div class="tutor-quiz-builder-form-cols-row">
            <div class="quiz-form-field-col full-width">
                <label><?php _e('Answer title', 'tutor'); ?></label>
                <div class="quiz-modal-field-wrap">
                    <input type="text" name="quiz_answer[<?php echo $question_id; ?>][answer_title]" value="">
                </div>
            </div>

            <div class="quiz-form-field-col full-width">
                <label><?php _e('Matched Answer title', 'tutor'); ?></label>
                <div class="quiz-modal-field-wrap">
                    <input type="text" name="quiz_answer[<?php echo $question_id; ?>][matched_answer_title]" value="">
                </div>
            </div>
        </div>

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
	}elseif ($question_type === 'image_matching'){
	    ?>
        <div class="tutor-quiz-builder-form-cols-row">
            <div class="quiz-form-field-col full-width">
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
                </div>
            </div>


            <div class="quiz-form-field-col full-width">
                <label><?php _e('Image matched text', 'tutor'); ?></label>
                <div class="quiz-modal-field-wrap">
                    <input type="text" name="quiz_answer[<?php echo $question_id; ?>][answer_title]" value="">
                </div>
            </div>

        </div>

        <?php
    }elseif($question_type === 'image_answering'){
	    ?>

        <div class="tutor-quiz-builder-form-cols-row">
            <div class="quiz-form-field-col full-width">
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
                </div>
            </div>


            <div class="quiz-form-field-col full-width">
                <label><?php _e('Answer input value', 'tutor'); ?></label>
                <div class="quiz-modal-field-wrap">
                    <input type="text" name="quiz_answer[<?php echo $question_id; ?>][answer_title]" value="">
                    <p class="quiz-modal-form-help"><?php _e('Student input text should be matched with this answer, write in <b>Small Caps</b>','tutor'); ?></p>
                </div>
            </div>

        </div>
    <?php
    }
	?>

    <div class="tutor-quiz-answers-form-footer  tutor-quiz-builder-form-row">
        <button type="button" id="quiz-answer-save-btn" class="tutor-answer-save-btn"><i class="tutor-icon-add-line"></i> <?php _e('Save Answer', 'tutor'); ?></button>

    </div>

</div>