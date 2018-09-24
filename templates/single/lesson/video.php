<?php

/**
 * Display Video
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

$video = lms_utils()->get_video();
$video_info = lms_utils()->get_video_info();

$poster = lms_utils()->avalue_dot('poster', $video);
$poster_url = $poster ? wp_get_attachment_url($poster) : '';
?>

<?php do_action('lms_lesson/single/before/video'); ?>

<?php
if ($video){
	?>
    <div class="lms-single-lesson-segment lms-lesson-video-wrap">
        <video poster="<?php echo $poster_url; ?>" id="lmsPlayer" playsinline controls>
            <source src="<?php echo lms_utils()->get_video_stream_url(); ?>" type="<?php echo lms_utils()->avalue_dot('type', $video_info); ?>">
        </video>
    </div>
<?php } ?>

<?php do_action('lms_lesson/single/after/video'); ?>