<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

?>

<p><?php _e('Dear {instructor_username},', 'tutor'); ?></p>

<p>
	<?php _e('{student_username} has enrolled on <strong>{course_name}</strong> at <strong>{enroll_time}</strong>. The enrolled course URL is: <strong>{course_url}</strong>.', 'tutor'); ?>
</p>