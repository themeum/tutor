<?php
/**
 * Upcoming Lesson Card Component
 *
 * @package TutorLMS\Templates
 */

use TUTOR\Icon;

defined( 'ABSPATH' ) || exit;

// Optional props with defaults.
$show_live_tag     = isset( $show_live_tag ) ? $show_live_tag : true;
$event_tag_text    = isset( $event_tag_text ) ? $event_tag_text : __( 'Live Session', 'tutor' );
$event_tag_icon    = isset( $event_tag_icon ) ? $event_tag_icon : Icon::ZOOM_COLORIZE;
$event_tag_variant = isset( $event_tag_variant ) ? $event_tag_variant : '';
$action_url        = isset( $action_url ) ? $action_url : '';
$action_text       = isset( $action_text ) ? $action_text : __( 'Open', 'tutor' );

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
				<div class="tutor-upcoming-lesson-card-live-tag-badge">
					<?php
					tutor_load_template(
						'core-components.event-badge',
						array(
							'text'    => $event_tag_text,
							'icon'    => $event_tag_icon,
							'variant' => $event_tag_variant,
						)
					);
					?>
				</div>
					<a class="tutor-btn tutor-btn-primary tutor-btn-x-small tutor-upcoming-lesson-card-action" href="<?php echo esc_url( $action_url ); ?>">
						<?php echo esc_html( $action_text ); ?>
					</a>
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

