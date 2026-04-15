<?php
/**
 * Show quiz nav item on the learning area
 *
 * @package Tutor\Templates
 * @subpackage LearningArea
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Quiz;

global $tutor_current_content_id;

$quiz       = $quiz ?? null;
$can_access = $can_access ?? false;

if ( ! $quiz && ! is_a( $quiz, 'WP_Post' ) ) {
	return;
}

Quiz::render_sidebar_nav( $quiz, $can_access, $tutor_current_content_id );
