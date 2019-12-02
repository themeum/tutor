<?php
/**
 * Template for displaying single lesson
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

get_header();

global $post;
$currentPost = $post;
?>
<?php do_action('tutor_lesson/single/before/wrap'); ?>
    <div <?php tutor_post_class('tutor-single-lesson-wrap tutor-page-wrap'); ?>>
        <div class="tutor-container">
            <div class="tutor-row">
                <div class="tutor-col-12">
                    <?php tutor_lesson_video(); ?>
                    <?php the_content(); ?>
                    <?php get_tutor_posts_attachments(); ?>
                </div>
            </div>
        </div>
    </div>
<?php do_action('tutor_lesson/single/after/wrap');

get_footer();