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

global $previous_id;
global $next_id;


$course_content_id = get_the_ID();
$course_id = tutor_utils()->get_course_id_by_subcontent($course_content_id);

$content_id = tutor_utils()->get_post_id($course_content_id);
$contents = tutor_utils()->get_course_prev_next_contents_by_id($content_id);
$previous_id = $contents->previous_id;
$next_id = $contents->next_id;

$disable_default_player_vimeo = tutor_utils()->get_option('disable_default_player_vimeo');

$video_info = tutor_utils()->get_video_info();
$video_url = tutor_utils()->avalue_dot('source_vimeo', $video_info);
$video_id = '';
if ( preg_match('%^https?:\/\/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)(?:[?]?.*)$%im', $video_url, $match ) ) {
    if ( isset( $match[3] ) ) {
        $video_id = $match[3];
    }
}

do_action('tutor_lesson/single/before/video/vimeo');
?>

<?php if($video_id ): ?>
    <div class="course-players ">
        <?php if (!$disable_default_player_vimeo): ?>
            <iframe src="https://player.vimeo.com/video/<?php echo $video_id; ?>" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>
        <?php else: ?>
            <div class="plyr__video-embed tutorPlayer">
                <iframe src="https://player.vimeo.com/video/<?php echo $video_id; ?>?loop=false&amp;byline=false&amp;portrait=false&amp;title=false&amp;speed=true&amp;transparent=0&amp;gesture=media" allowfullscreen allowtransparency allow="autoplay"></iframe>
            </div>
        <?php endif; ?>
        <?php require __DIR__ . '/video-navigation.php'; ?>
    </div>
<?php endif ?>

<?php do_action('tutor_lesson/single/after/video/vimeo'); ?>