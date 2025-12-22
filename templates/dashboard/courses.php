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
use Tutor\Components\Pagination;
use Tutor\Models\CourseModel;

// Pagination.
$per_page = tutor_utils()->get_option( 'pagination_per_page', 10 );
$paged    = max( 1, Input::get( 'current_page', 1, Input::TYPE_INT ) );
$offset   = ( $per_page * $paged ) - $per_page;

$post_type_query = Input::get( 'type', '' );

$post_type_args = $post_type_query ? array( 'type' => $post_type_query ) : array();

$page_tabs = apply_filters(
	'tutor_enrolled_courses_page_tabs',
	array(
		'courses'                   => __( 'Enrolled Courses', 'tutor' ),
		'courses/active-courses'    => __( 'Active Courses', 'tutor' ),
		'courses/completed-courses' => __( 'Completed Courses', 'tutor' ),
		'courses/wishlist'          => __( 'Wishlist', 'tutor' ),
		'courses/my-quiz-attempts'  => __( 'Quiz Attempts', 'tutor' ),
	),
	$post_type_query
);

// Default tab set.
( ! isset( $active_tab, $page_tabs[ $active_tab ] ) ) ? $active_tab = 'courses' : 0;

// Get Paginated course list.
$courses_list_array = array(
	'courses'                   => CourseModel::get_enrolled_courses_by_user( get_current_user_id(), array( 'private', 'publish' ), $offset, $per_page ),
	'courses/active-courses'    => CourseModel::get_active_courses_by_user( null, $offset, $per_page ),
	'courses/completed-courses' => CourseModel::get_completed_courses_by_user( null, $offset, $per_page ),
);

// Get Full course list.
$full_course_list_array = array(
	'courses'                   => CourseModel::get_enrolled_courses_by_user( get_current_user_id(), array( 'private', 'publish' ) ),
	'courses/active-courses'    => CourseModel::get_active_courses_by_user(),
	'courses/completed-courses' => CourseModel::get_completed_courses_by_user(),
);

// Count course list based on query param.
$enrolled_course_count  = $full_course_list_array['courses'] ? $full_course_list_array['courses']->found_posts : 0;
$active_course_count    = $full_course_list_array['courses/active-courses'] ? $full_course_list_array['courses/active-courses']->found_posts : 0;
$completed_course_count = $full_course_list_array['courses/completed-courses'] ? $full_course_list_array['courses/completed-courses']->found_posts : 0;

$courses_tab = array(
	array(
		'type'    => 'dropdown',
		'icon'    => Icon::ENROLLED,
		'active'  => ( ( 'courses' === $active_tab ) || ( 'courses/active-courses' === $active_tab ) || ( 'courses/completed-courses' === $active_tab ) ) ? true : false,
		'options' => array(
			array(
				'label'  => __( 'Enrolled', 'tutor' ),
				'icon'   => Icon::ENROLLED,
				'url'    => esc_url( add_query_arg( $post_type_args, tutor_utils()->get_tutor_dashboard_page_permalink( 'courses' ) ) ),
				'active' => 'courses' === $active_tab ? true : false,
				'count'  => $enrolled_course_count,
			),
			array(
				'label'  => __( 'Active', 'tutor' ),
				'icon'   => Icon::PLAY_LINE,
				'url'    => esc_url( add_query_arg( $post_type_args, tutor_utils()->get_tutor_dashboard_page_permalink( 'courses/active-courses' ) ) ),
				'active' => 'courses/active-courses' === $active_tab ? true : false,
				'count'  => $active_course_count,
			),
			array(
				'label'  => __( 'Complete', 'tutor' ),
				'icon'   => Icon::COMPLETED_CIRCLE,
				'url'    => esc_url( add_query_arg( $post_type_args, tutor_utils()->get_tutor_dashboard_page_permalink( 'courses/completed-courses' ) ) ),
				'active' => 'courses/completed-courses' === $active_tab ? true : false,
				'count'  => $completed_course_count,
			),
		),
	),
	array(
		'type'   => 'link',
		'label'  => __( 'Wishlist', 'tutor' ),
		'icon'   => Icon::WISHLIST,
		'url'    => esc_url( tutor_utils()->tutor_dashboard_url( 'courses/wishlist' ) ),
		'active' => 'courses/wishlist' === $active_tab ? true : false,
		'count'  => $wishlist_course_count ?? 0,
	),
	array(
		'type'   => 'link',
		'label'  => __( 'Quiz Attempts', 'tutor' ),
		'icon'   => Icon::QUIZ_2,
		'url'    => esc_url( tutor_utils()->tutor_dashboard_url( 'courses/my-quiz-attempts' ) ),
		'active' => 'courses/my-quiz-attempts' === $active_tab ? true : false,
		'count'  => 0,
	),
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
					'size'    => 'sm',
					'variant' => 'primary',
				)
			);
			?>
	</div>

	<?php
	if ( 'courses/wishlist' === $active_tab || 'courses/my-quiz-attempts' === $active_tab ) :
		match ( $active_tab ) {
			'courses/wishlist' => tutor_load_template( 'dashboard.wishlist' ),
			'courses/my-quiz-attempts' => tutor_load_template( 'dashboard.my-quiz-attempts' ),
		};
	elseif ( 'courses' === $active_tab || 'courses/active-courses' === $active_tab || 'courses/completed-courses' === $active_tab ) :
		// Prepare course list based on page tab.
		$courses_list           = $courses_list_array[ $active_tab ];
		$paginated_courses_list = $full_course_list_array[ $active_tab ];
		?>
		<div class="tutor-dashboard-courses tutor-flex tutor-flex-column tutor-gap-4 tutor-p-6">
		<?php
		if ( $courses_list && $courses_list->have_posts() ) :
			while ( $courses_list->have_posts() ) :
				$courses_list->the_post();
				tutor_load_template( 'dashboard.courses.course-card' );
			endwhile;
			?>
				<div class="tutor-dashboard-courses-pagination tutor-pt-6">
				<?php
					Pagination::make()
					->current( $paged )
					->total( $courses_list->found_posts )
					->limit( $per_page )
					->prev( tutor_utils()->get_svg_icon( Icon::CHEVRON_LEFT_2 ) )
					->next( tutor_utils()->get_svg_icon( Icon::CHEVRON_RIGHT_2 ) )
					->render();
				?>
				</div>
			<?php else : ?>
				<?php tutor_utils()->tutor_empty_state( tutor_utils()->not_found_text() ); ?>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
