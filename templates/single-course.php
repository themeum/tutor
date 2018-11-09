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

                <?php do_action('tutor_course/single/before/inner-wrap'); ?>

                <?php do_action('tutor_course/single/before/lead_info'); ?>
                <?php tutor_course_lead_info(); ?>
                <?php do_action('tutor_course/single/after/lead_info'); ?>


                <?php do_action('tutor_course/single/before/benefits'); ?>
                <?php tutor_course_benefits_html(); ?>
                <?php do_action('tutor_course/single/after/benefits'); ?>


                <?php do_action('tutor_course/single/before/topics'); ?>
                <?php tutor_course_topics(); ?>
                <?php do_action('tutor_course/single/after/topics'); ?>


                <?php do_action('tutor_course/single/before/content'); ?>
                <?php tutor_course_content(); ?>
                <?php do_action('tutor_course/single/after/content'); ?>


                <?php do_action('tutor_course/single/before/audience'); ?>
                <?php tutor_course_target_audience_html(); ?>
                <?php do_action('tutor_course/single/after/audience'); ?>


                <?php do_action('tutor_course/single/after/inner-wrap'); ?>

            </div> <!-- .tutor-col-8 -->


            <div class="tutor-col-4">
                <div class="tutor-single-course-sidebar">

                    <?php do_action('tutor_course/single/before/enrollbox'); ?>
                    <?php tutor_course_enroll_box(); ?>
                    <?php do_action('tutor_course/single/after/enrollbox'); ?>

                    <?php do_action('tutor_course/single/before/requirements'); ?>
                    <?php tutor_course_requirements_html(); ?>
                    <?php do_action('tutor_course/single/after/requirements'); ?>


                </div> <!-- .tutor-single-course-sidebar -->
            </div> <!-- .tutor-col-4 -->
        </div> <!-- .tutor-row -->
    </div> <!-- .tutor-container -->
</div>

<?php do_action('tutor_course/single/after/wrap'); ?>

<?php
get_footer();
