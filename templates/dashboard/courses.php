<?php
/**
 * Enrolled Courses Page
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


// Pagination.
$courses_per_page = tutor_utils()->get_option( 'pagination_per_page', 10 );
// $courses_per_page = 1;
$courses_paged = max( 1, Input::get( 'current_page', 1, Input::TYPE_INT ) );
$offset        = ( $courses_per_page * $courses_paged ) - $courses_per_page;

$courses_tab_query_param = Input::get( 'tab', '' );

// Default tab set.
$active_tab = 'courses?tab=enrolled';

if ( ! empty( $courses_tab_query_param ) ) {
	$active_tab = 'courses?tab=' . $courses_tab_query_param;
}

// Get Paginated course list.
$course_list_array = array(
	'courses?tab=enrolled'      => CourseModel::get_enrolled_courses_by_user( get_current_user_id(), array( 'private', 'publish' ), $offset, $courses_per_page ),
	'courses?tab=active'        => CourseModel::get_active_courses_by_user( null, $offset, $courses_per_page ),
	'courses?tab=completed'     => CourseModel::get_completed_courses_by_user( null, $offset, $courses_per_page ),
	'courses?tab=wishlist'      => CourseModel::get_completed_courses_by_user( null, $offset, $courses_per_page ),
	'courses?tab=quiz-attempts' => CourseModel::get_completed_courses_by_user( null, $offset, $courses_per_page ),
);

// Get Full course list.
$full_course_list_array = array(
	'courses?tab=enrolled'      => CourseModel::get_enrolled_courses_by_user( get_current_user_id(), array( 'private', 'publish' ) ),
	'courses?tab=active'        => CourseModel::get_active_courses_by_user(),
	'courses?tab=completed'     => CourseModel::get_completed_courses_by_user(),
	'courses?tab=wishlist'      => CourseModel::get_completed_courses_by_user(),
	'courses?tab=quiz-attempts' => CourseModel::get_completed_courses_by_user(),
);


// Prepare course list based on page tab.
$courses_list          = $course_list_array[ $active_tab ];
$paginated_course_list = $full_course_list_array[ $active_tab ];

// Count course list based on page tab.
$enrolled_course_count  = $full_course_list_array['courses?tab=enrolled'] ? $full_course_list_array['courses?tab=enrolled']->found_posts : 0;
$active_course_count    = $full_course_list_array['courses?tab=active'] ? $full_course_list_array['courses?tab=active']->found_posts : 0;
$completed_course_count = $full_course_list_array['courses?tab=completed'] ? $full_course_list_array['courses?tab=completed']->found_posts : 0;
$wishlist_course_count  = $full_course_list_array['courses?tab=wishlist'] ? $full_course_list_array['courses?tab=wishlist']->found_posts : 0;

$courses_tab = apply_filters(
	'tutor_dashboard_courses_tabs',
	array(
		array(
			'type'    => 'dropdown',
			'icon'    => Icon::ENROLLED,
			'active'  => ( ( 'courses?tab=enrolled' === $active_tab ) || ( 'courses?tab=active' === $active_tab ) || ( 'courses?tab=completed' === $active_tab ) ) ? true : false,
			'options' => array(
				array(
					'label'  => __( 'Enrolled', 'tutor' ),
					'icon'   => Icon::ENROLLED,
					'url'    => esc_url( tutor_utils()->tutor_dashboard_url( 'courses?tab=enrolled' ) ),
					'active' => 'courses?tab=enrolled' === $active_tab ? true : false,
					'count'  => $enrolled_course_count,
				),
				array(
					'label'  => __( 'Active', 'tutor' ),
					'icon'   => Icon::PLAY_LINE,
					'url'    => esc_url( tutor_utils()->tutor_dashboard_url( 'courses?tab=active' ) ),
					'active' => 'courses?tab=active' === $active_tab ? true : false,
					'count'  => $active_course_count,
				),
				array(
					'label'  => __( 'Complete', 'tutor' ),
					'icon'   => Icon::COMPLETED_CIRCLE,
					'url'    => esc_url( tutor_utils()->tutor_dashboard_url( 'courses?tab=completed' ) ),
					'active' => 'courses?tab=completed' === $active_tab ? true : false,
					'count'  => $completed_course_count,
				),
			),
		),
		array(
			'type'   => 'link',
			'label'  => __( 'Wishlist', 'tutor' ),
			'icon'   => Icon::WISHLIST,
			'url'    => esc_url( tutor_utils()->tutor_dashboard_url( 'courses?tab=wishlist' ) ),
			'active' => 'courses?tab=wishlist' === $active_tab ? true : false,
			'count'  => $wishlist_course_count,
		),
		array(
			'type'   => 'link',
			'label'  => __( 'Quiz Attempts', 'tutor' ),
			'icon'   => Icon::QUIZ_2,
			'url'    => esc_url( tutor_utils()->tutor_dashboard_url( 'courses?tab=quiz-attempts' ) ),
			'active' => 'courses?tab=quiz-attempts' === $active_tab ? true : false,
		),
	),
	$courses_tab_query_param
);
?>

<div class="tutor-dashboard-courses-wrapper">

	<?php tutor_load_template( 'dashboard.courses.courses-nav', array( 'course_tab' => $courses_tab ) ); ?>

	<!-- courses list  -->
	<div class="tutor-enrolled-courses tutor-flex tutor-flex-column tutor-gap-2 tutor-p-6">
		<?php
		if ( $courses_list && $courses_list->have_posts() ) :
			while ( $courses_list->have_posts() ) :
				?>
				<?php $courses_list->the_post(); ?>
				<?php
				match ( $courses_tab_query_param ) {
					'wishlist' => tutor_load_template( 'dashboard.wishlist' ),
					'quiz-attempts' => tutor_load_template( 'dashboard.quiz-attempts' ),
					default => tutor_load_template( 'dashboard.courses.courses-card' ),
				};
				?>
			<?php endwhile; ?>
		<?php endif; ?>
<?php
	Pagination::make()
		->base( esc_url( tutor_utils()->tutor_dashboard_url( $active_tab ) ) )
		->current( $courses_paged )
		->total( 100 )
		// ->total( $courses_list->found_posts )
		->limit( $courses_per_page )
		// ->limit( tutor_utils()->get_option( 'pagination_per_page' ) )
		->prev( tutor_utils()->get_svg_icon( Icon::CHEVRON_LEFT_2 ) )
		->next( tutor_utils()->get_svg_icon( Icon::CHEVRON_RIGHT_2 ) )
		->render();
?>
	</div>
</div>