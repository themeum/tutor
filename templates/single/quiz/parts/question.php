<div id="tutor-quiz-attempt-questions-wrap" data-question-layout-view="<?php echo $question_layout_view; ?>">

    <?php
    if ($question_layout_view === 'question_pagination'){
        $question_i = 0;
        ?>
        <div class="tutor-quiz-questions-pagination">
            <ul>
                <?php
                foreach ($questions as $question) {
                    $question_i++;
                    echo "<li><a href='#quiz-attempt-single-question-{$question->question_id}' class='tutor-quiz-question-paginate-item'>{$question_i}</a> </li>";
                }
                ?>
            </ul>
        </div>
        <?php
    }
    ?>

    <div id="tutor-quiz-time-expire-wrapper" data-attempt-allowed="<?php esc_attr_e( $attempts_allowed );?>" data-attempt-remaining="<?php esc_attr_e( $attempt_remaining );?>">
        <div class="tutor-alert">
            <div class="text">

            </div>
            <div>
                <form id="tutor-start-quiz" method="post">
                    <?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>

                    <input type="hidden" value="<?php echo $quiz_id; ?>" name="quiz_id"/>
                    <input type="hidden" value="tutor_start_quiz" name="tutor_action"/>

                    <button type="submit" class="tutor-btn tutor-is-warning" name="start_quiz_btn" value="start_quiz">
                        <?php _e( 'Reattempt', 'tutor' ); ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <form id="tutor-answering-quiz" method="post">
        <?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
        <input type="hidden" value="<?php echo $is_started_quiz->attempt_id; ?>" name="attempt_id"/>
        <input type="hidden" value="tutor_answering_quiz_question" name="tutor_action"/>
        <?php
        $question_i = 0;
        foreach ($questions as $question) {
            $question_i++;
            $question_settings = maybe_unserialize($question->question_settings);

            $style_display = ($question_layout_view !== 'question_below_each_other' && $question_i == 1) ? 'block' : 'none';
            if ($question_layout_view === 'question_below_each_other'){
                $style_display = 'block';
            }

            $next_question = isset($questions[$question_i]) ? $questions[$question_i] : false;
            $previous_question = $question_i>1 ? $questions[$question_i-1] : false;
            ?>
            <div id="quiz-attempt-single-question-<?php echo $question->question_id; ?>" class="quiz-attempt-single-question quiz-attempt-single-question-<?php echo $question_i; ?>" style="display: <?php echo $style_display; ?> ;" <?php echo $next_question ? "data-next-question-id='#quiz-attempt-single-question-{$next_question->question_id}'" : '' ; ?> data-quiz-feedback-mode="<?php echo $feedback_mode; ?>" >
                <div class="quiz-question tutor-mt-60 tutor-mr-100">
                    <?php echo "<input type='hidden' name='attempt[{$is_started_quiz->attempt_id}][quiz_question_ids][]' value='{$question->question_id}' />";

                        $question_type = $question->question_type;

                        $rand_choice = false;
                        if($question_type == 'single_choice' || $question_type == 'multiple_choice'){
                            $choice = maybe_unserialize($question->question_settings);
                            if(isset($choice['randomize_question'])){
                                $rand_choice = $choice['randomize_question'] == 1 ? true : false;
                            }
                        }

                        $answers = tutor_utils()->get_answers_by_quiz_question($question->question_id, $rand_choice);
                        $show_question_mark = (bool) tutor_utils()->avalue_dot('show_question_mark', $question_settings);
                        $answer_required = (bool) tutor_utils()->array_get('answer_required', $question_settings);

                        echo '<div class="quiz-question-title text-medium-h4 color-text-primary tutor-mb-20">';
                        if ( ! $hide_question_number_overview){
                            echo $question_i. ". ";
                        }
                        echo stripslashes($question->question_title);
                        echo '</div>';

                        if ($show_question_mark){
                            echo '<p class="question-marks"> '.__('Marks : ', 'tutor').$question->question_mark.' </p>';
                        }

                        $question_description = nl2br( stripslashes($question->question_description) );
                        if ($question_description){
                            echo "<div class='matching-quiz-question-desc'><span class='text-regular-caption color-text-subsued'>{$question_description}</span></div>";
                        }
                    ?>
                </div>
                <!-- Quiz Answer -->
                <?php

                    $answer = '';

                    // True False
                    if ( $question_type === 'true_false' || $question_type === 'single_choice' ) {
                        include 'true-false.php';
                    }

                    // Single Choice Image
                    if ( $question_type === 'single_choice' && $answer->answer_view_format == 'image' ||  $answer->answer_view_format === 'text_image' ) {
                        include 'true-false-image.php';
                    }

                    // Multiple Choice
                    if ($question_type === 'multiple_choice'){
                        include 'multiple-choice.php';
                    }

                    // Multiple Choice Image
                    if ($question_type === 'multiple_choice' && $answer->answer_view_format == 'image'){
                        include 'multiple-choice-image.php';
                    }

                    // Fill In The Blank
                    if ($question_type === 'fill_in_the_blank'){
                        include 'fill-in-the-blank.php';
                    }

                    // Ordering
                    if ($question_type === 'ordering'){
                        include 'ordering.php';
                    }
                    

                    // Matching
                    if ($question_type === 'matching'){
                        include 'matching.php';
                    }

                    // Image Matching
                    if ($question_type === 'image_matching'){
                        include 'image-matching.php';
                    }

                    // Image Answer
                    if ($question_type === 'image_answering'){
                        include 'image-answer.php';
                    }

                    // Open Ended
                    if ($question_type === 'open_ended'){
                        include 'open-ended.php';
                    }

                    // Short Answer
                    if ($question_type === 'short_answer'){
                        include 'short-answer.php';
                    }

                ?>
                

                <?php
                    if ($question_layout_view !== 'question_below_each_other'){
                ?>
                    <div class="tutor-quiz-btn-grp tutor-mt-60">
                        <button type="submit" class="tutor-btn tutor-btn-primary tutor-btn-md start-quiz-btn <?php echo $next_question ? 'tutor-quiz-answer-next-btn' : 'tutor-quiz-submit-btn'; ?>">
                            <?php $next_question ? _e( 'Submit &amp; Next', 'tutor' ) : _e( 'Submit Quiz', 'tutor' ); ?>
                        </button>
                        <button type="submit" class="tutor-ml-30 tutor-btn tutor-btn-disable-outline tutor-no-hover tutor-btn-md tutor-next-btn <?php echo $next_question ? 'tutor-quiz-answer-next-btn' : 'tutor-quiz-submit-btn'; ?>" style="border:0px;padding:0px;">
                            <?php _e( "Skip Quiz", "tutor" ); ?>
                        </button>
                    </div>
                <?php
                    }
                ?>
            </div>

            <?php
        }

        if ($question_layout_view === 'question_below_each_other'){
            ?>
            <div class="quiz-answer-footer-bar">
                <div class="quiz-footer-button">
                    <button type="submit" name="quiz_answer_submit_btn" value="quiz_answer_submit" class="tutor-btn"><?php _e( 'Submit Quiz', 'tutor' ); ?></button>
                </div>
            </div>
        <?php } ?>

    </form>
</div>