<?php 
    if ($question_type === 'open_ended' || $question_type === 'short_answer'){
        echo '<p class="tutor-px-32 tutor-py-16">'.
                __('No option is necessary for this answer type', 'tutor').
            '</p>';
        return '';
    }
?>

<div id="tutor_quiz_question_answers" data-question-id="<?php echo $question_id; ?>"><?php
    global $wpdb;
    $answers = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}tutor_quiz_question_answers 
        where belongs_question_id = %d 
            AND belongs_question_type = %s 
        order by answer_order asc ;", 
        $question_id, 
        $question_type
    ));
    
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
                        // Show image for the single answer
                        if ($answer->image_id){
                            echo '<span class="tutor-question-answer-image">
                                    <img src="'.wp_get_attachment_image_url($answer->image_id).'" />
                                </span>';
                        }

                        if ($question_type === 'true_false' || $question_type === 'single_choice')
                        {
                            ?>
                            <span class="tutor-quiz-answers-mark-correct-wrap tutor-mr-4">
                                <input type="radio" name="mark_as_correct[<?php echo $answer->belongs_question_id; ?>]" value="<?php echo $answer->answer_id; ?>" title="<?php _e('Mark as correct', 'tutor'); ?>" <?php checked(1, $answer->is_correct); ?> >
                            </span>
                            <?php
                        } elseif ($question_type === 'multiple_choice'){
                            ?>
                            <span class="tutor-quiz-answers-mark-correct-wrap tutor-mr-4">
                                <input type="checkbox" name="mark_as_correct[<?php echo $answer->belongs_question_id; ?>]" value="<?php echo $answer->answer_id; ?>" title="<?php _e('Mark as correct', 'tutor'); ?>" <?php checked(1, $answer->is_correct); ?> >
                            </span>
                            <?php
                        }
                    ?>

                    <?php if ( $question_type !== 'true_false' ): ?>
                        <span class="tutor-quiz-answer-edit">
                            <a href="javascript:;">
                                <i class="tutor-icon-pencil-line tutor-icon-22"></i> 
                            </a>
                        </span>
                    <?php endif; ?>

                    <?php if($question_type !== 'fill_in_the_blank'): ?>
                        <span class="tutor-quiz-answer-sort-icon">
                            <i class="tutor-d-flex tutor-icon-menu-line tutor-icon-24"></i>
                        </span>
                    <?php endif; ?>
                </div>

                <?php if ( $question_type !== 'true_false' && $question_type !== 'fill_in_the_blank' ): ?>
                    <div class="tutor-quiz-answer-trash-wrap tutor-d-flex">
                        <a href="javascript:;" class="answer-trash-btn answer-trash-btn tutor-d-flex tutor-align-items-center" data-answer-id="<?php echo $answer->answer_id; ?>">
                            <i class="tutor-icon-garbage-line tutor-icon-24"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            <?php
        }
    }
    ?>
</div>

<?php if($question_type!='true_false' && ($question_type!='fill_in_the_blank' || empty($answers))): ?>
    <a href="javascript:;" class="add_question_answers_option tutor-d-flex tutor-align-items-center" data-question-id="<?php echo $question_id; ?>">
        <i class="tutor-icon-plus-bold-filled tutor-icon-18"></i>
        <?php _e('Add An Option', 'tutor'); ?>
    </a>
<?php endif; ?>