<?php
/**
 * Tutor Course Progress for enrolled courses
 *
 * @package Tutor\Templates
 * @subpackage CourseLoopPart
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

$course_id       = get_the_ID();
$course_progress = tutor_utils()->get_course_completed_percent( $course_id, 0, true );
?>

<div class="tutor-course-progress">
	<div class="tutor-fs-6 tutor-color-secondary tutor-d-flex tutor-align-center tutor-justify-between">
		<span>
			<?php echo esc_html( $course_progress['completed_count'] ); ?>/<?php echo esc_html( $course_progress['total_count'] ); ?>
		</span>
		<span> 
			<?php echo esc_html( $course_progress['completed_percent'] . '%' ); ?>
			<?php esc_html_e( 'Complete', 'tutor' ); ?>
		</span>
	</div>
	<div class="tutor-progress-bar tutor-mt-12" style="--tutor-progress-value:<?php echo esc_attr( $course_progress['completed_percent'] ); ?>%;">
		<span class="tutor-progress-value" area-hidden="true"></span>
	</div>
</div>
