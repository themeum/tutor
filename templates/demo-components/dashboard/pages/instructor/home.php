<?php
/**
 * Dashboard page: Home for Instructor.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

// Define stat card variations with sample data matching the design.
$stat_cards = array(
	array(
		'variation' => 'success',
		'title'     => esc_html__( 'Total Earnings', 'tutor' ),
		'icon'      => Icon::EARNING,
		'value'     => '$740.00',
		'change'    => '+2',
		'data'      => array( 0, 5, 8 ),
	),
	array(
		'variation' => 'brand',
		'title'     => esc_html__( 'Total Courses', 'tutor' ),
		'icon'      => Icon::COURSES,
		'value'     => '12',
		'change'    => '+2',
		'data'      => array( 0, 8, 5 ),
	),
	array(
		'variation' => 'exception5',
		'title'     => esc_html__( 'Total Students', 'tutor' ),
		'icon'      => Icon::PASSED,
		'value'     => '3000',
		'change'    => '+2',
		'data'      => array( 0, 8, 5 ),
	),
	array(
		'variation' => 'exception4',
		'title'     => esc_html__( 'Avg. Rating', 'tutor' ),
		'icon'      => Icon::STAR,
		'value'     => '4.2',
		'change'    => '+2',
		'data'      => array( 4.5, 4.2, 3 ),
	),
);

$overview_chart_data = array(
	'earnings' => array( 30, 35, 45.2, 42.8, 41.5, 46.3, 52.1, 48.2, 45.8, 44.2, 46.5, 49.1, 52.8, 51.3 ),
	'enrolled' => array( 40, 60, 50.1, 48.5, 43.2, 48.9, 52.3, 49.7, 47.2, 48.8, 47.5, 49.2, 51.8, 53.2 ),
	'labels'   => array( '', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', '' ),
);

?>

<div class="tutor-flex tutor-flex-column tutor-gap-6 tutor-mt-7">
	<!-- Filters -->
	<div class="tutor-flex tutor-justify-between tutor-align-center">
		<button class="tutor-btn tutor-btn-outline tutor-btn-small tutor-flex-center tutor-gap-2">
			<?php tutor_utils()->render_svg_icon( Icon::CALENDAR ); ?>
			<?php esc_html_e( 'All Time', 'tutor' ); ?>
		</button>

		<button class="tutor-btn tutor-btn-outline tutor-btn-small tutor-btn-icon">
			<?php tutor_utils()->render_svg_icon( Icon::FILTER_2 ); ?>
		</button>
	</div>

	<!-- Stat cards -->
	<div class="tutor-flex tutor-gap-4">
		<?php foreach ( $stat_cards as $card ) : ?>
			<div class="tutor-flex-1">
			<?php
			tutor_load_template(
				'demo-components.dashboard.components.stat-card',
				array(
					'variation'  => isset( $card['variation'] ) ? $card['variation'] : 'enrolled',
					'card_title' => isset( $card['title'] ) ? $card['title'] : '',
					'icon'       => isset( $card['icon'] ) ? $card['icon'] : '',
					'value'      => isset( $card['value'] ) ? $card['value'] : '',
					'change'     => isset( $card['change'] ) ? $card['change'] : '',
					'data'       => isset( $card['data'] ) ? $card['data'] : array( 0, 0, 0 ),
					'show_graph' => true,
				)
			);
			?>
			</div>
		<?php endforeach; ?>
	</div>

	<!-- Overview Chart -->
	<div class="tutor-dashboard-home-chart">
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
		<canvas x-data='tutorOverviewChart(<?php echo wp_json_encode( $overview_chart_data ); ?>)' x-ref="canvas"></canvas>
	</div>
</div>