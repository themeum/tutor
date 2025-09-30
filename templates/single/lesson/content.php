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

$is_comment_enabled = tutor_utils()->get_option( 'enable_comment_for_lesson' ) && comments_open() && is_user_logged_in();

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
		<?php do_action( 'tutor_lesson_after_lesson_video', $video_info, $course_content_id ); ?>
	</div>

	<?php
	$referer_url        = wp_get_referer();
	$referer_comment_id = explode( '#', filter_input( INPUT_SERVER, 'REQUEST_URI' ) ?? '' );
	$url_components     = parse_url( $referer_url );
	$page_tab           = \TUTOR\Input::get( 'page_tab', 'overview' );

	isset( $url_components['query'] ) ? parse_str( $url_components['query'], $output ) : null;

	/**
	 * If lesson has no content, lesson tab will be hidden.
	 * To enable elementor and SCORM, only admin can see lesson tab.
	 *
	 * @since 2.2.2
	 */
	$has_lesson_content = apply_filters(
		'tutor_has_lesson_content',
		User::is_admin() || ! in_array( trim( get_the_content() ), array( null, '', '&nbsp;' ), true ),
		$course_content_id
	);

	$has_lesson_attachment = count( tutor_utils()->get_attachments() ) > 0;
	$has_lesson_comment    = (int) get_comments_number( $course_content_id );

	$nav_items = array();

	if ( $has_lesson_content ) {
		$nav_items[] = array(
			'label' => esc_html__( 'Overview', 'tutor' ),
			'value' => 'overview',
			'icon'  => 'document-text',
		);
	}

	if ( $has_lesson_attachment ) {
		$nav_items[] = array(
			'label' => esc_html__( 'Exercise Files', 'tutor' ),
			'value' => 'files',
			'icon'  => 'paperclip',
		);
	}

	if ( $is_comment_enabled ) {
		$nav_items[] = array(
			'label' => esc_html__( 'Comments', 'tutor' ),
			'value' => 'comments',
			'icon'  => 'comment',
		);
	}

	$nav_items = apply_filters( 'tutor_lesson_single_nav_items', $nav_items );

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
						<span class="tutor-icon-<?php echo esc_attr( $nav_item['icon'] ); ?> tutor-mr-8" area-hidden="true"></span>
						<span><?php echo esc_html( $nav_item['label'] ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>

			<?php do_action( 'tutor_lesson_single_after_nav_items', $course_content_id, $active_tab ); ?>
		</ul>
		<?php endif; ?>

		<div class="tutor-tab tutor-course-spotlight-tab">
			<?php if ( $has_lesson_content ) : ?>
			<div id="tutor-course-spotlight-overview" class="tutor-tab-item<?php echo esc_attr( 'overview' === $active_tab ? ' is-active' : '' ); ?>">
				<div class="tutor-container">
					<div class="tutor-row tutor-justify-center">
						<div class="tutor-col-xl-8">
							<?php do_action( 'tutor_lesson_before_the_content', $post, $course_id ); ?>
							<div class="tutor-fs-6 tutor-color-secondary tutor-lesson-wrapper">
								<?php the_content(); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ( $has_lesson_attachment ) : ?>
			<div id="tutor-course-spotlight-files" class="tutor-tab-item<?php echo esc_attr( ( 'files' === $active_tab ) ? ' is-active' : '' ); ?>">
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
			<div id="tutor-course-spotlight-comments" class="tutor-tab-item<?php echo esc_attr( ( 'comments' === $active_tab ) ? ' is-active' : '' ); ?>">
				<div class="tutor-container">
					<div class="tutor-course-spotlight-comments">
						<?php require __DIR__ . '/comment.php'; ?>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php do_action( 'tutor_lesson_single_after_tab_contents', $course_content_id, $active_tab ); ?>
		</div>
	</div>
</div>

<?php tutor_load_template( 'single.common.footer', array( 'course_id' => $course_id ) ); ?>

<?php do_action( 'tutor_lesson/single/after/content' ); ?>
