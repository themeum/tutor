<?php
/**
 * @package TutorLMS/Templates
 * @since 1.6.9
 */

?>

<p><?php _e('Hi,', 'tutor'); ?></p>
<p>
    <?php printf(__('The instructor posted a new announcement on course - %s', 'tutor'), '<strong>{course_name}</strong>'); ?>
    <br />
    {announcement}
</p>

