<?php
/**
 * @package TutorLMS/Templates
 * @version 1.6.2
 */

?>

<p><?php _e('Hi {instructor_username},', 'tutor'); ?></p>

<p>
    <?php _e('<strong>{username}</strong> just submitted answers for <strong>{quiz_name}</strong> in course <strong>{course_name}</strong> at <strong>{submission_time}</strong>. You can review it from: <strong>{quiz_review_url}</strong>.', 'tutor'); ?>
</p>