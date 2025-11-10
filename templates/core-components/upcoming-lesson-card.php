<?php
/**
 * Upcoming Lesson Card Component
 *
 * @package TutorLMS\Templates
 */

use TUTOR\Icon;

defined( 'ABSPATH' ) || exit;

// Default values.
$date_text     = isset( $date_text ) ? $date_text : __( 'Today', 'tutor' );
$time_text     = isset( $time_text ) ? $time_text : '';
$lesson_title  = isset( $lesson_title ) ? $lesson_title : '';
$course_name   = isset( $course_name ) ? $course_name : '';
$show_live_tag = isset( $show_live_tag ) ? $show_live_tag : true;

?>
<div class="tutor-card tutor-upcoming-lesson-card">
	<div class="tutor-upcoming-lesson-card-header">
		<div class="tutor-upcoming-lesson-card-meta">
			<span class="tutor-upcoming-lesson-card-icon">
				<?php tutor_utils()->render_svg_icon( Icon::CALENDAR_2, 16, 16 ); ?>
			</span>
			<span class="tutor-upcoming-lesson-card-date"><?php echo esc_html( $date_text ); ?></span>
			<?php if ( ! empty( $time_text ) ) : ?>
				<span class="tutor-upcoming-lesson-card-separator">•</span>
				<span class="tutor-upcoming-lesson-card-time"><?php echo esc_html( $time_text ); ?></span>
			<?php endif; ?>
		</div>
		<?php if ( $show_live_tag ) : ?>
			<div class="tutor-upcoming-lesson-card-live-tag">
				<?php tutor_load_template( 'core-components.live-session-card' ); ?>
			</div>
		<?php endif; ?>
	</div>
	<?php if ( ! empty( $lesson_title ) ) : ?>
		<h3 class="tutor-upcoming-lesson-card-title"><?php echo esc_html( $lesson_title ); ?></h3>
	<?php endif; ?>
	<?php if ( ! empty( $course_name ) ) : ?>
		<div class="tutor-upcoming-lesson-card-course">
			<span class="tutor-upcoming-lesson-card-course-label"><?php echo esc_html__( 'Course:', 'tutor' ); ?></span>
			<span class="tutor-upcoming-lesson-card-course-name"><?php echo esc_html( $course_name ); ?></span>
		</div>
	<?php endif; ?>
</div>

