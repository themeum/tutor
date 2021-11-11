<div id="quiz-matching-ans-area" class="quiz-question-ans-choice-area tutor-mt-70 question-type-<?php echo $question_type; ?> <?php echo $answer_required? 'quiz-answer-required':''; ?> ">
    <?php
        if ( is_array($answers) && count($answers) ) {
            foreach ($answers as $answer){
                $answer_title = stripslashes($answer->answer_title);
                $answer->is_correct ? $quiz_answers[] = $answer->answer_id : 0; 
            if ($answer->answer_view_format !== 'image'){
        ?>
        <div class="quiz-question-ans-choice">
            <label for="<?php echo $answer->answer_id; ?>">
                <input class="tutor-form-check-input" id="<?php echo $answer->answer_id; ?>" name="attempt[<?php echo $is_started_quiz->attempt_id; ?>][quiz_question][<?php echo $question->question_id; ?>]" type="checkbox" value="<?php echo $answer->answer_id; ?>">
                <span class="text-regular-body color-text-primary">
                    <?php
                        if ($answer->answer_view_format !== 'image'){ echo $answer_title;}
                    ?>
                </span>
            </label>
        </div>
    <?php } } } ?>
</div>