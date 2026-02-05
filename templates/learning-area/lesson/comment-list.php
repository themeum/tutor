<?php
/**
 * Lesson Comment List Template.
 *
 * @package Tutor\Templates
 * @subpackage Single\Lesson
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $comment_list ) ) {
	return;
}

foreach ( $comment_list as $comment_item ) {
	tutor_load_template(
		'learning-area.lesson.comment-card',
		array(
			'comment_item' => $comment_item,
			'lesson_id'    => $lesson_id,
			'user_id'      => $user_id,
		)
	);
}
