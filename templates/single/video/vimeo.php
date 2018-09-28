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
$video_id = lms_utils()->get_vimeo_video_id(lms_utils()->avalue_dot('source_vimeo', $video_info));

do_action('lms_lesson/single/before/video/vimeo');
?>
    <div class="lms-single-lesson-segment lms-lesson-video-wrap">
        <div class="plyr__video-embed" id="lmsPlayer">
            <iframe src="https://player.vimeo.com/video/<?php echo $video_id; ?>?loop=false&amp;byline=false&amp;portrait=false&amp;title=false&amp;speed=true&amp;transparent=0&amp;gesture=media" allowfullscreen allowtransparency allow="autoplay"></iframe>
        </div>
    </div>
<?php
do_action('lms_lesson/single/after/video/vimeo'); ?>