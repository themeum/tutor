<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

?>

<p><?php _e('Dear {instructor_username},', 'tutor'); ?></p>

<p>
	<?php _e('<strong>{student_username}</strong> has recently completed the lesson <strong>{lesson_name}</strong> of <strong>{course_name}</strong> at <strong>{completion_time}</strong>. The completed lesson URL is: <strong>{lesson_url}</strong>.', 'tutor'); ?>
</p>