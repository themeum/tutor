<?php
/**
 * Display Video HTML5
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

$disable_default_player_youtube = tutor_utils()->get_option('disable_default_player_youtube');
$video_info = tutor_utils()->get_video_info();
$youtube_video_id = tutor_utils()->get_youtube_video_id(tutor_utils()->avalue_dot('source_youtube', $video_info));

do_action('tutor_lesson/single/before/video/youtube');
?>
    <iframe src="https://www.youtube.com/embed/<?php echo $youtube_video_id; ?>" title="YouTube video player" frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen></iframe>
<?php
do_action('tutor_lesson/single/after/video/youtube'); ?>