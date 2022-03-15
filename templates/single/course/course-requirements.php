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
		<div class="tutor-course-details-widget-title tutor-mb-16">
			<span class="tutor-color-black tutor-fs-6 tutor-fw-medium"><?php _e('Requirements', 'tutor'); ?></span>
		</div>
		<ul class="tutor-course-details-widget-list">
			<?php
				foreach ($course_requirements as $requirement){
					echo "<li class='tutor-d-flex tutor-color-black tutor-fs-6 tutor-fw-normal tutor-mb-12'><span class='tutor-icon-mark-filled tutor-color-design-brand tutor-mr-4'></span><span>{$requirement}</span></li>";
				}
			?>
		</ul>
	</div>

<?php } ?>

<?php do_action( 'tutor_course/single/after/requirements' ); ?>
