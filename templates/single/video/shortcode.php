<?php
/**
 * Display shortcode
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

do_action( 'tutor_lesson/single/before/video/shortcode' );
?>
	<div class="tutor-video-player">
		<div class="loading-spinner" area-hidden="true"></div>
		<div class="tutor-ratio tutor-ratio-16x9">
			<?php echo do_shortcode( tutor_utils()->array_get( 'source_shortcode', $video_info ) ); ?>
		</div>
	</div>
<?php
do_action( 'tutor_lesson/single/after/video/shortcode' ); ?>
