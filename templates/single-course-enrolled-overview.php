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
    <div <?php tutor_post_class('tutor-single-overview-wrap'); ?>>
        <div class="tutor-container">
            <div class="tutor-row">
                <div class="tutor-col">
                    <?php tutor_course_enrolled_lead_info(); ?>
                    <?php tutor_course_enrolled_nav(); ?>
                    <?php get_tutor_posts_attachments(); ?>
                </div>
            </div>
        </div>
    </div><!-- .wrap -->
<?php
do_action('tutor_course/single/enrolled/after/wrap');
get_footer();
