<?php
/**
 * Student's Quiz Review Backend
 */

$attempt_id   = (int) sanitize_text_field( $_GET['view_quiz_attempt_id'] );
$attempt      = tutor_utils()->get_attempt( $attempt_id );
$attempt_data = $attempt;
$user_id      = tutor_utils()->avalue_dot( 'user_id', $attempt_data );
$quiz_id 	  = $attempt && isset( $attempt->quiz_id ) ? $attempt->quiz_id : 0;
if ( ! $attempt ) {
	tutor_utils()->tutor_empty_state( __( 'Attemp not found', 'tutor' ) );
	return;
}
if ( 0 === $quiz_id ) {
	tutor_utils()->tutor_empty_state( __( 'Attemp not found', 'tutor' ) );
	return;
}

$quiz_attempt_info = tutor_utils()->quiz_attempt_info( $attempt->attempt_info );
$answers           = tutor_utils()->get_quiz_answers_by_attempt_id( $attempt->attempt_id );

$user_id = tutor_utils()->avalue_dot( 'user_id', $attempt );
$user    = get_userdata( $user_id );
?>

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

		// $attempt_details 	= \TUTOR\Quiz::attempt_details( 102 );
		// $feedback 			= wp_kses_post( $_POST['feedback'] );
		// $attempt_info 		= isset( $attempt_details->attempt_info ) ? unserialize( $attempt_details->attempt_info ) : false;
		// if ( $attempt_info ) {
		// 	$attempt_info = unserialize( $attempt_details->attempt_info );
		// 	//$attempt_info->instructor_feedback = $feedback;
		// 	echo "<pre>";
		// 	print_r( $attempt_info );
		// 	exit;
		// 	do_action( 'tutor_quiz/attempt/submitted/feedback', $attempt_details );
		// 	$attempt_details->attempt_info = serialize( $attempt_details->attempt_info );
		// 	$update = \TUTOR\Quiz::update_attempt_info( $attempt_details->attempt_id, $attempt_details->attempt_info );
		// 	if ( $update ) {
		// 		var_dump( $update );
		// 	} else {
		// 		var_dump( $update );
		// 	}
		// }
		?>
</div>
