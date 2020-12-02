<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

?>

<p><?php printf(__('Dear %s', 'tutor'), '{instructor_username},'); ?></p>

<p>
	<?php printf(__('%s asked a question on %s at %s. The reply URL is: %s. You will find the question below.', 'tutor'), '{student_username}', '<strong>{course_name}</strong>', '<strong>{enroll_time}</strong>', '<strong>{course_url}</strong>'); ?>
</p>

<br />
<p>{question_title}</p>
<p>{question}</p>