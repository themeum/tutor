<?php
/**
 * Template for displaying single lesson, assignment, quiz etc.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

global $post;
//phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
$currentPost = $post;

$method_map = array(
	'lesson'     => 'tutor_lesson_content',
	'assignment' => 'tutor_assignment_content',
);

$content_id  = tutor_utils()->get_post_id();
$course_id   = tutor_utils()->get_course_id_by_subcontent( $content_id );
$contents    = tutor_utils()->get_course_prev_next_contents_by_id( $content_id );
$previous_id = $contents->previous_id;
$next_id     = $contents->next_id;

$enable_spotlight_mode = tutor_utils()->get_option( 'enable_spotlight_mode' );
//phpcs:ignore WordPress.PHP.DontExtract.extract_extract
extract( $data ); // $data variable consist $context, $html_content.

/**
 * Single course sidebar content
 *
 * @param boolean $echo echo the content or not.
 * @param string  $context device context (mobile/desktop).
 * @return string HTML output string.
 */
function tutor_course_single_sidebar( $echo = true, $context = 'desktop' ) {
	ob_start();
	tutor_load_template( 'single.lesson.lesson_sidebar', array( 'context' => $context ) );
	$output = apply_filters( 'tutor_lesson/single/lesson_sidebar', ob_get_clean() );

	if ( $echo ) {
		add_filter( 'wp_kses_allowed_html', 'tutor_kses_allowed_html', 10, 2 );
		echo wp_kses_post( $output );
		remove_filter( 'wp_kses_allowed_html', 'tutor_kses_allowed_html' );
	}

	return $output;
}

do_action( 'tutor/course/single/content/before/all', $course_id, $content_id );

get_tutor_header();

$show_mark_as_complete = false;

if ( tutor()->lesson_post_type === $post->post_type ) {
	$show_mark_as_complete = apply_filters( 'tutor_lesson_show_mark_as_complete', true );
}

?>

<?php do_action( 'tutor_' . $context . '/single/before/wrap' ); ?>
<div class="tutor-course-single-content-wrapper<?php echo $enable_spotlight_mode ? ' tutor-spotlight-mode' : ''; ?>">
	<div class="tutor-course-single-sidebar-wrapper tutor-<?php echo esc_attr( $context ); ?>-sidebar">
		<?php tutor_course_single_sidebar(); ?>
	</div>
	<div id="tutor-single-entry-content" class="tutor-quiz-single-entry-wrap">
		<?php ( isset( $method_map[ $context ] ) && is_callable( $method_map[ $context ] ) ) ? $method_map[ $context ]() : 0; ?>
		<?php
			/**
			 * Note: $html_content comes from extracted $data variable
			 * $html_content consist dynamic HTML content which is loaded by tutor_load_template_from_custom_path
			 */
			echo isset( $html_content ) ? $html_content : ''; //phpcs:ignore 
		?>
	</div>
</div>

<!-- Course Progressbar on sm/mobile  -->
<?php
	// Get total content count.
	$course_stats = tutor_utils()->get_course_completed_percent( $course_id, 0, true );

	// Is Lesstion Complete.
	$is_completed_lesson = tutor_utils()->is_completed_lesson();
?>

<?php if ( ! \TUTOR\Course_List::is_public( $course_id ) ) : ?>
	<div class="tutor-spotlight-mobile-progress-complete tutor-px-20 tutor-py-16 tutor-mt-20 tutor-d-xl-none tutor-d-block">
		<div class="tutor-row tutor-align-center">
			<div class="tutor-spotlight-mobile-progress-left <?php echo ! $is_completed_lesson ? 'tutor-col-sm-8 tutor-col-6' : 'tutor-col-12'; ?>">
				<div class="tutor-fs-7 tutor-color-muted">
					<?php echo esc_html( $course_stats['completed_percent'] ) . '% '; ?><span><?php esc_html_e( 'Complete', 'tutor' ); ?></span>
				</div>
				<div class="list-item-progress tutor-my-16">
					<div class="tutor-progress-bar tutor-mt-12" style="--tutor-progress-value:<?php echo esc_attr( $course_stats['completed_percent'] ); ?>%;">
						<span class="tutor-progress-value" area-hidden="true"></span>
					</div>
				</div>
			</div>

			<?php if ( ! $is_completed_lesson ) : ?>
				<div class="tutor-spotlight-mobile-progress-right tutor-col-sm-4 tutor-col-6">
					<?php
					if ( $show_mark_as_complete ) {
						tutor_lesson_mark_complete_html();
					}
					?>
				</div>
			<?php endif; ?>

		</div>
	</div>
<?php endif; ?>
<?php
do_action( 'tutor_' . $context . '/single/after/wrap' );

get_tutor_footer();
