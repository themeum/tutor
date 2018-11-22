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

$video_info = dozent_utils()->get_video_info();

$poster = dozent_utils()->avalue_dot('poster', $video_info);
$poster_url = $poster ? wp_get_attachment_url($poster) : '';

do_action('dozent_lesson/single/before/video/html5');
?>
	<div class="dozent-single-lesson-segment dozent-lesson-video-wrap">
		<video poster="<?php echo $poster_url; ?>" id="dozentPlayer" playsinline controls >
			<source src="<?php echo dozent_utils()->get_video_stream_url(); ?>" type="<?php echo dozent_utils()->avalue_dot('type', $video_info); ?>">
		</video>
	</div>
<?php
do_action('dozent_lesson/single/after/video/html5'); ?>