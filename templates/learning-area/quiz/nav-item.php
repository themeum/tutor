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

$quiz       = $quiz ?? null;
$can_access = $can_access ?? false;

if ( ! $quiz && ! is_a( $quiz, 'WP_Post' ) ) {
	return;
}

$is_completed = tutor_utils()->is_completed_lesson( $quiz->ID );
$lesson_title = $quiz->post_title;

$active_class = $tutor_current_content_id === $quiz->ID ? 'active' : '';
?>

<div class="<?php echo esc_html( sprintf( 'tutor-learning-nav-item %s', $active_class ) ); ?>">
	<?php if ( $is_completed ) : ?>
	<a href="<?php echo esc_url( $can_access ? get_permalink( $quiz->ID ) : '#' ); ?>">
		<?php tutor_utils()->render_svg_icon( Icon::COMPLETED_COLORIZE, 20, 20 ); ?>
		<div><?php echo esc_html( $lesson_title ); ?></div>
	</a>
	<?php else : ?>
	<a href="<?php echo esc_url( $can_access ? get_permalink( $quiz->ID ) : '#' ); ?>">
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
		<div><?php echo esc_html( $lesson_title ); ?></div>
	</a>
	<?php endif; ?>
</div>

