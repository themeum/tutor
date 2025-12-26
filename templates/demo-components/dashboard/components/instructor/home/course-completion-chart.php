<?php
/**
 * Course Completion Chart Component
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

?>

<!-- Course Completion Chart -->
<div class="tutor-dashboard-home-chart tutor-flex-1" data-stacked="true">
	<div class="tutor-small">
		<?php esc_html_e( 'Course Completion Distribution', 'tutor' ); ?>
	</div>

	<canvas class="tutor-dashboard-home-chart-canvas" x-data='tutorCourseCompletionChart(<?php echo wp_json_encode( $course_completion_data ); ?>)' x-ref="canvas"></canvas>
	
	<div class="tutor-grid tutor-grid-cols-3 tutor-gap-6 tutor-mt-11">
		<?php foreach ( $course_completion_data as $key => $value ) : ?>
			<div class="tutor-dashboard-home-chart-legend" data-color="<?php echo esc_attr( $key ); ?>">
				<div class="tutor-flex tutor-flex-column">
					<div>
						<?php echo esc_html( $value['label'] ); ?>
					</div>
					<div class="tutor-text-primary tutor-font-medium">
						<?php echo esc_html( $value['value'] ); ?>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>
