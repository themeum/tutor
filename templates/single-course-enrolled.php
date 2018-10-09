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

		<?php do_action('tutor_course/single/enrolled/before/inner-wrap'); ?>

		<?php do_action('tutor_course/single/enrolled/before/nav'); ?>
		<?php tutor_course_enrolled_nav(); ?>
		<?php do_action('tutor_course/single/enrolled/after/nav'); ?>

		<?php tutor_course_video(); ?>

		<?php do_action('tutor_course/single/enrolled/before/topics'); ?>
		<?php tutor_course_topics(); ?>
		<?php do_action('tutor_course/single/enrolled/after/topics'); ?>


		<?php do_action('tutor_course/single/enrolled/before/requirements'); ?>
		<?php tutor_course_requirements_html(); ?>
		<?php do_action('tutor_course/single/enrolled/after/requirements'); ?>


		<?php do_action('tutor_course/single/enrolled/before/content'); ?>
		<?php tutor_course_content(); ?>
		<?php do_action('tutor_course/single/enrolled/after/content'); ?>


		<?php do_action('tutor_course/single/enrolled/before/audience'); ?>
		<?php tutor_course_target_audience_html(); ?>
		<?php do_action('tutor_course/single/enrolled/after/audience'); ?>

		<?php do_action('tutor_course/single/enrolled/before/reviews'); ?>
		<?php tutor_course_target_reviews_html(); ?>
		<?php do_action('tutor_course/single/enrolled/after/reviews'); ?>


		<?php do_action('tutor_course/single/enrolled/after/inner-wrap'); ?>

    </div><!-- .wrap -->


<?php do_action('tutor_course/single/enrolled/after/wrap'); ?>


<?php
get_footer();
