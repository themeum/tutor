<?php
/**
 * Quiz header
 *
 * @package Tutor\Views
 * @subpackage Tutor\Quiz
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

// Load header based on context.
$file_path = __DIR__ . '/header-context/' . $context . '.php';
if ( $context && file_exists( $file_path ) ) {
	// Prepare header data.
	$course_title = get_the_title( $attempt_data->course_id );
	$course_url   = get_permalink( $attempt_data->course_id );

	$quiz_title = get_the_title( $attempt_data->quiz_id );
	$quiz_url   = get_permalink( $attempt_data->quiz_id );

	$user_data    = get_userdata( $attempt_data->user_id );
	$student_name = $user_data->display_name;
	$student_url  = '#';

	extract( \Tutor\Models\QuizModel::get_quiz_attempt_timing( $attempt_data ) ); // $attempt_duration, $attempt_duration_taken;

	$quiz_time    = $attempt_duration;
	$attempt_time = $attempt_duration_taken;

	$question_count      = $attempt_data->total_questions;
	$total_marks         = $attempt_data->total_marks;
	$earned_marks        = $attempt_data->earned_marks;
	$unserialize_attempt = unserialize( $attempt_data->attempt_info );
	$pass_marks          = '';
	$passing_grade       = isset( $unserialize_attempt['passing_grade'] ) ? $unserialize_attempt['passing_grade'] : 0;
	$back_url            = isset( $back_url ) ? $back_url : ( isset( $_GET['view_quiz_attempt_id'] ) ? remove_query_arg( 'view_quiz_attempt_id', tutor()->current_url ) : null );

	include $file_path;
}

