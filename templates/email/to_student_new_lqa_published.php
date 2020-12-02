<?php
/**
 * @package TutorLMS/Templates
 * @version 1.7.4
 */

?>

<p><?php printf(__('Dear %s,', 'tutor'), '{student_username}'); ?></p>

<p>
	<?php printf(__('A new %s has been published for the course %s called %s. Go ahead and get started today!', 'tutor'), '{lqa_type}', '<b>{course_title}</b>', '<b>{lqa_title}</b>'); ?>
</p>