<div id="quiz-ordering-ans-area" class="quiz-question-ans-choice-area quiz-image-ordering-ans-area tutor-mt-70 question-type-<?php echo $question_type; ?> <?php echo $answer_required? 'quiz-answer-required':''; ?> ">
    <?php
        if ( is_array($answers) && count($answers) ) {
            $answer_i = 0;
            foreach ($answers as $answer){
                $answer_i++;
                $answer_title = stripslashes($answer->answer_title);
                $answer->is_correct ? $quiz_answers[] = $answer->answer_id : 0; 
                if ($answer->answer_view_format === 'image' || $answer->answer_view_format === 'text_image'){
    ?>
    <div class="quiz-image-ordering-ans d-flex align-items-center">
        <div class="tutor-quiz-ans-no text-medium-body color-text-primary">
            <?php 
                if($answer_i < 9){
                    echo 0;
                }
                echo $answer_i.'.';
            ?>
        </div>
        <div class="quiz-image-ordering-ans-item d-flex tutor-ml-20">
            <div class="tutor-quiz-image-ordering-icon d-flex align-items-center justify-content-center">
                <span class="ttr-humnurger-filled color-black-fill"></span>
            </div>
            <div class="tutor-quiz-image-ordering-item d-flex align-items-center tutor-p-10">
                <img src="<?php echo wp_get_attachment_image_url($answer->image_id, 'full') ?>" />
                <span class="text-regular-body color-text-primary tutor-ml-16">
                <?php echo $answer_title; ?>
                </span>
                <input type="hidden" name="attempt[<?php echo $is_started_quiz->attempt_id; ?>][quiz_question][<?php echo $question->question_id; ?>][answers][]" value="<?php echo $answer->answer_id; ?>" >
            </div>
        </div>
    </div>
    <?php } else { ?>
    <div class="quiz-ordering-ans d-flex align-items-center">
        <div class="tutor-quiz-ans-no text-medium-body color-text-primary">
            <?php 
                if($answer_i < 9){
                    echo 0;
                }
                echo $answer_i.'.';
            ?>
        </div>
        <div class="quiz-ordering-ans-item tutor-ml-30">
            <div class="tutor-quiz-border-box">
                <span class="text-regular-body color-text-primary">
                    <?php echo $answer_title; ?>
                </span>
                <span class="ttr-humnurger-filled color-black-fill"></span>
                <input type="hidden" name="attempt[<?php echo $is_started_quiz->attempt_id; ?>][quiz_question][<?php echo $question->question_id; ?>][answers][]" value="<?php echo $answer->answer_id; ?>" >
            </div>
        </div>
    </div>

    <?php } } } ?>
</div>