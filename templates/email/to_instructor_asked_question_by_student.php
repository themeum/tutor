<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

?>

<p><?php _e('Dear {instructor_username},', 'tutor'); ?></p>

<p>
	<?php _e('{student_username} asked a question on <strong>{course_name}</strong> at <strong>{enroll_time}</strong>. The reply URL is: <strong>{course_url}</strong>. You will find the question below.', 'tutor'); ?>
</p>

<br />
<p>{question_title}</p>
<p>{question}</p>