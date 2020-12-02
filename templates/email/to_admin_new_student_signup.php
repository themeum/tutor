<?php
/**
 * @package TutorLMS/Templates
 * @since 1.6.9
 */

?>

<p><?php _e('Hi,', 'tutor'); ?></p>
<p>
	<?php printf(__('A new student has signed up to your site %s', 'tutor'), '<strong>{site_name}</strong>'); ?>
	<br />
	{student_name}
	<br />
	{student_email}
</p>