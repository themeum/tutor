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

$top_performing_courses = array(
	array(
		'name'     => 'Complete Web Development Bootcamp',
		'url'      => '#',
		'revenue'  => 5000,
		'students' => 50,
	),
	array(
		'name'     => 'Complete Web Development Bootcamp',
		'url'      => '#',
		'revenue'  => 5000,
		'students' => 50,
	),
	array(
		'name'     => 'Complete Web Development Bootcamp',
		'url'      => '#',
		'revenue'  => 4000,
		'students' => 40,
	),
	array(
		'name'     => 'Complete Web Development Bootcamp',
		'url'      => '#',
		'revenue'  => 3000,
		'students' => 30,
	),
);

$upcoming_tasks = array(
	array(
		'name'      => 'Complete Web Development Bootcamp',
		'date'      => '2022-01-01 10:00 AM',
		'url'       => '#',
		'post_type' => 'tutor_assignments',
		'meta_info' => 'Web Dev 101',
	),
	array(
		'name'      => 'Live Q&A: React Hooks',
		'date'      => '2022-01-02 10:00 AM',
		'url'       => '#',
		'post_type' => 'tutor-google-meet',
		'meta_info' => '67 registered',
	),
	array(
		'name'      => 'Quiz Closes: Python Functions',
		'date'      => '2022-01-03 10:00 AM',
		'url'       => '#',
		'post_type' => 'tutor_quiz',
		'meta_info' => 'Python Basics',
	),
	array(
		'name'      => 'Live Q&A: Python Functions',
		'date'      => '2022-01-04 10:00 AM',
		'url'       => '#',
		'post_type' => 'tutor_zoom_meeting',
		'meta_info' => '67 registered',
	),
	array(
		'name'      => 'Lesson Closes: Python Functions',
		'date'      => '2022-01-05 10:00 AM',
		'url'       => '#',
		'post_type' => 'lesson',
		'meta_info' => 'Python Basics',
	),
);

$recent_activity = array(
	array(
		'course_name' => 'Complete Web Development Bootcamp',
		'course_url'  => '#',
		'date'        => '2022-01-01 10:00 AM',
		'meta'        => 'enrolled in',
		'user'        => array(
			'name'   => 'John Doe',
			'avatar' => 'https://i.pravatar.cc/300?u=a04258a2462d826712d',
		),
	),
	array(
		'course_name' => 'Complete Web Development Bootcamp',
		'course_url'  => '#',
		'date'        => '2022-01-01 10:00 AM',
		'meta'        => 'enrolled in',
		'user'        => array(
			'name'   => 'John Doe',
			'avatar' => 'https://i.pravatar.cc/300?u=a04258a2462d826712d',
		),
	),
	array(
		'course_name' => 'Complete Web Development Bootcamp',
		'course_url'  => '#',
		'date'        => '2022-01-01 10:00 AM',
		'meta'        => 'enrolled in',
		'user'        => array(
			'name'   => 'John Doe',
			'avatar' => 'https://i.pravatar.cc/300?u=a04258a2462d826712d',
		),
	),
	array(
		'course_name' => 'Complete Web Development Bootcamp',
		'course_url'  => '#',
		'date'        => '2022-01-01 10:00 AM',
		'meta'        => 'enrolled in',
		'user'        => array(
			'name'   => 'John Doe',
			'avatar' => 'https://i.pravatar.cc/300?u=a04258a2462d826712d',
		),
	),
	array(
		'course_name' => 'Complete Web Development Bootcamp',
		'course_url'  => '#',
		'date'        => '2022-01-01 10:00 AM',
		'meta'        => 'enrolled in',
		'user'        => array(
			'name'   => 'John Doe',
			'avatar' => 'https://i.pravatar.cc/300?u=a04258a2462d826712d',
		),
	),
	array(
		'course_name' => 'Complete Web Development Bootcamp',
		'course_url'  => '#',
		'date'        => '2022-01-01 10:00 AM',
		'meta'        => 'enrolled in',
		'user'        => array(
			'name'   => 'John Doe',
			'avatar' => 'https://i.pravatar.cc/300?u=a04258a2462d826712d',
		),
	),
	array(
		'course_name' => 'Complete Web Development Bootcamp',
		'course_url'  => '#',
		'date'        => '2022-01-01 10:00 AM',
		'meta'        => 'enrolled in',
		'user'        => array(
			'name'   => 'John Doe',
			'avatar' => 'https://i.pravatar.cc/300?u=a04258a2462d826712d',
		),
	),
	array(
		'course_name' => 'Complete Web Development Bootcamp',
		'course_url'  => '#',
		'date'        => '2022-01-01 10:00 AM',
		'meta'        => 'enrolled in',
		'user'        => array(
			'name'   => 'John Doe',
			'avatar' => 'https://i.pravatar.cc/300?u=a04258a2462d826712d',
		),
	),
	array(
		'course_name' => 'Complete Web Development Bootcamp',
		'course_url'  => '#',
		'date'        => '2022-01-01 10:00 AM',
		'meta'        => 'enrolled in',
		'user'        => array(
			'name'   => 'John Doe',
			'avatar' => 'https://i.pravatar.cc/300?u=a04258a2462d826712d',
		),
	),
);

$get_icon_by_post_type = function ( $post_type ) {
	switch ( $post_type ) {
		case 'tutor_assignments':
			return Icon::ASSIGNMENT;
		case 'tutor-google-meet':
			return Icon::GOOGLE_MEET;
		case 'tutor_quiz':
			return Icon::QUIZ;
		case 'tutor_zoom_meeting':
			return Icon::ZOOM;
		case 'lesson':
			return Icon::LESSON;
	}
};

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
							<div class="tutor-text-primary tutor-font-medium">
								<?php echo esc_html( $value['value'] ); ?>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- Leaderboard -->
		<div class="tutor-dashboard-home-card tutor-flex-1">
			<div class="tutor-small">
				<?php esc_html_e( 'Leaderboard', 'tutor' ); ?>
			</div>

			<div class="tutor-dashboard-home-card-body">
				<?php foreach ( $leaderboard_data as $item_key => $item ) : ?>
					<div class="tutor-dashboard-home-card-item">
						<div class="tutor-flex tutor-align-center tutor-gap-4">
							<!-- Index -->
							<div class="tutor-dashboard-home-card-icon">
								<?php if ( 0 === $item_key ) : ?>
									<?php tutor_utils()->render_svg_icon( Icon::BADGE, 16, 16, array( 'class' => 'tutor-icon-exception4' ) ); ?>
								<?php else : ?>
									<?php echo esc_html( $item_key + 1 ); ?>
								<?php endif; ?>
							</div>

							<!-- Avatar -->
							<div class="tutor-dashboard-home-card-avatar">
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

	<!-- Top Performing Courses -->
	<div class="tutor-dashboard-home-card">
		<div class="tutor-small">
			<?php esc_html_e( 'Top Performing Courses', 'tutor' ); ?>
		</div>

		<div class="tutor-dashboard-home-card-body tutor-gap-4">
			<?php foreach ( $top_performing_courses as $item_key => $item ) : ?>
				<div class="tutor-dashboard-home-course">
					<div class="tutor-flex tutor-items-center tutor-gap-4">
						<div class="tutor-dashboard-home-course-index">
							#<?php echo esc_html( $item_key + 1 ); ?>
						</div>
						<div class="tutor-p3">
							<?php echo esc_html( $item['name'] ); ?>
						</div>
					</div>

					<div class="tutor-flex tutor-items-center tutor-gap-7">
						<!-- Revenue -->
						<div class="tutor-flex tutor-flex-column tutor-items-center">
							<div class="tutor-flex tutor-items-center tutor-gap-2">
								<!-- @TODO: Add revenue icon -->
								<?php tutor_utils()->render_svg_icon( Icon::EARNING ); ?>
								<div class="tutor-tiny tutor-text-subdued">
									<?php esc_html_e( 'Revenue', 'tutor' ); ?>
								</div>
							</div>

							<div class="tutor-tiny tutor-font-semibold">
								<?php echo esc_html( $item['revenue'] ); ?>
							</div>
						</div>

						<!-- Students -->
						<div class="tutor-flex tutor-flex-column tutor-items-center">
							<div class="tutor-flex tutor-items-center tutor-gap-2">
								<!-- @TODO: Add students icon -->
								<?php tutor_utils()->render_svg_icon( Icon::PASSED ); ?>
								<div class="tutor-tiny tutor-text-subdued">
									<?php esc_html_e( 'Students', 'tutor' ); ?>
								</div>
							</div>
							
							<div class="tutor-tiny tutor-font-semibold">
								<?php echo esc_html( $item['students'] ); ?>
							</div>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="tutor-flex tutor-gap-6">
		<!-- Upcoming Tasks -->
		<div class="tutor-dashboard-home-card tutor-flex-1">
			<div class="tutor-small">
				<?php esc_html_e( 'Upcoming Tasks', 'tutor' ); ?>
			</div>

			<div class="tutor-dashboard-home-card-body tutor-gap-4">
				<?php foreach ( $upcoming_tasks as $item ) : ?>
					<div class="tutor-dashboard-home-task">
						<div class="tutor-dashboard-home-task-icon">
							<?php tutor_utils()->render_svg_icon( $get_icon_by_post_type( $item['post_type'] ) ); ?>
						</div>
						<div class="tutor-flex tutor-flex-column tutor-mt-1">
							<div class="tutor-flex tutor-items-center tutor-gap-2 tutor-tiny tutor-text-secondary">
								<span class="tutor-text-secondary">
									<?php if ( gmdate( 'Y-m-d' ) === $item['date'] ) : ?>
										<?php esc_html_e( 'Today', 'tutor' ); ?>
									<?php else : ?>
										<?php echo esc_html( date_i18n( get_option( 'date_format' ), $item['date'] ) ); ?>
									<?php endif; ?>
								</span>
								<span class="tutor-icon-secondary">•</span>
								<span class="tutor-text-secondary">
									<?php echo esc_html( date_i18n( get_option( 'time_format' ), $item['date'] ) ); ?>
								</span>
							</div>
							<div class="tutor-small tutor-font-medium">
								<?php echo esc_html( $item['name'] ); ?>
							</div>
							<div class="tutor-dashboard-home-task-meta">
								<?php echo esc_html( $item['meta_info'] ); ?>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- Recent Activity -->
		<div class="tutor-dashboard-home-card tutor-flex-1">
			<div class="tutor-small">
				<?php esc_html_e( 'Recent Activity', 'tutor' ); ?>
			</div>

			<div class="tutor-dashboard-home-card-body">
				<?php foreach ( $recent_activity as $item ) : ?>
					<div class="tutor-dashboard-home-card-item tutor-dashboard-home-activity">
						<div class="tutor-flex tutor-items-center tutor-gap-4 tutor-w-full">
							<!-- Avatar -->
							<div class="tutor-dashboard-home-card-avatar">
								<?php Avatar::make()->src( $item['user']['avatar'] )->initials( $item['user']['name'] )->size( Size::SIZE_40 )->render(); ?>
							</div>
							<div class="tutor-flex-1 tutor-flex tutor-flex-column">
								<div class="tutor-flex tutor-justify-between tutor-items-center tutor-tiny">
									<div>
										<?php echo esc_html( $item['user']['name'] ); ?>
										<span class="tutor-text-subdued">
											<?php echo esc_html( $item['meta'] ); ?>
										</span>
									</div>
									<div class="tutor-tiny-2 tutor-text-subdued">
										<?php echo esc_html( human_time_diff( strtotime( $item['date'] ) ) ); ?>
									</div>
								</div>
								<a class="tutor-tiny tutor-font-medium" href="<?php echo esc_url( $item['course_url'] ); ?>">
									<?php echo esc_html( $item['course_name'] ); ?>
								</a>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</div>