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

<?php do_action('dozent_lesson/single/before/wrap'); ?>
    <div <?php dozent_post_class('dozent-single-lesson-wrap'); ?>>
        <div class="dozent-container">
            <div class="dozent-row">
                <div class="dozent-col-8">
                    <?php dozent_lesson_video(); ?>
                    <?php the_content(); ?>
                    <?php get_dozent_posts_attachments(); ?>
                    <?php dozent_lesson_mark_complete_html(); ?>
                </div>
                <div class="dozent-col-4">
                    <?php dozent_lessons_as_list(); ?>
                </div>
            </div>
        </div>
    </div>
<?php do_action('dozent_lesson/single/after/wrap');

get_footer();