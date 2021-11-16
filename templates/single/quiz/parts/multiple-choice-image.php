<div class="quiz-question-ans-choice-area tutor-mt-70 question-type-<?php echo $question_type; ?> <?php echo $answer_required? 'quiz-answer-required':''; ?> "">
    <div id="tutor-quiz-image-multiple-choice" class="tutor-quiz-wrap">
        <div class="tutor-image-checkbox">
            <?php
                if ( is_array($answers) && count($answers) ) {
                    foreach ($answers as $answer){
                        $answer_title = stripslashes($answer->answer_title);
                        $answer->is_correct ? $quiz_answers[] = $answer->answer_id : 0; ?>
            <label for="<?php echo $answer->answer_id; ?>" class="tutor-form-check-input">
                <input type="checkbox" class="tutor-form-check-input" id="<?php echo $answer->answer_id; ?>" name="attempt[<?php echo $is_started_quiz->attempt_id; ?>][quiz_question][<?php echo $question->question_id; ?>]" value="<?php echo $answer->answer_id; ?>" />
                <div class="tutor-multiple-checkbox">
                    <img src="<?php echo wp_get_attachment_image_url($answer->image_id, 'full') ?>" />
                    <?php
                            if ($answer->answer_view_format == 'text_image') {
                    ?>
                    <div class="text-regular-body color-text-primary tutor-px-15 tutor-py-10 ">
                        <?php
                             echo $answer_title;
                        ?>
                    </div>
                    <?php } ?>
                    <span class="tutor-icon-checkbox"></span>
                </div>
            </label>
            <?php } } ?>
        </div>
    </div>
</div>