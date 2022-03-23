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


do_action( 'tutor_course/single/before/requirements' );

$course_requirements = tutor_course_requirements();

if ( empty( $course_requirements ) ) {
	return;
}

if ( is_array( $course_requirements ) && count( $course_requirements ) ) {
	?>

	<div class="tutor-course-details-widget tutor-mt-40">
		<h3 class="tutor-course-details-widget-title tutor-fs-5 tutor-color-black tutor-fw-bold tutor-mb-16">
			<?php _e('Requirements', 'tutor'); ?>
		</h3>
		<ul class="tutor-course-details-widget-list tutor-fs-6 tutor-color-black">
			<?php
				foreach ($course_requirements as $requirement){
					echo "<li class='tutor-d-flex tutor-mb-12'><span class='tutor-icon-mark-filled tutor-color-design-brand tutor-mr-4' area-hidden='true'></span><span>{$requirement}</span></li>";
				}
			?>
		</ul>
	</div>

<?php } ?>

<?php do_action( 'tutor_course/single/after/requirements' ); ?>
