<?php
/**
 * Template for displaying assignment
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

get_tutor_header();

global $post;
$currentPost = $post;
$enable_spotlight_mode = tutor_utils()->get_option('enable_spotlight_mode');

?>

<?php do_action('tutor_assignment/single/before/wrap'); ?>
    <div class="tutor-single-lesson-wraper <?php echo $enable_spotlight_mode ? "tutor-spotlight-mode" : ""; ?>">
        <div class="tutor-lesson-sidebar  tutor-desktop-sidebar">
			<?php tutor_lessons_sidebar(); ?>
        </div>
        <div id="tutor-single-entry-content" class="tutor-quiz-single-entry-wrap tutor-single-entry-content sidebar-hidden">
            <?php tutor_assignment_content(); ?>
            <div class="tutor-lesson-sidebar tutor-mobile-sidebar">
                <?php tutor_lessons_sidebar(true, 'mobile'); ?>
            </div> 
        </div>
    </div>
<?php do_action('tutor_assignment/single/after/wrap');

get_tutor_footer();