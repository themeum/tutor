<?php
/**
 * @package TutorLMS/Templates
 * @since 1.6.9
 */

?>

<p><?php _e('Hi,', 'tutor'); ?></p>
<p>
    <?php _e('You have received a submission for an assignment.', 'tutor'); ?>
    <br />
    <?php printf(__('Student Name - %s', 'tutor'), '<strong>{student_name}</strong>'); ?>
    <br />
    <?php printf(__('Course Name - %s', 'tutor'), '<strong>{course_name}</strong>'); ?>
    <br />
    <?php printf(__('Assignment Name - %s', 'tutor'), '<strong>{assignment_name}</strong>'); ?>
    <br />
    <?php printf(__('Review Submission - %s', 'tutor'), '{review_link}'); ?>
</p>

<p><?php _e('Reply to this email to communicate with the instructor.', 'tutor'); ?></p>