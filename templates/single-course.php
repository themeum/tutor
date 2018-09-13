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


<?php do_action('lms_course/single/before/wrap'); ?>


<?php do_action('lms_course/single/before/lead_info'); ?>
<?php lms_course_lead_info(); ?>
<?php do_action('lms_course/single/after/lead_info'); ?>


    <div <?php lms_post_class(); ?>>

		<?php do_action('lms_course/single/before/inner-wrap'); ?>

		<?php do_action('lms_course/single/before/enroll'); ?>
		<?php lms_course_enroll_box(); ?>
		<?php do_action('lms_course/single/after/enroll'); ?>


		<?php do_action('lms_course/single/before/benefits'); ?>
		<?php lms_course_benefits_html(); ?>
		<?php do_action('lms_course/single/after/benefits'); ?>


		<?php do_action('lms_course/single/before/topics'); ?>
		<?php lms_course_topics(); ?>
		<?php do_action('lms_course/single/after/topics'); ?>


		<?php do_action('lms_course/single/before/requirements'); ?>
		<?php lms_course_requirements_html(); ?>
		<?php do_action('lms_course/single/after/requirements'); ?>


		<?php do_action('lms_course/single/before/content'); ?>
		<?php lms_course_content(); ?>
		<?php do_action('lms_course/single/after/content'); ?>


		<?php do_action('lms_course/single/before/audience'); ?>
		<?php lms_course_target_audience_html(); ?>
		<?php do_action('lms_course/single/after/audience'); ?>

		<?php do_action('lms_course/single/after/inner-wrap'); ?>

    </div><!-- .wrap -->


<?php do_action('lms_course/single/after/wrap'); ?>


<?php
get_footer();
