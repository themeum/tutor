<?php
/**
 * Courses Page
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.3
 */

use TUTOR\Icon;
use TUTOR\Input;
use Tutor\Models\CourseModel;
use Tutor\Components\Pagination;


$courses_tab_query_param = Input::get( 'tab', '' );
$courses_slug_prefix     = 'courses?tab=';
$active_tab              = 'enrolled';

if ( ! empty( $courses_tab_query_param ) ) {
	$active_tab = $courses_tab_query_param;
}

// Pagination.
$courses_per_page = tutor_utils()->get_option( 'pagination_per_page', 10 );
$courses_paged    = max( 1, Input::get( 'current_page', 1, Input::TYPE_INT ) );
$offset           = ( $courses_per_page * $courses_paged ) - $courses_per_page;

// Get Paginated course list.
$course_list_array = array(
	'enrolled'      => CourseModel::get_enrolled_courses_by_user( get_current_user_id(), array( 'private', 'publish' ), $offset, $courses_per_page ),
	'active'        => CourseModel::get_active_courses_by_user( null, $offset, $courses_per_page ),
	'completed'     => CourseModel::get_completed_courses_by_user( null, $offset, $courses_per_page ),
	'wishlist'      => CourseModel::get_completed_courses_by_user( null, $offset, $courses_per_page ),
	'quiz-attempts' => CourseModel::get_completed_courses_by_user( null, $offset, $courses_per_page ),
);

// Get Full course list.
$full_course_list_array = array(
	'enrolled'      => CourseModel::get_enrolled_courses_by_user( get_current_user_id(), array( 'private', 'publish' ) ),
	'active'        => CourseModel::get_active_courses_by_user(),
	'completed'     => CourseModel::get_completed_courses_by_user(),
	'wishlist'      => CourseModel::get_completed_courses_by_user(),
	'quiz-attempts' => CourseModel::get_completed_courses_by_user(),
);


// Prepare course list based on page tab.
$courses_list          = $course_list_array[ $active_tab ];
$paginated_course_list = $full_course_list_array[ $active_tab ];

// Count course list based on query param.
$enrolled_course_count  = $full_course_list_array['enrolled'] ? $full_course_list_array['enrolled']->found_posts : 0;
$active_course_count    = $full_course_list_array['active'] ? $full_course_list_array['active']->found_posts : 0;
$completed_course_count = $full_course_list_array['completed'] ? $full_course_list_array['completed']->found_posts : 0;
$wishlist_course_count  = $full_course_list_array['wishlist'] ? $full_course_list_array['wishlist']->found_posts : 0;

$courses_tab = apply_filters(
	'tutor_dashboard_courses_tabs',
	array(
		array(
			'type'    => 'dropdown',
			'icon'    => Icon::ENROLLED,
			'active'  => ( ( 'enrolled' === $active_tab ) || ( 'active' === $active_tab ) || ( 'completed' === $active_tab ) ) ? true : false,
			'options' => array(
				array(
					'label'  => __( 'Enrolled', 'tutor' ),
					'icon'   => Icon::ENROLLED,
					'url'    => esc_url( tutor_utils()->tutor_dashboard_url( $courses_slug_prefix . 'enrolled' ) ),
					'active' => 'enrolled' === $active_tab ? true : false,
					'count'  => $enrolled_course_count,
				),
				array(
					'label'  => __( 'Active', 'tutor' ),
					'icon'   => Icon::PLAY_LINE,
					'url'    => esc_url( tutor_utils()->tutor_dashboard_url( $courses_slug_prefix . 'active' ) ),
					'active' => 'active' === $active_tab ? true : false,
					'count'  => $active_course_count,
				),
				array(
					'label'  => __( 'Complete', 'tutor' ),
					'icon'   => Icon::COMPLETED_CIRCLE,
					'url'    => esc_url( tutor_utils()->tutor_dashboard_url( $courses_slug_prefix . 'completed' ) ),
					'active' => 'completed' === $active_tab ? true : false,
					'count'  => $completed_course_count,
				),
			),
		),
		array(
			'type'   => 'link',
			'label'  => __( 'Wishlist', 'tutor' ),
			'icon'   => Icon::WISHLIST,
			'url'    => esc_url( tutor_utils()->tutor_dashboard_url( $courses_slug_prefix . 'wishlist' ) ),
			'active' => 'wishlist' === $active_tab ? true : false,
			'count'  => $wishlist_course_count,
		),
		array(
			'type'   => 'link',
			'label'  => __( 'Quiz Attempts', 'tutor' ),
			'icon'   => Icon::QUIZ_2,
			'url'    => esc_url( tutor_utils()->tutor_dashboard_url( $courses_slug_prefix . 'quiz-attempts' ) ),
			'active' => 'quiz-attempts' === $active_tab ? true : false,
		),
	),
	$courses_tab_query_param
);
?>

<div class="tutor-dashboard-courses-wrapper">

	<!-- Courses nav  -->
	<div class="tutor-dashboard-page-nav tutor-p-6">
		<?php
			tutor_load_template(
				'core-components.nav',
				array(
					'items'   => $courses_tab,
					'size'    => 'lg',
					'variant' => 'primary',
				)
			);
			?>
	</div>

	<!-- courses list  -->
	<div class="tutor-dashboard-courses tutor-flex tutor-flex-column tutor-gap-4 tutor-p-6">
		<?php
		if ( $courses_list && $courses_list->have_posts() ) :
			while ( $courses_list->have_posts() ) :
				$courses_list->the_post();
				match ( $courses_tab_query_param ) {
					'wishlist' => tutor_load_template( 'dashboard.wishlist' ),
					'quiz-attempts' => tutor_load_template( 'demo-components.dashboard.components.quiz-attempts-list' ),
					default => tutor_load_template( 'dashboard.courses.course-card' ),
				};
				endwhile;
		endif;
		?>
		<div class="tutor-dashboard-courses-pagination tutor-pt-6">
			<?php
				Pagination::make()
					->current( $courses_paged )
					->base( esc_url( tutor_utils()->tutor_dashboard_url( $active_tab ) ) )
					->total( $courses_list->found_posts )
					->limit( $courses_per_page )
					->prev( tutor_utils()->get_svg_icon( Icon::CHEVRON_LEFT_2 ) )
					->next( tutor_utils()->get_svg_icon( Icon::CHEVRON_RIGHT_2 ) )
					->render();
			?>
		</div>
	</div>
</div>