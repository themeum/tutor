<?php
global $wpdb;
$settings = maybe_unserialize($question->question_settings);
?>

<div id="tutor-quiz-question-wrapper">
    <div class="question-form-header">
        <a href="javascript:;" class="back-to-quiz-questions-btn" data-quiz-id="<?php echo $quiz_id; ?>" data-topic-id="<?php echo $topic_id; ?>">
            <i class="tutor-icon-next-2"></i> <?php _e('Back', 'tutor'); ?>
        </a>
    </div>
    <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>"/>
    <input type="hidden" name="topic_id" value="<?php echo $topic_id; ?>"/>

    <div class="tutor-mb-30">
        <label class="tutor-form-label"><?php _e('Write your question here', 'tutor'); ?></label>
        <div class="tutor-input-group tutor-mb-15">
            <input type="text" name="tutor_quiz_question[<?php echo $question_id; ?>][question_title]" class="tutor-form-control tutor-mb-10" placeholder="<?php _e('Type your question here', 'tutor'); ?>" value="<?php echo htmlspecialchars( stripslashes($question->question_title) ); ?>">
        </div>
    </div>

    <div class="tutor-mb-30">
        <label class="tutor-form-label"><?php _e('Select your question type', 'tutor'); ?></label>
        <div class="tutor-input-group tutor-mb-15">
            <div class="tutor-bs-row tutor-bs-align-items-center">
                <div class="tutor-bs-col-12 tutor-bs-col-md-4">
                    <div class="question-type-select">
                        <?php 
                            $question_types = tutor_utils()->get_question_types(); 
                            $current_type = $question->question_type ? $question->question_type : 'true_false';
                        ?>

                        <div class="select-header">
                            <span class="lead-option"><?php echo $question_types[$current_type]['icon']; echo $question_types[$current_type]['name']; ?> </span>
                            <span class="select-dropdown"><i class="tutor-icon-light-down"></i> </span>
                            <input type="hidden" class="tutor_select_value_holder" name="tutor_quiz_question[<?php echo $question_id; ?>][question_type]" value="<?php echo $question->question_type; ?>" >
                        </div>

                        <div class="tutor-select-options" style="display: none;">
                            <?php
                            $has_tutor_pro = tutor()->has_pro;

                            foreach ($question_types as $type => $question_type){
                                ?>
                                <p class="tutor-select-option" data-value="<?php echo $type; ?>" <?php echo $question->question_type===$type ? ' data-selected="selected"' : ''; ?> data-is-pro="<?php echo (! $has_tutor_pro &&  $question_type['is_pro']) ? 'true' : 'false' ?>" >
                                    <?php echo $question_type['icon'].' '.$question_type['name']; ?>

                                    <?php
                                    if (! $has_tutor_pro && $question_type['is_pro']){
                                        $svg_lock = '<svg width="12" height="16" xmlns="http://www.w3.org/2000/svg"><path d="M11.667 6h-1V4.667A4.672 4.672 0 0 0 6 0a4.672 4.672 0 0 0-4.667 4.667V6h-1A.333.333 0 0 0 0 6.333v8.334C0 15.402.598 16 1.333 16h9.334c.735 0 1.333-.598 1.333-1.333V6.333A.333.333 0 0 0 11.667 6zm-4.669 6.963a.334.334 0 0 1-.331.37H5.333a.333.333 0 0 1-.331-.37l.21-1.89A1.319 1.319 0 0 1 4.667 10c0-.735.598-1.333 1.333-1.333S7.333 9.265 7.333 10c0 .431-.204.824-.545 1.072l.21 1.891zM8.667 6H3.333V4.667A2.67 2.67 0 0 1 6 2a2.67 2.67 0 0 1 2.667 2.667V6z" fill="#E2E2E2" fill-rule="nonzero"/></svg>';
                                        printf("<span class='question-type-pro' title='%s'>%s</span>",__('Pro version required', 'tutor'), $svg_lock );
                                    }
                                    ?>
                                </p>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <div class="tutor-bs-col-12 tutor-bs-col-md-8">
                    <div class="tutor-bs-row tutor-bs-align-items-center">
                        <div class="tutor-bs-col-sm-4 tutor-bs-col-md-6">
                            <label class="tutor-form-toggle">
                                <input type="checkbox" class="tutor-form-toggle-input"  value="1" name="tutor_quiz_question[<?php echo $question_id; ?>][answer_required]" <?php checked('1', tutor_utils()->avalue_dot('answer_required', $settings)); ?> />
                                <span class="tutor-form-toggle-control"></span> <?php _e('Answer Required', 'tutor'); ?>
                            </label>
                        </div>
                        <div class="tutor-bs-col-sm-4 tutor-bs-col-md-6">
                            <label class="tutor-form-toggle">
                                <input type="checkbox" class="tutor-form-toggle-input" value="1" name="tutor_quiz_question[<?php echo $question_id; ?>][randomize_question]" <?php checked('1', tutor_utils()->avalue_dot('randomize_question', $settings)); ?> />
                                <span class="tutor-form-toggle-control"></span> <?php _e('Randomize', 'tutor'); ?>
                            </label>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
    
    <div class="tutor-mb-30">
        <label class="tutor-form-label"><?php _e('Point(s) for this answer', 'tutor'); ?></label>
        <div class="tutor-input-group tutor-mb-15">
            <div class="tutor-bs-row tutor-bs-align-items-center">
                <div class="tutor-bs-col-sm-6 tutor-bs-col-md-4">
                    <input type="text" name="tutor_quiz_question[<?php echo $question_id; ?>][question_mark]" class="tutor-form-control tutor-mb-10" placeholder="<?php _e('set the mark ex. 10', 'tutor'); ?>" value="<?php echo $question->question_mark; ?>">
                </div>
                <div class="tutor-bs-col-sm-6 tutor-bs-col-md-4">
                    <label class="tutor-form-toggle">
                        <input type="checkbox" class="tutor-form-toggle-input" value="1" name="tutor_quiz_question[<?php echo $question_id; ?>][show_question_mark]" <?php checked('1', tutor_utils()->avalue_dot('show_question_mark', $settings)); ?> />
                        <span class="tutor-form-toggle-control"></span> <?php _e('Display Points', 'tutor'); ?>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="tutor-mb-30">
        <label class="tutor-form-label"><?php _e('Description', 'tutor'); ?> <span>(<?php _e('Optional', 'tutor'); ?>)</span></label>
        <div class="tutor-input-group tutor-mb-15">
            <textarea name="tutor_quiz_question[<?php echo $question_id; ?>][question_description]" class="tutor-form-control"><?php echo stripslashes($question->question_description);?></textarea>
        </div>
    </div>


                
    <?php 
        // Question answer builder section
        $message = null;

        switch ($question->question_type){
            case 'true_false':
                $message = __('Input options for the question and select the correct answer.', 'tutor');
                break;
            case 'ordering':
                $message = __('Make sure you’re saving the answers in the right order. Students will have to match this order.', 'tutor');
                break;
        }

        if($message) {
            ?>
            <div>
                <label class="tutor-form-label">
                    <strong><?php echo $message; ?></strong>
                </label>
            </div>
            <?php
        }
    ?>
    
    <div id="tutor-answer-builder">
        <div id="tutor_quiz_question_answers" data-question-id="<?php echo $question_id; ?>"><?php

            $answers = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}tutor_quiz_question_answers where belongs_question_id = %d AND belongs_question_type = %s order by answer_order asc ;", $question_id, $question->question_type));
            if (is_array($answers) && count($answers)){
                foreach ($answers as $answer){
                    ?>
                    <div class="tutor-quiz-answer-wrap" data-answer-id="<?php echo $answer->answer_id; ?>">
                        <div class="tutor-quiz-answer">
                    <span class="tutor-quiz-answer-title">
                        <?php
                        echo stripslashes($answer->answer_title);
                        if ($answer->belongs_question_type === 'fill_in_the_blank'){
                            echo ' ('.__('Answer', 'tutor').' : ';
                            echo '<strong>'.stripslashes($answer->answer_two_gap_match).'</strong>)';
                        }
                        if ($answer->belongs_question_type === 'matching'){
                            echo ' - '.stripslashes($answer->answer_two_gap_match);
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
                            <span class="tutor-quiz-answer-edit">
                                <?php if ( $question->question_type !== 'true_false' ){ ?>
                                    <a href="javascript:;"><i class="tutor-icon-pencil"></i> </a>
                                <?php } ?>
                            </span>
                            <span class="tutor-quiz-answer-sort-icon"><i class="tutor-icon-menu-2"></i> </span>
                        </div>

                        <?php if ( $question->question_type !== 'true_false' ){ ?>
                            <div class="tutor-quiz-answer-trash-wrap">
                                <a href="javascript:;" class="answer-trash-btn" data-answer-id="<?php echo $answer->answer_id; ?>"><i class="tutor-icon-garbage"></i> </a>
                            </div>
                        <?php } ?>
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
        
        <div id="quiz_validation_msg_wrap">

        </div>
    </div>
</div>