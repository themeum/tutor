<?php
/**
 * Template for displaying single lesson, assignment, quiz etc.
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

global $post;
$currentPost = $post;

$method_map = array(
    'lesson' => 'tutor_lesson_content',
    'assignment' => 'tutor_assignment_content',
);

$content_id = tutor_utils()->get_post_id();
$course_id = tutor_utils()->get_course_id_by_subcontent( $content_id );
$contents = tutor_utils()->get_course_prev_next_contents_by_id($content_id);
$previous_id = $contents->previous_id;
$next_id = $contents->next_id;

$enable_spotlight_mode = tutor_utils()->get_option( 'enable_spotlight_mode' );
extract($data); // $context, $html_content

function tutor_course_single_sidebar( $echo = true, $context='desktop' ) {
    ob_start();
    tutor_load_template( 'single.lesson.lesson_sidebar', array('context' => $context) );
    $output = apply_filters( 'tutor_lesson/single/lesson_sidebar', ob_get_clean() );

    if ( $echo ) {
        echo tutor_kses_html( $output );
    }

    return $output;
}

do_action( 'tutor/course/single/content/before/all', $course_id, $content_id );

get_tutor_header();

?>

<?php do_action('tutor_'.$context.'/single/before/wrap'); ?>
    <div class="tutor-course-single-content-wraper <?php echo $enable_spotlight_mode ? "tutor-spotlight-mode" : ""; ?>">
        <div class="tutor-course-single-sidebar-wraper tutor-<?php echo $context; ?>-sidebar tutor-desktop-sidebar">
			<?php tutor_course_single_sidebar(); ?>
        </div>
        <div id="tutor-single-entry-content" class="tutor-quiz-single-entry-wrap sidebar-hidden">
		    <?php (isset($method_map[$context]) && is_callable($method_map[$context])) ? $method_map[$context]() : 0; ?>
            <?php echo isset($html_content) ? $html_content  : '' ; ?>
            
            <?php if($context=='lesson'): ?>
                <?php if($previous_id): ?>
                    <div class="tutor-single-course-content-prev flex-center" style="display:none;">
                        <a href="<?php echo get_the_permalink($previous_id); ?>">
                            <span class="tutor-icon-angle-left-filled"></span>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if($next_id): ?>
                    <div class="tutor-single-course-content-next flex-center" style="display:none;">
                        <a href="<?php echo get_the_permalink($next_id); ?>">
                            <span class="tutor-icon-angle-right-filled"></span>
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="tutor-course-single-sidebar-wraper tutor-mobile-sidebar">
                <?php tutor_course_single_sidebar(true, 'mobile'); ?>
            </div> 
        </div>
    </div>
<?php do_action('tutor_'.$context.'/single/after/wrap');

get_tutor_footer();
