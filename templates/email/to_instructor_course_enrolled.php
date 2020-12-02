<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

?>

<p><?php printf(__('Dear %s,', 'tutor'), '{instructor_username}'); ?></p>

<p>
	<?php printf(__('%s has enrolled on %s at %s. The enrolled course URL is: %s.', 'tutor'), '{student_username}', '<strong>{course_name}</strong>', '<strong>{enroll_time}</strong>', '<strong>{course_url}</strong>'); ?>
</p>