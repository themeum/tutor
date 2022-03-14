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

<?php if($youtube_video_id ): ?>
    <div class="course-players-parent">
        <div class="course-players">
            <div class="loading-spinner"></div>
            <?php if (!$disable_default_player_youtube): ?>
                <iframe src="https://www.youtube.com/embed/<?php echo $youtube_video_id; ?>" frameborder="0" allowfullscreen allowtransparency allow="autoplay"></iframe>
            <?php else: ?>
                <div class="plyr__video-embed tutorPlayer">
                    <iframe src="https://www.youtube.com/embed/<?php echo $youtube_video_id; ?>?&amp;iv_load_policy=3&amp;modestbranding=1&amp;playsinline=1&amp;showinfo=0&amp;rel=0&amp;enablejsapi=1" allowfullscreen allowtransparency allow="autoplay"></iframe>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php do_action('tutor_lesson/single/after/video/youtube'); ?>