<div id="quiz-image-matching-ans-area" class="quiz-question-ans-choice-area tutor-mt-70 question-type-<?php echo $question_type; ?> <?php echo $answer_required? 'quiz-answer-required':''; ?> "">
    <div class="quiz-image-matching-ans d-flex align-items-start">
        <?php
            if ( is_array($answers) && count($answers) ) {
                foreach ($answers as $answer){
                    $answer_title = stripslashes($answer->answer_title);
                    $answer->is_correct ? $quiz_answers[] = $answer->answer_id : 0; ?>
        
        <div class="quiz-image-box">
            <label for="<?php echo $answer->answer_id; ?>">
                <?php if ($answer->answer_view_format === 'image' || $answer->answer_view_format === 'text_image'){ ?>
                <div class="quiz-image d-flex tutor-mb-15">
                    <img src="<?php echo wp_get_attachment_image_url($answer->image_id, 'full') ?>"  width="162" height="162" />
                </div>
                <?php } ?>
                <input class="tutor-form-check-input" id="<?php echo $answer->answer_id; ?>" name="attempt[<?php echo $is_started_quiz->attempt_id; ?>][quiz_question][<?php echo $question->question_id; ?>]" type="checkbox" value="<?php echo $answer->answer_id; ?>">
                <span class="text-regular-body color-text-primary">
                    <?php
                        if ($answer->answer_view_format !== 'image'){ echo $answer_title;}
                    ?>
                </span>
            </label>
        </div>
        <?php } } ?>
    </div>
</div>