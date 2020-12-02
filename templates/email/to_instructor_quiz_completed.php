<?php
/**
 * @package TutorLMS/Templates
 * @version 1.6.2
 */

?>

<p><?php printf(__('Hi %s,', 'tutor'), '{instructor_username}'); ?></p>

<p>
    <?php printf(__('%s just submitted answers for %s in course %s at %s. You can review it from: %s.', 'tutor'), '<strong>{username}</strong>', '<strong>{quiz_name}</strong>', '<strong>{course_name}</strong>', '<strong>{submission_time}</strong>', '<strong>{quiz_review_url}</strong>'); ?>
</p>