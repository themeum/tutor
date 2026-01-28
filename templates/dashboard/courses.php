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

use Tutor\Components\Constants\Size;
use TUTOR\Input;
use Tutor\Components\Pagination;
use Tutor\Models\CourseModel;
use Tutor\Components\EmptyState;
use Tutor\Components\Nav;
use TUTOR\Student;

// Pagination.
$courses_per_page = tutor_utils()->get_option( 'pagination_per_page', 10 );
$current_page     = max( 1, Input::get( 'current_page', 1, Input::TYPE_INT ) );
$offset           = ( $courses_per_page * $current_page ) - $courses_per_page;

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
if ( ! isset( $active_tab, $page_tabs[ $active_tab ] ) ) {
	$active_tab = 'courses';
}

$enrolled_courses  = CourseModel::get_enrolled_courses_by_user( get_current_user_id(), array( 'private', 'publish' ), $offset, $courses_per_page );
$active_courses    = CourseModel::get_active_courses_by_user( null, $offset, $courses_per_page );
$completed_courses = CourseModel::get_completed_courses_by_user( null, $offset, $courses_per_page );

$enrolled_course_count  = is_a( $enrolled_courses, 'WP_Query' ) ? $enrolled_courses->found_posts : 0;
$active_course_count    = is_a( $active_courses, 'WP_Query' ) ? $active_courses->found_posts : 0;
$completed_course_count = is_a( $completed_courses, 'WP_Query' ) ? $completed_courses->found_posts : 0;

// Get Paginated course list.
$courses_list_array = array(
	'courses'                   => $enrolled_courses,
	'courses/active-courses'    => $active_courses,
	'courses/completed-courses' => $completed_courses,
	'courses/wishlist'          => array(),
	'courses/my-quiz-attempts'  => __( 'Quiz Attempts', 'tutor' ),
);

$courses_tab = ( new Student() )->get_courses_tab( $active_tab, $post_type_args, $enrolled_course_count, $active_course_count, $completed_course_count );

// Prepare course list based on page tab.
$courses_list = $courses_list_array[ $active_tab ];

?>

<div class="tutor-dashboard-courses-wrapper ">

	<!-- Courses nav  -->
	<div class="tutor-surface-l1 tutor-border tutor-rounded-2xl">
		<div class="tutor-p-6 tutor-border-b">
			<?php Nav::make()->items( $courses_tab )->size( Size::SMALL )->render(); ?>
		</div>

		<!-- courses list  -->
		<?php
		if ( 'courses/wishlist' === $active_tab || 'courses/my-quiz-attempts' === $active_tab ) :
			switch ( $active_tab ) {
				case 'courses/wishlist':
					tutor_load_template( 'dashboard.wishlist' );
					break;
				case 'courses/my-quiz-attempts':
					tutor_load_template( 'dashboard.my-quiz-attempts' );
					break;
			}
		elseif ( 'courses' === $active_tab || 'courses/active-courses' === $active_tab || 'courses/completed-courses' === $active_tab ) :
			// Prepare course list based on page tab.
			$courses_list = $courses_list_array[ $active_tab ];
			?>
			<div class="tutor-dashboard-courses tutor-flex tutor-flex-column tutor-gap-4 tutor-p-6">
				<?php
				if ( $courses_list && $courses_list->have_posts() ) :
					while ( $courses_list->have_posts() ) :
						$courses_list->the_post();
						tutor_load_template( 'dashboard.courses.course-card' );
					endwhile;
				else :
					EmptyState::make()->title( __( 'No Courses Found', 'tutor' ) )->render();
				endif;
				?>
			</div>
			<?php if ( ! empty( $courses_list->found_posts ) && $courses_list->found_posts > $courses_per_page ) : ?>
			<div class="tutor-p-6 tutor-border-t">
				<?php
					Pagination::make()
					->current( $current_page )
					->total( $courses_list->found_posts )
					->limit( $courses_per_page )
					->render();
				?>
			</div>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</div>