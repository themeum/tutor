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

do_action('tutor_course/single/enrolled/before/wrap');

?>
    <div <?php tutor_post_class('tutor-single-overview-wrap tutor-page-wrap'); ?>>
        <div class="tutor-container">
            <div class="tutor-row">
                <div class="tutor-col-8">
                    <?php tutor_course_enrolled_lead_info(); ?>
                    <?php tutor_course_enrolled_nav(); ?>
                    <?php tutor_course_content(); ?>
                    <?php get_tutor_posts_attachments(); ?>
                </div>
                <div class="tutor-col-4">
                    <div class="tutor-single-course-sidebar">
                        <?php tutor_course_enroll_box(); ?>
                        <?php tutor_course_requirements_html(); ?>
                        <?php tutor_course_tags_html(); ?>
                        <?php tutor_course_target_audience_html(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- .wrap -->
<?php
do_action('tutor_course/single/enrolled/after/wrap');
get_footer();
