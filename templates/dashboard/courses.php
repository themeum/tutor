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

defined( 'ABSPATH' ) || exit;

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

// Only fetch the paginated list for the active tab.
switch ( $active_tab ) {
	case 'courses':
		$courses_list = CourseModel::get_enrolled_courses_by_user( get_current_user_id(), array( CourseModel::STATUS_PRIVATE, CourseModel::STATUS_PUBLISH ), $offset, $courses_per_page );
		break;
	case 'courses/active-courses':
		$courses_list = CourseModel::get_active_courses_by_user( null, $offset, $courses_per_page, array( 'post_status' => array( CourseModel::STATUS_PRIVATE, CourseModel::STATUS_PUBLISH ) ) );
		break;
	case 'courses/completed-courses':
		$courses_list = CourseModel::get_completed_courses_by_user( null, $offset, $courses_per_page, array( 'post_status' => array( CourseModel::STATUS_PRIVATE, CourseModel::STATUS_PUBLISH ) ) );
		break;
	default:
		$courses_list = null;
		break;
}

// Separate count queries with no offset so tab counts are always accurate regardless of current page.
$enrolled_courses_for_count  = CourseModel::get_enrolled_courses_by_user( get_current_user_id(), array( CourseModel::STATUS_PRIVATE, CourseModel::STATUS_PUBLISH ), 0, 1 );
$active_courses_for_count    = CourseModel::get_active_courses_by_user( null, 0, 1, array( 'post_status' => array( CourseModel::STATUS_PRIVATE, CourseModel::STATUS_PUBLISH ) ) );
$completed_courses_for_count = CourseModel::get_completed_courses_by_user( null, 0, 1, array( 'post_status' => array( CourseModel::STATUS_PRIVATE, CourseModel::STATUS_PUBLISH ) ) );

$enrolled_course_count  = is_a( $enrolled_courses_for_count, 'WP_Query' ) ? $enrolled_courses_for_count->found_posts : 0;
$active_course_count    = is_a( $active_courses_for_count, 'WP_Query' ) ? $active_courses_for_count->found_posts : 0;
$completed_course_count = is_a( $completed_courses_for_count, 'WP_Query' ) ? $completed_courses_for_count->found_posts : 0;

$courses_tab = ( new Student() )->get_courses_tab( $active_tab, $post_type_args, $enrolled_course_count, $active_course_count, $completed_course_count );

?>

<div class="tutor-dashboard-courses-wrapper">
	<div class="tutor-hidden tutor-sm-flex tutor-items-center tutor-justify-between tutor-mb-5">
		<h4 class="tutor-h4 tutor-my-none"><?php esc_html_e( 'Courses', 'tutor' ); ?></h4>
	</div>

	<!-- Courses nav  -->
	<div class="tutor-dashboard-courses-card">
		<div class="tutor-dashboard-courses-tab">
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
			?>
			<div class="tutor-dashboard-courses-list tutor-flex tutor-flex-column tutor-gap-4 tutor-p-6 tutor-sm-p-none tutor-sm-mt-4">
				<?php
				if ( $courses_list && $courses_list->have_posts() ) :
					while ( $courses_list->have_posts() ) :
						$courses_list->the_post();
						$course_id = get_the_ID();

						$default_template     = tutor_get_template( 'dashboard.courses.course-card' );
						$course_card_template = apply_filters( 'tutor_dashboard_course_card_template', $default_template, $course_id );

						if ( file_exists( $course_card_template ) ) {
							require $course_card_template;
						}
					endwhile;
					wp_reset_postdata();
				else :
					EmptyState::make()
						->title( __( 'No Courses Found', 'tutor' ) )
						->icon( tutor_utils()->get_themed_svg( 'images/illustrations/learning-empty.svg' ) )
						->render();
				endif;
				?>
			</div>
			<?php
			$found_posts = $courses_list ? $courses_list->found_posts : 0;
			Pagination::make()
				->current( $current_page )
				->total( $found_posts )
				->limit( $courses_per_page )
				->attr( 'class', 'tutor-px-6 tutor-pb-6 tutor-sm-p-none tutor-sm-mt-5' )
				->render();
			?>
		<?php endif; ?>
	</div>
</div>
