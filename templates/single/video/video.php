<?php

/**
 * Display Video
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

$video_info = lms_utils()->get_video_info();

do_action('lms_lesson/single/before/video');
if ($video_info){
    lms_load_template('single.video.'.$video_info->source);
}
do_action('lms_lesson/single/after/video'); ?>