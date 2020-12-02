<?php
/**
 * @package TutorLMS/Templates
 * @since 1.6.9
 */

?>

<p><?php _e('Hi,', 'tutor'); ?></p>
<p><?php printf(__('The grade has been submitted for the assignment %s for the course %', 'tutor'), '<strong>{assignment_name}</strong>', '<strong>{course_name}</strong>'); ?></p>
<p>
    <?php printf(__('Your score: %s', 'tutor'), '<strong>{assignemnt_score}</strong>'); ?>
    <br />
    <?php printf(__('Instructor Comment: %s', 'tutor'), '{assignment_comment}'); ?>
</p>
