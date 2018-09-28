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
$youtube_video_id = lms_utils()->get_youtube_video_id(lms_utils()->avalue_dot('source_youtube', $video_info));

do_action('lms_lesson/single/before/video/youtube');
?>
    <div class="lms-single-lesson-segment lms-lesson-video-wrap">

        <div class="plyr__video-embed" id="lmsPlayer">
            <iframe src="https://www.youtube.com/embed/<?php echo $youtube_video_id; ?>?&amp;iv_load_policy=3&amp;modestbranding=1&amp;playsinline=1&amp;showinfo=0&amp;rel=0&amp;enablejsapi=1" allowfullscreen allowtransparency allow="autoplay"></iframe>
        </div>

    </div>
<?php
do_action('lms_lesson/single/after/video/youtube'); ?>