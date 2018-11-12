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

<div <?php tutor_post_class('tutor-full-width-course-top tutor-course-top-info'); ?>>
    <div class="tutor-container">
        <div class="tutor-row">
            <div class="tutor-col">
                <?php do_action('tutor_course/single/enrolled/before/inner-wrap'); ?>
                <?php tutor_course_enrolled_lead_info(); ?>
                <?php tutor_course_enrolled_nav(); ?>
                <?php tutor_course_video(); ?>
                <?php tutor_course_topics(); ?>
                <?php tutor_course_requirements_html(); ?>
                <?php tutor_course_content(); ?>
                <?php tutor_course_target_audience_html(); ?>
                <?php tutor_course_material_includes_html(); ?>
                <?php tutor_course_teachers_html(); ?>
                <?php tutor_course_target_reviews_html(); ?>
		        <?php do_action('tutor_course/single/enrolled/after/inner-wrap'); ?>
            </div>
        </div>
    </div>
</div>


<?php
get_footer();
