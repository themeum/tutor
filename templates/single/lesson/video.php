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

$videoSource = lms_utils()->avalue_dot('source', $video);
$sourceVideoID = lms_utils()->avalue_dot('source_self_hosted', $video);

$runtimeHours = lms_utils()->avalue_dot('runtime.hours', $video);
$runtimeMinutes = lms_utils()->avalue_dot('runtime.minutes', $video);
$runtimeSeconds = lms_utils()->avalue_dot('runtime.seconds', $video);
$poster = lms_utils()->avalue_dot('poster', $video);

$poster_url = $poster ? wp_get_attachment_url($poster) : '';
?>

<?php do_action('lms_lesson/single/before/video'); ?>

<?php
if ($video){
	?>
    <div class="lms-single-lesson-segment lms-lesson-video-wrap">
        <video poster="<?php echo $poster_url; ?>" id="lmsPlayer" playsinline controls>
            <source src="<?php echo wp_get_attachment_url($sourceVideoID); ?>" type="video/mp4">
            <!--<source src="/path/to/video.webm" type="video/webm">-->
        </video>
    </div>
<?php } ?>

<?php do_action('lms_lesson/single/after/video'); ?>