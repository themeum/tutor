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
<div class="tutor-course-single-content-wrapper<?php echo $enable_spotlight_mode ? " tutor-spotlight-mode" : ""; ?>">
    <div class="tutor-course-single-sidebar-wrapper tutor-<?php echo $context; ?>-sidebar">
        <?php tutor_course_single_sidebar(); ?>
    </div>
    <div id="tutor-single-entry-content" class="tutor-quiz-single-entry-wrap">
        <?php (isset($method_map[$context]) && is_callable($method_map[$context])) ? $method_map[$context]() : 0; ?>
        <?php echo isset($html_content) ? $html_content  : '' ; ?>
    </div>
</div>

<!-- Course Progressbar on sm/mobile  -->
<?php 
    // Get the ID of this content and the corresponding course
    $course_content_id = get_the_ID();
    $course_id         = tutor_utils()->get_course_id_by_subcontent( $course_content_id );

    // Get total content count
    $course_stats = tutor_utils()->get_course_completed_percent( $course_id, 0, true );

    // Is Lesstion Complete
    $is_completed_lesson = tutor_utils()->is_completed_lesson();
?>

<?php if(!\TUTOR\Course_List::is_public($course_id)): ?>
    <div class="tutor-spotlight-mobile-progress-complete tutor-px-20 tutor-py-16 tutor-mt-20 tutor-d-sm-none tutor-d-block">
        <div class="tutor-row tutor-align-center">
            <div class="tutor-spotlight-mobile-progress-left <?php echo !$is_completed_lesson ? "tutor-col-6" : "tutor-col-12"?>">
                <div class="tutor-fs-7 tutor-color-muted">
                    <?php echo $course_stats['completed_percent'] . '%'; ?><span>&nbsp;Complete</span>
                </div>
                <div class="list-item-progress tutor-my-16">
                    <div class="tutor-progress-bar tutor-mt-12" style="--tutor-progress-value:<?php echo $course_stats['completed_percent']; ?>%;">
                        <span class="tutor-progress-value" area-hidden="true"></span>
                    </div>
                </div>
            </div>
            
            <?php if(!$is_completed_lesson): ?>
                <div class="tutor-spotlight-mobile-progress-right tutor-col-6">
                    <?php tutor_lesson_mark_complete_html(); ?>
                </div>
            <?php endif; ?>

        </div>
    </div>
<?php endif; ?>
<?php do_action('tutor_'.$context.'/single/after/wrap');

get_tutor_footer();
