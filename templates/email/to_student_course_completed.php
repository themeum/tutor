<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

?>

<p><?php printf(__('Dear %,', 'tutor'), '{student_username}'); ?> </p>

<p>
	<?php printf(__('Thank you for completing %s on %s. This message is to confirm that you have successfully completed the mentioned course. For future access, the course will be available on %s.', 'tutor'), '<strong>{course_name}</strong>', '<strong>{completion_time}</strong>', '<strong>{course_url}</strong>'); ?>
</p>