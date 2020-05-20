<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

$passing_grade = tutor_utils()->get_quiz_option($quiz_id, 'passing_grade', 0);

?>

<h4 class="tutor-quiz-attempt-history-title"><?php _e('Previous attempts', 'tutor-pro'); ?></h4>
<div class="tutor-quiz-attempt-history single-quiz-page">
    <table>
        <thead>
        <tr>
            <th>#</th>
            <th><?php _e('Attempts Date', 'tutor-pro'); ?></th>
            <th><?php _e('Correct Answer', 'tutor-pro'); ?></th>
            <th><?php _e('Incorrect Answer', 'tutor-pro'); ?></th>
            <th><?php _e('Earned Marks', 'tutor-pro'); ?></th>
            <th><?php _e('Result', 'tutor-pro'); ?></th>
			<?php do_action('tutor_quiz/previous_attempts/table/thead/col'); ?>
        </tr>
        </thead>

        <tbody>
		<?php
		foreach ( $previous_attempts as $attempt){
			?>
            <tr>
                <td><?php echo $attempt->attempt_id; ?></td>
                <td title="<?php _e('Attempts Date', 'tutor-pro'); ?>">
					<?php echo date_i18n(get_option('date_format'), strtotime($attempt->attempt_started_at)); ?>
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
					$earned_percentage = $attempt->earned_marks > 0 ? ( number_format(($attempt->earned_marks * 100) / $attempt->total_marks)) : 0;
					echo $attempt->earned_marks."({$earned_percentage}%)";
					?>
                </td>

                <td title="<?php _e('Result', 'tutor-pro'); ?>">
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
                    <a href="<?php echo $attempts_url; ?>"><?php _e('Details', 'tutor'); ?></a>
                </td>

				<?php do_action('tutor_quiz/previous_attempts/table/tbody/col', $attempt); ?>
            </tr>
			<?php
		}
		?>
        </tbody>

    </table>
</div>
