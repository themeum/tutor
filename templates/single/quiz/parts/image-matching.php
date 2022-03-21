<div id="quiz-image-matching-ans-area" class="quiz-question-ans-choice-area tutor-mt-40 question-type-<?php echo $question_type; ?> <?php echo $answer_required? 'quiz-answer-required':''; ?> "">
    <div class="matching-quiz-question-desc tutor-draggable tutor-mb-44">
        <?php
            $rand_answers = tutor_utils()->get_answers_by_quiz_question($question->question_id, true);
            foreach ($rand_answers as $rand_answer){
        ?>
        <div class="tutor-quiz-border-box" draggable="true">
            <?php
                if ($question_type === 'matching'){
                    echo '<span class="tutor-dragging-text-conent tutor-fs-6 tutor-color-black">'.stripslashes($rand_answer->answer_two_gap_match).'</span>';
                }else{
                    echo '<span class="tutor-dragging-text-conent tutor-fs-6 tutor-color-black">'.stripslashes($rand_answer->answer_title).'</span>';
                }
            ?>
            <span class="tutor-icon-humnurger-filled tutor-color-black-fill"></span>
            <input type="hidden" data-name="attempt[<?php echo $is_started_quiz->attempt_id; ?>][quiz_question][<?php echo $question->question_id; ?>][answers][]" value="<?php echo $rand_answer->answer_id; ?>" >
        </div>
        <?php } ?>
    </div>
    <div class="quiz-image-matching-ans tutor-d-flex tutor-align-items-start">
        <?php
            if ( is_array($answers) && count($answers) ) {
                foreach ($answers as $answer){
        ?>
        <div class="quiz-image-box">
            <div class="quiz-image tutor-d-flex tutor-mb-16">
                <img src="<?php echo wp_get_attachment_image_url($answer->image_id, 'full') ?>" />
            </div>
            <div class="tutor-quiz-dotted-box tutor-dropzone flex-center">
                <span class="tutor-dragging-text-conent tutor-fs-6 tutor-color-black">
                    <?php _e('Drag your answer', 'tutor'); ?>
                </span>
            </div>
        </div>
        <?php } } ?>
    </div>
</div>