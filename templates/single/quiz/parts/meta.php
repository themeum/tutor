<div class="quiz-meta-info d-flex justify-content-between">
    <div class="quiz-meta-info-left d-flex">
        <?php
            $total_questions = tutor_utils()->total_questions_for_student_by_quiz(get_the_ID());

            if($total_questions){
        ?>
        <div class="quiz-qno d-flex">
            <p class="text-regular-body color-text-hints tutor-mr-10"><?php _e('Questions No', 'tutor'); ?>:</p>
            <span class="text-bold-body color-text-title">
                <?php echo $total_questions; ?>
            </span>
        </div>
        <?php } ?>
        <div class="quiz-total-attempt d-flex d-xs-none">
            <p class="text-regular-body color-text-hints tutor-mr-10">Total
            <?php _e('Attempted', 'tutor'); ?>:</p>
            <span class="text-bold-body color-text-title">
            <?php
                if($attempts_allowed != 0){
                    if($attempted_count){
                        echo $attempted_count . '/';
                    }
                }
                echo $attempts_allowed == 0 ? __('No limit', 'tutor') : $attempts_allowed;
            ?>
            </span>
        </div>
    </div>
    <?php
        if ( ! $hide_quiz_time_display){
    ?>
    <div class="quiz-meta-info-right">
        <div class="quiz-time-remaining d-flex">
            <?php if ($remaining_time_context > 0) { ?>
            <div class="quiz-time-remaining-progress-circle">
                <svg viewBox="0 0 50 50" width="50" height="50" style="--quizeProgress: 30;">
                    <circle cx="0" cy="0" r="7"></circle>
                    <circle cx="0" cy="0" r="7"></circle>
                </svg>
            </div>
            <?php } ?>
            <?php if ($remaining_time_context < 0) { ?>
            <div class="quiz-time-remaining-expired-circle">
                <svg viewBox="0 0 50 50" width="50" height="50">
                    <circle cx="0" cy="0" r="8"></circle>
                </svg>
            </div>
            <?php } ?>
            <p class="text-regular-body color-text-hints tutor-mr-10"><?php _e( 'Time remaining: ', 'tutor' ); ?></p>
            <span id="tutor-quiz-time-update" class="text-medium-body <?php if ($remaining_time_context < 0) { echo 'color-text-error';} ?>" data-attempt-settings="<?php echo esc_attr(json_encode($is_started_quiz)) ?>" data-attempt-meta="<?php echo esc_attr(json_encode($quiz_attempt_info)) ?>"><?php echo $remaining_time_context; ?></span>
        </div>
    </div>
    <?php } ?>
</div>