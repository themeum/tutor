<?php
/**
 * @package TutorLMS/Templates
 * @version 1.6.9
 */

?>

<p><?php _e('Hi,', 'tutor'); ?></p>
<p>
    <?php printf(__('Congratulations on finishing the course %s. We hope that you had a great experience on our platform. We would really appreciate it if you can post a review on the course and the instructor. Your valuable feedback would help us improve the content on our site and improve the learning experience.', 'tutor'), '<strong>{course_name}</strong>'); ?>
</p>
<p>
    <?php printf(__('Here is the link to post a review on the course- %s', 'tutor'), '{course_url}'); ?>
    <br />
    <?php printf(__('Here is the link to post a review for the instructor- %s', 'tutor'), '{instructor_url}'); ?>
</p>