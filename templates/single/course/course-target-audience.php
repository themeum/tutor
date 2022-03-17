<?php
/**
 * Template for displaying course audience
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */


do_action( 'tutor_course/single/before/audience' );

$target_audience = tutor_course_target_audience();

if ( empty( $target_audience ) ) {
	return;
}

if ( is_array( $target_audience ) && count( $target_audience ) ) {
	?>

	<div class="tutor-course-details-widget tutor-mt-40">
		<div class="tutor-course-details-widget-title tutor-mb-16">
			<span class="tutor-color-black tutor-fs-6 tutor-fw-medium">
				<?php _e('Audience', 'tutor'); ?>
			</span>
		</div>
		<ul class="tutor-course-details-widget-list">
			<?php
				foreach ($target_audience as $audience){
					echo "<li class='tutor-d-flex tutor-color-black tutor-fs-6 tutor-fw-normal tutor-mb-12'><span class='tutor-icon-mark-filled tutor-color-design-brand tutor-mr-4'></span><span>{$audience}</span></li>";
				}
			?>
		</ul>
	</div>

<?php } ?>

<?php do_action( 'tutor_course/single/after/audience' ); ?>
