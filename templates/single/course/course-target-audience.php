<?php
/**
 * Template for displaying course audience
 *
 * @package Tutor\Templates
 * @subpackage Single\Course
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

do_action( 'tutor_course/single/before/audience' );

$target_audience = tutor_course_target_audience();

if ( empty( $target_audience ) ) {
	return;
}
?>

<?php if ( is_array( $target_audience ) && count( $target_audience ) ) : ?>
	<div class="tutor-course-details-widget">
		<h3 class="tutor-course-details-widget-title tutor-fs-5 tutor-color-black tutor-fw-bold tutor-mb-16">
			<?php esc_html_e( 'Audience', 'tutor' ); ?>
		</h3>
		<ul class="tutor-course-details-widget-list tutor-fs-6 tutor-color-black">
			<?php foreach ( $target_audience as $audience ) : ?>
				<li class="tutor-d-flex tutor-mb-12">
					<span class="tutor-icon-bullet-point tutor-color-muted tutor-mt-2 tutor-mr-8 tutor-fs-8" area-hidden="true"></span>
					<span><?php echo esc_html( $audience ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
<?php endif; ?>

<?php do_action( 'tutor_course/single/after/audience' ); ?>
