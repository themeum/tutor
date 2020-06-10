<?php
/**
 * Quiz Attempts, I attempted to courses
 *
 * @since v.1.1.2
 *
 * @author Themeum
 * @url https://themeum.com
 *
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

$previous_attempts = tutor_utils()->get_all_quiz_attempts_by_user();
$attempted_count = is_array($previous_attempts) ? count($previous_attempts) : 0;
?>
    <h3><?php _e('My Quiz Attempts', 'tutor'); ?></h3>
<?php
if ($attempted_count){
    ?>
    <div class="tutor-quiz-attempt-history my-quiz-attempts">
        <table>
            <tr>
                <th>#</th>
                <th><?php _e('Course Title', 'tutor'); ?></th>
                <th><?php _e('Questions', 'tutor'); ?></th>
                <th><?php _e('Total Marks', 'tutor'); ?></th>
                <th><?php _e('Attempts Date', 'tutor'); ?></th>
                <th><?php _e('Correct Answer', 'tutor'); ?></th>
                <th><?php _e('Incorrect Answer', 'tutor'); ?></th>
                <th><?php _e('Earned Marks', 'tutor'); ?></th>
                <th><?php _e('Result', 'tutor'); ?></th>
                <th></th>
            </tr>
            <?php
            foreach ( $previous_attempts as $attempt){
                $earned_percentage = $attempt->earned_marks > 0 ? ( number_format(($attempt->earned_marks * 100) / $attempt->total_marks)) : 0;
                $passing_grade = (int) tutor_utils()->get_quiz_option($attempt->quiz_id, 'passing_grade', 0);
                ?>
                <tr class="<?php echo esc_attr($earned_percentage >= $passing_grade ? 'pass' : 'fail') ?>">
                    <td  title="<?php _e('Attempt ID', 'tutor'); ?>"><?php echo $attempt->attempt_id; ?> </td>
                    <td class="td-course-title" title="<?php _e('Course Title', 'tutor'); ?>">
                        <a href="<?php echo get_the_permalink($attempt->course_id); ?>" target="_blank"><?php echo get_the_title($attempt->course_id); ?></a>
                    </td>
                    <td  title="<?php _e('Questions', 'tutor'); ?>"><?php echo count(tutor_utils()->get_quiz_answers_by_attempt_id($attempt->attempt_id)); ?></td>
                    <td  title="<?php _e('Total Marks', 'tutor'); ?>"><?php echo $attempt->total_marks; ?></td>
                    <td  title="<?php _e('Date', 'tutor'); ?>">
                        <?php
                            echo date_i18n(get_option('date_format'), strtotime($attempt->attempt_started_at)).' '.date_i18n(get_option('time_format'), strtotime($attempt->attempt_started_at));

                            if ($attempt->is_manually_reviewed){
                                ?>
                                <span class="attempt-reviewed-text" title="Manually reviewed at: ">
                                    <?php echo __(', Updated: ', 'tutor').date_i18n(get_option('date_format', strtotime($attempt->manually_reviewed_at))).' '.date_i18n(get_option('time_format', strtotime($attempt->manually_reviewed_at))); ?>
                                </span>
                                <?php
                            }
                        ?>
                    </td>
                    <td title="<?php _e('Correct Answer', 'tutor-pro'); ?>">
                        <?php
                        $correct = 0;
                        $incorrect = 0;
                        $answers = tutor_utils()->get_quiz_answers_by_attempt_id($attempt->attempt_id);
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
                        echo $correct;
                        ?>
                    </td>
                    <td title="<?php _e('Incorrect Answer', 'tutor-pro'); ?>"><?php echo $incorrect; ?></td>

                    <td title="<?php _e('Earned Marks', 'tutor-pro'); ?>">
                        <?php
                            if ($passing_grade > 0){
                                $pass_marks = ($attempt->total_marks * $passing_grade) / 100;
                                if ($pass_marks > 0){
                                    echo number_format_i18n($pass_marks, 2);
                                }
                            }
                            echo "({$passing_grade}%)";
                        ?>
                    </td>

                    <td  title="<?php _e('Result', 'tutor'); ?>" class="td-questions">
                        <?php
                            if ($attempt->attempt_status === 'review_required'){
                                echo '<span class="result-review-required">' . __('Under Review', 'tutor') . '</span>';
                            }else {

                                if ($earned_percentage >= $passing_grade) {
                                    echo '<span class="result-pass">' . __('Pass', 'tutor-pro') . '</span>';
                                } else {
                                    echo '<span class="result-fail">' . __('Fail', 'tutor-pro') . '</span>';
                                }
                            }
                        ?>
                        <?php 
                            $attempts_url = tutor_utils()->get_tutor_dashboard_page_permalink('my-quiz-attempts/attempts-details');
                            $attempts_url = add_query_arg( 'attempt_id', $attempt->attempt_id, $attempts_url );
                        ?>
                    </td>
                    <td>
                        <a href="<?php echo $attempts_url; ?>"><?php _e('Details', 'tutor'); ?></a>
                    </td>

                </tr>
                <?php
            }
            ?>

        </table>
    </div>

<?php } else {
    echo __('You have not attempted any quiz yet', 'tutor');
} ?>