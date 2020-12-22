<?php
/**
 * @package TutorLMS/Templates
 * @since 1.6.9
 */

?>

<p><?php _e('Hi,', 'tutor'); ?></p>
<p>
	<?php _e('A new student has signed up to your site <strong>{site_name}</strong>', 'tutor'); ?>
	<br />
	{student_name}
	<br />
	{student_email}
</p>