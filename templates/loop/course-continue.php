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
    $enroll_btn = '<div class="tutor-loop-cart-btn-wrap"><a href="'. get_the_permalink(). '" class="tutor-btn tutor-btn-icon- tutor-btn-disable-outline tutor-btn-md tutor-btn-full">' . __( 'Check This Course', 'tutor' ) . '</a></div>';
    $lesson_url = tutor_utils()->get_course_first_lesson();
    $completed_lessons = tutor_utils()->get_completed_lesson_count_by_course();
    $completed_percent = tutor_utils()->get_course_completed_percent();
    $is_completed_course = tutor_utils()->is_completed_course();
    $retake_course = tutor_utils()->get_option( 'course_retake_feature', false ) && ( $is_completed_course || $completed_percent >= 100 );

    if ( $lesson_url && ! $is_completed_course ) { 
        $button_class = 'tutor-btn tutor-btn-icon- tutor-btn-disable-outline tutor-btn-md tutor-btn-full ' . ( $retake_course ? ' tutor-course-retake-button' : '' );
        ?>
        <a href="<?php echo $lesson_url; ?>" class="<?php echo $button_class; ?>" data-course_id="<?php echo get_the_ID(); ?>">
            <?php
                if ( $retake_course ) {
                    _e( 'Retake This Course', 'tutor' );
                } 
                if ( ! $is_completed_course && $completed_percent != 0 ) {
                    _e( 'Continue Course', 'tutor' );
                }
                if ( $completed_percent == 0 && ! $is_completed_course ) {
                    esc_html_e( 'Start Course', 'tutor' );
                }
            ?>
        </a>
        <?php 
    } else {
        echo $enroll_btn;
    }
?>
</div>