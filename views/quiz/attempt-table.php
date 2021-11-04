<?php
    extract($data); // $attempt_list, $column_hook
?>

<table class="tutor-ui-table tutor-ui-table-responsive my-quiz-attempts">
    <thead>
        <tr>
            <th><span class="text-regular-small color-text-subsued"><?php _e('Quiz Info', 'tutor'); ?></span></th>
            <th><span class="text-regular-small color-text-subsued"><?php _e('Question', 'tutor'); ?></span></th>
            <th><span class="text-regular-small color-text-subsued"><?php _e('Total Marks', 'tutor'); ?></span></th>
            <th><span class="text-regular-small color-text-subsued"><?php _e('Correct Answer', 'tutor'); ?></span></th>
            <th><span class="text-regular-small color-text-subsued"><?php _e('Incorrect Answer', 'tutor'); ?></span></th>
            <th><span class="text-regular-small color-text-subsued"><?php _e('Earned Marks', 'tutor'); ?></span></th>
            <th><span class="text-regular-small color-text-subsued"><?php _e('Result', 'tutor'); ?></span></th>
            <?php do_action($column_hook[0]); ?>
        </tr>
    </thead>
    <tbody>
        <?php
            foreach ( $attempt_list as $attempt){
                $attempt_action = tutor_utils()->get_tutor_dashboard_page_permalink('my-quiz-attempts/attempts-details/?attempt_id='.$attempt->attempt_id);
                $earned_percentage = $attempt->earned_marks > 0 ? ( number_format(($attempt->earned_marks * 100) / $attempt->total_marks)) : 0;
                $passing_grade = (int) tutor_utils()->get_quiz_option($attempt->quiz_id, 'passing_grade', 0);
                $answers = tutor_utils()->get_quiz_answers_by_attempt_id($attempt->attempt_id);

                $correct = 0;
                $incorrect = 0;
                if(is_array($answers) && count($answers) > 0) {
                    foreach ($answers as $answer){
                        if ( (bool) isset( $answer->is_correct ) ? $answer->is_correct : '' ) {
                            $correct++;
                        } else {
                            if ($answer->question_type === 'open_ended' || $answer->question_type === 'short_answer'){
                            } else {
                                $incorrect++;
                            }
                        }
                    }
                }
                ?>
                <tr>
                    <td data-th="<?php _e('Quiz Info', 'tutor'); ?>" class="column-fullwidth">
                        <div class="td-statement-info">
                            <span class="text-regular-small color-text-primary">
                                <?php echo date_i18n(get_option('date_format').' '.get_option('time_format'), strtotime($attempt->attempt_ended_at)); ?>
                            </span>
                            <p class="text-medium-body color-text-primary tutor-margin-0">
                                <a href="<?php echo get_the_permalink($attempt->course_id); ?>" target="_blank">
                                    <?php echo get_the_title($attempt->course_id); ?>
                                </a>
                            </p>
                            <?php do_action('tutor_quiz/table/after/course_title', $attempt); ?>
                        </div>
                    </td>
                    <td data-th="<?php _e('Question', 'tutor'); ?>">
                        <span class="text-medium-caption color-text-primary">
                            <?php echo count($answers);?>
                        </span>
                    </td>
                    <td data-th="<?php _e('Total Marks', 'tutor'); ?>">
                        <span class="text-medium-caption color-text-primary">
                            <?php echo $attempt->total_marks;?>
                        </span>
                    </td>
                    <td data-th="<?php _e('Correct Answer', 'tutor'); ?>">
                        <span class="text-medium-caption color-text-primary">
                            <?php echo $correct; ?>
                        </span>
                    </td>
                    <td data-th="<?php _e('Incorrect Answer', 'tutor'); ?>">
                        <span class="text-medium-caption color-text-primary">
                            <?php echo $incorrect; ?>
                        </span>
                    </td>
                    <td data-th="<?php _e('Earned Marks', 'tutor'); ?>">
                        <span class="text-medium-caption color-text-primary">
                            <?php echo $attempt->earned_marks.' ('.$earned_percentage.'%)'; ?>
                        </span>
                    </td>
                    <td data-th="<?php _e('Result', 'tutor'); ?>">
                        <?php
                            if ($attempt->attempt_status === 'review_required'){
                                echo '<span class="tutor-badge-label label-warning">' . __('Pending', 'tutor') . '</span>';
                            }else{
                                echo $earned_percentage >= $passing_grade ? 
                                    '<span class="tutor-badge-label label-success">'.__('Pass', 'tutor').'</span>' : 
                                    '<span class="tutor-badge-label label-danger">'.__('Fail', 'tutor').'</span>';
                            }
                        ?>
                    </td>
                    <?php do_action($column_hook[1], $attempt); ?>
                </tr>
                <?php
            }
        ?>
    </tbody>
</table>