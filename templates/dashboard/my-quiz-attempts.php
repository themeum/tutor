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
    <div class="tutor-quiz-attempt-history">
        <table>
            <tr>
                <th><?php _e('Course Title', 'tutor'); ?></th>
                <th><?php _e('Questions', 'tutor'); ?></th>
                <th><?php _e('Total Marks', 'tutor'); ?></th>
                <th><?php _e('Earned Marks', 'tutor'); ?></th>
                <th><?php _e('Pass Mark', 'tutor'); ?></th>
            </tr>
			<?php
			foreach ( $previous_attempts as $attempt){
                $earned_percentage = $attempt->earned_marks > 0 ? ( number_format(($attempt->earned_marks * 100) / $attempt->total_marks)) : 0;
				$passing_grade = (int) tutor_utils()->get_quiz_option($attempt->quiz_id, 'passing_grade', 0);
				?>
                <tr class="<?php echo esc_attr($earned_percentage >= $passing_grade ? 'pass' : 'fail') ?>">
                    <td class="td-course-title" title="<?php _e('Course Title', 'tutor'); ?>">
                        <div>
                            <?php
                                echo $earned_percentage >= $passing_grade ? '<span class="result-pass">'.__('Pass', 'tutor').'</span>' : '<span class="result-fail">'.__('Fail', 'tutor').'</span>';
                            ?>

                            <?php
                                echo date_i18n(get_option('date_format'), strtotime($attempt->attempt_started_at)).' '.date_i18n(get_option('time_format'), strtotime($attempt->attempt_started_at));

                                if ($attempt->is_manually_reviewed){
                                    ?>
                                    <span class="attempt-reviewed-text" title="Manually reviewed at: ">
                                        <?php
                                        echo __(', Updated: ', 'tutor').date_i18n(get_option('date_format', strtotime($attempt->manually_reviewed_at))).' '.date_i18n(get_option('time_format', strtotime($attempt->manually_reviewed_at)));
                                        ?>
                                    </span>
                                    <?php
                                }
                            ?>
                        </div>
                        <a href="<?php echo get_the_permalink($attempt->course_id); ?>" target="_blank"><?php echo get_the_title($attempt->course_id); ?></a>
                    </td>
                    <td  title="<?php _e('Questions', 'tutor'); ?>" class="td-questions"><?php echo $attempt->total_questions; ?> </td>
                    <td   title="<?php _e('Total Marks', 'tutor'); ?>" class="td-total-marks"> <?php echo $attempt->total_marks; ?> </td>

                    <td  title="<?php _e('Earned Marks', 'tutor'); ?>" class="td-earned-marks">
						<?php echo $attempt->earned_marks."({$earned_percentage}%)"; ?>
                    </td>
                    <td  title="<?php _e('Pass Marks', 'tutor'); ?>" class="td-pass-marks">
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

                </tr>
				<?php
			}
			?>

        </table>
    </div>

<?php } else {
    echo __('You have not attempted any quiz yet', 'tutor');
} ?>