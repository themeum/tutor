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
$youtube_video_id = dozent_utils()->get_youtube_video_id(dozent_utils()->avalue_dot('source_youtube', $video_info));

do_action('dozent_lesson/single/before/video/youtube');
?>
    <div class="dozent-single-lesson-segment dozent-lesson-video-wrap">

        <div class="plyr__video-embed" id="dozentPlayer">
            <iframe src="https://www.youtube.com/embed/<?php echo $youtube_video_id; ?>?&amp;iv_load_policy=3&amp;modestbranding=1&amp;playsinline=1&amp;showinfo=0&amp;rel=0&amp;enablejsapi=1" allowfullscreen allowtransparency allow="autoplay"></iframe>
        </div>

    </div>
<?php
do_action('dozent_lesson/single/after/video/youtube'); ?>