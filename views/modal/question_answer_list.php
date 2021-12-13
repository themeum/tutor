<?php 
    if ($question_type === 'open_ended' || $question_type === 'short_answer'){
        echo '<p class="tutor-padding-30">'.
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
                            <span class="tutor-quiz-answers-mark-correct-wrap">
                                <input type="radio" name="mark_as_correct[<?php echo $answer->belongs_question_id; ?>]" value="<?php echo $answer->answer_id; ?>" title="<?php _e('Mark as correct', 'tutor'); ?>" <?php checked(1, $answer->is_correct); ?> >
                            </span>
                            <?php
                        } elseif ($question_type === 'multiple_choice'){
                            ?>
                            <span class="tutor-quiz-answers-mark-correct-wrap">
                                <input type="checkbox" name="mark_as_correct[<?php echo $answer->belongs_question_id; ?>]" value="<?php echo $answer->answer_id; ?>" title="<?php _e('Mark as correct', 'tutor'); ?>" <?php checked(1, $answer->is_correct); ?> >
                            </span>
                            <?php
                        }
                    ?>

                    <?php if ( $question_type !== 'true_false' ): ?>
                        <span class="tutor-quiz-answer-edit">
                            <a href="javascript:;">
                                <i class="tutor-icon-pencil"></i> 
                            </a>
                        </span>
                    <?php endif; ?>

                    <?php if($question_type !== 'fill_in_the_blank'): ?>
                        <span class="tutor-quiz-answer-sort-icon tutor-ml-10">
                            <i class="tutor-icon-menu-2"></i> 
                        </span>
                    <?php endif; ?>
                </div>

                <?php if ( $question_type !== 'true_false' && $question_type !== 'fill_in_the_blank' ): ?>
                    <div class="tutor-quiz-answer-trash-wrap">
                        <a href="javascript:;" class="answer-trash-btn" data-answer-id="<?php echo $answer->answer_id; ?>">
                        <i class="tutor-icon-garbage"></i> 
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
    <a href="javascript:;" class="add_question_answers_option" data-question-id="<?php echo $question_id; ?>">
        <i class="tutor-icon-block tutor-icon-plus"></i>
        <?php _e('Add An Option', 'tutor'); ?>
    </a>
<?php endif; ?>