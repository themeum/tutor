<div class="quiz-question-ans-choice-area tutor-mt-70">
    <?php
        if ( is_array($answers) && count($answers) ) {
            foreach ($answers as $answer){
                ?>
	<div class="quiz-question-ans-choice">
        <textarea class="tutor-form-control question_type_<?php echo $question_type; ?>" name="attempt[<?php echo $is_started_quiz->attempt_id; ?>][quiz_question][<?php echo $question->question_id; ?>]"></textarea>
	</div>
    <?php
        if ($question_type === 'short_answer') {
            $get_option_meta = tutor_utils()->get_quiz_option($quiz_id);
            if(isset($get_option_meta['short_answer_characters_limit'])){
                if($get_option_meta['short_answer_characters_limit'] != "" ){
                    $characters_limit = tutor_utils()->avalue_dot('short_answer_characters_limit', $quiz_attempt_info);
                    echo '<p class="answer_limit_desc">'. __('characters remaining', 'tutor' ) .' :<span class="characters_remaining">'.$characters_limit.'</span> </p>';
                }
            }
        }
    ?>
    <?php } } ?>
</div>