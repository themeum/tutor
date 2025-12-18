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
use Tutor\Components\Constants\InputType;
use Tutor\Components\InputField;

$sortable_sections = array(
	array(
		'id'        => 'current_stats',
		'label'     => esc_html__( 'Current Stats', 'tutor' ),
		'is_active' => true,
		'order'     => 0,
	),
	array(
		'id'        => 'overview_chart',
		'label'     => esc_html__( 'Earning Over Time', 'tutor' ),
		'is_active' => true,
		'order'     => 1,
	),
	array(
		'id'        => 'course_completion_and_leader',
		'label'     => esc_html__( 'Course Completion and Leader', 'tutor' ),
		'is_active' => false,
		'order'     => 2,
	),
	array(
		'id'        => 'top_performing_courses',
		'label'     => esc_html__( 'Top Performing Courses', 'tutor' ),
		'is_active' => false,
		'order'     => 3,
	),
	array(
		'id'        => 'upcoming_tasks_and_activity',
		'label'     => esc_html__( 'Upcoming Tasks and Recent Activity', 'tutor' ),
		'is_active' => false,
		'order'     => 4,
	),
	array(
		'id'        => 'recent_reviews',
		'label'     => esc_html__( 'Recent Student Reviews', 'tutor' ),
		'is_active' => false,
		'order'     => 6,
	),
);

$sortable_sections_defaults = array_reduce(
	$sortable_sections,
	function ( $carry, $section ) {
		$carry[ $section['id'] ] = $section['is_active'] ?? false;
		return $carry;
	},
	array()
);

$sortable_sections_ids = array_reduce(
	$sortable_sections,
	function ( $carry, $section ) {
		$carry[] = $section['id'];
		return $carry;
	},
	array()
);

$stat_cards = array(
	array(
		'variation' => 'success',
		'title'     => esc_html__( 'Total Earnings', 'tutor' ),
		'icon'      => Icon::EARNING,
		'value'     => '$740.00',
		'change'    => '+2',
		'data'      => array( 0, 1, 2, 3, 4, 5, 6, 7, 8, 9 ),
	),
	array(
		'variation' => 'brand',
		'title'     => esc_html__( 'Total Courses', 'tutor' ),
		'icon'      => Icon::COURSES,
		'value'     => '12',
		'change'    => '+2',
		'data'      => array( 0, 8, 5, 2, 3, 4, 5, 6, 7, 8, 9 ),
	),
	array(
		'variation' => 'exception5',
		'title'     => esc_html__( 'Total Students', 'tutor' ),
		'icon'      => Icon::PASSED,
		'value'     => '3000',
		'change'    => '+2',
		'data'      => array( 0, 8, 5, 2, 3, 4, 5, 6, 7, 8, 9 ),
	),
	array(
		'variation' => 'exception4',
		'title'     => esc_html__( 'Avg. Rating', 'tutor' ),
		'icon'      => Icon::STAR,
		'value'     => '4.2',
		'change'    => '+2',
		'data'      => array( 4.5, 4.2, 3, 3, 2.8, 2, 4.5, 4.2, 3, 2, 1, 0 ),
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

$recent_reviews = array(
	array(
		'user'          => array(
			'name'   => 'Sarah Johnson',
			'avatar' => 'https://i.pravatar.cc/300?u=sarah',
		),
		'course_name'   => 'Complete Web Development Bootcamp',
		'date'          => '2022-01-01 08:00 AM',
		'rating'        => 5,
		'review_text'   => 'Outstanding course! The instructor explains complex concepts in a very clear and practical way. I landed my first dev job within 3 months of completing this course.',
		'helpful_count' => 12,
	),
	array(
		'user'          => array(
			'name'   => 'Sarah Johnson',
			'avatar' => 'https://i.pravatar.cc/300?u=sarah',
		),
		'course_name'   => 'Complete Web Development Bootcamp',
		'date'          => '2022-01-01 08:00 AM',
		'rating'        => 5,
		'review_text'   => 'Outstanding course! The instructor explains complex concepts in a very clear and practical way. I landed my first dev job within 3 months of completing this course.',
		'helpful_count' => 12,
	),
	array(
		'user'          => array(
			'name'   => 'Sarah Johnson',
			'avatar' => 'https://i.pravatar.cc/300?u=sarah',
		),
		'course_name'   => 'Complete Web Development Bootcamp',
		'date'          => '2022-01-01 08:00 AM',
		'rating'        => 5,
		'review_text'   => 'Outstanding course! The instructor explains complex concepts in a very clear and practical way. I landed my first dev job within 3 months of completing this course.',
		'helpful_count' => 12,
	),
);

?>

<form x-data='tutorForm({
		id: "sortable-sections",
		mode: "onBlur",
		defaultValues: <?php echo wp_json_encode( $sortable_sections_defaults ); ?>
	})' 
	x-bind="getFormBindings()"
	class="tutor-flex tutor-flex-column tutor-gap-6 tutor-mt-7"
>
	<!-- Filters -->
	<div class="tutor-flex tutor-justify-between tutor-align-center">
		<button class="tutor-btn tutor-btn-outline tutor-btn-small tutor-flex-center tutor-gap-2">
			<?php tutor_utils()->render_svg_icon( Icon::CALENDAR ); ?>
			<?php esc_html_e( 'All Time', 'tutor' ); ?>
		</button>

		<div class="tutor-dashboard-home-sort" x-data="tutorPopover({ placement: 'bottom-end' })">
			<button
				x-ref="trigger"
				@click="toggle()"
				class="tutor-btn tutor-btn-outline tutor-btn-small tutor-btn-icon"
			>
				<?php tutor_utils()->render_svg_icon( Icon::FILTER_2 ); ?>
			</button>

			<div
				x-ref="content"
				x-show="open"
				x-cloak
				@click.outside="handleClickOutside()"
				class="tutor-popover tutor-popover-bottom"
			>
				<div 
					class="tutor-popover-menu"
					style="width: 288px;"
					x-data='tutorSortableSections(
							<?php echo wp_json_encode( $sortable_sections_ids ); ?>
						)'
				>
					<?php foreach ( $sortable_sections as $section ) : ?>
						<div
							data-id="<?php echo esc_attr( $section['id'] ); ?>"
							class="tutor-popover-menu-item"
						>
							<button data-grab>
								<?php tutor_utils()->render_svg_icon( Icon::DRAG_VERTICAL, 16, 16 ); ?>
							</button>
							<?php
								InputField::make()
									->type( InputType::CHECKBOX )
									->name( "$section[id]" )
									->label( $section['label'] )
									->attr( 'x-bind', "register('$section[id]')" )
									->render();
							?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>

	<!-- Stat cards -->
	<div 
		data-section-id="current_stats" 
		class="tutor-flex tutor-gap-5"					
		:class="{ 'tutor-hidden':  !watch('current_stats')}"
	>
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
	<?php
	tutor_load_template(
		'demo-components.dashboard.components.instructor.home.overview-chart',
		array(
			'overview_chart_data' => $overview_chart_data,
		)
	);
	?>

	<div 
		data-section-id="course_completion_and_leader" 
		class="tutor-flex tutor-gap-6"
		:class="{ 'tutor-hidden':  !watch('course_completion_and_leader')}"
	>
		<!-- Course Completion Chart -->
		<?php
		tutor_load_template(
			'demo-components.dashboard.components.instructor.home.course-completion-chart',
			array(
				'course_completion_data' => $course_completion_data,
			)
		);
		?>

		<!-- Leaderboard -->
		<div class="tutor-dashboard-home-card tutor-flex-1">
			<div class="tutor-small">
				<?php esc_html_e( 'Leaderboard', 'tutor' ); ?>
			</div>

			<div class="tutor-dashboard-home-card-body">
				<?php foreach ( $leaderboard_data as $item_key => $item ) : ?>
					<?php
					tutor_load_template(
						'demo-components.dashboard.components.instructor.home.leaderboard-item',
						array(
							'item_key' => $item_key,
							'item'     => $item,
						)
					);
					?>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

	<!-- Top Performing Courses -->
	<div 
		data-section-id="top_performing_courses"
		class="tutor-dashboard-home-card"
		:class="{ 'tutor-hidden':  !watch('top_performing_courses')}"
	>
		<div class="tutor-small">
			<?php esc_html_e( 'Top Performing Courses', 'tutor' ); ?>
		</div>

		<div class="tutor-dashboard-home-card-body tutor-gap-4">
			<?php foreach ( $top_performing_courses as $item_key => $item ) : ?>
				<?php
				tutor_load_template(
					'demo-components.dashboard.components.instructor.home.top-performing-course-item',
					array(
						'item_key' => $item_key,
						'item'     => $item,
					)
				);
				?>
			<?php endforeach; ?>
		</div>
	</div>

	<div
		data-section-id="upcoming_tasks_and_activity"
		class="tutor-flex tutor-gap-6"
		:class="{ 'tutor-hidden':  !watch('upcoming_tasks_and_activity')}"
	>
		<!-- Upcoming Tasks -->
		<div class="tutor-dashboard-home-card tutor-flex-1">
			<div class="tutor-small">
				<?php esc_html_e( 'Upcoming Tasks', 'tutor' ); ?>
			</div>

			<div class="tutor-dashboard-home-card-body tutor-gap-4">
				<?php foreach ( $upcoming_tasks as $item ) : ?>
					<?php
					tutor_load_template(
						'demo-components.dashboard.components.instructor.home.upcoming-task-item',
						array(
							'item' => $item,
						)
					);
					?>
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
					<?php
					tutor_load_template(
						'demo-components.dashboard.components.instructor.home.recent-activity-item',
						array(
							'item' => $item,
						)
					);
					?>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

	<!-- Recent Student Reviews -->
	<div 
		data-section-id="recent_reviews" 
		class="tutor-dashboard-home-card"
		:class="{ 'tutor-hidden':  !watch('recent_reviews')}"
	>
		<div class="tutor-small">
			<?php esc_html_e( 'Recent Student Reviews', 'tutor' ); ?>
		</div>

		<div class="tutor-dashboard-home-card-body tutor-gap-6">
			<?php foreach ( $recent_reviews as $review ) : ?>
				<?php
				tutor_load_template(
					'demo-components.dashboard.components.instructor.home.recent-student-review-item',
					array(
						'review' => $review,
					)
				);
				?>
			<?php endforeach; ?>
		</div>
	</div>
</form>