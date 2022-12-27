<?php
/**
 * Display Vimeo Video
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

$disable_default_player_vimeo = tutor_utils()->get_option( 'disable_default_player_vimeo' );

$video_info = tutor_utils()->get_video_info();
$video_url  = tutor_utils()->avalue_dot( 'source_vimeo', $video_info );
$video_id   = '';
if ( preg_match( '%^https?:\/\/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)(?:[?]?.*)$%im', $video_url, $match ) ) {
	if ( isset( $match[3] ) ) {
		$video_id = $match[3];
	}
}

do_action( 'tutor_lesson/single/before/video/vimeo' );
?>

<?php if ( $video_id ) : ?>
	<div class="tutor-video-player">
		<div class="loading-spinner" area-hidden="true"></div>
		<div class="<?php echo $disable_default_player_vimeo ? 'plyr__video-embed tutorPlayer' : 'tutor-ratio tutor-ratio-16x9'; ?>">
			<?php if ( ! $disable_default_player_vimeo ) : ?>
				<iframe src="https://player.vimeo.com/video/<?php echo esc_attr( $video_id ); ?>" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>
			<?php else : ?>
				<iframe src="https://player.vimeo.com/video/<?php echo esc_attr( $video_id ); ?>?loop=false&amp;byline=false&amp;portrait=false&amp;title=false&amp;speed=true&amp;transparent=0&amp;gesture=media" allowfullscreen allowtransparency allow="autoplay"></iframe>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>

<?php do_action( 'tutor_lesson/single/after/video/vimeo' ); ?>
