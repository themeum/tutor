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
$youtube_video_id               = tutor_utils()->get_youtube_video_id( tutor_utils()->avalue_dot( 'source_youtube', $video_info ) );

do_action( 'tutor_lesson/single/before/video/youtube' );
?>

<?php if ( $youtube_video_id ) : ?>
	<div class="tutor-video-player">
		<div class="loading-spinner" area-hidden="true"></div>
		<div class="<?php echo $disable_default_player_youtube ? 'plyr__video-embed tutorPlayer' : 'tutor-ratio tutor-ratio-16x9'; ?>">
			<?php if ( ! $disable_default_player_youtube ) : ?>
				<iframe src="https://www.youtube.com/embed/<?php echo esc_attr( $youtube_video_id ); ?>" frameborder="0" allowfullscreen allowtransparency allow="autoplay"></iframe>
			<?php else : ?>
				<iframe src="https://www.youtube.com/embed/<?php echo esc_attr( $youtube_video_id ); ?>?&amp;iv_load_policy=3&amp;modestbranding=1&amp;playsinline=1&amp;showinfo=0&amp;rel=0&amp;enablejsapi=1" allowfullscreen allowtransparency allow="autoplay"></iframe>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>

<?php do_action( 'tutor_lesson/single/after/video/youtube' ); ?>
