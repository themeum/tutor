<?php

/**
 * Course loop continue when enrolled
 *
 * @package Tutor\Templates
 * @subpackage CourseLoopPart
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.7.4
 */

?>	
<div class="list-item-button">
<?php

	$course_id  = get_the_ID();
	$enroll_btn = '<a href="' . esc_url( get_the_permalink() ) . '" class="tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-btn-block">
                        ' . __( 'Start Learning', 'tutor' ) . '
                    </a>
                ';

	$lesson_url          = tutor_utils()->get_course_first_lesson();
	$completed_percent   = tutor_utils()->get_course_completed_percent();
	$is_completed_course = tutor_utils()->is_completed_course();
	$retake_course       = tutor_utils()->can_user_retake_course();
	$button_class        = 'tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-btn-block ' . ( $retake_course ? ' tutor-course-retake-button' : '' );

if ( $lesson_url && ! $is_completed_course ) {
	ob_start();
	?>
		<a href="<?php echo esc_url( $lesson_url ); ?>" class="<?php echo esc_attr( $button_class ); ?>" data-course_id="<?php echo get_the_ID(); ?>">
		<?php
		if ( ! $is_completed_course && $completed_percent != 0 ) {
			esc_html_e( 'Continue Learning', 'tutor' );
		}
		if ( 0 == $completed_percent && ! $is_completed_course ) {
			esc_html_e( 'Start Learning', 'tutor' );
		}
		?>
		</a>
		<?php
		$enroll_btn = ob_get_clean();
}

    //phpcs:ignore --printing safe data.
	echo apply_filters( 'tutor_course/loop/start/button', $enroll_btn, get_the_ID() );
?>
</div>
