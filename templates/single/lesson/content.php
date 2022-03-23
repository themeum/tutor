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
?>

<?php do_action( 'tutor_lesson/single/before/content' ); ?>
<?php if ( $is_enrolled ) : ?>
	<div class="tutor-single-page-top-bar tutor-d-flex tutor-justify-content-between">
		<div class="tutor-topbar-left-item tutor-d-flex">
			<div class="tutor-topbar-item tutor-topbar-sidebar-toggle tutor-hide-sidebar-bar flex-center tutor-d-none tutor-d-xl-flex">
				<a href="javascript:;" class="tutor-lesson-sidebar-hide-bar">
					<span class="tutor-icon-icon-light-left-line tutor-color-white flex-center"></span>
				</a>
			</div>
			<div class="tutor-topbar-item tutor-topbar-content-title-wrap flex-center">
				<span class="tutor-icon-youtube-brand tutor-icon-24 tutor-color-white tutor-mr-4"></span>
				<span class="tutor-fs-7 tutor-color-design-white">
					<?php
						esc_html_e( 'Lesson: ', 'tutor' );
						the_title();
					?>
				</span>
			</div>
		</div>
		<div class="tutor-topbar-right-item tutor-d-flex">
			<div class="tutor-topbar-assignment-details tutor-d-flex tutor-align-items-center">
				<?php
					do_action( 'tutor_course/single/enrolled/before/lead_info/progress_bar' );
				?>
				<div class="tutor-fs-7 tutor-color-design-white">
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
				<?php
					do_action( 'tutor_course/single/enrolled/after/lead_info/progress_bar' );
				?>
				<!-- <div class="tutor-topbar-complete-btn tutor-ml-24"> -->
					<?php tutor_lesson_mark_complete_html(); ?>
				<!-- </div> -->
			</div>
			<div class="tutor-topbar-cross-icon tutor-ml-16 flex-center">
				<?php $course_id = tutor_utils()->get_course_id_by( 'lesson', get_the_ID() ); ?>
				<a href="<?php echo get_the_permalink( $course_id ); ?>">
					<span class="tutor-icon-line-cross-line tutor-color-white flex-center"></span>
				</a>
			</div>
		</div>
	</div>
	<div class="tutor-mobile-top-navigation tutor-d-block tutor-d-sm-none tutor-my-20 tutor-mx-12">
		<div class="tutor-mobile-top-nav tutor-d-grid">
			<a href="<?php echo get_the_permalink( $previous_id ); ?>">
				<span class="tutor-top-nav-icon tutor-icon-previous-line design-lightgrey"></span>
			</a>
			<div class="tutor-top-nav-title tutor-fs-6 tutor-color-black">
				<?php
					the_title();
				?>
			</div>
		</div>
	</div>
<?php else : ?>
	<div class="tutor-single-page-top-bar tutor-d-flex tutor-justify-content-between">
		<div class="tutor-topbar-item tutor-topbar-sidebar-toggle tutor-hide-sidebar-bar flex-center tutor-d-none tutor-d-xl-flex">
			<a href="javascript:;" class="tutor-lesson-sidebar-hide-bar">
				<span class="tutor-icon-icon-light-left-line tutor-color-white flex-center"></span>
			</a>
		</div>
		<div class="tutor-topbar-item tutor-topbar-content-title-wrap flex-center">
			<span class="tutor-icon-youtube-brand tutor-icon-24 tutor-color-white tutor-mr-4"></span>
			<span class="tutor-fs-7 tutor-color-design-white">
				<?php
					esc_html_e( 'Lesson: ', 'tutor' );
					the_title();
				?>
			</span>
		</div>

		<div class="tutor-topbar-cross-icon tutor-ml-16 flex-center">
			<?php $course_id = tutor_utils()->get_course_id_by( 'lesson', get_the_ID() ); ?>
			<a href="<?php echo get_the_permalink( $course_id ); ?>">
				<span class="tutor-icon-line-cross-line tutor-color-white flex-center"></span>
			</a>
		</div>
	</div>
<?php endif; ?>

<!-- Load Lesson Video -->
<input type="hidden" id="tutor_video_tracking_information" value="<?php echo esc_attr( json_encode( $jsonData ) ); ?>">
<?php
tutor_lesson_video();

$referer_url        = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '';
$referer_comment_id = explode( '#', $_SERVER['REQUEST_URI'] );
$url_components     = parse_url( $referer_url );
isset( $url_components['query'] ) ? parse_str( $url_components['query'], $output ) : null;
$page_tab = isset( $_GET['page_tab'] ) ? esc_attr( $_GET['page_tab'] ) : ( isset( $output['page_tab'] ) ? $output['page_tab'] : null );
?>

<style>
	.tutor-actual-comment.viewing{
		box-shadow: 0 0 10px #cdcfd5;
		animation: blinkComment 1s infinite;
	}
	@keyframes blinkComment { 50% { box-shadow:0 0 0px #ffffff; }  }
</style>

<div class="tutor-course-spotlight-wrapper">
	<div class="tutor-spotlight-tab tutor-default-tab tutor-course-details-tab">
		<div class="tab-header tutor-d-flex justify-content-center">
			<div class="tab-header-item flex-center<?php echo ( ! isset( $page_tab ) || 'overview' == $page_tab ) ? ' is-active' : ''; ?>" data-tutor-spotlight-tab-target="tutor-course-spotlight-tab-1" data-tutor-query-string="overview">
				<span class="tutor-icon-document-alt-filled"></span>
				<span><?php _e( 'Overview', 'tutor' ); ?></span>
			</div>
			<div class="tab-header-item flex-center<?php echo 'files' == $page_tab ? ' is-active' : ''; ?>" data-tutor-spotlight-tab-target="tutor-course-spotlight-tab-2" data-tutor-query-string="files">
				<span class="tutor-icon-attach-filled"></span>
				<span><?php _e( 'Exercise Files', 'tutor' ); ?></span>
			</div>
			<?php if ( $is_comment_enabled ) : ?>
				<div class="tab-header-item flex-center<?php echo 'comments' == $page_tab ? ' is-active' : ''; ?>" data-tutor-spotlight-tab-target="tutor-course-spotlight-tab-3" data-tutor-query-string="comments">
					<span class="tutor-icon-comment-filled"></span>
					<span><?php _e( 'Comments', 'tutor' ); ?></span>
				</div>
			<?php endif; ?>
		</div>
		<div class="tab-body">
			<div class="tab-body-item<?php echo (!isset($page_tab) || 'overview'==$page_tab) ? ' is-active' : ''; ?>" id="tutor-course-spotlight-tab-1" data-tutor-query-string-content="overview">
				<div class="tutor-fs-6 tutor-fw-medium tutor-color-black">
					<?php _e( 'About Lesson', 'tutor' ); ?>
				</div>
				<div class="tutor-fs-6 tutor-color-black-60 tutor-mt-12" style="min-height:293px;">
					<?php the_content(); ?>
				</div>
			</div>
			<div class="tab-body-item<?php echo 'files'==$page_tab ? ' is-active' : ''; ?>" id="tutor-course-spotlight-tab-2" data-tutor-query-string-content="files">
				<div class="tutor-fs-6 tutor-fw-medium tutor-color-black"><?php _e( 'Exercise Files', 'tutor' ); ?></div>
				<?php get_tutor_posts_attachments(); ?>
			</div>
			<?php if ( $is_comment_enabled ) : ?>
				<div class="tab-body-item<?php echo 'comments' == $page_tab ? ' is-active' : ''; ?>" id="tutor-course-spotlight-tab-3" data-tutor-query-string-content="comments">
					<?php require __DIR__ . '/comment.php'; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<?php do_action( 'tutor_lesson/single/after/content' ); ?>
