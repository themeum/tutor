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


<?php do_action('tutor_lesson/single/before/lead_info'); ?>
<?php tutor_lesson_lead_info(); ?>
<?php do_action('tutor_lesson/single/after/lead_info'); ?>


    <div <?php tutor_post_class(); ?>>
        <div class="tutor-lesson-single-wrap">

            <div class="tutor-topics-wrap">
	            <?php tutor_lessons_as_list(); ?>
            </div>


            <div class="tutor-lesson-content-wrap">

				<?php tutor_lesson_video(); ?>
				<?php the_content(); ?>
				<?php get_tutor_posts_attachments(); ?>
				<?php tutor_lesson_mark_complete_html(); ?>

            </div>

        </div>
    </div><!-- .wrap -->

<?php do_action('tutor_lesson/single/after/wrap');

get_footer();
