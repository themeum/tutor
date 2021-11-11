<?php
/**
 * Template for displaying single quiz
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

get_tutor_header();

$course = tutor_utils()->get_course_by_quiz(get_the_ID());

$enable_spotlight_mode = tutor_utils()->get_option('enable_spotlight_mode');
?>

<?php do_action('tutor_quiz/single/before/wrap'); ?>


    <div class="tutor-single-lesson-wraper <?php echo $enable_spotlight_mode ? "tutor-spotlight-mode" : ""; ?>">

        <div class="tutor-lesson-sidebar">
		    <?php tutor_lessons_sidebar(); ?>
        </div>
        <div id="tutor-single-entry-content" class="tutor-quiz-single-entry-wrap tutor-single-entry-content sidebar-hidden">
            <input type="hidden" name="tutor_quiz_id" id="tutor_quiz_id" value="<?php the_ID(); ?>">
            <div class="tutor-single-page-top-bar d-flex justify-content-between">
                <div class="tutor-topbar-item tutor-topbar-sidebar-toggle tutor-hide-sidebar-bar flex-center">
                    <a href="javascript:;" class="tutor-lesson-sidebar-hide-bar">
                        <span class="ttr-icon-light-left-line color-text-white flex-center"></span>
                    </a>
                </div>
                <div class="tutor-topbar-item tutor-topbar-content-title-wrap flex-center">
                    <?php

                    if ($post->post_type === 'tutor_quiz') {
                        echo wp_kses_post( '<span class="ttr-quiz-filled color-text-white tutor-mr-5"></span>' );
                        echo wp_kses_post( '<span class="text-regular-caption color-design-white">' );
                        esc_html_e( 'Quiz: ', 'tutor' );
                        the_title(); 
                        echo wp_kses_post( '</span>' );
                    } elseif ($post->post_type === 'tutor_assignments'){
                        echo wp_kses_post( '<span class="ttr-assignment-filled color-text-white tutor-mr-5"></span>' );
                        echo wp_kses_post( '<span class="text-regular-caption color-design-white">' );
                        esc_html_e( 'Assignment: ', 'tutor' );
                        the_title(); 
                        echo wp_kses_post( '</span>' );
                    } elseif ($post->post_type === 'tutor_zoom_meeting'){
                        echo wp_kses_post( '<span class="ttr-zoom-brand color-text-white tutor-mr-5"></span>' );
                        echo wp_kses_post( '<span class="text-regular-caption color-design-white">' );
                        esc_html_e( 'Zoom Meeting: ', 'tutor' );
                        the_title(); 
                        echo wp_kses_post( '</span>' );
                    } else{
                        echo wp_kses_post( '<span class="ttr-youtube-brand color-text-white tutor-mr-5"></span>' );
                        echo wp_kses_post( '<span class="text-regular-caption color-design-white">' );
                        esc_html_e( 'Lesson: ', 'tutor' );
                        the_title(); 
                        echo wp_kses_post( '</span>' );
                    }

                    ?>
                </div>

                <div class="tutor-topbar-cross-icon flex-center">
                    <?php $course_id = tutor_utils()->get_course_id_by('lesson', get_the_ID()); ?>
                    <a href="<?php echo get_the_permalink($course_id); ?>">
                        <span class="ttr-line-cross-line color-text-white flex-center"></span>
                    </a>
                </div>

            </div>
            <div class="tutor-quiz-wrapper tutor-quiz-wrapper d-flex justify-content-center tutor-mt-100 tutor-pb-100">
                <input type="hidden" name="tutor_quiz_id" id="tutor_quiz_id" value="<?php the_ID(); ?>">

		        <?php
		        if ($course){
			        tutor_single_quiz_top();
			        tutor_single_quiz_content();
			        tutor_single_quiz_body();
		        }else{
			        tutor_single_quiz_no_course_belongs();
		        }
		        ?>
            </div>

        </div>
    </div><!-- .wrap -->

<?php
do_action('tutor_quiz/single/after/wrap');
get_tutor_footer();