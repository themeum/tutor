<?php
/**
 * Display YouTube Video
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

$disable_default_player_youtube = tutor_utils()->get_option( 'disable_default_player_youtube' );
$video_info                     = tutor_utils()->get_video_info();
$video_info                     = $video_info ? (array) $video_info : array();
$source_youtube                 = (string) tutor_utils()->avalue_dot( 'source_youtube', $video_info );
$youtube_video_id               = tutor_utils()->get_youtube_video_id( $source_youtube );

// The adapter parses watch/share/embed URLs itself; keep the user's params intact (ADR-0010).
$tutor_player_youtube_url = $source_youtube;

do_action( 'tutor_lesson/single/before/video/youtube' );
?>

<?php if ( $youtube_video_id ) : ?>
	<div class="tutor-video-player">
		<div class="loading-spinner" aria-hidden="true"></div>
		<?php if ( ! $disable_default_player_youtube ) : ?>
			<div class="tutor-ratio tutor-ratio-16x9">
				<iframe src="https://www.youtube.com/embed/<?php echo esc_attr( $youtube_video_id ); ?>" frameborder="0" allowfullscreen allowtransparency allow="autoplay"></iframe>
			</div>
		<?php else : ?>
			<tutor-video-player>
				<video-skin>
					<youtube-video src="<?php echo esc_url( $tutor_player_youtube_url ); ?>"></youtube-video>
				</video-skin>
			</tutor-video-player>
		<?php endif; ?>
	</div>
<?php endif; ?>

<?php do_action( 'tutor_lesson/single/after/video/youtube' ); ?>
