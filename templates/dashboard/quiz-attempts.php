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
 * @version 1.4.3
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
                <th><?php _e('Title (Quiz & Course)', 'tutor'); ?></th>
                <th><?php _e('Students', 'tutor'); ?></th>
                <th><?php _e('Count', 'tutor'); ?></th>
                <th><?php _e('Earned Mark', 'tutor'); ?></th>
                <th><?php _e('Status', 'tutor'); ?></th>
                <th><?php _e('Review', 'tutor'); ?></th>
            </tr>
			<?php
			foreach ( $quiz_attempts as $attempt){
				$attempt_action = tutor_utils()->get_tutor_dashboard_page_permalink('quiz-attempts/quiz-reviews/?attempt_id='.$attempt->attempt_id);
				$earned_percentage = $attempt->earned_marks > 0 ? ( number_format(($attempt->earned_marks * 100) / $attempt->total_marks)) : 0;
				$passing_grade = tutor_utils()->get_quiz_option($attempt->quiz_id, 'passing_grade', 0);
				?>
                <tr class="<?php echo esc_attr($earned_percentage >= $passing_grade ? 'pass' : 'fail') ?>">
                    <td title="<?php echo __('Quiz', 'tutor'); ?>">
						<?php
						echo $earned_percentage >= $passing_grade ? '<span class="result-pass">'.__('Pass', 'tutor').'</span>' : '<span class="result-fail">'.__('Fail', 'tutor').'</span>';
						if ($attempt->attempt_ended_at){
							$ended_ago_time = human_time_diff(strtotime($attempt->attempt_ended_at)).__(' ago', 'tutor');
							echo " <small>{$ended_ago_time}</small>";
						}
						?>
                        <div>
							<?php echo "#".$attempt->attempt_id; ?>: <a href="<?php echo esc_url($attempt_action); ?>"><?php echo $attempt->post_title; ?></a>
                        </div>
                        <div>
                            <?php //echo __('Course:', 'tutor'); ?><!-- <a href="--><?php //echo get_the_permalink($attempt->course_id); ?><!--" target="_blank">--><?php //echo get_the_title($attempt->course_id); ?><!--</a>-->
							<?php echo __('Course:', 'tutor'); ?> <?php echo get_the_title($attempt->course_id); ?>
                        </div>
                    </td>
                    <td class="td-course-title" title="<?php _e('Course Title', 'tutor'); ?>">
						<?php
						$quiz_title = "<div><strong>{$attempt->display_name}</strong></div>";
						$quiz_title .= "<div>{$attempt->user_email}</div>";
						echo $quiz_title;
						?>
                    </td>
                    <td title="<?php echo __('Total Questions', 'tutor'); ?>"><?php echo $attempt->total_questions; ?></td>
                    <td title="<?php echo __('Earned Mark', 'tutor'); ?>" style="white-space: nowrap">
						<?php

						$pass_marks = 0;
                        if ($passing_grade > 0){
	                        $pass_marks = ($attempt->total_marks * $passing_grade) / 100;
                        }

//						if ($pass_marks > 0){
//							echo number_format_i18n($pass_marks, 2);
//						}

//						if ($passing_grade){
//							echo "({$passing_grade}%)";
//						}

						echo sprintf(__('%1$s out of %2$s <br> Pass Mark: %3$s <br> Earned total: %4$s%%','tutor'), $attempt->earned_marks, $attempt->total_marks, $pass_marks ,$passing_grade );
						?>
                    </td>
                    <td title="<?php echo __('Attempt Status', 'tutor'); ?>"><?php echo str_replace('attempt_', '', $attempt->attempt_status); ?></td>
                    <td><a href="<?php echo $attempt_action; ?>"><i class="tutor-icon-angle-right"></i></a></td>
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