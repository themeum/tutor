<?php
/**
 * Header
 *
 * @package Tutor\Views
 * @subpackage Tutor\Quiz
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Core\Input;
use Tutor\Models\QuizModel;

/**
 * Header data.
 *
 * @var object|null $attempt_data Attempt data.
 * @var string|null $context      Rendering context.
 * @var string|null $back_url     Optional back URL.
 */

if ( ! isset( $attempt_data ) || ! is_object( $attempt_data ) ) {
	return;
}

// Load header based on context.
$file_path = __DIR__ . '/header-context/' . ( $context ?? '' ) . '.php';
if ( ! empty( $context ) && file_exists( $file_path ) ) {
	// Prepare header data.
	$course_title = get_the_title( $attempt_data->course_id );
	$course_url   = get_permalink( $attempt_data->course_id );

	$quiz_title = get_the_title( $attempt_data->quiz_id );
	$quiz_url   = get_permalink( $attempt_data->quiz_id );

	$user_data    = get_userdata( $attempt_data->user_id );
	$student_name = $user_data ? $user_data->display_name : '';
	$student_url  = '#';

	$attempt_timing = QuizModel::get_quiz_attempt_timing( $attempt_data );
	$quiz_time      = $attempt_timing['attempt_duration'] ?? '';
	$attempt_time   = $attempt_timing['attempt_duration_taken'] ?? '';

	$question_count      = $attempt_data->total_questions;
	$total_marks         = $attempt_data->total_marks;
	$earned_marks        = $attempt_data->earned_marks;
	$unserialize_attempt = maybe_unserialize( $attempt_data->attempt_info );
	$pass_marks          = '';
	$passing_grade       = isset( $unserialize_attempt['passing_grade'] ) ? $unserialize_attempt['passing_grade'] : 0;
	$back_url            = $back_url ?? ( Input::has( 'attempt_id', Input::GET_REQUEST ) ? remove_query_arg( 'attempt_id', is_admin() ? admin_url( 'admin.php?page=tutor_quiz_attempts' ) : tutor()->current_url ) : null );

	include $file_path;
}
