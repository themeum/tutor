<?php
/**
 * Template for displaying course benefits
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 *
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */



do_action('tutor_course/single/before/benefits');


$course_benefits = tutor_course_benefits();
if ( empty($course_benefits)){
	return;
}
?>

<?php if (is_array($course_benefits) && count($course_benefits)): ?>
	<div class="tutor-course-details-widget tutor-course-details-widget-col-2 tutor-mt-30">
		<div class="widget-title tutor-m-0">
			<span class="color-text-primary text-medium-h6">
				<?php _e('What Will I Learn?', 'tutor'); ?>
			</span>
		</div>
		<ul class="tutor-course-details-widget-list tutor-m-0 tutor-mt-16">
			<?php foreach ($course_benefits as $benefit): ?>
				<li class="tutor-bs-d-flex tutor-bs-align-items-center color-text-primary text-regular-body tutor-mb-10">
					<span class="ttr-mark-filled color-design-brand tutor-mr-5"></span>
					<span><?php echo $benefit; ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
<?php endif; ?>

<?php do_action('tutor_course/single/after/benefits'); ?>