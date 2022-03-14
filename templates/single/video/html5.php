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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$video_info = tutor_utils()->get_video_info();

$poster     = tutor_utils()->avalue_dot( 'poster', $video_info );
$poster_url = $poster ? wp_get_attachment_url( $poster ) : '';
$video_url = ($video_info && $video_info->source_video_id) ? wp_get_attachment_url($video_info->source_video_id) : null;

do_action( 'tutor_lesson/single/before/video/html5' );
?>

<?php if($video_url): ?>
    <div class="course-players-parent">
        <div class="course-players">
            <div class="loading-spinner"></div>
            <input type="hidden" id="tutor_video_tracking_information" value="<?php echo esc_attr(json_encode($jsonData??null)); ?>">

            <video poster="<?php echo $poster_url; ?>" class="tutorPlayer" playsinline controls >
                <source src="<?php echo $video_url; ?>" type="<?php echo tutor_utils()->avalue_dot('type', $video_info); ?>">
            </video>
        </div>
    </div>
<?php endif; ?>

<?php do_action( 'tutor_lesson/single/after/video/html5' ); ?>
