<?php
/**
 * Display Video
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

$video_info = tutor_utils()->get_video_info();

do_action('tutor_lesson/single/before/video');

$source_key = is_object($video_info) && 'html5' !== $video_info->source ? 'source_'.$video_info->source : null;

$has_source = (is_object($video_info) && $video_info->source_video_id) || (isset($source_key) ? $video_info->$source_key : null);

if ($has_source){
    tutor_load_template('single.video.'.$video_info->source);
} else {
    $feature_image = get_post_meta( get_the_ID(  ), '_thumbnail_id', true );
    $url = $feature_image ? wp_get_attachment_url( $feature_image ) : null;

    if($url) {
        echo '<div class="tutor-lesson-feature-image">
                <img src="'.$url.'" />
            </div>';
    }
}
do_action('tutor_lesson/single/after/video'); ?>