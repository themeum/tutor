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
    <?php _e('Student Name - <strong>{student_name}</strong>', 'tutor'); ?>
    <br />
    <?php _e('Course Name - <strong>{course_name}</strong>', 'tutor'); ?>
    <br />
    <?php _e('Assignment Name - <strong>{assignment_name}</strong>', 'tutor'); ?>
    <br />
    <?php _e('Review Submission - {review_link}', 'tutor'); ?>
</p>

<p><?php _e('Reply to this email to communicate with the instructor.', 'tutor'); ?></p>