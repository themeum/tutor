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


do_action('dozent_course/single/enrolled/before/wrap');
?>

<div <?php dozent_post_class('dozent-full-width-course-top dozent-course-top-info'); ?>>
    <div class="dozent-container">
        <div class="dozent-row">
            <div class="dozent-col-8">
                <?php do_action('dozent_course/single/enrolled/before/inner-wrap'); ?>
                <?php dozent_course_enrolled_lead_info(); ?>
                <?php dozent_course_enrolled_nav(); ?>
                <?php dozent_course_topics(); ?>
                <?php dozent_course_teachers_html(); ?>
                <?php dozent_course_target_reviews_html(); ?>
                <?php dozent_course_target_review_form_html(); ?>
		        <?php do_action('dozent_course/single/enrolled/after/inner-wrap'); ?>
            </div>
            <div class="dozent-col-4">
                <div class="dozent-single-course-sidebar">
                    <?php dozent_course_enroll_box(); ?>
                    <?php dozent_course_requirements_html(); ?>
                    <?php dozent_course_tags_html(); ?>
                    <?php dozent_course_target_audience_html(); ?>
                </div>
            </div>
        </div>
    </div>
</div>


<?php do_action('dozent_course/single/enrolled/after/wrap'); ?>

<?php
get_footer();
