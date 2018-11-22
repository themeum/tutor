<?php
/**
 * Progress bar
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 */

$completed_count = dozent_utils()->get_course_completed_percent();

do_action('dozent_course/single/enrolled/before/lead_info/progress_bar');
?>

<div class="dozent-course-status">
    <h4 class="dozent-segment-title"><?php _e('Course Status', 'dozent'); ?></h4>
    <div class="dozent-progress-bar-wrap">
        <div class="dozent-progress-bar">
            <div class="dozent-progress-filled" style="--dozent-progress-left: <?php echo $completed_count.'%;'; ?>"></div>
        </div>
        <span class="dozent-progress-percent"><?php echo $completed_count; ?>% <?php _e(' Complete', 'dozent')?></span>
    </div>
</div>

<?php
    do_action('dozent_course/single/enrolled/after/lead_info/progress_bar');
?>

