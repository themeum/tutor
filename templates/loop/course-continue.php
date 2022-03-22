<?php

/**
 * Course loop continue when enrolled
 *
 * @since v.1.7.4
 * @author themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.7.4
 */

?>

    
<div  class="list-item-button">
<?php
    
    $course_id = get_the_ID();
    $enroll_btn = '
                    <a href="'. get_the_permalink(). '" class="tutor-btn tutor-pr-0 tutor-pl-0 tutor-btn-disable-outline tutor-btn-md tutor-btn-full">
                        ' . __( 'Start Learning', 'tutor' ) . '
                    </a>
                ';

    $lesson_url = tutor_utils()->get_course_first_lesson();
    $completed_percent = tutor_utils()->get_course_completed_percent();
    $is_completed_course = tutor_utils()->is_completed_course();
    $retake_course = tutor_utils()->can_user_retake_course();
    $button_class = 'tutor-btn tutor-btn-disable-outline tutor-btn-outline-fd tutor-btn-md tutor-btn-full tutor-pr-0 tutor-pl-0 ' . ( $retake_course ? ' tutor-course-retake-button' : '' );
    
    if ( $lesson_url && ! $is_completed_course ) { 
        ob_start();
        ?>
        <a href="<?php echo $lesson_url; ?>" class="<?php echo $button_class; ?>" data-course_id="<?php echo get_the_ID(); ?>">
            <?php
                if ( ! $is_completed_course && $completed_percent != 0 ) {
                    esc_html_e( 'Continue Learning', 'tutor' );
                }
                if ( $completed_percent == 0 && ! $is_completed_course ) {
                    esc_html_e( 'Start Learning', 'tutor' );
                }
            ?>
        </a>
        <?php 
        $enroll_btn = ob_get_clean();
    }
    
    echo apply_filters( 'tutor_course/loop/start/button', $enroll_btn, get_the_ID() );
?>
</div>
