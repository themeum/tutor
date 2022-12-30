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

do_action( 'tutor_lesson/single/before/video/embedded' );
?>
<?php if ( $video_info ) : ?>
	<div class="tutor-video-player">
		<input type="hidden" id="tutor_video_tracking_information" value="<?php echo esc_attr( json_encode( $jsonData ?? null ) ); ?>">
		<div class="loading-spinner" area-hidden="true"></div>
		<div class="tutor-ratio tutor-ratio-16x9">
			<?php
			echo wp_kses(
				tutor_utils()->array_get( 'source_embedded', $video_info ),
				array(
					'iframe' => array(
						'src'             => true,
						'title'           => true,
						'height'          => true,
						'width'           => true,
						'frameborder'     => true,
						'allowfullscreen' => true,
						'allow'           => true,
						'style'           => true,
					),
				)
			);
			?>
		</div>
	</div>
<?php endif; ?>

<?php do_action( 'tutor_lesson/single/after/video/embedded' ); ?>
