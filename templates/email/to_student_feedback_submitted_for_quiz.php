<?php
/**
 * @package TutorLMS/Templates
 * @since 1.6.9
 */

?>

<p><?php _e('Hi,', 'tutor'); ?></p>
<p>
    <?php _e('The instructor has submitted the marks for the quiz <strong>{quiz_name}</strong> in the course <strong>{course_name}</strong>. You have got- <strong>{earned_marks}</strong>', 'tutor'); ?>
    <br />
    <?php _e('Instructor feedback: {instructor_feedback}', 'tutor'); ?>
</p>

<p><?php _e('You may reply to this email to communicate with the instructor.', 'tutor'); ?></p>

