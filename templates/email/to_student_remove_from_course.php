<?php
/**
 * @package TutorLMS/Templates
 * @since 1.6.9
 */

?>

<p><?php _e('Hi,', 'tutor'); ?></p>
<p>
    <?php printf(__('This is to notify you that the instructor has removed you from the course - %s', 'tutor'), '<strong>{course_name}</strong>'); ?>
    <br />
    <br />
    --
    <?php _e('Regards', 'tutor'); ?>,
    <br />
    {site_name}
</p>

