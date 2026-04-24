<?php
/**
 * Tutor learning area quiz attempt details wrapper.
 *
 * @package Tutor\Templates
 * @subpackage LearningArea
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

global $tutor_current_post;

tutor_load_template(
	'shared.components.quiz.attempt-details',
	array(
		'quiz_id'          => (int) ( $tutor_current_post->ID ?? 0 ),
		'user_id'          => get_current_user_id(),
		'back_url'         => get_permalink( $tutor_current_post->ID ?? 0 ),
		'is_learning_area' => true,
	)
);
