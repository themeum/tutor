<?php
/**
 * Display the content
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

global $post;

$jsonData = array();
$jsonData['post_id'] = get_the_ID();
$jsonData['best_watch_time'] = 0;
$jsonData['autoload_next_course_content'] = (bool) get_tutor_option('autoload_next_course_content');

$best_watch_time = tutor_utils()->get_lesson_reading_info(get_the_ID(), 0, 'video_best_watched_time');
if ($best_watch_time > 0){
	$jsonData['best_watch_time'] = $best_watch_time;
}
?>

<?php do_action('tutor_lesson/single/before/content'); ?>

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

    <div class="tutor-topbar-item flex-center">
        <?php tutor_lesson_mark_complete_html(); ?>
    </div>
    <div class="tutor-topbar-cross-icon flex-center">
        <?php $course_id = tutor_utils()->get_course_id_by('lesson', get_the_ID()); ?>
        <a href="<?php echo get_the_permalink($course_id); ?>">
            <span class="ttr-line-cross-line color-text-white flex-center"></span>
        </a>
    </div>

</div>


<div class="tutor-lesson-content-area">

    <input type="hidden" id="tutor_video_tracking_information" value="<?php echo esc_attr(json_encode($jsonData)); ?>">
	<?php tutor_lesson_video(); ?>
	<?php the_content(); ?>
	<?php get_tutor_posts_attachments(); ?>
	<?php tutor_next_previous_pagination(); ?>
</div>

<?php do_action('tutor_lesson/single/after/content'); ?>