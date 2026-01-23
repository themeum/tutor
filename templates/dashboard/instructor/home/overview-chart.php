<?php
/**
 * Overview Chart Component - Earnings Over Time
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;
?>

<!-- Overview Chart -->
<div 
	data-section-id="overview_chart"
	class="tutor-dashboard-home-chart"
	:class="{ 'tutor-hidden':  !watch('overview_chart')}"
>
	<div class="tutor-dashboard-home-chart-header">
		<div class="tutor-small">
			<?php esc_html_e( 'Earnings Over Time', 'tutor' ); ?>
		</div>
		<div class="tutor-flex tutor-align-center tutor-gap-6">
			<div class="tutor-dashboard-home-chart-legend" data-color="brand">
				<?php esc_html_e( 'Earnings', 'tutor' ); ?>
			</div>
			<div class="tutor-dashboard-home-chart-legend" data-color="success">
				<?php esc_html_e( 'Enrolled', 'tutor' ); ?>
			</div>
		</div>
	</div>
	<canvas class="tutor-dashboard-home-chart-canvas" x-data='tutorOverviewChart(<?php echo wp_json_encode( $overview_chart_data ); ?>)' x-ref="canvas"></canvas>
</div>
