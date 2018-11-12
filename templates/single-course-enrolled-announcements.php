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

<div <?php tutor_post_class('tutor-single-anouncement-wrap'); ?>>
    <div class="tutor-container">
        <div class="tutor-row">
            <div class="tutor-col">
                <?php do_action('tutor_course/single/enrolled/before/wrap'); ?>
                <?php tutor_course_enrolled_lead_info(); ?>
                <?php tutor_course_enrolled_nav(); ?>
                <?php tutor_course_announcements(); ?>
                <?php do_action('tutor_course/single/enrolled/after/wrap'); ?>
            </div>
        </div>
    </div>
</div>
<?php
do_action('tutor_course/single/enrolled/after/wrap');
get_footer();
