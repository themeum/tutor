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


<?php do_action('lms_lesson/single/before/wrap'); ?>


<?php do_action('lms_lesson/single/before/lead_info'); ?>
<?php lms_lesson_lead_info(); ?>
<?php do_action('lms_lesson/single/after/lead_info'); ?>


    <div <?php lms_post_class(); ?>>

        <div class="lms-lesson-single-wrap">

            <div class="lms-topics-wrap">


	            <?php lms_lessons_as_list(); ?>


            </div>


            <div class="lms-lesson-content-wrap">

				<?php lms_lesson_video(); ?>

				<?php the_content(); ?>

				<?php lms_lesson_attachments(); ?>


            </div>

        </div>



    </div><!-- .wrap -->

<?php do_action('lms_lesson/single/after/wrap'); ?>


<?php
get_footer();
