<?php
/**
 * Quiz Attempts, I attempted to courses
 *
 * @since v.1.1.2
 *
 * @author Themeum
 * @url https://themeum.com
 * @package Tutor
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
                <th><?php _e('Time', 'tutor'); ?></th>
                <th><?php _e('Questions', 'tutor'); ?></th>
                <th><?php _e('Total Marks', 'tutor'); ?></th>
                <th><?php _e('Earned Marks', 'tutor'); ?></th>
                <th><?php _e('Pass Mark', 'tutor'); ?></th>
                <th><?php _e('Result', 'tutor'); ?></th>
            </tr>
			<?php
			foreach ( $previous_attempts as $attempt){
				$passing_grade = tutor_utils()->get_quiz_option($attempt->quiz_id, 'passing_grade', 0);
				?>
                <tr>
                    <td><a href="<?php echo get_the_permalink($attempt->course_id); ?>" target="_blank"><?php echo get_the_title($attempt->course_id); ?></a>
                    </td>
                    <td>
						<?php
						echo date_i18n(get_option('date_format'), strtotime($attempt->attempt_started_at)).' '.date_i18n(get_option('time_format'), strtotime($attempt->attempt_started_at));

						if ($attempt->is_manually_reviewed){
							?>
                            <p class="attempt-reviewed-text">
								<?php
								echo __('Manually reviewed at', 'tutor').' <br /> '.date_i18n(get_option('date_format', strtotime($attempt->manually_reviewed_at))).' '.date_i18n(get_option('time_format', strtotime($attempt->manually_reviewed_at)));
								?>
                            </p>
							<?php
						}
						?>
                    </td>
                    <td><?php echo $attempt->total_questions; ?> </td>
                    <td> <?php echo $attempt->total_marks; ?> </td>

                    <td>
						<?php
						$earned_percentage = $attempt->earned_marks > 0 ? ( number_format(($attempt->earned_marks * 100) / $attempt->total_marks)) : 0;
						echo $attempt->earned_marks."({$earned_percentage}%)";
						?>
                    </td>

                    <td>
						<?php

						$pass_marks = ($attempt->total_marks * $passing_grade) / 100;
						if ($pass_marks > 0){
							echo number_format_i18n($pass_marks, 2);
						}
						echo "({$passing_grade}%)";
						?>
                    </td>

                    <td>
						<?php

                            echo $earned_percentage >= $passing_grade ? '<span class="result-pass">'.__('Pass', 'tutor').'</span>' : '<span class="result-fail">'.__('Fail', 'tutor').'</span>';
						?>
                    </td>
                </tr>
				<?php
			}
			?>

        </table>
    </div>

<?php } ?>