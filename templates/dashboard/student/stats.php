<?php
/**
 * Student Dashboard Stats Container Template
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Models\CourseModel;
use TUTOR\Icon;

$user_id   = $user_id ?? get_current_user_id();
$user_data = $user_data ?? get_userdata( $user_id );

// Query metrics and stats data.
$enrolled_course       = CourseModel::get_enrolled_courses_by_user( $user_id, array( 'private', 'publish' ) );
$completed_courses     = CourseModel::get_completed_courses_by_user( $user_id, 0, -1, array( 'post_status' => array( 'private', 'publish' ) ) );
$has_completed_courses = is_object( $completed_courses ) && $completed_courses->have_posts();
$active_courses        = CourseModel::get_active_courses_by_user( $user_id, 0, -1, array( 'post_status' => array( 'private', 'publish' ) ) );

$enrolled_course_count  = $enrolled_course ? $enrolled_course->post_count : 0;
$completed_course_count = $has_completed_courses ? $completed_courses->post_count : 0;
$active_course_count    = is_object( $active_courses ) && $active_courses->have_posts() ? $active_courses->post_count : 0;
$enrolled_courses_ids   = $enrolled_course_count ? wp_list_pluck( $enrolled_course->posts, 'ID' ) : array();

// Time spent calculation.
$time_spent       = CourseModel::get_total_estimated_time_spent( $enrolled_courses_ids );
$is_hour_format   = $time_spent['hours'] > 0;
$has_time_spent   = $is_hour_format || $time_spent['minutes'] > 0;
$time_spent_value = $is_hour_format ? $time_spent['hours'] : $time_spent['minutes'];

$time_spent_unit = $is_hour_format
	? _x( 'h+', 'abbreviation for hours with plus sign', 'tutor' )
	: _x( 'm+', 'abbreviation for minutes with plus sign', 'tutor' );

$time_spent_unit_modal = $is_hour_format
	? _x( 'hours', 'time unit', 'tutor' )
	: _x( 'minutes', 'time unit', 'tutor' );

$time_spent_card_value = $has_time_spent ? sprintf(
	/* translators: 1: total time spent 2: Unit. */
	__( '%1$d %2$s', 'tutor' ),
	$time_spent_value,
	$time_spent_unit
) : '0';

$time_spent_modal_value = sprintf(
	/* translators: 1: total time spent 2: Unit. */
	__( '%1$d+ %2$s', 'tutor' ),
	$time_spent_value,
	$time_spent_unit_modal
);

$cards = array(
	array(
		'title' => __( 'Enrolled Courses', 'tutor' ),
		'icon'  => Icon::COURSES,
		'value' => $enrolled_course_count,
		'url'   => tutor_utils()->tutor_dashboard_url( 'courses' ),
		'class' => 'tutor-stat-card-enrolled',
	),
	array(
		'title' => __( 'Active', 'tutor' ),
		'icon'  => Icon::PLAY_LINE,
		'value' => $active_course_count,
		'url'   => tutor_utils()->tutor_dashboard_url( 'courses/active-courses' ),
		'class' => 'tutor-stat-card-active',
	),
	array(
		'title' => __( 'Completed', 'tutor' ),
		'icon'  => Icon::COMPLETED_CIRCLE,
		'value' => $completed_course_count,
		'url'   => tutor_utils()->tutor_dashboard_url( 'courses/completed-courses' ),
		'class' => 'tutor-stat-card-completed',
	),
	array(
		'title'    => __( 'Time Spent', 'tutor' ),
		'icon'     => Icon::TIME,
		'value'    => $time_spent_card_value,
		'class'    => 'tutor-stat-card-time-spent',
		'modal_id' => $has_time_spent ? 'tutor-time-spent-modal' : '',
	),
);
?>

<div class="tutor-student-dashboard-stats" x-data>
	<div class="tutor-grid tutor-sm-grid-cols-2 tutor-gap-5 tutor-mb-7 tutor-grid-cols-4">
		<?php
		foreach ( $cards as $card ) {
			tutor_load_template( 'dashboard.student.stat-card', $card );
		}
		?>
	</div>

	<?php if ( $has_time_spent ) : ?>
		<?php
		tutor_load_template(
			'dashboard.student.time-spent-modal',
			array(
				'user_data'        => $user_data,
				'time_spent'       => $time_spent,
				'time_spent_value' => $time_spent_modal_value,
			)
		);
		?>
	<?php endif; ?>
</div>
