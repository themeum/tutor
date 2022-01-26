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

global $previous_id;

// Get the ID of this content and the corresponding course
$course_content_id = get_the_ID();
$course_id = tutor_utils()->get_course_id_by_subcontent($course_content_id);

$content_id = tutor_utils()->get_post_id($course_content_id);
$contents = tutor_utils()->get_course_prev_next_contents_by_id($content_id);
$previous_id = $contents->previous_id;
$course = tutor_utils()->get_course_by_quiz(get_the_ID());

// Get total content count
$course_stats = tutor_utils()->get_course_completed_percent($course_id, 0, true);

$enable_spotlight_mode = tutor_utils()->get_option('enable_spotlight_mode');
?>

<?php do_action('tutor_quiz/single/before/wrap'); ?>
    <div class="tutor-single-lesson-wraper <?php echo $enable_spotlight_mode ? "tutor-spotlight-mode" : ""; ?>">
        <div class="tutor-lesson-sidebar tutor-desktop-sidebar">
		    <?php tutor_lessons_sidebar(); ?>
        </div>
        <div id="tutor-single-entry-content" class="tutor-quiz-single-entry-wrap tutor-single-entry-content sidebar-hidden">
            <input type="hidden" name="tutor_quiz_id" id="tutor_quiz_id" value="<?php the_ID(); ?>">

            <div class="tutor-single-page-top-bar d-flex justify-content-between">
                <div class="tutor-topbar-left-item d-flex"> 
                    <div class="tutor-topbar-item tutor-topbar-sidebar-toggle tutor-hide-sidebar-bar flex-center tutor-bs-d-none tutor-bs-d-xl-flex">
                        <a href="javascript:;" class="tutor-lesson-sidebar-hide-bar">
                            <span class="tutor-icon icon-light-left-line tutor-color-text-white flex-center"></span>
                        </a>
                    </div>
                    <div class="tutor-topbar-item tutor-topbar-content-title-wrap flex-center">
                        <span class="tutor-icon quiz-filled tutor-color-text-white tutor-mr-5"></span>
                        <span class="tutor-text-regular-caption tutor-color-design-white">
                            <?php 
                                esc_html_e( 'Quiz: ', 'tutor' );
                                the_title();
                            ?>
                        </span>
                    </div>
                </div>
                <div class="tutor-topbar-right-item d-flex align-items-center">
                    <div class="tutor-topbar-assignment-details d-flex align-items-center">
                        <?php
                            do_action('tutor_course/single/enrolled/before/lead_info/progress_bar');
                        ?>
                        <div class="tutor-text-regular-caption tutor-color-design-white">
                            <span class="tutor-progress-content tutor-color-primary-60">
                                <?php _e('Your Progress:', 'tutor'); ?>
                            </span>
                            <span class="tutor-text-bold-caption">
                                <?php echo $course_stats['completed_count']; ?>
                            </span> 
                            <?php _e('of ', 'tutor'); ?>
                            <span class="tutor-text-bold-caption">
                                <?php echo $course_stats['total_count']; ?>
                            </span>
                            (<?php echo $course_stats['completed_percent'] .'%'; ?>)
                        </div>
                        <?php
                            do_action('tutor_course/single/enrolled/after/lead_info/progress_bar');
                        ?>
                    </div>
                    <div class="tutor-topbar-cross-icon flex-center">
                        <?php $course_id = tutor_utils()->get_course_id_by('lesson', get_the_ID()); ?>
                        <a href="<?php echo get_the_permalink($course_id); ?>">
                            <span class="tutor-icon line-cross-line tutor-color-text-white flex-center"></span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="tutor-mobile-top-navigation tutor-bs-d-block tutor-bs-d-sm-none tutor-my-20 tutor-mx-10">
                <div class="tutor-mobile-top-nav d-grid">
                    <a href="<?php echo get_the_permalink($previous_id); ?>">
                        <span class="tutor-top-nav-icon tutor-icon previous-line design-lightgrey"></span>
                    </a>
                    <div class="tutor-top-nav-title tutor-text-regular-body  tutor-color-text-primary">
                        <?php 
                            the_title();
                        ?>
                    </div>
                </div>
            </div>

            <?php ob_start(); ?>
            <div class="tutor-quiz-wrapper tutor-quiz-wrapper d-flex justify-content-center tutor-mt-100 tutor-pb-100">
                <input type="hidden" name="tutor_quiz_id" id="tutor_quiz_id" value="<?php the_ID(); ?>">

		        <?php
		        if ($course){
			        tutor_single_quiz_top();
			        // tutor_single_quiz_content();
			        tutor_single_quiz_body();
		        }else{
			        tutor_single_quiz_no_course_belongs();
		        }
		        ?>
            </div>
            <?php echo apply_filters( 'tutor_quiz/single/wrapper', ob_get_clean() ); ?>


            <div class="tutor-lesson-sidebar tutor-mobile-sidebar">
                <?php tutor_lessons_sidebar(true, 'mobile'); ?>
            </div> 
        </div>
    </div><!-- .wrap -->

<?php
do_action( 'tutor_quiz/single/after/wrap' );
get_tutor_footer();
