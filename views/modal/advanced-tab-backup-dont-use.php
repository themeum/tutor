
<div id="quiz-builder-tab-advanced-options" class="quiz-builder-tab-container">
    <div class="tutor-quiz-builder-group">
        <div class="tutor-quiz-builder-row">
            <div class="tutor-quiz-builder-col auto-width">
                <label class="btn-switch">
                    <input type="checkbox" value="1" name="quiz_option[quiz_auto_start]" <?php checked('1', tutor_utils()->get_quiz_option($quiz_id, 'quiz_auto_start')); ?> />
                    <div class="btn-slider btn-round"></div>
                </label>
                <span><?php _e('Quiz Auto Start', 'tutor'); ?></span>
            </div>
        </div>
        <p class="help"><?php _e('If you enable this option, the quiz will start automatically after the page is loaded.', 'tutor'); ?></p>
    </div>

    <div class="tutor-quiz-builder-group">
        <div class="tutor-quiz-builder-row">
            <div class="tutor-quiz-builder-col auto-width">
                <h4><?php _e('Question Layout', 'tutor'); ?></h4>

                <select name="quiz_option[question_layout_view]">
                    <option value=""><?php _e('Set question layout view', 'tutor'); ?></option>
                    <option value="single_question" <?php selected('single_question', tutor_utils()->get_quiz_option($quiz_id, 'question_layout_view')); ?>> <?php _e('Single Question', 'tutor'); ?> </option>
                    <option value="question_pagination" <?php selected('question_pagination', tutor_utils()->get_quiz_option($quiz_id, 'question_layout_view')); ?>> <?php _e('Question Pagination', 'tutor'); ?> </option>
                    <option value="question_below_each_other" <?php selected('question_below_each_other', tutor_utils()->get_quiz_option($quiz_id, 'question_layout_view')); ?>> <?php _e('Question below each other', 'tutor'); ?> </option>
                </select>
            </div>

            <div class="tutor-quiz-builder-col auto-width">
                <h4><?php _e('Questions Order', 'tutor'); ?></h4>

                <select name="quiz_option[questions_order]">
                    <option value="rand" <?php selected('rand', tutils()->get_quiz_option($quiz_id, 'questions_order')); ?>> <?php _e('Random', 'tutor'); ?> </option>
                    <option value="sorting" <?php selected('sorting', tutils()->get_quiz_option($quiz_id, 'questions_order')); ?>> <?php _e('Sorting', 'tutor'); ?> </option>

                    <option value="asc" <?php selected('asc', tutils()->get_quiz_option($quiz_id, 'questions_order')); ?>> <?php _e('Ascending', 'tutor'); ?> </option>
                    <option value="desc" <?php selected('desc', tutils()->get_quiz_option($quiz_id, 'questions_order')); ?>> <?php _e('Descending', 'tutor'); ?> </option>
                </select>
            </div>

        </div>
    </div>

    <div class="tutor-quiz-builder-group">
        <div class="tutor-quiz-builder-row">
            <div class="tutor-quiz-builder-col auto-width">
                <label class="btn-switch">
                    <input type="checkbox" value="1" name="quiz_option[hide_question_number_overview]" <?php checked('1', tutor_utils()->get_quiz_option($quiz_id, 'hide_question_number_overview')); ?> />
                    <div class="btn-slider btn-round"></div>
                </label>
                <span><?php _e('Hide question number', 'tutor'); ?></span>
            </div>
        </div>
        <p class="help"><?php _e('Show/hide question number during attempt.', 'tutor'); ?></p>
    </div>

    <div class="tutor-quiz-builder-group">
        <h4><?php _e('Short answer characters limit', 'tutor'); ?></h4>
        <div class="tutor-quiz-builder-row">
            <div class="tutor-quiz-builder-col">
                <input type="number" name="quiz_option[short_answer_characters_limit]" value="<?php echo tutor_utils()->get_quiz_option($quiz_id, 'short_answer_characters_limit', 200); ?>">
            </div>
        </div>
        <p class="help"><?php _e('Student will place answer in short answer question type within this characters limit.', 'tutor'); ?></p>
    </div>

    <div class="tutor-quiz-builder-group">
        <h4><?php _e('Open-Ended/Essay questions answer character limit', 'tutor'); ?></h4>
        <div class="tutor-quiz-builder-row">
            <div class="tutor-quiz-builder-col">
                <input type="number" name="quiz_option[open_ended_answer_characters_limit]" value="<?php echo tutor_utils()->get_quiz_option($quiz_id, 'open_ended_answer_characters_limit', 500); ?>">
            </div>
        </div>
        <p class="help"><?php _e('Students will place the answer in the Open-Ended/Essay question type within this character limit.', 'tutor'); ?></p>
    </div>

    <div class="tutor-quiz-builder-modal-control-btn-group">
        <div class="quiz-builder-btn-group-left">
            <a href="#quiz-builder-tab-settings" class="quiz-modal-tab-navigation-btn quiz-modal-btn-back"><?php _e('Back', 'tutor'); ?></a>
            <a href="#quiz-builder-tab-advanced-options" class="quiz-modal-tab-navigation-btn quiz-modal-settings-save-btn" data-toast_success_message="<?php _e('Saved', 'tutor'); ?>"><?php _e('Save', 'tutor'); ?></a>
        </div>
    </div>
</div>