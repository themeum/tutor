<?php
/**
 * @package TutorLMS/Templates
 * @version 1.6.9
 */

?>

<p><?php _e('Hi,', 'tutor'); ?></p>
<p>
    <?php printf(__('Welcome to the course %s at %s. You can start learning from here-', 'tutor'), '<strong>{course_name}</strong>', '{site_url}'); ?> 
    <br />
    {course_start_url}.
</p>
