<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

?>

<p><?php _e('Dear {instructor_username},', 'tutor'); ?></p>

<p>
	<?php _e('{student_username} has recently completed <strong>{course_name}</strong> at <strong>{completion_time}</strong>. The completed course URL is: <strong>{course_url}</strong>.', 'tutor'); ?>
</p>