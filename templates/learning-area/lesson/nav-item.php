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
use TUTOR\Lesson;

global $tutor_current_content_id;

$lesson     = $lesson ?? null;
$can_access = $can_access ?? false;

if ( ! $lesson && ! is_a( $lesson, 'WP_Post' ) ) {
	return;
}

$is_completed = tutor_utils()->is_completed_lesson( $lesson->ID );
$lesson_type  = Lesson::get_content_type_info( $lesson );

$icon_name = Icon::COURSES;
if ( ! $can_access ) {
	$icon_name = Icon::LOCK_STROKE_2;
} elseif ( $is_completed ) {
	$icon_name = Icon::COMPLETED_COLORIZE;
}

tutor_load_template(
	'learning-area.components.sidebar-nav-item',
	array(
		'item'         => $lesson,
		'active'       => $tutor_current_content_id === $lesson->ID,
		'can_access'   => $can_access,
		'is_completed' => $is_completed,
		'type_label'   => $lesson_type,
		'icon'         => $icon_name,
		'status_class' => '',
	)
);
