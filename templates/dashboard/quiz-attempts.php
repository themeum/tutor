<?php
/**
 * Students Quiz Attempts Frontend
 *
 * @since v.1.4.0
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.6.4
 */

$per_page = 20;
$current_page = max( 1, tutils()->array_get('current_page', $_GET) );
$offset = ($current_page-1)*$per_page;
?>
    <h3><?php _e('My Quiz Attempts', 'tutor'); ?></h3>
<?php
$course_id = tutor_utils()->get_assigned_courses_ids_by_instructors();
$quiz_attempts = tutor_utils()->get_quiz_attempts_by_course_ids($offset, $per_page, $course_id);
$quiz_attempts_count = tutor_utils()->get_total_quiz_attempts_by_course_ids($course_id);

if ( $quiz_attempts_count ){
	?>
    <div class="tutor-quiz-attempt-history">
        <table>
            <tr>
                <th><?php _e('Course InfoA', 'tutor'); ?></th>
                <th><?php _e('Correct Answer', 'tutor'); ?></th>
                <th><?php _e('Incorrect Answer', 'tutor'); ?></th>
                <th><?php _e('Earned Mark', 'tutor'); ?></th>
                <th><?php _e('Result', 'tutor'); ?></th>
                <th></th>
                <?php do_action('tutor_quiz/my_attempts/table/thead/col'); ?>
            </tr>
			<?php
			foreach ( $quiz_attempts as $attempt){
				$attempt_action = tutor_utils()->get_tutor_dashboard_page_permalink('quiz-attempts/quiz-reviews/?attempt_id='.$attempt->attempt_id);
				$earned_percentage = $attempt->earned_marks > 0 ? ( number_format(($attempt->earned_marks * 100) / $attempt->total_marks)) : 0;
                $passing_grade = tutor_utils()->get_quiz_option($attempt->quiz_id, 'passing_grade', 0);
                $answers = tutor_utils()->get_quiz_answers_by_attempt_id($attempt->attempt_id);
				?>
                <tr>
                    <td>
                        <div>
                            <a href="<?php echo get_the_permalink($attempt->course_id); ?>" target="_blank"><?php echo get_the_title($attempt->course_id); ?></a>
                        </div>
                        <div>
                            <?php echo date_i18n(get_option('date_format').' '.get_option('time_format'), strtotime($attempt->attempt_ended_at)); ?>
                            <strong><?php _e('Question: ','tutor'); ?></strong><?php echo count($answers); ?>
                            <strong><?php _e('Total Marks: ','tutor'); ?></strong><?php echo $attempt->total_marks; ?>
                        </div>
                    </td>
                    <td>
                        <?php
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
                            echo $correct;
                        ?>
                    </td>
                    <td>
                        <?php echo $incorrect; ?>
                    </td>
                    <td>
                        <?php echo $attempt->earned_marks.' ('.$passing_grade.'%)'; ?>
                    </td>
                    <td>
                        <?php
                            if ($attempt->attempt_status === 'review_required'){
                                echo '<span class="result-review-required">' . __('Under Review', 'tutor') . '</span>';
                            }else{
                                echo $earned_percentage >= $passing_grade ? '<span class="result-pass">'.__('Pass', 'tutor').'</span>' : '<span class="result-fail">'.__('Fail', 'tutor').'</span>';
                            }
                        ?>
                    </td>
                    <td><a href="<?php echo $attempt_action; ?>"><?php _e('Details', 'tutor'); ?></a></td>
                    <?php do_action('tutor_quiz/my_attempts/table/tbody/col'); ?>
                </tr>
				<?php
			}
			?>
        </table>
    </div>
    <div class="tutor-pagination">
		<?php
		echo paginate_links( array(
			'format' => '?current_page=%#%',
			'current' => $current_page,
			'total' => ceil($quiz_attempts_count/$per_page)
		) );
		?>
    </div>
<?php } else {
	_e('You have not attempted for any quiz yet.', 'tutor');
} ?>