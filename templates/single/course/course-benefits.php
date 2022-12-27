<?php
/**
 * Template for displaying course benefits
 *
 * @package Tutor\Templates
 * @subpackage Single\Course
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

do_action( 'tutor_course/single/before/benefits' );

$course_benefits = tutor_course_benefits();
if ( empty( $course_benefits ) ) {
	return;
}
?>

<?php if ( is_array( $course_benefits ) && count( $course_benefits ) ) : ?>
	<div class="tutor-course-details-widget tutor-course-details-widget-col-2 tutor-mt-lg-50 tutor-mt-32">
		<h3 class="tutor-course-details-widget-title tutor-fs-5 tutor-fw-bold tutor-color-black tutor-mb-16">
			<?php echo esc_html( apply_filters( 'tutor_course_benefit_title', __( 'What Will You Learn?', 'tutor' ) ) ); ?>
		</h3>
		<ul class="tutor-course-details-widget-list tutor-color-black tutor-fs-6 tutor-m-0 tutor-mt-16">
			<?php foreach ( $course_benefits as $benefit ) : ?>
				<li class="tutor-d-flex tutor-mb-12">
					<span class="tutor-icon-bullet-point tutor-color-muted tutor-mt-2 tutor-mr-8 tutor-fs-8" area-hidden="true"></span>
					<span><?php echo esc_html( $benefit ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
<?php endif; ?>

<?php do_action( 'tutor_course/single/after/benefits' ); ?>
