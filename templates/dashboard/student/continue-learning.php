<?php
/**
 * Student dashboard continue learning section
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\EmptyState;
use Tutor\Models\CourseModel;

$user_id             = $user_id ?? get_current_user_id();
$courses_in_progress = CourseModel::get_active_courses_by_user( $user_id, 0, 2, array( 'post_status' => array( 'private', 'publish' ) ) );
?>
<div class="tutor-student-dashboard-courses">
	<div class="tutor-flex tutor-items-center tutor-justify-between tutor-mb-4">
		<div class="tutor-small tutor-font-medium">
			<?php esc_html_e( 'Continue Learning', 'tutor' ); ?>
		</div>
		<?php if ( $courses_in_progress && $courses_in_progress->have_posts() ) : ?>
		<a 
			href="<?php echo esc_url( tutor_utils()->tutor_dashboard_url( 'courses/active-courses' ) ); ?>" 
			class="tutor-btn tutor-btn-link tutor-btn-x-small tutor-text-brand tutor-p-none tutor-min-h-0"
		>
			<?php esc_html_e( 'See All', 'tutor' ); ?>
		</a>
		<?php endif; ?>
	</div>
	<div class="tutor-flex tutor-flex-column tutor-gap-4">
		<?php
		if ( $courses_in_progress && $courses_in_progress->have_posts() ) :
			while ( $courses_in_progress->have_posts() ) :
				$courses_in_progress->the_post();
				tutor_load_template( 'dashboard.courses.course-card' );
			endwhile;
			wp_reset_postdata();
		else :
			EmptyState::make()
				->title( __( 'No Courses Found', 'tutor' ) )
				->icon( tutor_utils()->get_themed_svg( 'images/illustrations/learning-empty.svg' ) )
				->attr( 'class', 'tutor-card' )
				->render();
		endif;
		?>
	</div>
</div>
