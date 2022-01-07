<?php
/**
 * Tutor Course Progress for enrolled courses
 *
 * @package TutorEnrolledCourseProgress
 *
 * @since v2.0.0
 */

 $course_id           = get_the_ID();
 $course_progress     = tutor_utils()->get_course_completed_percent( $course_id, 0, true ); 
?>

<div class="list-item-progress tutor-mt-16 tutor-px-20">
    <div class="text-regular-body tutor-color-text-subsued tutor-bs-d-flex tutor-bs-align-items-center tutor-bs-justify-content-between">
        <span class="progress-steps">
            <?php echo esc_html( $course_progress['completed_count'] ); ?>/<?php echo esc_html( $course_progress['total_count'] ); ?>
        </span>
        <span class="progress-percentage"> 
            <?php echo esc_html( $course_progress['completed_percent'] . '%' ); ?>
            <?php esc_html_e( 'Complete', 'tutor' ); ?>
        </span>
    </div>
    <div class="progress-bar tutor-mt-10" style="--progress-value:<?php echo esc_attr( $course_progress['completed_percent'] ); ?>%;">
        <span class="progress-value"></span>
    </div>
</div>