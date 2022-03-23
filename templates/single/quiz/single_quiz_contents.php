<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

$course = tutor_utils()->get_course_by_quiz( get_the_ID() );
?>

<div class="tutor-single-page-top-bar tutor-d-flex tutor-justify-content-between">
    <div class="tutor-topbar-item tutor-topbar-sidebar-toggle tutor-hide-sidebar-bar flex-center tutor-d-none tutor-d-xl-flex">
        <a href="javascript:;" class="tutor-lesson-sidebar-hide-bar">
            <span class="tutor-icon-icon-light-left-line tutor-color-white flex-center"></span>
        </a>
    </div>
    <div class="tutor-topbar-item tutor-topbar-content-title-wrap flex-center">
        <?php
            if ($post->post_type === 'tutor_quiz') {
                echo '<span class="tutor-icon-quiz-filled tutor-color-white tutor-mr-4"></span>';
                echo '<span class="tutor-fs-7 tutor-color-design-white">';
                    esc_html_e( 'Quiz: ', 'tutor' );
                    the_title(); 
                echo '</span>';
            } elseif ($post->post_type === 'tutor_assignments'){
                echo '<span class="tutor-icon-assignment-filled tutor-color-white tutor-mr-4"></span>';
                echo '<span class="tutor-fs-7 tutor-color-design-white">';
                    esc_html_e( 'Assignment: ', 'tutor' );
                    the_title(); 
                echo '</span>';
            } elseif ($post->post_type === 'tutor_zoom_meeting'){
                echo '<span class="tutor-icon-zoom tutor-color-white tutor-mr-4"></span>';
                echo '<span class="tutor-fs-7 tutor-color-design-white">';
                    esc_html_e( 'Zoom Meeting: ', 'tutor' );
                    the_title(); 
                echo '</span>';
            } else{
                echo '<span class="tutor-icon-youtube-brand tutor-color-white tutor-mr-4"></span>';
                echo '<span class="tutor-fs-7 tutor-color-design-white">';
                    esc_html_e( 'Lesson: ', 'tutor' );
                    the_title(); 
                echo '</span>';
            }
        ?>
    </div>

    <div class="tutor-topbar-cross-icon tutor-ml-16 flex-center">
        <?php $course_id = tutor_utils()->get_course_id_by('lesson', get_the_ID()); ?>
        <a href="<?php echo get_the_permalink($course_id); ?>">
            <span class="tutor-icon-line-cross-line tutor-color-white flex-center"></span>
        </a>
    </div>
</div>


<?php ob_start(); ?>
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
<?php echo apply_filters( 'tutor_quiz/single/wrapper', ob_get_clean() ); ?>