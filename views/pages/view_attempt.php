<?php
/**
 * Student's Quiz Review Backend
 *
 * @package Tutor\Views
 * @subpackage Tutor\Uninstall
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use TUTOR\Input;
use Tutor\Models\QuizModel;

$attempt_id    = Input::get( 'view_quiz_attempt_id', 0, Input::TYPE_INT );
$attempt       = tutor_utils()->get_attempt( $attempt_id );
$attempt_data  = $attempt;
$user_id       = tutor_utils()->avalue_dot( 'user_id', $attempt_data );
$quiz_id       = $attempt && isset( $attempt->quiz_id ) ? $attempt->quiz_id : 0;
$course_id     = tutor_utils()->avalue_dot( 'course_id', $attempt_data );
$is_instructor = tutor_utils()->is_instructor_of_this_course( get_current_user_id(), $course_id );
if ( ! $attempt ) {
	tutor_utils()->tutor_empty_state( __( 'Attempt not found', 'tutor' ) );
	return;
}
if ( 0 === $quiz_id ) {
	tutor_utils()->tutor_empty_state( __( 'Attempt not found', 'tutor' ) );
	return;
}

if ( ! current_user_can( 'manage_options' ) && ! $is_instructor ) {
	tutor_utils()->tutor_empty_state( __( 'Access denied!', 'tutor' ) );
	return;
}

$quiz_attempt_info = tutor_utils()->quiz_attempt_info( $attempt->attempt_info );
$answers           = QuizModel::get_quiz_answers_by_attempt_id( $attempt->attempt_id );

$user_id = tutor_utils()->avalue_dot( 'user_id', $attempt );
$user    = get_userdata( $user_id );
?>

<div class="tutor-admin-wrap">
	<div class="tutor-quiz-attempt-details-wrapper">
		<?php
			tutor_load_template_from_custom_path(
				tutor()->path . '/views/quiz/attempt-details.php',
				array(
					'attempt_id'   => $attempt_id,
					'attempt_data' => $attempt_data,
					'user_id'      => $user_id,
					'context'      => 'backend-dashboard-students-attempts',
				)
			);
			?>
	</div>

	<div class="tutor-admin-body">
		<?php
			/**
			 * Load Instructor Feedback template
			 * pass quiz id
			 *
			 * @since v2.0.0
			 */
			tutor_load_template_from_custom_path(
				tutor()->path . 'views/quiz/instructor-feedback.php',
				array( 'attempt_data' => $attempt_data )
			);
			?>
	</div>
</div>
