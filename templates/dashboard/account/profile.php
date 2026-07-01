<?php
/**
 * Profile Template for Account
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;
use TUTOR\User;
use Tutor\Models\CourseModel;
use Tutor\Models\WithdrawModel;

$statistics      = array();
$user            = wp_get_current_user();
$show_statistics = User::is_instructor_view();

if ( $show_statistics ) {
	$statistics = array(
		array(
			'value'      => WithdrawModel::get_withdraw_summary( $user->ID )->total_income ?? 0,
			'icon'       => Icon::EARNING_FILL,
			'label'      => __( 'Total Earnings', 'tutor' ),
			'icon_class' => 'tutor-actions-success-primary',
		),
		array(
			'value'      => CourseModel::get_course_count_by_instructor( $user->ID ),
			'icon'       => Icon::COURSES_FILL,
			'label'      => __( 'Total Courses', 'tutor' ),
			'icon_class' => 'tutor-icon-brand',
		),
		array(
			'value'      => tutor_utils()->get_instructor_ratings( $user->ID )->rating_avg ?? 0,
			'icon'       => Icon::STAR_FILL,
			'label'      => __( 'Ratings', 'tutor' ),
			'icon_class' => 'tutor-text-exception4',
		),
		array(
			'value'      => tutor_utils()->get_total_students_by_instructor( $user->ID ) ?? 0,
			'icon'       => Icon::PASSED_FILL,
			'label'      => __( 'Total Students', 'tutor' ),
			'icon_class' => 'tutor-text-exception5',
		),
	);
}
?>

<div class="tutor-profile-wrapper">
	<?php require_once tutor_get_template( 'account-header' ); ?>

	<div class="tutor-user-profile tutor-my-9 tutor-sm-my-6" role="main">
		<div class="tutor-account-container">
			<?php tutor_load_template( 'user-profile' ); ?>

			<?php if ( $show_statistics ) : ?>
			<div class="tutor-user-profile-statistics">
				<?php tutor_load_template( 'dashboard.instructor.profile-statistics', array( 'statistics' => $statistics ) ); ?>
			</div>
			<?php endif; ?>

		</div>
	</div>
</div>	
