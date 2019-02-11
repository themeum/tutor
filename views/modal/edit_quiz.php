<?php
$quiz = null;
if ( ! empty($_POST['tutor_quiz_builder_quiz_id'])){
    $quiz_id = sanitize_text_field($_POST['tutor_quiz_builder_quiz_id']);
    $quiz = get_post($quiz_id);

    echo '<input type="hidden"  id="tutor_quiz_builder_quiz_id" value="'.$quiz_id.'" />';
}elseif( ! empty($quiz_id)){
	$quiz = get_post($quiz_id);

	echo '<input type="hidden" id="tutor_quiz_builder_quiz_id" value="'.$quiz_id.'" />';
}

if ( ! $quiz){
    die('No quiz found');
}

?>

<div class="tutor-quiz-builder-modal-contents">

    <div id="tutor-quiz-modal-tab-items-wrap" class="tutor-quiz-modal-tab-items-wrap">

        <a href="#quiz-builder-tab-quiz-info" class="tutor-quiz-modal-tab-item active">
            <i class="tutor-icon-list"></i> <?php _e('Quiz Info', 'tutor'); ?>
        </a>
        <a href="#quiz-builder-tab-questions" class="tutor-quiz-modal-tab-item">
            <i class="tutor-icon-doubt"></i> <?php _e('Questions', 'tutor'); ?>
        </a>
        <a href="#quiz-builder-tab-settings" class="tutor-quiz-modal-tab-item">
            <i class="tutor-icon-settings-1"></i> <?php _e('Settings', 'tutor'); ?>
        </a>
        <a href="#quiz-builder-tab-advanced-options" class="tutor-quiz-modal-tab-item">
            <i class="tutor-icon-filter-tool-black-shape"></i> <?php _e('Advanced Options', 'tutor'); ?>
        </a>

    </div>



    <div id="tutor-quiz-builder-modal-tabs-container" class="tutor-quiz-builder-modal-tabs-container">

        <div id="quiz-builder-tab-quiz-info" class="quiz-builder-tab-container">

            <div class="quiz-builder-tab-body">
                <div class="tutor-quiz-builder-form-row">
                    <input type="text" name="quiz_title" placeholder="<?php _e('Type your quiz title here', 'tutor'); ?>" value="<?php echo
                    $quiz->post_title; ?>">

                    <div class="quiz_form_msg"></div>
                </div>

                <div class="tutor-quiz-builder-form-row">
                    <textarea name="quiz_description" rows="5"><?php echo $quiz->post_content; ?></textarea>
                </div>
            </div>


            <div class="tutor-quiz-builder-modal-control-btn-group">
                <div class="quiz-builder-btn-group-left">
                    <a href="#quiz-builder-tab-questions" class="quiz-modal-tab-navigation-btn quiz-modal-btn-first-step"><?php _e('Save &amp; Next', 'tutor'); ?></a>
                </div>
                <div class="quiz-builder-btn-group-right">
                    <a href="#quiz-builder-tab-questions" class="quiz-modal-tab-navigation-btn  quiz-modal-btn-cancel"><?php _e('Cancel', 'tutor');
						?></a>
                </div>
            </div>


        </div>

        <div id="quiz-builder-tab-questions" class="quiz-builder-tab-container" style="display: none;">

            <div class="quiz-builder-tab-body">




                <div class="quiz_question_form">

                    <div class="tutor-quiz-builder-form-row">
                        <label><?php _e('Write your question here', 'tutor'); ?></label>
                        <div class="quiz-modal-field-wrap">
                            <input type="text" name="tutor_quiz[<?php echo $quiz_id; ?>][question_title]" placeholder="<?php _e('Type your quiz title here',
                                'tutor'); ?>" value="<?php
                            echo $quiz->post_title; ?>">
                        </div>
                    </div>



                    <div class="tutor-quiz-builder-form-row">

                        <div class="tutor-quiz-builder-form-cols-row">
                            <div class="tutor-quiz-builder-form-field-cols">
                                <label><?php _e('Mark for this question', 'tutor'); ?></label>
                                <div class="quiz-modal-field-wrap">
                                    <input type="text" name="tutor_quiz[<?php echo $quiz_id; ?>][question_mark]" placeholder="<?php _e('set the mark ex. 10', 'tutor'); ?>" value="<?php
		                            echo $quiz->post_title; ?>">
                                </div>
                            </div>


                            <div class="tutor-quiz-builder-form-field-cols">
                                <div class="quiz-modal-field-wrap">
                                    <div class="quiz-modal-switch-field">
                                        <label class="btn-switch">
                                            <input type="checkbox" value="1" name="tutor_quiz[<?php echo $quiz_id; ?>][show_question_mark]" />
                                            <div class="btn-slider btn-round"></div>
                                        </label>
                                        <label><?php _e('Show question mark', 'tutor'); ?></label>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>



                    <div class="tutor-quiz-builder-form-row">
                        <label><?php _e('Description', 'tutor'); ?> <span>(<?php _e('Optional', 'tutor'); ?>)</span> </label>
                        <div class="quiz-modal-field-wrap">
                            <textarea name="tutor_quiz[<?php echo $quiz_id; ?>][question_description]"></textarea>
                        </div>
                    </div>


                    <div class="tutor-quiz-builder-form-row">

                        <div class="tutor-quiz-builder-form-cols-row">
                            <div class="tutor-quiz-builder-form-field-cols">
                                <label><?php _e('Question Type', 'tutor'); ?></label>
                                <div class="quiz-modal-field-wrap">

                                    <div class="tutor-select">
                                        <div class="select-header">
                                            <span class="lead-option"> <i class="tutor-icon-yes-no"></i> True or False  </span>
                                            <span class="select-dropdown"><i class="tutor-icon-light-down"></i> </span>
                                            <input type="hidden" class="tutor_select_value_holder" name="tutor_quiz[<?php echo $quiz_id; ?>][question_type]" value="" >
                                        </div>

                                        <div class="tutor-select-options" style="display: none;">
                                            <p class="tutor-select-option" data-value="true_false">
                                                <i class="tutor-icon-block tutor-icon-yes-no"></i> <?php _e('True False'); ?>
                                            </p>
                                            <p class="tutor-select-option" data-value="single_choice">
                                                <i class="tutor-icon-block tutor-icon-mark"></i> <?php _e('Single Choice'); ?>
                                            </p>
                                            <p class="tutor-select-option" data-value="multiple_choice" data-selected="selected">
                                                <i class="tutor-icon-block tutor-icon-multiple-choice"></i> <?php _e('Multiple Choice', 'tutor'); ?>
                                            </p>
                                            <p class="tutor-select-option" data-value="open_ended">
                                                <i class="tutor-icon-block tutor-icon-open-ended"></i> <?php _e('Open Ended', 'tutor'); ?>
                                            </p>
                                            <p class="tutor-select-option" data-value="fil_in_the_blank">
                                                <i class="tutor-icon-block tutor-icon-fill-gaps"></i> <?php _e('Fill In The Gaps'); ?>
                                            </p>
                                            <p class="tutor-select-option" data-value="answer_sorting">
                                                <i class="tutor-icon-block tutor-icon-answer-shorting"></i> <?php _e('Answer Sorting', 'tutor'); ?>
                                            </p>
                                            <p class="tutor-select-option" data-value="assessment">
                                                <i class="tutor-icon-block tutor-icon-assesment"></i> <?php _e('Assessment', 'tutor'); ?>
                                            </p>
                                            <p class="tutor-select-option" data-value="matching">
                                                <i class="tutor-icon-block tutor-icon-matching"></i> <?php _e('Matching', 'tutor'); ?>
                                            </p>
                                            <p class="tutor-select-option" data-value="ordering">
                                                <i class="tutor-icon-block tutor-icon-ordering"></i> <?php _e('Ordering', 'tutor'); ?>
                                            </p>
                                        </div>
                                    </div>

                                </div>
                            </div>


                            <div class="tutor-quiz-builder-form-field-cols">
                                <div class="quiz-modal-field-wrap">
                                    <div class="quiz-modal-switch-field">
                                        <label class="btn-switch">
                                            <input type="checkbox" value="1" name="tutor_quiz[<?php echo $quiz_id;
                                            ?>][answer_required]" />
                                            <div class="btn-slider btn-round"></div>
                                        </label>
                                        <label><?php _e('Answer Required', 'tutor'); ?></label>
                                    </div>
                                </div>
                            </div>

                            <div class="tutor-quiz-builder-form-field-cols">
                                <div class="quiz-modal-field-wrap">
                                    <div class="quiz-modal-switch-field">
                                        <label class="btn-switch">
                                            <input type="checkbox" value="1" name="tutor_quiz[<?php echo $quiz_id; ?>][randomize_question]" />
                                            <div class="btn-slider btn-round"></div>
                                        </label>
                                        <label><?php _e('Randomize', 'tutor'); ?></label>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>

                    <div class="tutor-quiz-builder-form-row">
                        <label><?php _e('Answer options &amp; mark correct', 'tutor'); ?> </label>
                        <div id="tuotr_question_options_for_quiz" class="quiz-modal-field-wrap">
                            <div class="question_options_group_wrap">

                            </div>

                            <a href="javascript:;" class="add_question_option">
                                <i class="tutor-icon-block tutor-icon-plus-square-button"></i>
                                <?php _e('Add An Option', 'tutor'); ?>
                            </a>
                        </div>
                    </div>

                    <div class="tutor-quiz-builder-form-row">
                        <a href="javascript:;" class="tutor-quiz-add-question-btn">
                            <i class="tutor-icon-add-line"></i>
			                <?php _e('Add Question', 'tutor'); ?>
                        </a>
                    </div>

                </div>


            </div>



            <div class="tutor-quiz-builder-modal-control-btn-group">
                <div class="quiz-builder-btn-group-left">
                    <a href="#quiz-builder-tab-quiz-info" class="quiz-modal-tab-navigation-btn quiz-modal-btn-back"><?php _e('Back', 'tutor'); ?></a>
                    <a href="#quiz-builder-tab-settings" class="quiz-modal-tab-navigation-btn quiz-modal-btn-next"><?php _e('Next', 'tutor'); ?></a>
                </div>
                <div class="quiz-builder-btn-group-right">
                    <a href="#quiz-builder-tab-questions" class="quiz-modal-tab-navigation-btn quiz-modal-btn-cancel"><?php _e('Cancel', 'tutor'); ?></a>
                </div>
            </div>


        </div>

        <div id="quiz-builder-tab-settings" class="quiz-builder-tab-container" style="display: none;">


            <div class="quiz-builder-tab-body">
                <h1>Settings</h1>
            </div>




            <div class="tutor-quiz-builder-modal-control-btn-group">
                <div class="quiz-builder-btn-group-left">
                    <a href="#quiz-builder-tab-questions" class="quiz-modal-tab-navigation-btn quiz-modal-btn-back"><?php _e('Back', 'tutor');
						?></a>
                    <a href="#quiz-builder-tab-advanced-options" class="quiz-modal-tab-navigation-btn quiz-modal-btn-next"><?php _e('Next', 'tutor'); ?></a>
                </div>
                <div class="quiz-builder-btn-group-right">
                    <a href="#quiz-builder-tab-questions" class="quiz-modal-tab-navigation-btn quiz-modal-btn-cancel"><?php _e('Cancel', 'tutor');
						?></a>
                </div>
            </div>


        </div>

        <div id="quiz-builder-tab-advanced-options" class="quiz-builder-tab-container" style="display: none;">
            <h1>Advanced Options</h1>
        </div>



    </div>

</div>