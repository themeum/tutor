<?php

/**
 * Display Video HTML5
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

$video_info = lms_utils()->get_video_info();

$poster = lms_utils()->avalue_dot('poster', $video_info);
$poster_url = $poster ? wp_get_attachment_url($poster) : '';

do_action('lms_lesson/single/before/video/html5');
?>
	<div class="lms-single-lesson-segment lms-lesson-video-wrap">
		<video poster="<?php echo $poster_url; ?>" id="lmsPlayer" playsinline controls >
			<source src="<?php echo lms_utils()->get_video_stream_url(); ?>" type="<?php echo lms_utils()->avalue_dot('type', $video_info); ?>">
		</video>
	</div>
<?php
do_action('lms_lesson/single/after/video/html5'); ?>