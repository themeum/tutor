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




	    <?php do_action('lms_course/single/enrolled/before/requirements'); ?>
	    <?php lms_course_requirements_html(); ?>
	    <?php do_action('lms_course/single/enrolled/after/requirements'); ?>


	    <?php do_action('lms_course/single/enrolled/before/audience'); ?>
	    <?php lms_course_target_audience_html(); ?>
	    <?php do_action('lms_course/single/enrolled/after/audience'); ?>




	    <?php do_action('lms_course/single/enrolled/after/inner-wrap'); ?>


    </div><!-- .wrap -->


<?php do_action('lms_course/single/enrolled/after/wrap'); ?>


<?php
get_footer();
