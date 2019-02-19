<?php
global $wpdb;
$settings = maybe_unserialize($question->question_settings);
?>

<div class="quiz-questions-form">


    <div class="question-form-header">
        <a href="javascript:;" class="back-to-quiz-questions-btn open-tutor-quiz-modal" data-quiz-id="<?php echo $quiz_id; ?>"
           data-back-to-tab="#quiz-builder-tab-questions"><i class="tutor-icon-next-2"></i> <?php _e('Back', 'tutor'); ?></a>
    </div>


    <div class="quiz-question-form-body">


        <div class="quiz_question_form">

            <div class="tutor-quiz-builder-form-row">
                <label><?php _e('Write your question here', 'tutor'); ?></label>
                <div class="quiz-modal-field-wrap">
                    <input type="text" name="tutor_quiz_question[<?php echo $question_id; ?>][question_title]" placeholder="<?php _e('Type your quiz title here',
						'tutor'); ?>" value="<?php echo $question->question_title; ?>">
                </div>
            </div>



            <div class="tutor-quiz-builder-form-row">

                <div class="tutor-quiz-builder-form-cols-row">
                    <div class="quiz-form-field-col">
                        <label><?php _e('Mark for this question', 'tutor'); ?></label>
                        <div class="quiz-modal-field-wrap">
                            <input type="text" name="tutor_quiz_question[<?php echo $question_id; ?>][question_mark]" placeholder="<?php _e('set the mark ex. 10', 'tutor'); ?>" value="<?php
							echo $question->question_mark; ?>">
                        </div>
                    </div>


                    <div class="quiz-form-field-col">
                        <div class="quiz-modal-field-wrap">
                            <div class="quiz-modal-switch-field">
                                <label class="btn-switch">
                                    <input type="checkbox" value="1" name="tutor_quiz_question[<?php echo $question_id; ?>][show_question_mark]" <?php checked('1', tutor_utils()->avalue_dot('show_question_mark', $settings)); ?> />
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
                    <textarea name="tutor_quiz_question[<?php echo $question_id; ?>][question_description]"><?php echo $question->question_description;?></textarea>
                </div>
            </div>


            <div class="tutor-quiz-builder-form-row">

                <div class="tutor-quiz-builder-form-cols-row">
                    <div class="quiz-form-field-col">
                        <label><?php _e('Question Type', 'tutor'); ?></label>
                        <div class="quiz-modal-field-wrap">

                            <div class="tutor-select">
                                <div class="select-header">
                                    <span class="lead-option"> <i class="tutor-icon-yes-no"></i> True or False  </span>
                                    <span class="select-dropdown"><i class="tutor-icon-light-down"></i> </span>
                                    <input type="hidden" class="tutor_select_value_holder" name="tutor_quiz_question[<?php echo $question_id; ?>][question_type]" value="" >
                                </div>

                                <div class="tutor-select-options" style="display: none;">
                                    <p class="tutor-select-option" data-value="true_false" <?php echo $question->question_type === 'true_false' ? ' data-selected="selected"' : ''; ?> >
                                        <i class="tutor-icon-block tutor-icon-yes-no"></i> <?php _e('True False'); ?>
                                    </p>
                                    <p class="tutor-select-option" data-value="single_choice" <?php echo $question->question_type === 'single_choice' ? ' data-selected="selected"' : ''; ?>>
                                        <i class="tutor-icon-block tutor-icon-mark"></i> <?php _e('Single Choice'); ?>
                                    </p>
                                    <p class="tutor-select-option" data-value="multiple_choice" <?php echo $question->question_type === 'multiple_choice' ? ' data-selected="selected"' : ''; ?>>
                                        <i class="tutor-icon-block tutor-icon-multiple-choice"></i> <?php _e('Multiple Choice', 'tutor'); ?>
                                    </p>
                                    <p class="tutor-select-option" data-value="open_ended" <?php echo $question->question_type === 'open_ended' ? ' data-selected="selected"' : ''; ?>>
                                        <i class="tutor-icon-block tutor-icon-open-ended"></i> <?php _e('Open Ended/Essay', 'tutor'); ?>
                                    </p>
                                    <p class="tutor-select-option" data-value="fill_in_the_blank" <?php echo $question->question_type === 'fill_in_the_blank' ? ' data-selected="selected"' : ''; ?>>
                                        <i class="tutor-icon-block tutor-icon-fill-gaps"></i> <?php _e('Fill In The Gaps'); ?>
                                    </p>

                                    <!--
                                    <p class="tutor-select-option" data-value="answer_sorting" <?php echo $question->question_type === 'answer_sorting' ? ' data-selected="selected"' : ''; ?>>
                                        <i class="tutor-icon-block tutor-icon-answer-shorting"></i> <?php _e('Answer Sorting', 'tutor'); ?>
                                    </p>
                                    <p class="tutor-select-option" data-value="assessment" <?php /*echo $question->question_type === 'assessment' ? ' data-selected="selected"' : ''; */?>>
                                        <i class="tutor-icon-block tutor-icon-assesment"></i> <?php /*_e('Assessment', 'tutor'); */?>
                                    </p>-->

                                    <p class="tutor-select-option" data-value="matching" <?php echo $question->question_type === 'matching' ? ' data-selected="selected"' : ''; ?>>
                                        <i class="tutor-icon-block tutor-icon-matching"></i> <?php _e('Matching', 'tutor'); ?>
                                    </p>
                                    <p class="tutor-select-option" data-value="image_matching" <?php echo $question->question_type === 'image_matching' ? ' data-selected="selected"' : ''; ?>>
                                        <i class="tutor-icon-block tutor-icon-image-matching"></i> <?php _e('Image Matching', 'tutor'); ?>
                                    </p>
                                    <p class="tutor-select-option" data-value="ordering" <?php echo $question->question_type === 'ordering' ? ' data-selected="selected"' : ''; ?>>
                                        <i class="tutor-icon-block tutor-icon-ordering"></i> <?php _e('Ordering', 'tutor'); ?>
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>


                    <div class="quiz-form-field-col">
                        <div class="quiz-modal-field-wrap">
                            <div class="quiz-modal-switch-field">
                                <label class="btn-switch">
                                    <input type="checkbox" value="1" name="tutor_quiz_question[<?php echo $question_id; ?>][answer_required]" <?php checked('1', tutor_utils()->avalue_dot('answer_required', $settings)); ?> />
                                    <div class="btn-slider btn-round"></div>
                                </label>
                                <label><?php _e('Answer Required', 'tutor'); ?></label>
                            </div>
                        </div>
                    </div>

                    <div class="quiz-form-field-col">
                        <div class="quiz-modal-field-wrap">
                            <div class="quiz-modal-switch-field">
                                <label class="btn-switch">
                                    <input type="checkbox" value="1" name="tutor_quiz_question[<?php echo $question_id; ?>][randomize_question]" <?php checked('1', tutor_utils()->avalue_dot('randomize_question', $settings)); ?> />
                                    <div class="btn-slider btn-round"></div>
                                </label>
                                <label><?php _e('Randomize', 'tutor'); ?></label>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <div class="tutor-quiz-builder-form-row">


                <div id="tuotr_question_options_for_quiz" class="quiz-modal-field-wrap">
                    <div id="tutor_quiz_question_answers" data-question-id="<?php echo $question_id; ?>">
						<?php
						switch ($question->question_type){
							case 'true_false':
								echo '<label>'.__('Answer options &amp; mark correct', 'tutor').'</label>';
								break;
							case 'ordering':
								echo '<label>'.__('Student should order below items exact this order, make sure your answer is in right order, you can re-order them', 'tutor').'</label>';
								break;
						}

						$answers = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}tutor_quiz_question_answers where belongs_question_id = {$question_id} AND belongs_question_type = '{$question->question_type}' order by answer_order asc ;");
						if (is_array($answers) && count($answers)){
							foreach ($answers as $answer){
								?>
                                <div class="tutor-quiz-answer-wrap" data-answer-id="<?php echo $answer->answer_id; ?>">
                                    <div class="tutor-quiz-answer">
                                        <span class="tutor-quiz-answer-title">
                                            <?php
                                            echo $answer->answer_title;
                                            if ($answer->belongs_question_type === 'fill_in_the_blank'){
	                                            echo ' ('.__('Answer', 'tutor').' : ';
	                                            echo "<strong>{$answer->answer_two_gap_match} </strong>)";
                                            }
                                            if ($answer->belongs_question_type === 'matching'){
	                                            echo " - {$answer->answer_two_gap_match}";
                                            }
                                            ?>
                                        </span>

										<?php
										if ($answer->image_id){
											echo '<span class="tutor-question-answer-image"><img src="'.wp_get_attachment_image_url($answer->image_id).'" /> </span>';
										}
										if ($question->question_type === 'true_false' || $question->question_type === 'single_choice'){
											?>
                                            <span class="tutor-quiz-answers-mark-correct-wrap">
                                                <input type="radio" name="mark_as_correct[<?php echo $answer->belongs_question_id; ?>]"
                                                       value="<?php echo $answer->answer_id; ?>" title="<?php _e('Mark as correct', 'tutor'); ?>" <?php checked(1, $answer->is_correct); ?> >
                                            </span>
											<?php
										}elseif ($question->question_type === 'multiple_choice'){
											?>
                                            <span class="tutor-quiz-answers-mark-correct-wrap">
                                                <input type="checkbox" name="mark_as_correct[<?php echo $answer->belongs_question_id; ?>]"
                                                       value="<?php echo $answer->answer_id; ?>" title="<?php _e('Mark as correct', 'tutor'); ?>" <?php checked(1, $answer->is_correct); ?> >
                                            </span>
											<?php
										}
										?>
                                        <span class="tutor-quiz-answer-sort-icon"><i class="tutor-icon-menu-2"></i> </span>
                                    </div>

                                    <div class="tutor-quiz-answer-trash-wrap">
                                        <a href="javascript:;" class="answer-trash-btn" data-answer-id="<?php echo $answer->answer_id; ?>"><i class="tutor-icon-garbage"></i> </a>
                                    </div>
                                </div>
								<?php
							}
						}
						?>
                    </div>


                    <div id="tutor_quiz_question_answer_form"></div>


                    <a href="javascript:;" class="add_question_answers_option" data-question-id="<?php echo $question_id; ?>">
                        <i class="tutor-icon-block tutor-icon-plus"></i>
						<?php _e('Add An Option', 'tutor'); ?>
                    </a>
                </div>
            </div>

        </div>

    </div>





    <div class="tutor-quiz-builder-modal-control-btn-group">
        <div class="quiz-builder-btn-group-left">
            <a href="javascript:;" class="quiz-modal-tab-navigation-btn quiz-modal-question-save-btn"><?php _e('Save &amp; Continue', 'tutor');
				?></a>
        </div>
        <div class="quiz-builder-btn-group-right">
            <a href="javascript:;" class="quiz-modal-tab-navigation-btn quiz-modal-btn-cancel"><?php _e('Cancel', 'tutor'); ?></a>
        </div>
    </div>




</div>