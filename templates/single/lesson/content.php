<?php
/**
 * Display the content
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

global $post;
global $previous_id;
global $next_id;

// Get the ID of this content and the corresponding course
$course_content_id = get_the_ID();
$course_id         = tutor_utils()->get_course_id_by_subcontent( $course_content_id );

$_is_preview = get_post_meta( $course_content_id, '_is_preview', true );
$content_id  = tutor_utils()->get_post_id( $course_content_id );
$contents    = tutor_utils()->get_course_prev_next_contents_by_id( $content_id );
$previous_id = $contents->previous_id;
$next_id     = $contents->next_id;

$prev_is_preview = get_post_meta( $previous_id, '_is_preview', true );
$next_is_preview = get_post_meta( $next_id, '_is_preview', true );
$is_enrolled = tutor_utils()->is_enrolled( $course_id );
$is_public = get_post_meta( $course_id, '_tutor_is_public_course', true );

$prev_is_locked = !($is_enrolled || $prev_is_preview || $is_public);
$next_is_locked = !($is_enrolled || $next_is_preview || $is_public);

// Get total content count
$course_stats = tutor_utils()->get_course_completed_percent( $course_id, 0, true );

$jsonData                                 = array();
$jsonData['post_id']                      = get_the_ID();
$jsonData['best_watch_time']              = 0;
$jsonData['autoload_next_course_content'] = (bool) get_tutor_option( 'autoload_next_course_content' );

$best_watch_time = tutor_utils()->get_lesson_reading_info( get_the_ID(), 0, 'video_best_watched_time' );
if ( $best_watch_time > 0 ) {
	$jsonData['best_watch_time'] = $best_watch_time;
}

$is_comment_enabled = tutor_utils()->get_option( 'enable_comment_for_lesson' ) && comments_open();
$is_enrolled        = tutor_utils()->is_enrolled( $course_id );
$course_id = tutor_utils()->get_course_id_by( 'lesson', get_the_ID() );
?>

<?php do_action( 'tutor_lesson/single/before/content' ); ?>

<div class="tutor-course-topic-single-header tutor-d-flex tutor-single-page-top-bar">
	<a href="#" class="tutor-iconic-btn tutor-iconic-btn-secondary tutor-d-none tutor-d-xl-inline-flex" tutor-course-topics-sidebar-toggler>
		<span class="tutor-icon-left" area-hidden="true"></span>
	</a>

	<a href="<?php echo get_the_permalink( $course_id ); ?>" class="tutor-iconic-btn tutor-d-flex tutor-d-xl-none">
		<span class="tutor-icon-previous" area-hidden="true"></span>
	</a>

	<div class="tutor-course-topic-single-header-title tutor-d-flex tutor-align-center tutor-ml-12 tutor-ml-xl-24">
		<span class="tutor-course-topic-single-header-icon tutor-icon-brand-youtube-bold tutor-mr-12 tutor-d-none tutor-d-xl-block" area-hidden="true"></span>
		<span class="tutor-fs-6">
			<?php
				esc_html_e( 'Lesson: ', 'tutor' );
				the_title();
			?>
		</span>
	</div>

	<div class="tutor-ml-auto tutor-align-center tutor-d-none tutor-d-xl-flex">
		<?php if ( $is_enrolled ) : ?>
			<?php do_action( 'tutor_course/single/enrolled/before/lead_info/progress_bar' ); ?>
			<div class="tutor-fs-7 tutor-mr-20">
				<?php if ( true == get_tutor_option( 'enable_course_progress_bar' ) ) : ?>
					<span class="tutor-progress-content tutor-color-primary-60">
						<?php _e( 'Your Progress:', 'tutor' ); ?>
					</span>
					<span class="tutor-fs-7 tutor-fw-bold">
						<?php echo $course_stats['completed_count']; ?>
					</span>
					<?php _e( 'of ', 'tutor' ); ?>
					<span class="tutor-fs-7 tutor-fw-bold">
						<?php echo $course_stats['total_count']; ?>
					</span>
					(<?php echo $course_stats['completed_percent'] . '%'; ?>)
				<?php endif; ?>
			</div>
			<?php do_action( 'tutor_course/single/enrolled/after/lead_info/progress_bar' ); ?>
			<?php tutor_lesson_mark_complete_html(); ?>
		<?php endif; ?>

		<a class="tutor-iconic-btn" href="<?php echo get_the_permalink( $course_id ); ?>">
			<span class="tutor-icon-times" area-hidden="true"></span>
		</a>
	</div>

	<div class="tutor-ml-auto tutor-align-center tutor-d-block tutor-d-xl-none">
		<a class="tutor-iconic-btn" href="#">
			<span class="tutor-icon-hamburger-menu" area-hidden="true"></span>
		</a>
	</div>
</div>

<div class="tutor-course-topic-single-body">
	<!-- Load Lesson Video -->
	<?php
		$video_info = tutor_utils()->get_video_info();
		$source_key = is_object($video_info) && 'html5' !== $video_info->source ? 'source_'.$video_info->source : null;
		$has_source = (is_object($video_info) && $video_info->source_video_id) || (isset($source_key) ? $video_info->$source_key : null);
	?>
	<?php if ($has_source) : ?>
		<input type="hidden" id="tutor_video_tracking_information" value="<?php echo esc_attr( json_encode( $jsonData ) ); ?>">
		<div class="tutor-video-player-wrapper">
			<?php tutor_lesson_video(); ?>
		</div>
	<?php endif; ?>

	<?php
	$referer_url        = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '';
	$referer_comment_id = explode( '#', $_SERVER['REQUEST_URI'] );
	$url_components     = parse_url( $referer_url );
	isset( $url_components['query'] ) ? parse_str( $url_components['query'], $output ) : null;
	$page_tab = isset( $_GET['page_tab'] ) ? esc_attr( $_GET['page_tab'] ) : ( isset( $output['page_tab'] ) ? $output['page_tab'] : null );
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
			<li class="tutor-nav-item">
				<a href="#" class="tutor-nav-link<?php echo ( ! isset( $page_tab ) || 'overview' == $page_tab ) ? ' is-active' : ''; ?>" data-tutor-nav-target="tutor-course-spotlight-overview" data-tutor-query-variable="page_tab" data-tutor-query-value="overview">
					<span class="tutor-icon-document-text tutor-mr-8" area-hidden="true"></span>
					<span><?php _e( 'Overview', 'tutor' ); ?></span>
				</a>
			</li>

			<li class="tutor-nav-item">
				<a href="#" class="tutor-nav-link<?php echo 'files' == $page_tab ? ' is-active' : ''; ?>" data-tutor-nav-target="tutor-course-spotlight-files" data-tutor-query-variable="page_tab" data-tutor-query-value="files">
					<span class="tutor-icon-paperclip tutor-mr-8" area-hidden="true"></span>
					<span><?php _e( 'Exercise Files', 'tutor' ); ?></span>
				</a>
			</li>

			<?php if ( $is_comment_enabled ) : ?>
			<li class="tutor-nav-item">
				<a href="#" class="tutor-nav-link<?php echo 'comments' == $page_tab ? ' is-active' : ''; ?>" data-tutor-nav-target="tutor-course-spotlight-comments" data-tutor-query-variable="page_tab" data-tutor-query-value="comments">
					<span class="tutor-icon-comment tutor-mr-8" area-hidden="true"></span>
					<span><?php _e( 'Comments', 'tutor' ); ?></span>
				</a>
			</li>
			<?php endif; ?>
		</ul>

		<div class="tutor-tab tutor-course-spotlight-tab">
			<div id="tutor-course-spotlight-overview" class="tutor-tab-item<?php echo ( ! isset( $page_tab ) || 'overview' == $page_tab ) ? ' is-active' : ''; ?>">
				<div class="tutor-container">
					<div class="tutor-row tutor-justify-center">
						<div class="tutor-col-xl-8">
							<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-12">
								<?php _e( 'About Lesson', 'tutor' ); ?>
							</div>
							<div class="tutor-fs-6 tutor-color-secondary">
								<?php the_content(); ?>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div id="tutor-course-spotlight-files" class="tutor-tab-item<?php echo 'files' == $page_tab ? ' is-active' : ''; ?>">
				<div class="tutor-container">
					<div class="tutor-row tutor-justify-center">
						<div class="tutor-col-xl-8">
							<div class="tutor-fs-5 tutor-fw-medium tutor-color-black"><?php _e( 'Exercise Files', 'tutor' ); ?></div>
							<?php get_tutor_posts_attachments(); ?>
						</div>
					</div>
				</div>
			</div>
			
			<?php if ( $is_comment_enabled ) : ?>
			<div id="tutor-course-spotlight-comments" class="tutor-tab-item<?php echo 'comments' == $page_tab ? ' is-active' : ''; ?>">
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


<?php if( $next_id || $previous_id ): ?>
<div class="tutor-course-topic-single-footer tutor-px-32 tutor-py-12">
	<?php
		$prev_link = $prev_is_locked || !$previous_id ? '#' : get_the_permalink( $previous_id );
		$next_link = $next_is_locked || !$next_id ? '#' : get_the_permalink( $next_id );
	?>
	<div class="tutor-single-course-content-prev">
		<a class="tutor-btn tutor-btn-secondary tutor-btn-sm" href="<?php echo $prev_link; ?>"<?php echo !$previous_id ? ' disabled="disabled"' : ""; ?>>
			<span class="tutor-icon-previous" area-hidden="true"></span>
			<span class="tutor-ml-8"><?php _e("Previous", "tutor"); ?></span>
		</a>
	</div>

	<div class="tutor-single-course-content-next">
		<a class="tutor-btn tutor-btn-secondary tutor-btn-sm" href="<?php echo $next_link; ?>"<?php echo !$next_id ? ' disabled="disabled"' : ""; ?>>
			<span class="tutor-mr-8"><?php _e("Next", "tutor"); ?></span>
			<span class="tutor-icon-next" area-hidden="true"></span>
		</a>
	</div>
</div>
<?php endif; ?>

<?php do_action( 'tutor_lesson/single/after/content' ); ?>
