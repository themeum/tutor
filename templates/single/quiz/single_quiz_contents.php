<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

$course = tutor_utils()->get_course_by_quiz( get_the_ID() );
?>

<div class="tutor-single-page-top-bar d-flex justify-content-between">
    <div class="tutor-topbar-item tutor-topbar-sidebar-toggle tutor-hide-sidebar-bar flex-center tutor-bs-d-none tutor-bs-d-xl-flex">
        <a href="javascript:;" class="tutor-lesson-sidebar-hide-bar">
            <span class="ttr-icon-light-left-line tutor-color-text-white flex-center"></span>
        </a>
    </div>
    <div class="tutor-topbar-item tutor-topbar-content-title-wrap flex-center">
        <?php

        if ($post->post_type === 'tutor_quiz') {
            echo wp_kses_post( '<span class="ttr-quiz-filled tutor-color-text-white tutor-mr-5"></span>' );
            echo wp_kses_post( '<span class="text-regular-caption tutor-color-design-white">' );
            esc_html_e( 'Quiz: ', 'tutor' );
            the_title(); 
            echo wp_kses_post( '</span>' );
        } elseif ($post->post_type === 'tutor_assignments'){
            echo wp_kses_post( '<span class="ttr-assignment-filled tutor-color-text-white tutor-mr-5"></span>' );
            echo wp_kses_post( '<span class="text-regular-caption tutor-color-design-white">' );
            esc_html_e( 'Assignment: ', 'tutor' );
            the_title(); 
            echo wp_kses_post( '</span>' );
        } elseif ($post->post_type === 'tutor_zoom_meeting'){
            echo wp_kses_post( '<span class="ttr-zoom-brand tutor-color-text-white tutor-mr-5"></span>' );
            echo wp_kses_post( '<span class="text-regular-caption tutor-color-design-white">' );
            esc_html_e( 'Zoom Meeting: ', 'tutor' );
            the_title(); 
            echo wp_kses_post( '</span>' );
        } else{
            echo wp_kses_post( '<span class="ttr-youtube-brand tutor-color-text-white tutor-mr-5"></span>' );
            echo wp_kses_post( '<span class="text-regular-caption tutor-color-design-white">' );
            esc_html_e( 'Lesson: ', 'tutor' );
            the_title(); 
            echo wp_kses_post( '</span>' );
        }

        ?>
    </div>

    <div class="tutor-topbar-cross-icon flex-center">
        <?php $course_id = tutor_utils()->get_course_id_by('lesson', get_the_ID()); ?>
        <a href="<?php echo get_the_permalink($course_id); ?>">
            <span class="ttr-line-cross-line tutor-color-text-white flex-center"></span>
        </a>
    </div>

</div>


<div class="tutor-quiz-single-wrap ">
	<input type="hidden" name="tutor_quiz_id" id="tutor_quiz_id" value="<?php the_ID(); ?>">

	<?php
	if ( $course ) {
		tutor_single_quiz_top();
		tutor_single_quiz_body();
	} else {
		tutor_single_quiz_no_course_belongs();
	}
	?>
</div>
