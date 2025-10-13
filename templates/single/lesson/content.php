<?php
/**
 * Display the content
 *
 * @package Tutor\Templates
 * @subpackage Single\Lesson
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

use TUTOR\Lesson;
use TUTOR\User;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;
global $previous_id;
global $next_id;

// Get the ID of this content and the corresponding course.
$course_content_id = get_the_ID();
$course_id         = tutor_utils()->get_course_id_by_subcontent( $course_content_id );

$_is_preview = get_post_meta( $course_content_id, '_is_preview', true );
$content_id  = tutor_utils()->get_post_id( $course_content_id );
$contents    = tutor_utils()->get_course_prev_next_contents_by_id( $content_id );
$previous_id = $contents->previous_id;
$next_id     = $contents->next_id;

$prev_is_preview = get_post_meta( $previous_id, '_is_preview', true );
$next_is_preview = get_post_meta( $next_id, '_is_preview', true );
$is_enrolled     = tutor_utils()->is_enrolled( $course_id );
$is_public       = get_post_meta( $course_id, '_tutor_is_public_course', true );

$prev_is_locked = ! ( $is_enrolled || $prev_is_preview || $is_public );
$next_is_locked = ! ( $is_enrolled || $next_is_preview || $is_public );

$json_data                                 = array();
$json_data['post_id']                      = get_the_ID();
$json_data['best_watch_time']              = 0;
$json_data['autoload_next_course_content'] = (bool) get_tutor_option( 'autoload_next_course_content' );

$best_watch_time = tutor_utils()->get_lesson_reading_info( get_the_ID(), 0, 'video_best_watched_time' );
if ( $best_watch_time > 0 ) {
	$json_data['best_watch_time'] = $best_watch_time;
}
?>

<?php do_action( 'tutor_lesson/single/before/content' ); ?>

<?php
tutor_load_template(
	'single.common.header',
	array(
		'course_id'        => $course_id,
		'mark_as_complete' => true,
	)
);
?>

<div class="tutor-course-topic-single-body">
	<!-- Load Lesson Video -->
	<?php
		$video_info = tutor_utils()->get_video_info();
		$source_key = is_object( $video_info ) && 'html5' !== $video_info->source ? 'source_' . $video_info->source : null;
		$has_source = ( is_object( $video_info ) && $video_info->source_video_id ) || ( isset( $source_key ) ? $video_info->$source_key : null );
	?>
	<?php
	if ( $has_source ) :
		$completion_mode                              = tutor_utils()->get_option( 'course_completion_process' );
		$json_data['strict_mode']                     = ( 'strict' === $completion_mode );
		$json_data['control_video_lesson_completion'] = (bool) tutor_utils()->get_option( 'control_video_lesson_completion', false );
		$json_data['required_percentage']             = (int) tutor_utils()->get_option( 'required_percentage_to_complete_video_lesson', 80 );
		$json_data['video_duration']                  = $video_info->duration_sec ?? 0;
		$json_data['lesson_completed']                = tutor_utils()->is_completed_lesson( $content_id, get_current_user_id() ) !== false;
		$json_data['is_enrolled']                     = tutor_utils()->is_enrolled( $course_id, get_current_user_id() ) !== false;
		?>
		<input type="hidden" id="tutor_video_tracking_information" value="<?php echo esc_attr( json_encode( $json_data ) ); ?>">
	<?php endif; ?>
	<div class="tutor-video-player-wrapper">
		<?php echo apply_filters( 'tutor_single_lesson_video', tutor_lesson_video( false ), $video_info, $source_key ); //phpcs:ignore ?>
	</div>

	<?php
	$referer_url        = wp_get_referer();
	$referer_comment_id = explode( '#', filter_input( INPUT_SERVER, 'REQUEST_URI' ) ?? '' );
	$url_components     = parse_url( $referer_url );
	$page_tab           = \TUTOR\Input::get( 'page_tab', 'overview' );

	isset( $url_components['query'] ) ? parse_str( $url_components['query'], $output ) : null;

	$has_lesson_content    = Lesson::has_lesson_content( $course_content_id );
	$has_lesson_attachment = Lesson::has_lesson_attachment( $course_content_id );

	$is_comment_enabled = Lesson::is_comment_enabled();
	$has_lesson_comment = Lesson::has_lesson_comment( $course_content_id );

	$nav_items    = Lesson::get_nav_items( $course_content_id );
	$nav_contents = Lesson::get_nav_contents( $course_content_id );

	$active_tab = $page_tab;
	$valid_tabs = wp_list_pluck( $nav_items, 'value' );
	if ( ! in_array( $active_tab, $valid_tabs, true ) && ! empty( $nav_items ) ) {
		$active_tab = $nav_items[0]['value'];
	}
	?>

	<style>
		.tutor-actual-comment.viewing {
			box-shadow: 0 0 10px #cdcfd5;
			animation: blinkComment 1s infinite;
		}
		@keyframes blinkComment { 50% { box-shadow:0 0 0px #ffffff; }  }
	</style>

	<div class="tutor-course-spotlight-wrapper">
		<?php if ( count( $nav_items ) > 1 ) : ?>
		<ul class="tutor-nav tutor-course-spotlight-nav tutor-justify-center">
			<?php foreach ( $nav_items as $index => $nav_item ) : ?>
				<li class="tutor-nav-item">
					<a 
						href="#" 
						class="tutor-nav-link<?php echo esc_attr( ( $nav_item['value'] === $page_tab || ( 'overview' === $page_tab && 0 === $index ) ) ? ' is-active' : '' ); ?>" 
						data-tutor-nav-target="tutor-course-spotlight-<?php echo esc_attr( $nav_item['value'] ); ?>" 
						data-tutor-query-variable="page_tab" 
						data-tutor-query-value="<?php echo esc_attr( $nav_item['value'] ); ?>"
					>
						<?php
						if ( isset( $nav_item['icon_type'] ) && 'svg' === $nav_item['icon_type'] ) {
							tutor_utils()->render_svg_icon( $nav_item['icon'], 20, 20 );
						} else {
							?>
							<span 
								class="tutor-icon-<?php echo esc_attr( $nav_item['icon'] ); ?> tutor-mr-8" 
								aria-hidden="true">
							</span>
							<?php
						}
						?>
						<span><?php echo esc_html( $nav_item['label'] ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>

			<?php do_action( 'tutor_lesson_single_after_nav_items', $course_content_id, $active_tab ); ?>
		</ul>
		<?php endif; ?>

		<div class="tutor-tab tutor-course-spotlight-tab">
			<?php
			if ( ! empty( $nav_contents ) ) {
				foreach ( $nav_contents as $key => $content ) {
					$is_pro = isset( $content['is_pro'] ) && true === $content['is_pro'];
					tutor_load_template(
						$content['template_path'],
						array(
							'is_active' => $content['value'] === $active_tab,
							'post'      => $post,
							'course_id' => $course_id,
							'lesson_id' => $course_content_id,
						),
						$is_pro
					);
				}
			}
			?>
		</div>
	</div>
</div>

<?php tutor_load_template( 'single.common.footer', array( 'course_id' => $course_id ) ); ?>

<?php do_action( 'tutor_lesson/single/after/content' ); ?>
