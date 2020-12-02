<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

?>

<p><?php printf(__('Dear %s,', 'tutor'), '{instructor_username}'); ?></p>

<p>
	<?php printf(__('%s has recently completed the lesson %s of %s at %s. The completed lesson URL is: %s.', 'tutor'), '<strong>{student_username}</strong>', '<strong>{lesson_name}</strong>', '<strong>{course_name}</strong>', '<strong>{completion_time}</strong>', '<strong>{lesson_url}</strong>'); ?>
</p>