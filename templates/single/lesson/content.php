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

$is_comment_enabled = tutor_utils()->get_option( 'enable_comment_for_lesson' ) && comments_open();

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
	<?php if ( $has_source ) : ?>
		<input type="hidden" id="tutor_video_tracking_information" value="<?php echo esc_attr( json_encode( $json_data ) ); ?>">
	<?php endif; ?>
	<div class="tutor-video-player-wrapper">
		<?php echo apply_filters( 'tutor_single_lesson_video', tutor_lesson_video( false ), $video_info, $source_key ); //phpcs:ignore ?>
	</div>

	<?php
	$referer_url        = wp_get_referer();
	$referer_comment_id = explode( '#', filter_input( INPUT_SERVER, 'REQUEST_URI' ) );
	$url_components     = parse_url( $referer_url );
	$page_tab           = \TUTOR\Input::get( 'page_tab', 'overview' );

	isset( $url_components['query'] ) ? parse_str( $url_components['query'], $output ) : null;

	$has_lesson_content    = ! in_array( trim( get_the_content() ), array( null, '', '&nbsp;' ) );
	$has_lesson_attachment = count( tutor_utils()->get_attachments() ) > 0;
	$has_lesson_comment    = (int) get_comments_number( $course_content_id );
	?>

	<style>
		.tutor-actual-comment.viewing {
			box-shadow: 0 0 10px #cdcfd5;
			animation: blinkComment 1s infinite;
		}
		@keyframes blinkComment { 50% { box-shadow:0 0 0px #ffffff; }  }
	</style>

	<div class="tutor-course-spotlight-wrapper">
		<ul class="tutor-nav tutor-course-spotlight-nav tutor-justify-center">
			<?php if ( $has_lesson_content && ( $has_lesson_attachment || $is_comment_enabled ) ) : ?>
			<li class="tutor-nav-item">
				<a href="#" class="tutor-nav-link<?php echo 'overview' == $page_tab ? ' is-active' : ''; ?>" data-tutor-nav-target="tutor-course-spotlight-overview" data-tutor-query-variable="page_tab" data-tutor-query-value="overview">
					<span class="tutor-icon-document-text tutor-mr-8" area-hidden="true"></span>
					<span><?php esc_html_e( 'Overview', 'tutor' ); ?></span>
				</a>
			</li>
			<?php endif; ?>
			
			<?php if ( $has_lesson_attachment && ( $has_lesson_content || $is_comment_enabled ) ) : ?>
			<li class="tutor-nav-item">
				<a href="#" class="tutor-nav-link<?php echo ( 'files' == $page_tab || false === $has_lesson_content ) ? ' is-active' : ''; ?>" data-tutor-nav-target="tutor-course-spotlight-files" data-tutor-query-variable="page_tab" data-tutor-query-value="files">
					<span class="tutor-icon-paperclip tutor-mr-8" area-hidden="true"></span>
					<span><?php esc_html_e( 'Exercise Files', 'tutor' ); ?></span>
				</a>
			</li>
			<?php endif; ?>

			<?php if ( $is_comment_enabled && ( $has_lesson_content || $has_lesson_attachment ) ) : ?>
			<li class="tutor-nav-item">
				<a  href="#" 
					class="tutor-nav-link<?php echo ( 'comments' == $page_tab || ( false === $has_lesson_content && false === $has_lesson_attachment ) ) ? ' is-active' : ''; ?>" 
					data-tutor-nav-target="tutor-course-spotlight-comments" data-tutor-query-variable="page_tab" 
					data-tutor-query-value="comments">
					
					<span class="tutor-icon-comment tutor-mr-8" area-hidden="true"></span>
					<span><?php esc_html_e( 'Comments', 'tutor' ); ?></span>
				</a>
			</li>
			<?php endif; ?>
		</ul>

		<div class="tutor-tab tutor-course-spotlight-tab">
			<?php if ( $has_lesson_content ) : ?>
			<div id="tutor-course-spotlight-overview" class="tutor-tab-item<?php echo 'overview' == $page_tab ? esc_attr( ' is-active' ) : esc_attr( '' ); ?>">
				<div class="tutor-container">
					<div class="tutor-row tutor-justify-center">
						<div class="tutor-col-xl-8">
							<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-12">
								<?php esc_html_e( 'About Lesson', 'tutor' ); ?>
							</div>
							<div class="tutor-fs-6 tutor-color-secondary tutor-lesson-wrapper">
								<?php the_content(); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ( $has_lesson_attachment ) : ?>
			<div id="tutor-course-spotlight-files" class="tutor-tab-item<?php echo esc_attr( ( 'files' == $page_tab || false === $has_lesson_content ) ? ' is-active' : '' ); ?>">
				<div class="tutor-container">
					<div class="tutor-row tutor-justify-center">
						<div class="tutor-col-xl-8">
							<div class="tutor-fs-5 tutor-fw-medium tutor-color-black"><?php esc_html_e( 'Exercise Files', 'tutor' ); ?></div>
							<?php get_tutor_posts_attachments(); ?>
						</div>
					</div>
				</div>
			</div>
			<?php endif; ?>
			
			<?php if ( $is_comment_enabled ) : ?>
			<div id="tutor-course-spotlight-comments" class="tutor-tab-item<?php echo esc_attr( ( 'comments' == $page_tab || ( false === $has_lesson_content && false === $has_lesson_attachment ) ) ? ' is-active' : '' ); ?>">
				<div class="tutor-container">
					<div class="tutor-course-spotlight-comments">
						<?php require __DIR__ . '/comment.php'; ?>
					</div>
				</div>
			</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<?php tutor_load_template( 'single.common.footer', array( 'course_id' => $course_id ) ); ?>

<?php do_action( 'tutor_lesson/single/after/content' ); ?>
