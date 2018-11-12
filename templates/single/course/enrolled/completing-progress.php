<?php
/**
 * Progress bar
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 */

$completed_count = tutor_utils()->get_course_completed_percent();

do_action('tutor_course/single/enrolled/before/lead_info/progress_bar');
?>

<div class="tutor-progress-bar">
	<div class="tutor-progress-filled" style="width: <?php echo $completed_count; ?>%"></div>
	<span class="tutor-progress-percent"><?php echo $completed_count; ?>%</span>
</div>

<?php
    do_action('tutor_course/single/enrolled/after/lead_info/progress_bar');
?>

