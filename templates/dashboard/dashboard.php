<?php
/**
 * Frontend Dashboard Template
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.3
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Alert;
use Tutor\Components\Button;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Models\CourseModel;
use TUTOR\Icon;
use TUTOR\Instructors_List;
use TUTOR\User;

$user_id                         = get_current_user_id();
$instructor_status               = tutor_utils()->instructor_status( 0, false );
$instructor_status               = is_string( $instructor_status ) ? strtolower( $instructor_status ) : '';
$is_instructor_pending           = Instructors_List::STATUS_PENDING === $instructor_status;
$is_instructor_approved          = Instructors_List::STATUS_APPROVED === $instructor_status;
$registered_as_instructor = User::registered_as_instructor( $user_id );

do_action( 'tutor_before_dashboard_content' );
tutor_load_template( 'dashboard.components.profile-completion' );

if ( $is_instructor_pending ) {
	tutor_load_template( 'dashboard.instructor.instructor-request-alert' );

	tutor_load_template( 'dashboard.instructor.dashboard-empty' );
	return;
}

if ( $is_instructor_approved && $registered_as_instructor ) {
	$hide_notice_url = add_query_arg( 'tutor_action', 'hide_instructor_approval_notice' );

	if ( get_user_meta( $user_id, User::INSTRUCTOR_APPROVAL_NOTICE_META, true ) ) {
		tutor_load_template( 'dashboard.instructor.instructor-request-alert', array( 'variant' => 'success' ) );
	}

	$course_query = CourseModel::get_courses_by_args(
		array(
			'author'         => $user_id,
			'fields'         => 'ids',
			'posts_per_page' => 1,
			'post_status'    => array( 'publish', 'private', 'draft', 'pending', 'future' ),
		)
	);

	if ( ! $course_query->found_posts ) {
		tutor_load_template( 'dashboard.instructor.dashboard-empty' );
		return;
	}
}

tutor_load_template( 'dashboard.instructor.home' );
