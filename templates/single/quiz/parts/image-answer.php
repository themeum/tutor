<div class="quiz-question-ans-choice-area tutor-mt-40 question-type-<?php echo $question_type; ?> <?php echo $answer_required? 'quiz-answer-required':''; ?> ">
    <?php
        if ( is_array($answers) && count($answers) ) {
            foreach ($answers as $answer){
                ?>
        <div class="tutor-image-answer">
            <?php
                if (intval($answer->image_id)){
            ?>
            <div class="quiz-short-ans-image tutor-mb-32 tutor-mb-md-30">
                <?php echo '<img src="'.wp_get_attachment_image_url($answer->image_id, 'full').'" />'; ?>
            </div>
            <?php } ?>
            <div class="quiz-question-ans-choice">
                <input type="text" class="tutor-form-control" placeholder="<?php _e('Write your answer here', 'tutor'); ?>" name="attempt[<?php echo $is_started_quiz->attempt_id; ?>][quiz_question][<?php echo $question->question_id; ?>][answer_id][<?php echo $answer->answer_id; ?>]" />
            </div>
        </div>
    <?php } } ?>
</div>