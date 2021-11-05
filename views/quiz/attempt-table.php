<?php
    extract($data); // $attempt_list, $context;

    $page_key = 'attempt-table';
    $table_columns = include __DIR__ . '/contexts.php';
?>

<table class="tutor-ui-table tutor-ui-table-responsive my-quiz-attempts">
    <thead>
        <tr>
            <?php 
                foreach($table_columns as $key=>$column) {
                    echo '<th><span class="text-regular-small color-text-subsued">'. $column . '</span></th>';
                }
            ?>
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
                    <?php 
                        foreach($table_columns as $key=>$column) {
                            switch($key) {
                                case 'checkbox' :
                                    ?>
                                    <td data-th="<?php _e('Mark', 'tutor'); ?>">
                                        <div class="td-checkbox d-flex ">
                                            <input id="tutor-admin-list-<?php echo $attempt->attempt_id; ?>" type="checkbox" class="tutor-form-check-input tutor-bulk-checkbox" name="tutor-bulk-checkbox-all" value="<?php echo $attempt->attempt_id; ?>" />
                                        </div>
                                    </td>
                                    <?php
                                    break;
                                    
                                case 'quiz_info' :
                                    ?>
                                    <td data-th="<?php echo $column; ?>" class="column-fullwidth">
                                        <div class="td-statement-info">
                                            <span class="text-regular-small color-text-primary">
                                                <?php echo date_i18n(get_option('date_format').' '.get_option('time_format'), strtotime($attempt->attempt_ended_at)); ?>
                                            </span>
                                            <p class="text-medium-body color-text-primary tutor-margin-0">
                                                <a href="<?php echo get_the_permalink($attempt->course_id); ?>" target="_blank">
                                                    <?php echo get_the_title($attempt->course_id); ?>
                                                </a>
                                            </p>
                                            <?php do_action('tutor_quiz/table/after/course_title', $attempt, $context); ?>
                                        </div>
                                    </td>
                                    <?php
                                    break;

                                case 'course' :
                                    ?>
                                    <td data-th="<?php echo $column; ?>">
                                        <span class="text-medium-caption color-text-primary">
                                            <?php echo get_the_title($attempt->course_id);?>
                                        </span>
                                    </td>
                                    <?php
                                    break;

                                case 'question' :
                                    ?>
                                    <td data-th="<?php echo $column; ?>">
                                        <span class="text-medium-caption color-text-primary">
                                            <?php echo count($answers);?>
                                        </span>
                                    </td>
                                    <?php
                                    break;

                                case 'total_marks' :
                                    ?>
                                    <td data-th="<?php echo $column; ?>">
                                        <span class="text-medium-caption color-text-primary">
                                            <?php echo $attempt->total_marks;?>
                                        </span>
                                    </td>
                                    <?php
                                    break;

                                case 'correct_answer' :
                                    ?>
                                    <td data-th="<?php echo $column; ?>">
                                        <span class="text-medium-caption color-text-primary">
                                            <?php echo $correct; ?>
                                        </span>
                                    </td>
                                    <?php
                                    break;

                                case 'incorrect_answer' :
                                    ?>
                                    <td data-th="<?php echo $column; ?>">
                                        <span class="text-medium-caption color-text-primary">
                                            <?php echo $incorrect; ?>
                                        </span>
                                    </td>
                                    <?php
                                    break;

                                case 'earned_marks' :
                                    ?>
                                    <td data-th="<?php echo $column; ?>">
                                        <span class="text-medium-caption color-text-primary">
                                            <?php echo $attempt->earned_marks.' ('.$earned_percentage.'%)'; ?>
                                        </span>
                                    </td>
                                    <?php
                                    break;

                                case 'result' :
                                    ?>
                                    <td data-th="<?php echo $column; ?>">
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
                                    <?php
                                    break;

                                case 'details' :
                                    $url = add_query_arg( array( 'view_quiz_attempt_id' => $attempt->attempt_id ), tutor()->current_url );
                                    ?>
                                        <td data-th="See Details">
                                            <div class="inline-flex-center td-action-btns">
                                                <a href="<?php echo $url; ?>" class="btn-outline tutor-btn">
                                                    <?php _e( 'Details', 'tutor-pro' ); ?>
                                                </a>
                                            </div>
                                        </td>
                                    <?php
                                    break;
                            }
                        }
                    ?>
                </tr>
                <?php
            }
        ?>
    </tbody>
</table>