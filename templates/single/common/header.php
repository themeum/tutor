<?php
/**
 * Common header template.
 *
 * @package Tutor\Templates
 * @subpackage Single\Common
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

$course_id    = isset( $course_id ) ? (int) $course_id : 0;
$is_enrolled  = tutor_utils()->is_enrolled( $course_id );
$course_stats = tutor_utils()->get_course_completed_percent( $course_id, 0, true );

// Options.
$show_mark_complete = isset( $mark_as_complete ) ? $mark_as_complete : false;

/**
 * Auto course complete on all lesson, quiz, assignment complete
 *
 * @since 2.0.7
 */
$auto_course_complete_option = tutor_utils()->get_option( 'auto_course_complete_on_all_lesson_completion' );
$is_course_completed         = tutor_utils()->is_completed_course( $course_id, get_current_user_id() );
if ( true === $auto_course_complete_option && false === $is_course_completed ) {
	if ( $course_stats['completed_count'] === $course_stats['total_count'] ) {
		// Complete the course.
		\Tutor\Models\CourseModel::mark_course_as_completed( $course_id, get_current_user_id() );
	}
}

?>
<div class="tutor-course-topic-single-header tutor-single-page-top-bar">
	<a href="#" class="tutor-course-topics-sidebar-toggler tutor-iconic-btn tutor-iconic-btn-secondary tutor-d-none tutor-d-xl-inline-flex tutor-flex-shrink-0" tutor-course-topics-sidebar-toggler>
		<span class="tutor-icon-left" area-hidden="true"></span>
	</a>

	<a href="<?php echo esc_url( get_the_permalink( $course_id ) ); ?>" class="tutor-iconic-btn tutor-d-flex tutor-d-xl-none">
		<span class="tutor-icon-previous" area-hidden="true"></span>
	</a>

	<div class="tutor-course-topic-single-header-title tutor-fs-6 tutor-ml-12 tutor-ml-xl-24">
		<?php echo esc_html( get_the_title( $course_id ) ); ?>
	</div>

	<div class="tutor-ml-auto tutor-align-center tutor-d-none tutor-d-xl-flex">
		<?php if ( $is_enrolled ) : ?>
			<?php do_action( 'tutor_course/single/enrolled/before/lead_info/progress_bar' ); ?>
			<div class="tutor-fs-7 tutor-mr-20">
				<?php if ( true == get_tutor_option( 'enable_course_progress_bar' ) ) : ?>
					<span class="tutor-progress-content tutor-color-primary-60">
						<?php esc_html_e( 'Your Progress:', 'tutor' ); ?>
					</span>
					<span class="tutor-fs-7 tutor-fw-bold">
						<?php echo esc_html( $course_stats['completed_count'] ); ?>
					</span>
					<?php esc_html_e( 'of ', 'tutor' ); ?>
					<span class="tutor-fs-7 tutor-fw-bold">
						<?php echo esc_html( $course_stats['total_count'] ); ?>
					</span>
					(<?php echo esc_html( $course_stats['completed_percent'] . '%' ); ?>)
				<?php endif; ?>
			</div>
			<?php do_action( 'tutor_course/single/enrolled/after/lead_info/progress_bar' ); ?>
			<?php
			if ( $show_mark_complete ) {
				tutor_lesson_mark_complete_html();
			}
			?>
			<?php endif; ?>
		<?php
		if ( 0 === $course_id && 'tutor_zoom_meeting' === get_post_type( get_the_ID() ) ) {
			// Zoom General Meeting.
			$course_id = wp_get_post_parent_id( get_the_ID() );
		}
		?>
		<a class="tutor-iconic-btn tutor-flex-shrink-0" href="<?php echo esc_url( get_the_permalink( $course_id ) ); ?>">
			<span class="tutor-icon-times" area-hidden="true"></span>
		</a>
	</div>

	<div class="tutor-ml-auto tutor-align-center tutor-d-block tutor-d-xl-none">
		<a class="tutor-iconic-btn" href="#" tutor-course-topics-sidebar-offcanvas-toggler>
			<span class="tutor-icon-hamburger-menu" area-hidden="true"></span>
		</a>
	</div>
</div>
