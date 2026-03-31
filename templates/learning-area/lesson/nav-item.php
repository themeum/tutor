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

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;
use Tutor\Components\SvgIcon;
use TUTOR\Lesson;

global $tutor_current_content_id;

$lesson     = $lesson ?? null;
$can_access = $can_access ?? false;

if ( ! $lesson && ! is_a( $lesson, 'WP_Post' ) ) {
	return;
}

$is_completed = tutor_utils()->is_completed_lesson( $lesson->ID );
$lesson_title = $lesson->post_title;

$active_class   = $tutor_current_content_id === $lesson->ID ? 'active' : '';
$disabled_class = $can_access ? '' : 'disabled';

// Check if lesson has video.
$lesson_type = Lesson::get_content_type_info( $lesson );

$icon_name = Icon::COURSES;
if ( ! $can_access ) {
	$icon_name = Icon::LOCK_STROKE_2;
} elseif ( $is_completed ) {
	$icon_name = Icon::COMPLETED_COLORIZE;
}
?>

<a
	href="<?php echo esc_url( $can_access ? get_permalink( $lesson->ID ) : '#' ); ?>" 
	title="<?php echo esc_attr( $lesson_title ); ?>"
	class="<?php echo esc_attr( sprintf( 'tutor-learning-nav-item %s %s', $active_class, $disabled_class ) ); ?>"
	<?php echo ! $can_access ? 'aria-disabled="true"' : ''; ?>
>
	<?php SvgIcon::make()->name( $icon_name )->size( 20 )->render(); ?>
	<div class="tutor-overflow-hidden">
		<div class="tutor-truncate"><?php echo esc_html( $lesson_title ); ?></div>
		<div class="tutor-tiny-2 tutor-text-subdued"><?php echo esc_html( $lesson_type ); ?></div>
	</div>
</a>
