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
global $jsonData;
$course_content_id = '';
$content_id = tutor_utils()->get_post_id($course_content_id);
$contents = tutor_utils()->get_course_prev_next_contents_by_id($content_id);
$previous_id = $contents->previous_id;
$next_id = $contents->next_id;
$disable_default_player_youtube = tutor_utils()->get_option('disable_default_player_youtube');
$video_info = tutor_utils()->get_video_info();
$youtube_video_id = tutor_utils()->get_youtube_video_id(tutor_utils()->avalue_dot('source_youtube', $video_info));

do_action('tutor_lesson/single/before/video/youtube');
?>

<?php if($youtube_video_id ) { ?>
<div class="course-players flex-center">
    <input type="hidden" id="tutor_video_tracking_information" value="<?php echo esc_attr(json_encode($jsonData)); ?>">
	
    <?php
        if (!$disable_default_player_youtube){
    ?>
        <iframe src="https://www.youtube.com/embed/<?php echo $youtube_video_id; ?>" frameborder="0" allowfullscreen allowtransparency allow="autoplay"></iframe>
    <?php 
        } else { 
    ?>
        <iframe src="https://www.youtube.com/embed/<?php echo $youtube_video_id; ?>?&amp;iv_load_policy=3&amp;modestbranding=1&amp;playsinline=1&amp;showinfo=0&amp;rel=0&amp;enablejsapi=1" allowfullscreen allowtransparency allow="autoplay"></iframe>
    <?php } ?>

    <?php
        if($previous_id){ 
    ?>
    <div class="tutor-lesson-prev flex-center">
        <a href="<?php echo get_the_permalink($previous_id); ?>">
            <span class="ttr-angle-left-filled"></span>
        </a>
    </div>
    <?php } ?>

    <?php
        if($next_id){ 
    ?>
    <div class="tutor-lesson-next flex-center">
        <a href="<?php echo get_the_permalink($next_id); ?>">
            <span class="ttr-angle-right-filled"></span>
        </a>
    </div>
    <?php } ?>
</div>
<?php } ?>
<?php
do_action('tutor_lesson/single/after/video/youtube'); ?>