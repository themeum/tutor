<div class="quiz-meta-info d-flex justify-content-between">
    <div class="quiz-meta-info-left d-flex">
        <?php
            $total_questions = tutor_utils()->total_questions_for_student_by_quiz(get_the_ID());

            if($total_questions){
        ?>
        <div class="quiz-qno d-flex">
            <span class="text-regular-body color-text-hints tutor-mr-10"><?php _e('Questions No', 'tutor'); ?>:</span>
            <span class="text-bold-body color-text-title">
                <?php echo $total_questions; ?>
            </span>
        </div>
        <?php } ?>
        <div class="quiz-total-attempt d-flex d-xs-none">
            <span class="text-regular-body color-text-hints tutor-mr-10">Total <?php _e('Attempted', 'tutor'); ?>:</span>
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

<div class="quiz-flash-message">
    <?php if ($remaining_time_context < 0) { ?>
    <div class="tutor-quiz-warning-box time-remaining-warning d-flex align-items-center justify-content-between" data-attempt-allowed="<?php esc_attr_e( $attempts_allowed );?>" data-attempt-remaining="<?php esc_attr_e( $attempt_remaining );?>">
        <div class="flash-info d-flex align-items-center">
            <span class="ttr-warning-outline-circle-filled color-design-warning tutor-mr-7"></span>
            <span class="text-regular-caption color-text-title">
                <?php
                    _e('Your time limit for this quiz has expired, please reattempt the quiz. Attempts
                    remaining:', 'tutor');
                ?>
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
        <div class="flash-action">
            <form id="tutor-start-quiz" method="post">
                <?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>

                <input type="hidden" value="<?php echo $quiz_id; ?>" name="quiz_id"/>
                <input type="hidden" value="tutor_start_quiz" name="tutor_action"/>

                <button type="submit" class="tutor-btn tutor-btn-md reattempt-btn" name="start_quiz_btn" value="start_quiz">
                    <?php _e( 'Reattempt', 'tutor' ); ?>
                </button>
            </form>
        </div>
    </div>
    <?php 
        }
        if ($remaining_time_context < 0 && $attempts_allowed == $attempted_count) {
    ?>
    <div class="tutor-quiz-warning-box time-over d-flex align-items-center justify-content-between">
        <div class="flash-info d-flex align-items-center">
            <span class="ttr-cross-circle-outline-filled color-design-danger tutor-mr-7"></span>
            <span class="text-regular-caption color-text-title">
                <?php _e('Unfortunately, you are out of time and quiz attempts.', 'tutor'); ?>
            </span>
        </div>
    </div>
    <?php } ?>
</div>