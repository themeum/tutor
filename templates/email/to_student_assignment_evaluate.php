<?php
/**
 * @package TutorLMS/Templates
 * @since 1.6.9
 */

?>

<p><?php _e('Hi,', 'tutor'); ?></p>
<p><?php _e('The grade has been submitted for the assignment <strong>{assignment_name}</strong> for the course <strong>{course_name}</strong>', 'tutor'); ?></p>
<p>
    <?php _e('Your score: <strong>{assignemnt_score}</strong>', 'tutor'); ?>
    <br />
    <?php _e('Instructor Comment: {assignment_comment}', 'tutor'); ?>
</p>
