<?php
/**
 * Template for displaying single lesson
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 */

get_header();

global $post;
$currentPost = $post;
?>

<?php do_action('tutor_lesson/single/before/wrap'); ?>
    <div <?php tutor_post_class('tutor-single-lesson-wrap tutor-page-wrap'); ?>>
        <div class="tutor-container">
            <div class="tutor-row">
                <div class="tutor-col-4">
		            <?php tutor_lessons_as_list(); ?>
                </div>
                <div class="tutor-col-8">
                    <div id="tutor-single-lesson-entry-content tutor-single-lesson-entry-content-<?php the_ID(); ?>">
	                    <?php tutor_lesson_video(); ?>
	                    <?php the_content(); ?>
	                    <?php get_tutor_posts_attachments(); ?>
	                    <?php tutor_lesson_mark_complete_html(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php do_action('tutor_lesson/single/after/wrap');

get_footer();