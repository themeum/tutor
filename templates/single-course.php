<?php
/**
 * Template for displaying single course
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

get_header();

do_action('tutor_course/single/before/wrap'); 

$sub_page = get_query_var('course_subpage'); 
!$sub_page ? $sub_page='info' : 0;

$sub_page_method = tutor_utils()->course_sub_pages(get_the_ID());
?>

<div <?php tutor_post_class('tutor-full-width-course-top tutor-course-top-info tutor-page-wrap'); ?>>
    <div class="tutor-course-details-page tutor-bs-container">
        <?php (isset($is_enrolled) && $is_enrolled) ? tutor_course_enrolled_lead_info() : tutor_course_lead_info(); ?>
        <div class="tutor-bs-row">
            <div class="tutor-bs-col-8 tutor-bs-col-md-100">
                <?php tutor_utils()->has_video_in_single() ? tutor_course_video() : get_tutor_course_thumbnail(); ?>
	            <?php do_action('tutor_course/single/before/inner-wrap'); ?>
                <?php tutor_course_enrolled_nav(); ?>
                <?php
                    if(isset($sub_page_method[$sub_page])){
                        $method = $sub_page_method[$sub_page]['method'];
                        
                        if(is_string($method)) {
                            $method();
                        } else {
                            $_object = $method[0];
                            $_method = $method[1];
                            $_object->$_method(get_the_ID());
                        }
                    } 
                ?>
	            <?php do_action('tutor_course/single/after/inner-wrap'); ?>
            </div> <!-- .tutor-bs-col-8 -->

            <div class="tutor-bs-col-4">
                <div class="tutor-single-course-sidebar">
                    <?php do_action('tutor_course/single/before/sidebar'); ?>
                    <?php tutor_course_enroll_box(); ?>
                    <?php tutor_course_requirements_html(); ?>
                    <?php tutor_course_tags_html(); ?>
                    <?php tutor_course_target_audience_html(); ?>
                    <?php do_action('tutor_course/single/after/sidebar'); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php do_action('tutor_course/single/after/wrap'); ?>

<?php
get_footer();
