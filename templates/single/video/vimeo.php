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
$video_info = $video_info ? (array) $video_info : array();
$video_url  = tutor_utils()->avalue_dot( 'source_vimeo', $video_info );
$video_id   = '';
$video_hash = '';

if ( preg_match( '%^https?:\/\/(?:www\.|player\.)?vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/[^\/]*\/videos\/|album\/\d+\/video\/|video\/)?(\d+)(?:\/([a-zA-Z0-9]+))?(?:$|\/|\?)(?:[?]?.*)?$%im', $video_url, $match ) ) {
	$video_id   = $match[1] ?? '';
	$video_hash = $match[2] ?? '';
}

$embed_url = "https://player.vimeo.com/video/{$video_id}";

if ( $video_hash ) {
	$embed_url = add_query_arg( 'h', $video_hash, $embed_url );
}

$tutor_player_embed_url = add_query_arg(
	array(
		'loop'        => 'false',
		'byline'      => 'false',
		'portrait'    => 'false',
		'title'       => 'false',
		'speed'       => 'true',
		'transparent' => '0',
		'gesture'     => 'media',
	),
	$embed_url
);

do_action( 'tutor_lesson/single/before/video/vimeo' );
?>

<?php if ( $video_id ) : ?>
	<div class="tutor-video-player">
		<div class="loading-spinner" aria-hidden="true"></div>
		<div class="<?php echo $disable_default_player_vimeo ? 'plyr__video-embed tutorPlayer' : 'tutor-ratio tutor-ratio-16x9'; ?>">
			<?php if ( ! $disable_default_player_vimeo ) : ?>
				<iframe src="<?php echo esc_url( $embed_url ); ?>" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>
			<?php else : ?>
				<iframe src="<?php echo esc_url( $tutor_player_embed_url ); ?>" allowfullscreen allowtransparency allow="autoplay"></iframe>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>

<?php do_action( 'tutor_lesson/single/after/video/vimeo' ); ?>
