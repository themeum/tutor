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

use Tutor\Models\CourseModel;

?>	
<div class="list-item-button">
<?php
$user_id    = get_current_user_id();
$course_id  = get_the_ID();
$enroll_btn = '<a href="' . esc_url( get_the_permalink() ) . '" class="tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-btn-block">
					' . __( 'Start Learning', 'tutor' ) . '
				</a>
			';

$lesson_url          = tutor_utils()->get_course_first_lesson();
$completed_percent   = tutor_utils()->get_course_completed_percent();
$is_completed_course = tutor_utils()->is_completed_course();
$retake_course       = tutor_utils()->can_user_retake_course();
$button_class        = 'tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-btn-block ';
$can_complete_course = CourseModel::can_complete_course( $course_id, $user_id );
$completion_mode     = tutor_utils()->get_option( 'course_completion_process' );

if ( $retake_course && $can_complete_course && CourseModel::MODE_FLEXIBLE === $completion_mode ) {
	$button_class .= ' tutor-course-retake-button';
}

if ( $lesson_url && ! $is_completed_course ) {
	ob_start();
	$link_text = __( 'Continue Learning', 'tutor' );
	if ( 0 === (int) $completed_percent ) {
		$link_text = __( 'Start Learning', 'tutor' );
	} elseif ( $completed_percent > 0 && $completed_percent < 100 ) {
		$link_text = __( 'Continue Learning', 'tutor' );
	} elseif ( 100 === (int) $completed_percent && false === $can_complete_course ) {
		$lesson_url = CourseModel::get_review_progress_link( $course_id, $user_id );
		$link_text  = __( 'Review Progress', 'tutor' );
	} else {
		$link_text = __( 'Continue Learning', 'tutor' );
	}
	?>
	<a 	href="<?php echo esc_url( $lesson_url ); ?>" 
		class="<?php echo esc_attr( $button_class ); ?>" 
		data-course_id="<?php echo get_the_ID(); ?>">
		<?php echo esc_html( $link_text ); ?>
	</a>
	<?php
		$enroll_btn = ob_get_clean();
}

    //phpcs:ignore --printing safe data.
	echo apply_filters( 'tutor_course/loop/start/button', $enroll_btn, get_the_ID() );
?>
</div>
