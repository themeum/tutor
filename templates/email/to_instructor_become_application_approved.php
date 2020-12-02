<?php
/**
 * @package TutorLMS/Templates
 * @version 1.7.4
 */

?>

<h4><?php _e('Congrats!', 'tutor'); ?></h4>
<p><?php printf(__('Dear %s,', 'tutor'), '{instructor_username}'); ?></p>

<p>
	<?php printf(__('You are now an instructor for the %s team. Go ahead and start creating your first course today!', 'tutor'), '<b>{site_name}</b>'); ?>
</p>