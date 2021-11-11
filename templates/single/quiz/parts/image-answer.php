<div id="quiz-matching-ans-area" class="quiz-question-ans-choice-area tutor-mt-70 question-type-<?php echo $question_type; ?> <?php echo $answer_required? 'quiz-answer-required':''; ?> ">
    <div class="quiz-image-answering-wrap">
        <?php
            if ( is_array($answers) && count($answers) ) {
                foreach ($answers as $answer){
                    ?>
        <div class="quiz-image-answering-answer">
            <?php
                if (intval($answer->image_id)){
            ?>
            <div class="quiz-image-answering-image-wrap">
                <?php echo '<img src="'.wp_get_attachment_image_url($answer->image_id, 'full').'" />'; ?>
            </div>

            <div class="quiz-image-answering-input-field-wrap">
                <input type="text"  name="attempt[<?php echo $is_started_quiz->attempt_id; ?>][quiz_question][<?php echo $question->question_id; ?>][answer_id][<?php echo $answer->answer_id; ?>]" >
            </div>
            <?php } ?>
        </div>
        <?php } } ?>
    </div>
</div>