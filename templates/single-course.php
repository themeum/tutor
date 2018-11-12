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


<div <?php tutor_post_class('tutor-full-width-course-top tutor-course-top-info'); ?>>
    <div class="tutor-container">
        <div class="tutor-row">
            <div class="tutor-col-8">
                <?php tutor_course_lead_info(); ?>
                <?php tutor_course_benefits_html(); ?>
                <?php tutor_course_topics(); ?>
                <?php tutor_course_content(); ?>
                <?php tutor_course_target_audience_html(); ?>
                <?php tutor_course_material_includes_html(); ?>
                <?php tutor_course_teachers_html(); ?>
                <?php tutor_course_target_reviews_html(); ?>

            </div> <!-- .tutor-col-8 -->

            <div class="tutor-col-4">
                <div class="tutor-single-course-sidebar">
                    <?php tutor_course_enroll_box(); ?>
                    <?php tutor_course_requirements_html(); ?>

                </div> <!-- .tutor-single-course-sidebar -->
            </div> <!-- .tutor-col-4 -->
        </div> <!-- .tutor-row -->
    </div> <!-- .tutor-container -->
</div>

<?php do_action('tutor_course/single/after/wrap'); ?>

<?php
get_footer();
