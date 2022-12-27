<?php
/**
 * Template for displaying course requirements
 *
 * @package Tutor\Templates
 * @subpackage Single\Course
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

do_action( 'tutor_course/single/before/requirements' );

$course_requirements = tutor_course_requirements();

if ( empty( $course_requirements ) ) {
	return;
}

if ( is_array( $course_requirements ) && count( $course_requirements ) ) {
	?>

	<div class="tutor-course-details-widget">
		<h3 class="tutor-course-details-widget-title tutor-fs-5 tutor-color-black tutor-fw-bold tutor-mb-16">
			<?php esc_html_e( 'Requirements', 'tutor' ); ?>
		</h3>
		<ul class="tutor-course-details-widget-list tutor-fs-6 tutor-color-black">
			<?php
			foreach ( $course_requirements as $requirement ) {
				echo '<li class="tutor-d-flex tutor-mb-12"><span class="tutor-icon-bullet-point tutor-color-muted tutor-mt-2 tutor-mr-8 tutor-fs-8" area-hidden="true"></span><span>' . esc_html( $requirement ) . '</span></li>';
			}
			?>
		</ul>
	</div>

<?php } ?>

<?php do_action( 'tutor_course/single/after/requirements' ); ?>
