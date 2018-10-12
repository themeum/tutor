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

<?php do_action('tutor_course/single/enrolled/before/wrap'); ?>


<?php do_action('tutor_course/single/enrolled/before/lead_info'); ?>
<?php tutor_course_enrolled_lead_info(); ?>
<?php do_action('tutor_course/single/enrolled/after/lead_info'); ?>

    <div <?php tutor_post_class(); ?>>


	    <?php do_action('tutor_course/single/enrolled/nav/before'); ?>
	    <?php tutor_course_enrolled_nav(); ?>
	    <?php do_action('tutor_course/single/enrolled/nav/after'); ?>


	    <?php do_action('tutor_course/announcements/before'); ?>
	    <?php tutor_course_announcements(); ?>
	    <?php do_action('tutor_course/announcements/after'); ?>



    </div><!-- .wrap -->

<?php do_action('tutor_course/single/enrolled/after/wrap'); ?>


<?php
get_footer();
