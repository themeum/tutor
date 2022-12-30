<?php
/**
 * Display Video HTML5
 *
 * @package Tutor\Templates
 * @subpackage Single\Video
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$video_info = tutor_utils()->get_video_info();

$poster     = tutor_utils()->avalue_dot( 'poster', $video_info );
$poster_url = $poster ? wp_get_attachment_url( $poster ) : '';
$video_url  = ( $video_info && $video_info->source_video_id ) ? wp_get_attachment_url( $video_info->source_video_id ) : null;

do_action( 'tutor_lesson/single/before/video/html5' );
?>

<?php if ( $video_url ) : ?>
	<div class="tutor-video-player">
		<input type="hidden" id="tutor_video_tracking_information" value="<?php echo esc_attr( json_encode( $jsonData ?? null ) ); ?>">
		<div class="loading-spinner" area-hidden="true"></div>
		<video poster="<?php echo esc_url( $poster_url ); ?>" class="tutorPlayer" playsinline controls >
			<source src="<?php echo esc_url( $video_url ); ?>" type="<?php echo esc_attr( tutor_utils()->avalue_dot( 'type', $video_info ) ); ?>">
		</video>
	</div>
<?php endif; ?>

<?php do_action( 'tutor_lesson/single/after/video/html5' ); ?>
