<?php
/**
 * Frontend Dashboard Template for Students
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Course;
use Tutor\Models\CourseModel;

$user_id   = get_current_user_id();
$user_data = get_userdata( $user_id );

$enrolled_course       = CourseModel::get_enrolled_courses_by_user( $user_id, array( 'private', 'publish' ) );
$enrolled_course_count = $enrolled_course ? $enrolled_course->post_count : 0;

do_action( 'tutor_before_dashboard_content' );
tutor_load_template( 'dashboard.components.profile-completion' );

if ( 0 === $enrolled_course_count ) {
	tutor_load_template( 'dashboard.student.dashboard-empty' );
	return;
}

tutor_load_template(
	'dashboard.student.stats',
	array(
		'user_id'   => $user_id,
		'user_data' => $user_data,
	)
);

tutor_load_template(
	'dashboard.student.continue-learning',
	array(
		'user_id' => $user_id,
	)
);

tutor_load_template( 'dashboard.components.tour' );
do_action( 'tutor_after_continue_learning_section' );
