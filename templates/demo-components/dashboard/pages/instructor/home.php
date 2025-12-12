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
use Tutor\Components\Avatar;
use Tutor\Components\Constants\Size;

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

$course_completion_data = array(
	'enrolled'    => array(
		'label' => esc_html__( 'Enrolled', 'tutor' ),
		'value' => 22000,
	),
	'completed'   => array(
		'label' => esc_html__( 'Completed', 'tutor' ),
		'value' => 12000,
	),
	'in_progress' => array(
		'label' => esc_html__( 'In Progress', 'tutor' ),
		'value' => 5000,
	),
	'inactive'    => array(
		'label' => esc_html__( 'Inactive', 'tutor' ),
		'value' => 4000,
	),
	'cancelled'   => array(
		'label' => esc_html__( 'Cancelled', 'tutor' ),
		'value' => 2000,
	),
);

$leaderboard_data = array(
	array(
		'name'                  => esc_html__( 'John Doe', 'tutor' ),
		'avatar'                => 'https://i.pravatar.cc/300?u=a04258a2462d826712d',
		'no_of_courses'         => 10,
		'completion_percentage' => 50,
	),
	array(
		'name'                  => esc_html__( 'Jane Doe', 'tutor' ),
		'avatar'                => 'https://i.pravatar.cc/300?u=a042581f4e29026704d',
		'no_of_courses'         => 20,
		'completion_percentage' => 30,
	),
	array(
		'name'                  => esc_html__( 'Bob Doe', 'tutor' ),
		'avatar'                => 'https://i.pravatar.cc/300?u=a04258a2462d826732d',
		'no_of_courses'         => 30,
		'completion_percentage' => 70,
	),
	array(
		'name'                  => esc_html__( 'Alice Doe', 'tutor' ),
		'avatar'                => 'https://i.pravatar.cc/300?u=a04258a2462d826752d',
		'no_of_courses'         => 40,
		'completion_percentage' => 10,
	),
	array(
		'name'                  => esc_html__( 'Charlie Doe', 'tutor' ),
		'avatar'                => 'https://i.pravatar.cc/300?u=a04258a2462d823712d',
		'no_of_courses'         => 50,
		'completion_percentage' => 40,
	),
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
		<canvas class="tutor-dashboard-home-chart-canvas" x-data='tutorOverviewChart(<?php echo wp_json_encode( $overview_chart_data ); ?>)' x-ref="canvas"></canvas>
	</div>

	<div class="tutor-flex tutor-gap-6">
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
							<div class="tutor-tiny tutor-font-medium">
								<?php echo esc_html( $value['value'] ); ?>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- Leaderboard -->
		<div class="tutor-dashboard-home-leaderboard tutor-flex-1">
			<div class="tutor-small">
				<?php esc_html_e( 'Leaderboard', 'tutor' ); ?>
			</div>

			<div class="tutor-dashboard-home-leaderboard-body">
				<?php foreach ( $leaderboard_data as $item_key => $item ) : ?>
					<div class="tutor-dashboard-home-leaderboard-item">
						<div class="tutor-flex tutor-align-center tutor-gap-4">
							<!-- Index -->
							<div class="tutor-dashboard-home-leaderboard-icon">
								<?php if ( 0 === $item_key ) : ?>
									<?php tutor_utils()->render_svg_icon( Icon::BADGE, 16, 16, array( 'class' => 'tutor-icon-exception4' ) ); ?>
								<?php else : ?>
									<?php echo esc_html( $item_key + 1 ); ?>
								<?php endif; ?>
							</div>

							<!-- Avatar -->
							<div class="tutor-dashboard-home-leaderboard-avatar">
								<?php Avatar::make()->src( $item['avatar'] )->initials( $item['name'] )->size( Size::SIZE_32 )->render(); ?>
							</div>

							<!-- Info -->
							<div class="tutor-flex-1 tutor-flex tutor-flex-column">
								<div class="tutor-tiny tutor-font-medium">
									<?php echo esc_html( $item['name'] ); ?>
								</div>
								<div class="tutor-tiny">
									<span class="tutor-text-subdued">
										<?php
										printf(
											// translators: %s: Number of courses.
											esc_html__( '%s courses', 'tutor' ),
											esc_html( $item['no_of_courses'] )
										);
										?>
									</span>
									<span class="tutor-text-subdued tutor-mx-2">•</span>
									<span class="tutor-text-success">
										<?php
										printf(
											// translators: %s: Completion percentage.
											esc_html__( '%s completion', 'tutor' ),
											esc_html( $item['completion_percentage'] . '%' )
										);
										?>
									</span>
								</div>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</div>