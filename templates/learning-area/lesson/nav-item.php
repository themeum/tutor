<?php
/**
 * Show lesson nav item on the learning area
 *
 * @package Tutor\Templates
 * @subpackage LearningArea
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

global $tutor_current_content_id;

$lesson     = $lesson ?? null;
$can_access = $can_access ?? false;

if ( ! $lesson && ! is_a( $lesson, 'WP_Post' ) ) {
	return;
}

$is_completed = tutor_utils()->is_completed_lesson( $lesson->ID );
$lesson_title = $lesson->post_title;

$active_class = $tutor_current_content_id === $lesson->ID ? 'active' : '';

// Check if lesson has video.
$video_info  = tutor_utils()->get_video_info( $lesson->ID );
$lesson_type = __( 'Reading', 'tutor' );

if ( $video_info && ! empty( $video_info->playtime ) ) {
	$duration = tutor_utils()->get_optimized_duration( $video_info->playtime );

	/* translators: %s: duration in minutes */
	$lesson_type = sprintf( __( 'Video - %s mins', 'tutor' ), $duration );
}

$lesson_title_html = '<div>
	<div>' . esc_html( $lesson_title ) . '</div>
	<div class="tutor-tiny tutor-text-subdued">' . esc_html( $lesson_type ) . '</div>
</div>';
?>

<div class="<?php echo esc_html( sprintf( 'tutor-learning-nav-item %s', $active_class ) ); ?>">
	<?php if ( $is_completed ) : ?>
	<a href="<?php echo esc_url( $can_access ? get_permalink( $lesson->ID ) : '#' ); ?>">
		<?php tutor_utils()->render_svg_icon( Icon::COMPLETED_COLORIZE, 20, 20 ); ?>
		<?php echo $lesson_title_html; // phpcs:ignore -- already escaped ?>
	</a>
	<?php else : ?>
	<a href="<?php echo esc_url( $can_access ? get_permalink( $lesson->ID ) : '#' ); ?>">
		<div class="tutor-learning-nav-progress">
			<div x-data="tutorStatics({ 
				value: 0,
				size: 'tiny',
				type: 'progress',
				showLabel: false,
				background: 'var(--tutor-actions-gray-empty)',
				strokeColor: 'var(--tutor-border-hover)' })">
				<div x-html="render()"></div>
			</div>
		</div>
		<?php echo $lesson_title_html; // phpcs:ignore -- already escaped ?>
	</a>
	<?php endif; ?>
</div>

