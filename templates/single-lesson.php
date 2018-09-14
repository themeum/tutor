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
?>


<?php do_action('lms_lesson/single/before/wrap'); ?>

    <div <?php lms_post_class(); ?>>

        <h2>Lesson Title</h2>

        <?php echo get_the_ID(); ?>
    </div><!-- .wrap -->

<?php do_action('lms_lesson/single/after/wrap'); ?>


<?php
get_footer();
