<?php
/**
 * @package TutorLMS/Templates
 * @since 1.6.9
 */

?>

<p><?php _e('Hi,', 'tutor'); ?></p>
<p>
    <?php printf(__('The instructor has submitted the marks for the quiz %s in the course %s. You have got- %s', 'tutor'), '<strong>{quiz_name}</strong>', '<strong>{course_name}</strong>', '<strong>{earned_marks}</strong>'); ?>
    <br />
    <?php printf(__('Instructor feedback: %s', 'tutor'), '{instructor_feedback}'); ?>
</p>

<p><?php _e('You may reply to this email to communicate with the instructor.', 'tutor'); ?></p>

