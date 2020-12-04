<?php
/**
 * @package TutorLMS/Templates
 * @since 1.6.9
 */

?>

<p><?php _e('Hi,', 'tutor'); ?></p>
<p>
    <?php _e('The instructor has answered your question on the course- <strong>{course_name}</strong>. Here is the answer-', 'tutor'); ?>
    <br />
    {answer}
</p>

<p><?php _e('You can continue the discussion here - {course_url}', 'tutor'); ?></p>

