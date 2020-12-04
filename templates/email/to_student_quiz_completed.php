<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

?>

<p><?php printf(__('Dear %s,', 'tutor'), '{username}'); ?> </p>

<p>
	<?php printf(__('Thank you for submitting your answers for %s in course %s at %s. This message is to confirm that we have received your answers. You can access this quiz on: %s.', 'tutor'), '<strong>{quiz_name}</strong>', '<strong>{course_name}</strong>', '<strong>{submission_time}</strong>', '<strong>{quiz_url}</strong>'); ?>
</p>