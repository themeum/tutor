<?php
/**
 * @package TutorLMS/Templates
 * @since 1.6.9
 */

?>

<p><?php _e('Hi,', 'tutor'); ?></p>
<p>
    <?php printf(__('The instructor has answered your question on the course- %s. Here is the answer-', 'tutor'), '<strong>{course_name}</strong>'); ?>
    <br />
    {answer}
</p>

<p><?php printf(__('You can continue the discussion here - %s', 'tutor'), '{course_url}'); ?></p>

