<?php
/**
 * Dashboard page: Home for Instructor.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;
use TUTOR\Input;
use TUTOR\Instructor;
use TUTOR_REPORT\Analytics;
use Tutor\Models\CourseModel;
use Tutor\Helpers\QueryHelper;
use Tutor\Components\DateFilter;
use Tutor\Components\InputField;
use Tutor\Components\Constants\InputType;


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
		'is_active' => true,
		'order'     => 2,
	),
	array(
		'id'        => 'top_performing_courses',
		'label'     => esc_html__( 'Top Performing Courses', 'tutor' ),
		'is_active' => true,
		'order'     => 3,
	),
	array(
		'id'        => 'upcoming_tasks_and_activity',
		'label'     => esc_html__( 'Upcoming Tasks and Recent Activity', 'tutor' ),
		'is_active' => true,
		'order'     => 4,
	),
	array(
		'id'        => 'recent_reviews',
		'label'     => esc_html__( 'Recent Student Reviews', 'tutor' ),
		'is_active' => true,
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

$upcoming_tasks          = array();
$get_upcoming_live_tasks = array();

$user                  = wp_get_current_user();
$instructor_course_ids = CourseModel::get_courses_by_args(
	array(
		'post_author'    => $user->ID,
		'posts_per_page' => -1,
		'fields'         => 'ids',
	)
)->posts;


$start_date     = Input::has( 'start_date' ) ? tutor_get_formated_date( 'Y-m-d', Input::get( 'start_date' ) ) : '';
$end_date       = Input::has( 'end_date' ) ? tutor_get_formated_date( 'Y-m-d', Input::get( 'end_date' ) ) : '';
$previous_dates = Instructor::get_comparison_date_range( $start_date, $end_date );

// Total Earnings.
$total_earnings           = Analytics::get_earnings_by_user( $user->ID, '', $start_date, $end_date );
$previous_period_earnings = Analytics::get_earnings_by_user( $user->ID, '', $previous_dates['previous_start_date'], $previous_dates['previous_end_date'] )['total_earnings'] ?? 0;

// Total Courses.
$total_courses           = CourseModel::get_course_count_by_date( $start_date, $end_date, $user->ID );
$previous_period_courses = CourseModel::get_course_count_by_date( $previous_dates['previous_start_date'], $previous_dates['previous_end_date'], $user->ID );

// Total Students.
$total_students           = Instructor::get_instructor_total_students_by_date_range( $start_date, $end_date, $user->ID );
$previous_period_students = Instructor::get_instructor_total_students_by_date_range( $previous_dates['previous_start_date'], $previous_dates['previous_end_date'], $user->ID );

// Total Ratings.
$where                   = empty( $start_date ) && empty( $end_date ) ? array() : array( 'reviews.comment_date' => array( 'BETWEEN', array( $start_date, $end_date ) ) );
$total_ratings           = tutor_utils()->get_instructor_ratings( $user->ID, $where );
$previous_period_ratings = tutor_utils()->get_instructor_ratings( $user->ID, array( 'reviews.comment_date' => array( 'BETWEEN', array( $previous_dates['previous_start_date'], $previous_dates['previous_end_date'] ) ) ) );

$stat_cards = array(
	array(
		'variation' => 'success',
		'title'     => esc_html__( 'Total Earnings', 'tutor' ),
		'icon'      => Icon::EARNING,
		'value'     => wp_kses_post( tutor_utils()->tutor_price( $total_earnings['total_earnings'] ?? 0 ) ),
		'change'    => Instructor::get_stat_card_comparison_subtitle( $start_date, $end_date, $total_earnings['total_earnings'] ?? 0, $previous_period_earnings ),
		// 'data'      => array( 0, 1, 2, 3, 4, 5, 6, 7, 8, 9 ), @todo will be added later.
	),
	array(
		'variation' => 'brand',
		'title'     => esc_html__( 'Total Courses', 'tutor' ),
		'icon'      => Icon::COURSES,
		'value'     => $total_courses,
		'change'    => Instructor::get_stat_card_comparison_subtitle( $start_date, $end_date, $total_courses, $previous_period_courses, false ),
		// 'data'      => array( 0, 8, 5, 2, 3, 4, 5, 6, 7, 8, 9 ),  @todo will be added later.
	),
	array(
		'variation' => 'exception5',
		'title'     => esc_html__( 'Total Students', 'tutor' ),
		'icon'      => Icon::PASSED,
		'value'     => $total_students,
		'change'    => Instructor::get_stat_card_comparison_subtitle( $start_date, $end_date, $total_students, $previous_period_students, false ),
		// 'data'      => array( 0, 8, 5, 2, 3, 4, 5, 6, 7, 8, 9 ),
	),
	array(
		'variation' => 'exception4',
		'title'     => esc_html__( 'Avg. Rating', 'tutor' ),
		'icon'      => Icon::STAR_LINE,
		'value'     => $total_ratings->rating_avg,
		'change'    => Instructor::get_stat_card_comparison_subtitle( $start_date, $end_date, $total_ratings->rating_avg, $previous_period_ratings->rating_avg, false ),
		// 'data'      => array( 4.5, 4.2, 3, 3, 2.8, 2, 4.5, 4.2, 3, 2, 1, 0 ),
	),
);

// Graph.
$labels              = wp_list_pluck( $total_earnings['earnings'], 'label_name' );
$graph_earnings      = array_map( 'intval', wp_list_pluck( $total_earnings['earnings'], 'total' ) );
$enrollments         = Analytics::get_total_students_by_user( $user->ID, '', $start_date, $end_date );
$graph_enrollments   = array_map( 'intval', wp_list_pluck( $enrollments['enrollments'], 'total' ) );
$overview_chart_data = array(
	'earnings' => array_merge( array( 0 ), $graph_earnings, array( 0 ) ),
	'enrolled' => array_merge( array( 0 ), $graph_enrollments, array( 0 ) ),
	'labels'   => array_merge( array( '' ), $labels, array( '' ) ),
);

// Course Completion Distribution.
$course_completion_distribution = Instructor::get_course_completion_distribution_data_by_instructor( $instructor_course_ids );

$course_completion_data = array(
	'enrolled'    => array(
		'label' => esc_html__( 'Enrolled', 'tutor' ),
		'value' => $course_completion_distribution['enrolled'],
	),
	'completed'   => array(
		'label' => esc_html__( 'Completed', 'tutor' ),
		'value' => $course_completion_distribution['completed'],
	),
	'in_progress' => array(
		'label' => esc_html__( 'In Progress', 'tutor' ),
		'value' => $course_completion_distribution['inprogress'],
	),
	'inactive'    => array(
		'label' => esc_html__( 'Inactive', 'tutor' ),
		'value' => $course_completion_distribution['inactive'],
	),
	'cancelled'   => array(
		'label' => esc_html__( 'Cancelled', 'tutor' ),
		'value' => $course_completion_distribution['cancelled'],
	),
);

// @todo Will be added on later.
// $leaderboard_data = array(
// 	array(
// 		'name'                  => esc_html__( 'John Doe', 'tutor' ),
// 		'avatar'                => 'https://i.pravatar.cc/300?u=a04258a2462d826712d',
// 		'no_of_courses'         => 10,
// 		'completion_percentage' => 50,
// 	),
// 	array(
// 		'name'                  => esc_html__( 'Jane Doe', 'tutor' ),
// 		'avatar'                => 'https://i.pravatar.cc/300?u=a042581f4e29026704d',
// 		'no_of_courses'         => 20,
// 		'completion_percentage' => 30,
// 	),
// 	array(
// 		'name'                  => esc_html__( 'Bob Doe', 'tutor' ),
// 		'avatar'                => 'https://i.pravatar.cc/300?u=a04258a2462d826732d',
// 		'no_of_courses'         => 30,
// 		'completion_percentage' => 70,
// 	),
// );

// Top Performing Courses.
$args                   = array(
	'start_date' => $start_date,
	'end_date'   => $end_date,
	'order_by'   => Input::get( 'type', 'revenue' ),
);
$top_courses            = Instructor::get_top_performing_courses_by_instructor( $user->ID, $args );
$top_performing_courses = array_map(
	function ( $course ) {
		return array(
			'name'     => $course->course_title,
			'url'      => get_permalink( $course->course_id ),
			'revenue'  => wp_kses_post( tutor_utils()->tutor_price( $course->total_revenue ?? 0 ) ),
			'students' => $course->total_student ?? 0,
		);
	},
	$top_courses
);

if ( empty( $start_date ) && empty( $end_date ) ) {
	$get_upcoming_live_tasks = get_posts(
		array(
			'post_type'   => array( tutor()->zoom_post_type, tutor()->meet_post_type ),
			'post_status' => 'publish',
			'post_author' => $user->ID,
			'numberposts' => 5,
			'meta_query'  => array(
				array(
					'key'     => array( '_tutor_zm_start_datetime', 'tutor-google-meet-start-datetime' ),
					'value'   => gmdate( 'Y-m-d H:i:s', strtotime( 'now' ) ),
					'compare' => '>=',
					'type'    => 'DATETIME',
				),
			),
		)
	);

	if ( ! empty( $get_upcoming_live_tasks ) ) {
		$upcoming_tasks = array_map(
			function ( $task ) {

				switch ( $task->post_type ) {
					case tutor()->zoom_post_type:
						$meta_key = '_tutor_zm_start_datetime';
						$url      = json_decode( get_post_meta( $task->ID, '_tutor_zm_data' )[0] )->join_url ?? '';
						break;

					case tutor()->meet_post_type:
						$meta_key = 'tutor-google-meet-start-datetime';
						$url      = get_post_meta( $task->ID, 'tutor-google-meet-link', true );
						break;

					default:
						$meta_key = null;
						$url      = '';
						break;
				}

				$start_date = get_post_meta( $task->ID, $meta_key, true );

				return array(
					'name'      => $task->post_title,
					'date'      => wp_date( 'Y-m-d h:i A', strtotime( $start_date ) ),
					'url'       => $url,
					'post_type' => $task->post_type,
				);
			},
			$get_upcoming_live_tasks
		);
	}
}

// @todo will be added later.
// $recent_activity = array(
// array(
// 'course_name' => 'Complete Web Development Bootcamp',
// 'course_url'  => '#',
// 'date'        => '2022-01-01 10:00 AM',
// 'meta'        => 'enrolled in',
// 'user'        => array(
// 'name'   => 'John Doe',
// 'avatar' => 'https://i.pravatar.cc/300?u=a04258a2462d826712d',
// ),
// ),
// array(
// 'course_name' => 'Complete Web Development Bootcamp',
// 'course_url'  => '#',
// 'date'        => '2022-01-01 10:00 AM',
// 'meta'        => 'enrolled in',
// 'user'        => array(
// 'name'   => 'John Doe',
// 'avatar' => 'https://i.pravatar.cc/300?u=a04258a2462d826712d',
// ),
// ),
// array(
// 'course_name' => 'Complete Web Development Bootcamp',
// 'course_url'  => '#',
// 'date'        => '2022-01-01 10:00 AM',
// 'meta'        => 'enrolled in',
// 'user'        => array(
// 'name'   => 'John Doe',
// 'avatar' => 'https://i.pravatar.cc/300?u=a04258a2462d826712d',
// ),
// ),
// );

// Recent Reviews.
$review_where = array( 'comment_post_ID' => array( 'IN', $instructor_course_ids ) );
if ( ! empty( $start_date ) && ! empty( $end_date ) ) {
	$review_where['comment_date'] = array( 'BETWEEN', array( $start_date, $end_date ) );
}
$review_args    = array( 'where' => QueryHelper::prepare_where_clause( $review_where ) );
$reviews        = Analytics::get_reviews( 3, $review_args );
$recent_reviews = array_map(
	function ( $review ) {
		return array(
			'user'        => array(
				'name'   => $review->display_name,
				'avatar' => get_avatar_url( $review->user_id ),
			),
			'course_name' => get_the_title( $review->comment_post_ID ),
			'date'        => $review->comment_date,
			'rating'      => $review->rating,
			'review_text' => $review->comment_content,
		);
	},
	$reviews
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
			
		<?php DateFilter::make()->type( DateFilter::TYPE_RANGE )->placement( 'bottom-start' )->render(); ?>

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
				'dashboard.instructor.analytics.stat-card',
				array(
					'variation'  => isset( $card['variation'] ) ? $card['variation'] : 'enrolled',
					'card_title' => isset( $card['title'] ) ? $card['title'] : '',
					'icon'       => isset( $card['icon'] ) ? $card['icon'] : '',
					'value'      => isset( $card['value'] ) ? $card['value'] : '',
					'change'     => isset( $card['change'] ) ? $card['change'] : '',
					'data'       => isset( $card['data'] ) ? $card['data'] : array( 0, 0, 0 ),
					'show_graph' => false,
				)
			);
			?>
			</div>
		<?php endforeach; ?>
	</div>

	<!-- Overview Chart -->
	<?php
	tutor_load_template(
		'dashboard.instructor.home.overview-chart',
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
			'dashboard.instructor.home.course-completion-chart',
			array(
				'course_completion_data' => $course_completion_data,
			)
		);
		?>

		<!-- @todo Will be added later. -->
		<!-- Leaderboard -->
		<!-- <div class="tutor-dashboard-home-card tutor-flex-1">
			<div class="tutor-small">
				<?php //esc_html_e( 'Leaderboard', 'tutor' ); ?>
			</div>

			<div class="tutor-dashboard-home-card-body">
				<?php // foreach ( $leaderboard_data as $item_key => $item ) : ?>
					<?php
					// tutor_load_template(
					// 'demo-components.dashboard.components.instructor.home.leaderboard-item',
					// array(
					// 'item_key' => $item_key,
					// 'item'     => $item,
					// )
					// );
					?>
				<?php // endforeach; ?>
			</div>
		</div> -->
	</div>

	<!-- Top Performing Courses -->
	<?php if ( ! empty( $top_performing_courses ) ) : ?>
		<div 
			data-section-id="top_performing_courses"
			class="tutor-dashboard-home-card"
			:class="{ 'tutor-hidden':  !watch('top_performing_courses')}"
		> 
			<div class="tutor-flex tutor-row tutor-justify-between tutor-align-center tutor-gap-9">
				<div class="tutor-small">
					<?php esc_html_e( 'Top Performing Courses', 'tutor' ); ?>
				</div>

				<!-- Sorting -->
				<?php
				$data = array(
					'options'  => array(
						'revenue' => __( 'Revenue', 'tutor' ),
						'student' => __( 'Student', 'tutor' ),
					),
					'selected' => Input::get( 'type', 'revenue' ),
				);
				tutor_load_template(
					'dashboard.instructor.home.top-performing-course-filter',
					$data,
				);
				?>
			</div>

			<div class="tutor-dashboard-home-card-body tutor-gap-4">
				<?php foreach ( $top_performing_courses as $item_key => $item ) : ?>
					<?php
					tutor_load_template(
						'dashboard.instructor.home.top-performing-course-item',
						array(
							'item_key' => $item_key,
							'item'     => $item,
						),
					)
					?>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<!-- Upcoming Task And Activity -->
	<?php if ( ! empty( $upcoming_tasks ) ) : ?>
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
							'dashboard.instructor.home.upcoming-task-item',
							array( 'item' => $item )
						);
						?>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Recent Activity -->
			<!-- @todo Will be added later. -->
			<!-- <div class="tutor-dashboard-home-card tutor-flex-1">
			<div class="tutor-small">
				<?php // esc_html_e( 'Recent Activity', 'tutor' ); ?>
			</div>

			<div class="tutor-dashboard-home-card-body">
				<?php // foreach ( $recent_activity as $item ) : ?>
					<?php
					// tutor_load_template(
					// 'demo-components.dashboard.components.instructor.home.recent-activity-item',
					// array(
					// 'item' => $item,
					// )
					// );
					?>
				<?php // endforeach; ?>
			</div> -->
		</div>
	<?php endif; ?>

	<!-- Recent Student Reviews -->
	<?php if ( ! empty( $recent_reviews ) ) : ?>
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
					'dashboard.instructor.home.recent-student-review-item',
					array( 'review' => $review ),
				);
				?>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>
</form>
