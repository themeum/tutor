<?php
/**
 * Template for displaying single course
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 */

get_header();
?>

<?php do_action('lms_course/single/enrolled/before/wrap'); ?>

<?php do_action('lms_course/single/enrolled/before/lead_info'); ?>
<?php lms_course_enrolled_lead_info(); ?>
<?php do_action('lms_course/single/enrolled/after/lead_info'); ?>

    <div <?php lms_post_class(); ?>>
		<?php do_action('lms_course/single/enrolled/before/inner-wrap'); ?>

		<?php do_action('lms_course/single/enrolled/before/nav'); ?>
		<?php lms_course_enrolled_nav(); ?>
		<?php do_action('lms_course/single/enrolled/after/nav'); ?>

		<?php get_lms_posts_attachments(); ?>

		<?php do_action('lms_course/single/enrolled/after/inner-wrap'); ?>

    </div><!-- .wrap -->

<?php do_action('lms_course/single/enrolled/after/wrap'); ?>

<?php
get_footer();
