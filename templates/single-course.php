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


<?php do_action('tutor_course/single/before/wrap'); ?>


<?php do_action('tutor_course/single/before/lead_info'); ?>
<?php tutor_course_lead_info(); ?>
<?php do_action('tutor_course/single/after/lead_info'); ?>


    <div <?php tutor_post_class(); ?>>

		<?php do_action('tutor_course/single/before/inner-wrap'); ?>

		<?php do_action('tutor_course/single/before/enroll'); ?>
		<?php tutor_course_enroll_box(); ?>
		<?php do_action('tutor_course/single/after/enroll'); ?>

		<?php tutor_course_video(); ?>
		<?php tutor_course_benefits_html(); ?>
		<?php tutor_course_topics(); ?>
		<?php tutor_course_requirements_html(); ?>
		<?php tutor_course_content(); ?>
		<?php tutor_course_target_audience_html(); ?>
		<?php tutor_course_material_includes_html(); ?>
		<?php tutor_course_teachers_html(); ?>
		<?php tutor_course_target_reviews_html(); ?>

		<?php do_action('tutor_course/single/after/inner-wrap'); ?>

    </div><!-- .wrap -->


<?php do_action('tutor_course/single/after/wrap'); ?>


<?php
get_footer();
