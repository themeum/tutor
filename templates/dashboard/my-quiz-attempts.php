<?php
/**
 * Students Quiz Attempts Frontend
 *
 * @since v.1.4.0
 *
 * @author Themeum
 * @url https://themeum.com
 * @package Tutor
 */

$per_page = 1;
$current_page = max( 1, tutor_utils()->avalue_dot('current_page', $_GET) );
$offset = ($current_page-1)*$per_page;
?>
    <h3><?php _e('My Quiz Attempts', 'tutor'); ?></h3>
<?php
$course_id = tutor_utils()->get_assigned_courses_ids_by_instructors();
$quiz_attempts = tutor_utils()->get_quiz_attempts_by_course_ids($offset, $per_page*$current_page, $course_id);
$quiz_attempts_count = tutor_utils()->get_total_quiz_attempts_by_course_ids($course_id);

if ( $quiz_attempts_count ){
	?>
    <div class="tutor-quiz-attempt-history">
        <table>
            <tr>
                <th><?php _e('Students', 'tutor'); ?></th>
                <th><?php _e('Quiz', 'tutor'); ?></th>
                <th><?php _e('Course', 'tutor'); ?></th>
                <th><?php _e('Total Questions', 'tutor'); ?></th>
                <th><?php _e('Earned Mark', 'tutor'); ?></th>
                <th><?php _e('Attempt Status', 'tutor'); ?></th>
            </tr>
			<?php
			foreach ( $quiz_attempts as $attempt){
                $earned_percentage = $attempt->earned_marks > 0 ? ( number_format(($attempt->earned_marks * 100) / $attempt->total_marks)) : 0;
				$passing_grade = tutor_utils()->get_quiz_option($attempt->quiz_id, 'passing_grade', 0);
				?>
                <tr class="<?php echo esc_attr($earned_percentage >= $passing_grade ? 'pass' : 'fail') ?>">
                    <td class="td-course-title" title="<?php _e('Course Title', 'tutor'); ?>">
                        <?php
                        	$quiz_title = "<p><strong>{$attempt->display_name}</strong></p>";
                            $quiz_title .= "<p>{$attempt->user_email}</p>";
                    
                            if ($attempt->attempt_ended_at){
                                $ended_ago_time = human_time_diff(strtotime($attempt->attempt_ended_at)).__(' ago', 'tutor');
                                $quiz_title .= "<span>{$ended_ago_time}</span>";
                            }

                            $attempt_action = tutor_utils()->get_tutor_dashboard_page_permalink('my-quiz-attempts/quiz-reviews/?attempt_id='.$attempt->attempt_id);

                            echo sprintf('%1$s <span style="color:silver">(id:%2$s)</span> <a href="%3$s">Action</a>',
                                $quiz_title,
                                $attempt->attempt_id,
                                $attempt_action
                            );
                        ?>
                    </td>
                    <td title="<?php echo __('Quiz', 'tutor'); ?>"><?php echo $attempt->post_title; ?></td>
                    <td title="<?php echo __('Course', 'tutor'); ?>"><a href="<?php echo get_the_permalink($attempt->course_id); ?>" target="_blank"><?php echo get_the_title($attempt->course_id); ?></a></td>
                    <td title="<?php echo __('Total Questions', 'tutor'); ?>"><?php echo $attempt->total_questions; ?></td>
                    <td title="<?php echo __('Earned Mark', 'tutor'); ?>">
                        <?php
                            echo sprintf(__('%1$s out of %2$s (%3$s%) pass (%4$s)','tutor'), $attempt->earned_marks, $attempt->total_marks, $earned_percentage, $passing_grade );
                            echo $earned_percentage >= $passing_grade ? '<span class="result-pass">'.__('Pass', 'tutor').'</span>' : '<span class="result-fail">'.__('Fail', 'tutor').'</span>';
                        ?>
                    </td>
                    <td title="<?php echo __('Attempt Status', 'tutor'); ?>"><?php echo $attempt->attempt_status; ?></td>
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
    echo __('My Quiz data is empty', 'tutor');
} ?>