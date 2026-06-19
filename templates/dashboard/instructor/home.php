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
use Tutor\Models\WithdrawModel;
use Tutor\Components\DateFilter;
use Tutor\Components\InputField;
use Tutor\Components\Constants\InputType;
use Tutor\Components\SvgIcon;

$upcoming_tasks          = array();
$get_upcoming_live_tasks = array();
$overview_chart_data     = array();
$recent_reviews          = array();
$course_completion_data  = array();
$sortable_sections       = array();

$user                  = wp_get_current_user();
$instructor_course_ids = CourseModel::get_courses_by_args(
	array(
		'post_author'    => $user->ID,
		'posts_per_page' => -1,
		'fields'         => 'ids',
	)
)->posts;

$tutor_pro_enabled = tutor_utils()->is_plugin_active( 'tutor-pro/tutor-pro.php' );
$is_pro_reports    = $tutor_pro_enabled && tutor_utils()->is_addon_enabled( 'tutor-report' );

$start_date     = Input::has( 'start_date' ) ? tutor_get_formated_date( 'Y-m-d', Input::get( 'start_date' ) ) : '';
$end_date       = Input::has( 'end_date' ) ? tutor_get_formated_date( 'Y-m-d', Input::get( 'end_date' ) ) : '';
$is_all_time    = empty( $start_date ) && empty( $end_date );
$previous_dates = $is_all_time ? array() : Instructor::get_comparison_date_range( $start_date, $end_date );

$date_range = fn( $from, $to ): array => array(
	'from' => $from,
	'to'   => $to,
);

$stat = function ( $current, $previous, $previous_dates ) {
	return array_merge( $previous_dates, Instructor::get_stat_card_details( (float) $current, (float) $previous ) );
};

// Total Earnings.
if ( $is_pro_reports ) {
	$earnings       = Analytics::get_earnings_by_user( $user->ID, '', $start_date, $end_date );
	$total_earnings = $earnings['total_earnings'] ?? 0;

	if ( ! $is_all_time ) {
		$previous_period_earnings = Analytics::get_earnings_by_user(
			$user->ID,
			'',
			$previous_dates['previous_start_date'],
			$previous_dates['previous_end_date']
		)['total_earnings'] ?? 0;
	}
} else {
	$total_earnings = WithdrawModel::get_withdraw_summary( $user->ID )->total_income ?? 0;

	if ( ! $is_all_time ) {
		$previous_period_earnings = WithdrawModel::get_withdraw_summary(
			$user->ID,
			$date_range( $previous_dates['previous_start_date'], $previous_dates['previous_end_date'] )
		)->total_income ?? 0;
	}
}

// Total Courses.
$total_courses           = CourseModel::get_course_count_by_date( $start_date, $end_date, $user->ID );
$previous_period_courses = ! $is_all_time
							? CourseModel::get_course_count_by_date( $previous_dates['previous_start_date'], $previous_dates['previous_end_date'], $user->ID )
							: 0;

// Total Students.
$total_students           = tutor_utils()->get_total_students_by_instructor( $user->ID, $date_range( $start_date, $end_date ) );
$previous_period_students = ! $is_all_time
							? tutor_utils()->get_total_students_by_instructor( $user->ID, $date_range( $previous_dates['previous_start_date'], $previous_dates['previous_end_date'] ) )
							: 0;


// Total Ratings.
$total_ratings_where     = ! $is_all_time ? $date_range( $start_date, $end_date ) : array();
$total_ratings           = tutor_utils()->get_instructor_ratings( $user->ID, $total_ratings_where );
$previous_period_ratings = ! $is_all_time
							? tutor_utils()->get_instructor_ratings( $user->ID, $date_range( $previous_dates['previous_start_date'], $previous_dates['previous_end_date'] ) )
							: (object) array( 'rating_avg' => 0 );

/**
 * -------------------------
 * Hover (comparison) data
 * Only for Pro Reports and “All Time” is not chosen.
 * -------------------------
 */
$total_earnings_state_card_details = array();
$total_courses_state_card_details  = array();
$total_students_state_card_details = array();
$total_ratings_state_card_details  = array();

if ( $tutor_pro_enabled && ! $is_all_time ) {
	$total_earnings_state_card_details = $stat( $total_earnings, $previous_period_earnings, $previous_dates );
	$total_courses_state_card_details  = $stat( $total_courses, $previous_period_courses, $previous_dates );
	$total_students_state_card_details = $stat( $total_students, $previous_period_students, $previous_dates );
	$total_ratings_state_card_details  = $stat( $total_ratings->rating_avg, $previous_period_ratings->rating_avg, $previous_dates );
}

/**
 * -------------------------
 * Stat cards
 * -------------------------
 */
$stat_cards = array(
	array(
		'variation'     => 'brand',
		'title'         => esc_html__( 'Total Earnings', 'tutor' ),
		'icon'          => Icon::EARNING,
		'value'         => tutor_utils()->tutor_price( $total_earnings ?? 0 ),
		'hover_content' => $total_earnings_state_card_details,
	),
	array(
		'variation'     => 'exception1',
		'title'         => esc_html__( 'Total Courses', 'tutor' ),
		'icon'          => Icon::COURSES,
		'value'         => $total_courses,
		'hover_content' => $total_courses_state_card_details,
	),
	array(
		'variation'     => 'exception5',
		'title'         => esc_html__( 'Total Students', 'tutor' ),
		'icon'          => Icon::PASSED,
		'value'         => $total_students,
		'hover_content' => $total_students_state_card_details,
	),
	array(
		'variation'     => 'exception4',
		'title'         => esc_html__( 'Avg. Rating', 'tutor' ),
		'icon'          => Icon::STAR_LINE,
		'value'         => $total_ratings->rating_avg,
		'hover_content' => $total_ratings_state_card_details,
	),
);


/**
 * -------------------------
 * Graph data (only for pro)
 * -------------------------
 */
if ( $is_pro_reports ) {
	$enrollments = Analytics::get_total_students_by_user( $user->ID, '', $start_date, $end_date );

	$overview_chart_data = array(
		'earnings'        => array( 0 ),
		'enrolled'        => array( 0 ),
		'labels'          => array( '' ),
		'currency'        => tutor_utils()->get_monetization_currency_config(),
		'enrollment_date' => array( '' ),
		'earning_date'    => array( '' ),
	);

	foreach ( $earnings['earnings'] as $item ) {
		$overview_chart_data['earnings'][]     = (float) ( $item->total ?? 0 );
		$overview_chart_data['labels'][]       = $item->label_name ?? '';
		$overview_chart_data['earning_date'][] = ! empty( $item->date_format )
													? wp_date( 'M d', strtotime( $item->date_format ) ) : '';
	}

	foreach ( $enrollments['enrollments'] as $item ) {
		$overview_chart_data['enrolled'][]        = (float) ( $item->total ?? 0 );
		$overview_chart_data['enrollment_date'][] = ! empty( $item->date_format )
													? wp_date( 'M d', strtotime( $item->date_format ) ) : '';
	}

	$overview_chart_data['earnings'][]        = 0;
	$overview_chart_data['enrolled'][]        = 0;
	$overview_chart_data['labels'][]          = '';
	$overview_chart_data['earning_date'][]    = '';
	$overview_chart_data['enrollment_date'][] = '';
}

/**
 * ---------------------------------------------
 * Course Completion Distribution (For All Time)
 * ---------------------------------------------
 */

if ( $is_all_time ) {
	$distribution = Instructor::get_course_completion_distribution_data_by_instructor( $instructor_course_ids );

	$course_completion_data = array(
		'enrolled'    => array(
			'label' => esc_html__( 'Enrolled', 'tutor' ),
			'value' => $distribution['enrolled'],
		),
		'completed'   => array(
			'label' => esc_html__( 'Completed', 'tutor' ),
			'value' => $distribution['completed'],
		),
		'in_progress' => array(
			'label' => esc_html__( 'In Progress', 'tutor' ),
			'value' => $distribution['inprogress'],
		),
		'inactive'    => array(
			'label' => esc_html__( 'Inactive', 'tutor' ),
			'value' => $distribution['inactive'],
		),
		'cancelled'   => array(
			'label' => esc_html__( 'Cancelled', 'tutor' ),
			'value' => $distribution['cancelled'],
		),
	);
}

// Top Performing Courses.
$args = array(
	'start_date' => $start_date,
	'end_date'   => $end_date,
	'order_by'   => Input::get( 'type', 'revenue' ),
);

$top_performing_courses = Instructor::format_instructor_top_performing_courses(
	Instructor::get_top_performing_courses_by_instructor( $user->ID, $args )
);

// Upcoming Live Tasks (all-time + pro only).
if ( $is_all_time && $tutor_pro_enabled ) {
	$upcoming_tasks = Instructor::format_instructor_upcoming_live_tasks(
		Instructor::get_instructor_upcoming_live_tasks( $user->ID )
	);
}

// Recent Reviews.
$review_args = array( 'comment_approved' => 'approved' );
if ( ! $is_all_time ) {
	$review_args = $date_range( $start_date, $end_date );
}
$reviews        = tutor_utils()->get_reviews_by_instructor( $user->ID, 0, 3, '', '', $review_args );
$recent_reviews = Instructor::format_instructor_recent_reviews( $reviews->results );


/**
 * ------------------------------------
 * Sortable sections data preparation
 * ------------------------------------
 */
$saved_order      = get_user_meta( get_current_user_id(), '_tutor_instructor_home_sections_order', true );
$saved_visibility = get_user_meta( get_current_user_id(), '_tutor_instructor_home_sections_visibility', true );

$sortable_sections = array(
	array(
		'id'        => 'current_stats',
		'label'     => esc_html__( 'Current Stats', 'tutor' ),
		'is_active' => $saved_visibility['current_stats'] ?? true,
		'order'     => $saved_order['current_stats'] ?? 0,
		'data'      => true,
	),
	array(
		'id'        => 'overview_chart',
		'label'     => esc_html__( 'Earnings Over Time', 'tutor' ),
		'is_active' => $saved_visibility['overview_chart'] ?? true,
		'order'     => $saved_order['overview_chart'] ?? 1,
		'data'      => ! empty( $overview_chart_data ),
	),
	array(
		'id'        => 'course_completion_and_leader',
		'label'     => esc_html__( 'Course Completion Rate', 'tutor' ),
		'is_active' => $saved_visibility['course_completion_and_leader'] ?? true,
		'order'     => $saved_order['course_completion_and_leader'] ?? 2,
		'data'      => ! empty( $course_completion_data ),
	),
	array(
		'id'        => 'top_performing_courses',
		'label'     => esc_html__( 'Top Performing Courses', 'tutor' ),
		'is_active' => $saved_visibility['top_performing_courses'] ?? true,
		'order'     => $saved_order['top_performing_courses'] ?? 3,
		'data'      => ! empty( $top_performing_courses ),
	),
	array(
		'id'        => 'upcoming_tasks_and_activity',
		'label'     => esc_html__( 'Upcoming Tasks', 'tutor' ),
		'is_active' => $saved_visibility['upcoming_tasks_and_activity'] ?? true,
		'order'     => $saved_order['upcoming_tasks_and_activity'] ?? 4,
		'data'      => ! empty( $upcoming_tasks ),
	),
	array(
		'id'        => 'recent_reviews',
		'label'     => esc_html__( 'Recent Student Reviews', 'tutor' ),
		'is_active' => $saved_visibility['recent_reviews'] ?? true,
		'order'     => $saved_order['recent_reviews'] ?? 5,
		'data'      => ! empty( $recent_reviews ),
	),
);

// Remove sections which don't have data to show.
$sortable_sections = array_filter(
	$sortable_sections,
	fn( $section ) => $section['data']
);

usort(
	$sortable_sections,
	function ( $a, $b ) {
		return $a['order'] <=> $b['order'];
	}
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
		$carry[ $section['order'] ] = $section['id'];
		return $carry;
	},
	array()
);
?>

<form x-data='tutorForm({
		id: "sortable-sections",
		mode: "onBlur",
		defaultValues: <?php echo wp_json_encode( $sortable_sections_defaults ); ?>
	})' 
	x-bind="getFormBindings()"
	class="tutor-flex tutor-flex-column tutor-gap-6"
>
	<!-- Filters -->
	<div class="tutor-flex tutor-justify-between tutor-items-center">
		<?php if ( $tutor_pro_enabled ) : ?>
			<?php DateFilter::make()->type( DateFilter::TYPE_RANGE )->render(); ?>
		<?php endif; ?>

		<div class="tutor-dashboard-home-sort" x-data="tutorPopover({ placement: '<?php echo esc_js( $tutor_pro_enabled ? 'bottom-end' : 'bottom-start' ); ?>' })">
			<button
				x-ref="trigger"
				@click="toggle()"
				class="tutor-btn tutor-btn-outline tutor-btn-small tutor-btn-icon"
				aria-label="<?php esc_attr_e( 'Filter dashboard sections', 'tutor' ); ?>"
			>
				<?php SvgIcon::make()->name( Icon::FILTER_2 )->render(); ?>
			</button>

			<div
				x-ref="content"
				x-show="open"
				x-cloak
				x-transition.origin.top.right
				@click.outside="handleClickOutside()"
				class="tutor-popover tutor-popover-bottom"
			>
				<div 
					class="tutor-popover-menu"
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
								<?php SvgIcon::make()->name( Icon::DRAG_VERTICAL )->size( 16 )->render(); ?>
							</button>
							<?php
								InputField::make()
									->type( InputType::CHECKBOX )
									->name( "$section[id]" )
									->label( $section['label'] )
									->attr( 'x-bind', "\$el.closest('[data-dnd-placeholder]') ? {} : register('{$section['id']}')" )
									->attr( '@click.stop', 'handleCheckboxClick(event)' )
									->render();
							?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>

	<?php foreach ( $sortable_sections as $section ) : ?>
		<?php if ( 'current_stats' === $section['id'] ) : ?>
			<!-- Stat cards -->
			<div 
				data-section-id="current_stats" 
				class="tutor-flex tutor-flex-wrap tutor-gap-5 tutor-z-positive"					
				x-show="watch('current_stats')"
				x-cloak
			>
				<?php foreach ( $stat_cards as $card ) : ?>
					<div class="tutor-flex-1">
					<?php
					tutor_load_template(
						'dashboard.instructor.analytics.stat-card',
						array(
							'variation'     => $card['variation'] ?? 'enrolled',
							'card_title'    => $card['title'] ?? '',
							'icon'          => $card['icon'] ?? '',
							'icon_size'     => $card['icon_size'] ?? 20,
							'value'         => $card['value'] ?? '',
							'content'       => $card['content'] ?? '',
							'hover_content' => $card['hover_content'] ?? array(),
						)
					);
					?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<!-- Overview Chart -->
		<?php
		if ( 'overview_chart' === $section['id'] && $overview_chart_data ) :
			tutor_load_template(
				'dashboard.instructor.home.overview-chart',
				array(
					'overview_chart_data' => $overview_chart_data,
				)
			);
		endif;
		?>

		<?php if ( 'course_completion_and_leader' === $section['id'] && ! empty( $course_completion_data ) ) : ?>
			<div 
				data-section-id="course_completion_and_leader" 
				class="tutor-flex tutor-gap-6"
				x-show="watch('course_completion_and_leader')"
				x-cloak
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
			</div>
		<?php endif; ?>

		<!-- Top Performing Courses -->
		<?php if ( 'top_performing_courses' === $section['id'] && ! empty( $top_performing_courses ) ) : ?>
			<div 
				data-section-id="top_performing_courses"
				class="tutor-dashboard-home-card"
				x-show="watch('top_performing_courses')"
				x-cloak
			> 
				<div class="tutor-flex tutor-row tutor-justify-between tutor-items-center tutor-gap-9">
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
		<?php if ( 'upcoming_tasks_and_activity' === $section['id'] && ! empty( $upcoming_tasks ) ) : ?>
			<div
				data-section-id="upcoming_tasks_and_activity"
				class="tutor-flex tutor-gap-6"
				x-show="watch('upcoming_tasks_and_activity')"
				x-cloak
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
			</div>
		<?php endif; ?>

		<!-- Recent Student Reviews -->
		<?php if ( 'recent_reviews' === $section['id'] && ! empty( $recent_reviews ) ) : ?>
			<div 
				data-section-id="recent_reviews" 
				class="tutor-dashboard-home-card"
				x-show="watch('recent_reviews')"
				x-cloak
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
	<?php endforeach; ?>
</form>
