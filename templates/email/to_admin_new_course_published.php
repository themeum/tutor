<?php
/**
 * @package TutorLMS/Templates
 * @since 1.6.9
 */

?>

<p><?php _e('Hi,', 'tutor'); ?></p>
<p>
    <?php printf(__('A new course has been published by %s on your site %s', 'tutor'), '<strong>{instructor_name}</strong>', '<strong>{site_name}</strong>'); ?>
    <br />
    <?php printf(__('Course name - %s', 'tutor'), '<strong>{course_name}</strong>'); ?>
    <br />
    <?php printf(__('View the course - %s', 'tutor'), '{course_url}'); ?>
</p>

<p><?php _e('Reply to this email to communicate with the instructor.', 'tutor'); ?></p>