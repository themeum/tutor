<input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>"/>
<input type="hidden" name="topic_id" value="<?php echo $topic_id; ?>"/>

<div id="quiz-builder-tab-quiz-info">
    <div class="tutor-mb-30">
        <label class="tutor-form-label"><?php _e('Quiz Title', 'tutor'); ?></label>
        <div class="tutor-input-group tutor-mb-15">
            <input type="text" name="quiz_title" class="tutor-form-control tutor-mb-10" placeholder="<?php _e('Type your quiz title here', 'tutor'); ?>" value="<?php echo $quiz ? htmlspecialchars( stripslashes($quiz->post_title) ) : ''; ?>"/>
        </div>
    </div>
    <div class="tutor-mb-30">
        <label class="tutor-form-label">Lesson Summary</label>
        <div class="tutor-input-group tutor-mb-15">
            <textarea name="quiz_description" class="tutor-form-control tutor-mb-10" placeholder="Lesson Summary" rows="5"><?php echo $quiz ? stripslashes($quiz->post_content) : ''; ?></textarea>
        </div>
    </div>
    <?php do_action('tutor_quiz_edit_modal_info_tab_after', $quiz) ?>
</div>

<div id="quiz-builder-tab-questions" class="quiz-builder-tab-container">
    <div class="quiz-builder-tab-body">
        <div class="quiz-builder-questions-wrap">

            <?php
            $questions = ($quiz_id && $quiz_id>0) ? tutor_utils()->get_questions_by_quiz($quiz_id) : array();

            if ($questions) {
                foreach ($questions as $question) {
                    ?>
                    <div class="quiz-builder-question-wrap" data-question-id="<?php echo $question->question_id; ?>">
                        <div class="quiz-builder-question">
                            <span class="question-sorting">
                                <i class="tutor-icon-move"></i>
                            </span>

                            <span class="question-title"><?php echo stripslashes($question->question_title); ?></span>

                            <span class="question-icon">
                                <?php
                                $type = tutor_utils()->get_question_types($question->question_type);
                                echo $type['icon'] . ' ' . $type['name'];
                                ?>
                            </span>

                            <span class="question-edit-icon">
                                <a href="javascript:;" class="tutor-quiz-open-question-form" data-question-id="<?php echo $question->question_id; ?>">
                                <i class="tutor-icon-pencil"></i> 
                            </a>
                            </span>
                        </div>

                        <div class="quiz-builder-qustion-trash">
                            <a href="javascript:;" class="tutor-quiz-question-trash" data-question-id="<?php echo $question->question_id; ?>"><i class="tutor-icon-garbage"></i> </a>
                        </div>
                    </div>
                    <?php
                }
            }
            ?>
        </div>

        <div class="tutor-quiz-builder-form-row">
            <a href="javascript:;" class="tutor-quiz-add-question-btn tutor-quiz-open-question-form">
                <i class="tutor-icon-add-line"></i>
                <?php _e('Add Question', 'tutor'); ?>
            </a>
        </div>
    </div>
</div>

<div id="quiz-builder-tab-settings" class="quiz-builder-tab-container">
    <div class="quiz-builder-tab-body">

        <div class="quiz-builder-modal-settins">
            <div class="tutor-quiz-builder-group">
                <h4> <?php _e('Time Limit', 'tutor'); ?> </h4>
                <div class="tutor-quiz-builder-row">
                    <div class="tutor-quiz-builder-col auto-width">
                        <input type="text" name="quiz_option[time_limit][time_value]" value="<?php echo tutor_utils()->get_quiz_option($quiz_id, 'time_limit.time_value', 0) ?>">
                    </div>
                    <div class="tutor-quiz-builder-col auto-width">
                        <?php $limit_time_type = tutor_utils()->get_quiz_option($quiz_id, 'time_limit.time_type', 'minutes') ?>
                        <select name="quiz_option[time_limit][time_type]">
                            <option value="seconds" <?php selected('seconds', $limit_time_type); ?>><?php _e('Seconds', 'tutor'); ?></option>
                            <option value="minutes" <?php selected('minutes', $limit_time_type); ?>><?php _e('Minutes', 'tutor'); ?></option>
                            <option value="hours" <?php selected('hours', $limit_time_type); ?>><?php _e('Hours', 'tutor'); ?></option>
                            <option value="days" <?php selected('days', $limit_time_type); ?>><?php _e('Days', 'tutor'); ?></option>
                            <option value="weeks" <?php selected('weeks', $limit_time_type); ?>><?php _e('Weeks', 'tutor'); ?></option>
                        </select>
                    </div>
                    <div class="tutor-quiz-builder-col auto-width">
                        <label class="btn-switch">
                            <input type="checkbox" value="1" name="quiz_option[hide_quiz_time_display]" <?php checked('1', tutor_utils()->get_quiz_option($quiz_id, 'hide_quiz_time_display')); ?> />
                            <div class="btn-slider btn-round"></div>
                        </label>
                        <span><?php _e('Hide quiz time - display', 'tutor'); ?></span>
                    </div>
                </div>
                <p class="help"><?php _e('Time limit for this quiz. 0 means no time limit.', 'tutor'); ?></p>
            </div> <!-- .tutor-quiz-builder-group -->

            <div class="tutor-quiz-builder-group">
                <h4><?php _e('Quiz Feedback Mode', 'tutor'); ?> </h4>

                <p class="help">(<?php _e('Pick the quiz system"s behaviour on choice based questions', 'tutor'); ?>)</p>

                <div class="tutor-quiz-builder-row">
                    <div class="tutor-quiz-builder-col">
                        <label class="tutor-quiz-feedback-mode">
                            <input type="radio" name="quiz_option[feedback_mode]" value="default" <?php checked('default', tutor_utils()->get_quiz_option($quiz_id, 'feedback_mode')); ?>>
                            <span class="radio-icon"></span>
                            <div class="tutor-quiz-feedback-mode-option">
                                <h4 class="tutor-quiz-feedback-option-option-title"><?php _e('Default', 'tutor'); ?></h4>
                                <p class="tutor-quiz-feedback-option-subtitle"><?php _e('Answers shown after quiz is finished', 'tutor'); ?></p>
                            </div>
                        </label>
                    </div>
                    <div class="tutor-quiz-builder-col">
                        <label class="tutor-quiz-feedback-mode">
                            <input type="radio" name="quiz_option[feedback_mode]" value="retry" <?php checked('retry', tutor_utils()->get_quiz_option($quiz_id, 'feedback_mode')); ?>>
                            <span class="radio-icon"></span>
                            <div class="tutor-quiz-feedback-mode-option">
                                <h4 class="tutor-quiz-feedback-option-option-title"><?php _e('Retry Mode', 'tutor'); ?></h4>
                                <p class="tutor-quiz-feedback-option-subtitle"><?php _e('Unlimited attempts on each question.', 'tutor'); ?></p>
                            </div>
                        </label>
                    </div>
                    <div class="tutor-quiz-builder-col">
                        <label class="tutor-quiz-feedback-mode">
                            <input type="radio" name="quiz_option[feedback_mode]" value="reveal" <?php checked('reveal', tutor_utils()->get_quiz_option($quiz_id, 'feedback_mode')); ?>>
                            <span class="radio-icon"></span>
                            <div class="tutor-quiz-feedback-mode-option">
                                <h4 class="tutor-quiz-feedback-option-option-title"><?php _e('Reveal Mode', 'tutor'); ?></h4>
                                <p class="tutor-quiz-feedback-option-subtitle"><?php _e('Show result after the attempt.', 'tutor'); ?></p>
                            </div>
                        </label>
                    </div>

                </div>
            </div> <!-- .tutor-quiz-builder-group -->

            <div class="tutor-quiz-builder-group">
                <h4><?php _e('Attempts Allowed', 'tutor'); ?> <span>(<?php _e('Optional', 'tutor'); ?>)</span></h4>
                <div class="tutor-quiz-builder-row">
                    <div class="tutor-quiz-builder-col">
                        <?php
                        $default_attempts_allowed = tutor_utils()->get_option('quiz_attempts_allowed');
                        $attempts_allowed = (int) tutor_utils()->get_quiz_option($quiz_id, 'attempts_allowed', $default_attempts_allowed);
                        ?>

                        <div class="tutor-field-type-slider" data-min="0" data-max="20">
                            <p class="tutor-field-type-slider-value"><?php echo $attempts_allowed; ?></p>
                            <div class="tutor-field-slider"></div>
                            <input type="hidden" value="<?php echo $attempts_allowed; ?>" name="quiz_option[attempts_allowed]" />
                        </div>
                    </div>
                </div>
                <p class="help"><?php _e('Restriction on the number of attempts a student is allowed to take for this quiz. 0 for no limit', 'tutor'); ?></p>
            </div> <!-- .tutor-quiz-builder-group -->

            <div class="tutor-quiz-builder-group">
                <h4><?php _e('Passing Grade (%)', 'tutor'); ?></h4>
                <div class="tutor-quiz-builder-row">
                    <div class="tutor-quiz-builder-col">
                        <input type="number" name="quiz_option[passing_grade]" value="<?php echo tutor_utils()->get_quiz_option($quiz_id, 'passing_grade', 80) ?>" size="10">
                    </div>
                </div>
                <p class="help"><?php _e('Set the passing percentage for this quiz', 'tutor'); ?></p>
            </div> <!-- .tutor-quiz-builder-group -->

            <div class="tutor-quiz-builder-group">
                <h4><?php _e('Max questions allowed to answer', 'tutor'); ?></h4>
                <div class="tutor-quiz-builder-row">
                    <div class="tutor-quiz-builder-col">
                        <input type="number" name="quiz_option[max_questions_for_answer]" value="<?php echo tutor_utils()->get_quiz_option($quiz_id, 'max_questions_for_answer', 10) ?>">
                    </div>
                </div>
                <p class="help"><?php _e('This amount of question will be available for students to answer, and question will comes randomly from all available questions belongs with a quiz, if this amount greater than available question, then all questions will be available for a student to answer.', 'tutor'); ?></p>
            </div> <!-- .tutor-quiz-builder-group -->

            <?php do_action('tutor_quiz_edit_modal_settings_tab_after', $quiz) ?>

        </div>
    </div>
</div>
