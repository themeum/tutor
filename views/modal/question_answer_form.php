<?php
/**
 * Template for single answer editor
 */

if ($question_type === 'open_ended' || $question_type === 'short_answer'){
	echo '<p class="open-ended-notice" style="color: #ff0000;">'.
            __('No option is necessary for this answer type', 'tutor').
        '</p>';
	return '';
}

empty($old_answer)      ? $old_answer=(object)array() : 0;
$answer_title           = ! empty($old_answer->answer_title) ? stripslashes($old_answer->answer_title) : '';
$image_id               = ! empty($old_answer->image_id) ? $old_answer->image_id : '';
$answer_view_format     = ! empty($old_answer->answer_view_format) ? $old_answer->answer_view_format : 'text';
$answer_two_gap_match   = ! empty($old_answer->answer_two_gap_match) ? stripslashes($old_answer->answer_two_gap_match) : '';
?>

<div class="tutor-quiz-question-answers-form">
    <input type="hidden" name="tutor_quiz_answer_id" value="<?php echo !empty($old_answer->answer_id) ? $old_answer->answer_id : ''; ?>" />

    <?php
	if ($question_type === 'true_false'){
		?>
        <div class="tutor-quiz-builder-group">
            <!-- <h4><?php _e('Select the correct option', 'tutor'); ?></h4> -->
            <div class="tutor-quiz-builder-row">
                <div class="tutor-quiz-builder-col auto-width">
                    <label>
                        <input type="radio" name="quiz_answer[<?php echo esc_attr( $question_id ); ?>][true_false]" value="true" checked="checked">
                        <?php _e('True', 'tutor'); ?>
                    </label>
                    <label>
                        <input type="radio" name="quiz_answer[<?php echo esc_attr( $question_id ); ?>][true_false]" value="false">
                        <?php _e('False', 'tutor'); ?>
                    </label>
                </div>
            </div>
        </div>
		<?php
	} elseif ($question_type === 'multiple_choice' || $question_type === 'single_choice' || $question_type === 'ordering' ){
		?>

        <div class="tutor-mb-32">
            <label class="tutor-form-label">
                <?php _e('Answer title', 'tutor'); ?>
            </label>
            <div class="tutor-input-group tutor-mb-16">
                <input class="tutor-form-control" type="text" name="quiz_answer[<?php echo $question_id; ?>][answer_title]" value="<?php echo $answer_title; ?>">
            </div>
        </div>

        <div class="tutor-mb-32">
            <label class="tutor-form-label">
                <?php _e('Upload Image', 'tutor'); ?>
            </label>
            
            <?php 
                // Load thumbnail segment
                tutor_load_template_from_custom_path(tutor()->path.'/views/fragments/thumbnail-uploader.php', array(
                    'media_id' => $image_id,
                    'input_name' => 'quiz_answer['.$question_id.'][image_id]'
                ), false);
            ?>
        </div>

        <div class="tutor-row tutor-mb-32">
            <div class="tutor-col-12">
                <label class="tutor-form-label">
                    <?php _e('Display format for options', 'tutor'); ?>
                </label>
            </div>
            <div class="tutor-col-auto">
                <div class="tutor-form-check tutor-mb-16">
                    <input type="radio" id="tutor_quiz_type_text" class="tutor-form-check-input" name="quiz_answer[<?php echo $question_id; ?>][answer_view_format]" value="text" <?php echo $answer_view_format ? checked('text', $answer_view_format) : 'checked="checked"' ?>/>
                    <label for="tutor_quiz_type_text"><?php _e('Only text', 'tutor'); ?></label>
                </div>
            </div>
            <div class="tutor-col-auto">
                <div class="tutor-form-check tutor-mb-16">
                    <input type="radio" id="tutor_quiz_type_img" class="tutor-form-check-input" name="quiz_answer[<?php echo $question_id; ?>][answer_view_format]" value="image" <?php echo checked('image', $answer_view_format) ?>/>
                    <label for="tutor_quiz_type_img"><?php _e('Only Image', 'tutor'); ?></label>
                </div>
            </div>
            <div class="tutor-col-auto">
                <div class="tutor-form-check tutor-mb-16">
                    <input type="radio" id="tutor_quiz_type_img_text" class="tutor-form-check-input" name="quiz_answer[<?php echo $question_id; ?>][answer_view_format]" value="text_image" <?php echo checked('text_image', $answer_view_format) ?>/>
                    <label for="tutor_quiz_type_img_text"><?php _e('Text &amp; Image both', 'tutor'); ?></label>
                </div>
            </div>
        </div>
		<?php
	} elseif ($question_type === 'fill_in_the_blank'){ ?>

        <div class="tutor-mb-32">
            <label class="tutor-form-label">
                <?php _e('Question Title', 'tutor'); ?>
            </label>
            <div class="tutor-input-group tutor-mb-16">
                <input class="tutor-form-control" type="text" name="quiz_answer[<?php echo $question_id; ?>][answer_title]" value="<?php echo $answer_title; ?>">
                <p class="tutor-input-feedback tutor-has-icon">
                    <i class="tutor-icon-info-circle-outline-filled tutor-input-feedback-icon"></i>
                    <?php _e( 'Please make sure to use the <strong>{dash}</strong> variable in your question title to show the blanks in your question. You can use multiple <strong>{dash}</strong> variables in one question.', 'tutor' ); ?>
                </p>
            </div>
        </div>
        
        <div class="tutor-mb-32">
            <label class="tutor-form-label">
                <?php _e('Correct Answer(s)', 'tutor'); ?>
            </label>
            <div class="tutor-input-group tutor-mb-16">
                <input class="tutor-form-control" type="text" name="quiz_answer[<?php echo $question_id; ?>][answer_two_gap_match]" value="<?php echo $answer_two_gap_match; ?>"/>
                <p class="tutor-input-feedback tutor-has-icon">
                    <i class="tutor-icon-info-circle-outline-filled tutor-input-feedback-icon"></i>
                    <?php _e( 'Separate multiple answers by a vertical bar <strong>|</strong>. 1 answer per <strong>{dash}</strong> variable is defined in the question. Example: Apple | Banana | Orange', 'tutor' ); ?>
                </p>
            </div>
        </div>
		<?php
	} elseif ( $question_type === 'answer_sorting' ) {
		?>

		<div class="tutor-quiz-builder-group">
			<h4><?php _e( 'Answer title', 'tutor' ); ?></h4>
			<div class="tutor-quiz-builder-row">
				<div class="tutor-quiz-builder-col">
					<input type="text" name="quiz_answer[<?php echo esc_attr( $question_id ); ?>][answer_title]" value="">
				</div>
			</div>
		</div> <!-- /.tutor-quiz-builder-group -->

		<div class="tutor-quiz-builder-group">
			<h4><?php _e( 'Matched Answer title', 'tutor' ); ?></h4>
			<div class="tutor-quiz-builder-row">
				<div class="tutor-quiz-builder-col">
					<input type="text" name="quiz_answer[<?php echo esc_attr( $question_id ); ?>][matched_answer_title]" value="">
				</div>
			</div>
			<p class="help"></p>
		</div> <!-- /.tutor-quiz-builder-group -->

		<?php
	} elseif ( $question_type === 'matching' ) {
		?>
        <div class="tutor-mb-32">
            <label class="tutor-form-label">
                <?php _e('Answer title', 'tutor'); ?>
            </label>
            <div class="tutor-input-group tutor-mb-16">
                <input class="tutor-form-control" type="text" name="quiz_answer[<?php echo $question_id; ?>][answer_title]" value="<?php echo $answer_title; ?>"/>
            </div>
        </div>

        <div class="tutor-mb-32">
            <label class="tutor-form-label">
                <?php _e('Matched Answer title', 'tutor'); ?>
            </label>
            <div class="tutor-input-group tutor-mb-16">
                <input class="tutor-form-control" type="text" name="quiz_answer[<?php echo $question_id; ?>][matched_answer_title]" value="<?php echo $answer_two_gap_match; ?>"/>
            </div>
        </div>

        <div class="tutor-mb-32">
            <label class="tutor-form-label">
                <?php _e('Upload Image', 'tutor'); ?>
            </label>
            
            <?php 
                // Load thumbnail segment
                tutor_load_template_from_custom_path(tutor()->path.'/views/fragments/thumbnail-uploader.php', array(
                    'media_id' => $image_id,
                    'input_name' => 'quiz_answer['.$question_id.'][image_id]'
                ), false);
            ?>
        </div> 
        
        <div class="tutor-row tutor-mb-32">
            <div class="tutor-col-12">
                <label class="tutor-form-label">
                    <?php _e('Display format for options', 'tutor'); ?>
                </label>
            </div>
            <div class="tutor-col-auto">
                <div class="tutor-form-check tutor-mb-16">
                    <input type="radio" id="tutor_quiz_type_text" class="tutor-form-check-input" name="quiz_answer[<?php echo $question_id; ?>][answer_view_format]" value="text" <?php echo $answer_view_format ? checked('text', $answer_view_format) : 'checked="checked"' ?>/>
                    <label for="tutor_quiz_type_text"><?php _e('Only text', 'tutor'); ?></label>
                </div>
            </div>
            <div class="tutor-col-auto">
                <div class="tutor-form-check tutor-mb-16">
                    <input type="radio" id="tutor_quiz_type_img" class="tutor-form-check-input" name="quiz_answer[<?php echo $question_id; ?>][answer_view_format]" value="image" <?php echo checked('image', $answer_view_format) ?>/>
                    <label for="tutor_quiz_type_img"><?php _e('Only Image', 'tutor'); ?></label>
                </div>
            </div>
            <div class="tutor-col-auto">
                <div class="tutor-form-check tutor-mb-16">
                    <input type="radio" id="tutor_quiz_type_img_text" class="tutor-form-check-input" name="quiz_answer[<?php echo $question_id; ?>][answer_view_format]" value="text_image" <?php echo checked('text_image', $answer_view_format) ?>/>
                    <label for="tutor_quiz_type_img_text"><?php _e('Text &amp; Image both', 'tutor'); ?></label>
                </div>
            </div>
        </div>
		<?php
	} elseif ($question_type === 'image_matching'){ 
        ?>
        <div class="tutor-mb-32">
            <label class="tutor-form-label">
                <?php _e('Upload Image', 'tutor'); ?>
            </label>
            
            <?php 
                // Load thumbnail segment
                tutor_load_template_from_custom_path(tutor()->path.'/views/fragments/thumbnail-uploader.php', array(
                    'media_id' => $image_id,
                    'input_name' => 'quiz_answer['.$question_id.'][image_id]'
                ), false);
            ?>
        </div> 
        <div class="tutor-mb-32">
            <label class="tutor-form-label">
                <?php _e('Image matched text', 'tutor'); ?>
            </label>
            <div class="tutor-input-group tutor-mb-16">
                <input class="tutor-form-control" type="text" name="quiz_answer[<?php echo $question_id; ?>][answer_title]"  value="<?php echo $answer_title; ?>"/>
            </div>
        </div>
		<?php
	} elseif ( $question_type === 'image_answering' ) {
		?>

        <div class="tutor-mb-32">
            <label class="tutor-form-label">
                <?php _e('Upload Image', 'tutor'); ?>
            </label>
            
            <?php 
                // Load thumbnail segment
                tutor_load_template_from_custom_path(tutor()->path.'/views/fragments/thumbnail-uploader.php', array(
                    'media_id' => $image_id,
                    'input_name' => 'quiz_answer['.$question_id.'][image_id]'
                ), false);
            ?>
        </div> 
        
        <div class="tutor-mb-32">
            <label class="tutor-form-label">
                <?php _e('Answer input value', 'tutor'); ?>
            </label>
            <div class="tutor-input-group tutor-mb-16">
                <input class="tutor-form-control" type="text" name="quiz_answer[<?php echo $question_id; ?>][answer_title]" value="<?php echo $answer_title; ?>"/>
                <p class="tutor-input-feedback tutor-has-icon">
                    <i class="tutor-icon-info-circle-outline-filled tutor-input-feedback-icon"></i>
                    <?php _e('The answers that students enter should match with this text. Write in <strong>small caps</strong>','tutor'); ?>
                </p>
            </div>
        </div>
		<?php
	}
	?>

    <div class="tutor-quiz-answers-form-footer">
        <button type="button" id="quiz-answer-save-btn" class="tutor-answer-save-btn tutor-btn tutor-btn-primary tutor-btn-sm">
            <?php _e('Update Answer', 'tutor'); ?>
        </button>
    </div>
</div>
