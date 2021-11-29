<?php
/**
 * Template for displaying course requirements
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */


do_action('tutor_course/single/before/requirements');

$course_requirements = tutor_course_requirements();

if ( empty($course_requirements)){
	return;
}

if (is_array($course_requirements) && count($course_requirements)){
	?>

	<div class="tutor-course-details-widget tutor-mt-40">
		<div class="tutor-course-details-widget-title tutor-mb-16">
			<span class="color-text-primary text-medium-h6"><?php _e('Requirements', 'tutor'); ?></span>
		</div>
		<ul class="tutor-course-details-widget-list">
			<?php
				foreach ($course_requirements as $requirement){
					echo "<li class='tutor-bs-d-flex color-text-primary text-regular-body tutor-mb-10'><span class='ttr-mark-filled color-design-brand tutor-mr-5'></span><span>{$requirement}</span></li>";
				}
			?>
		</ul>
	</div>

<?php } ?>

<?php do_action('tutor_course/single/after/requirements'); ?>
