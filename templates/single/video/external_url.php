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
$poster = tutor_utils()->avalue_dot('poster', $video_info);
$poster_url = $poster ? wp_get_attachment_url($poster) : '';

do_action( 'tutor_lesson/single/before/video/external_url' );
?>

<?php if($video_info ): ?>
    <div class="course-players-parent">
        <div class="course-players">
            <div class="loading-spinner"></div>
            <input type="hidden" id="tutor_video_tracking_information" value="<?php echo esc_attr(json_encode($jsonData??null)); ?>">

            <video poster="<?php echo $poster_url; ?>" class="tutorPlayer" playsinline controls >
                <source src="<?php echo tutor_utils()->array_get('source_external_url', $video_info); ?>" type="<?php echo tutor_utils()->avalue_dot('type', $video_info); ?>">
            </video>
        </div>
    </div>
<?php endif; ?>

<?php do_action( 'tutor_lesson/single/after/video/external_url' ); ?>
