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

use Tutor\Components\Modal;
use TUTOR\Icon;
use Tutor\Models\CourseModel;

if ( tutor_utils()->get_option( 'enable_profile_completion' ) ) {
	$profile_completion = tutor_utils()->user_profile_completion();
	if ( ! $profile_completion['_tutor_profile_photo']['is_set'] ) {
		$alert_message = sprintf(
			'<div class="tutor-alert tutor-primary tutor-mb-20">
				<div class="tutor-alert-text">
					<span class="tutor-alert-icon tutor-fs-4 tutor-icon-circle-info tutor-mr-12"></span>
					<span>
						%s
					</span>
				</div>
				<div class="alert-btn-group">
					<a href="%s" class="tutor-btn tutor-btn-sm">' . __( 'Click Here', 'tutor' ) . '</a>
				</div>
			</div>',
			$profile_completion['_tutor_profile_photo']['text'],
			tutor_utils()->tutor_dashboard_url( 'settings' )
		);

		echo $alert_message; //phpcs:ignore
	}
}
?>
<?php do_action( 'tutor_before_dashboard_content' ); ?>
<div class="tutor-student-dashboard" x-data>
	<?php
	$user_id           = get_current_user_id();
	$enrolled_course   = CourseModel::get_enrolled_courses_by_user( $user_id, array( 'private', 'publish' ) );
	$completed_courses = tutor_utils()->get_completed_courses_ids_by_user();
	$active_courses    = CourseModel::get_active_courses_by_user( $user_id );

	$enrolled_course_count  = $enrolled_course ? $enrolled_course->post_count : 0;
	$completed_course_count = count( $completed_courses );
	$active_course_count    = is_object( $active_courses ) && $active_courses->have_posts() ? $active_courses->post_count : 0;

	// @TODO:: Need to implement this.
	$enrolled_course_count_this_month  = 0;
	$completed_course_count_this_month = 0;
	$active_course_count_this_month    = 0;

	$enrolled_course_link  = tutor_utils()->tutor_dashboard_url( 'enrolled-courses' );
	$completed_course_link = tutor_utils()->tutor_dashboard_url( 'enrolled-courses/completed-courses' );
	$active_course_link    = tutor_utils()->tutor_dashboard_url( 'enrolled-courses/active-courses' );
	?>
	<div class="tutor-grid tutor-grid-cols-4 tutor-gap-5 tutor-mb-7">
		<a href="<?php echo esc_url( $enrolled_course_link ); ?>" class="tutor-card tutor-stat-card tutor-stat-card-enrolled">
			<div class="tutor-stat-card-header">
				<h3 class="tutor-stat-card-title">
					<?php echo esc_html__( 'Enrolled Courses', 'tutor' ); ?>
				</h3>
				<div class="tutor-stat-card-icon">
					<?php tutor_utils()->render_svg_icon( Icon::COURSES, 20, 20 ); ?>
				</div>
			</div>
			<div class="tutor-stat-card-content">
				<div class="tutor-stat-card-value">
					<?php echo esc_html( $enrolled_course_count ); ?>
				</div>
				<p class="tutor-stat-card-change">
					<?php
					// translators: %s is the number of enrolled courses this month.
					echo esc_html( sprintf( __( '%s this month', 'tutor' ), $enrolled_course_count_this_month ) );
					?>
				</p>
			</div>
		</a>
		<a href="<?php echo esc_url( $active_course_link ); ?>" class="tutor-card tutor-stat-card tutor-stat-card-active">
			<div class="tutor-stat-card-header">
				<h3 class="tutor-stat-card-title">
					<?php echo esc_html__( 'Active', 'tutor' ); ?>
				</h3>
				<div class="tutor-stat-card-icon">
					<?php tutor_utils()->render_svg_icon( Icon::PLAY_LINE, 20, 20 ); ?>
				</div>
			</div>
			<div class="tutor-stat-card-content">
				<div class="tutor-stat-card-value">
					<?php echo esc_html( $enrolled_course_count ); ?>
				</div>
				<p class="tutor-stat-card-change">
					<?php
					// translators: %s is the number of active courses this month.
					echo esc_html( sprintf( __( '%s this month', 'tutor' ), $active_course_count_this_month ) );
					?>
				</p>
			</div>
		</a>
		<a href="<?php echo esc_url( $completed_course_link ); ?>" class="tutor-card tutor-stat-card tutor-stat-card-completed">
			<div class="tutor-stat-card-header">
				<h3 class="tutor-stat-card-title">
					<?php echo esc_html__( 'Completed', 'tutor' ); ?>
				</h3>
				<div class="tutor-stat-card-icon">
					<?php tutor_utils()->render_svg_icon( Icon::COMPLETED_CIRCLE, 20, 20 ); ?>
				</div>
			</div>
			<div class="tutor-stat-card-content">
				<div class="tutor-stat-card-value">
					<?php echo esc_html( $enrolled_course_count ); ?>
				</div>
				<p class="tutor-stat-card-change">
					<?php
					// translators: %s is the number of completed courses this month.
					echo esc_html( sprintf( __( '%s this month', 'tutor' ), $completed_course_count_this_month ) );
					?>
				</p>
			</div>
		</a>
		<div 
			class="tutor-card tutor-stat-card tutor-stat-card-time-spent"
			@click="TutorCore.modal.showModal('tutor-time-spent-modal')"
		>
			<div class="tutor-stat-card-header">
				<h3 class="tutor-stat-card-title">
					<?php echo esc_html__( 'Time Spent', 'tutor' ); ?>
				</h3>
				<div class="tutor-stat-card-icon">
					<?php tutor_utils()->render_svg_icon( Icon::TIME, 20, 20 ); ?>
				</div>
			</div>
			<div class="tutor-stat-card-content">
				<div class="tutor-stat-card-value">
					375h+
				</div>
				<p class="tutor-stat-card-change">
					+2 this month
				</p>
			</div>
		</div>
	</div>

	<!-- @TODO:: Need to update this with dynamic data -->
	<div x-data="tutorModal({ id: 'tutor-time-spent-modal' })" x-cloak>
		<template x-teleport="body">
			<div x-bind="getModalBindings()">
				<div x-bind="getBackdropBindings()"></div>
				<div x-bind="getModalContentBindings()" style="width: 354px;">
					<div class="tutor-modal-body tutor-px-9 tutor-pt-9 tutor-pb-8 tutor-text-center">
						<?php tutor_utils()->render_svg_icon( Icon::CONFETTI, 32, 32, array( 'class' => 'tutor-icon-exception2' ) ); ?>

						<h3 class="tutor-h3 tutor-mb-2 tutor-mt-6"><span class="tutor-font-regular">Fantastic,</span> Johny!</h3>

						<div class="tutor-tiny tutor-text-secondary tutor-mb-6">
							<?php echo esc_html__( "You've dedicated over", 'tutor' ); ?>
						</div>

						<h2 class="tutor-h2 tutor-text-exception4 tutor-py-6 tutor-surface-l3 tutor-rounded-lg tutor-mb-6">375+ hours</h2>

						<p class="tutor-p2 tutor-mb-7">That's <span class="tutor-font-medium">1,350,000</span> minutes, and <span class="tutor-font-medium">81,000,000</span> seconds! Incredible!</p>

						<button 
							class="tutor-btn tutor-btn-primary tutor-btn-large tutor-rounded-full tutor-btn-block tutor-gap-2" 
							@click="TutorCore.modal.closeModal('tutor-time-spent-modal')"
						>
							<?php tutor_utils()->render_svg_icon( Icon::HAPPY, 20, 20 ); ?>
							<?php esc_html_e( "I'm Happy", 'tutor' ); ?>
						</button>
					</div>
				</div>
			</div>
		</template>
	</div>
</div>

<?php
/**
 * Active users in progress courses
 */
$placeholder_img     = tutor()->url . 'assets/images/placeholder.svg';
$courses_in_progress = CourseModel::get_active_courses_by_user( get_current_user_id() );
?>

<?php if ( $courses_in_progress && $courses_in_progress->have_posts() ) : ?>
	<div class="tutor-frontend-dashboard-course-progress">
		<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-text-capitalize tutor-mb-24">
			<?php esc_html_e( 'In Progress Courses', 'tutor' ); ?>
		</div>
		<?php
		while ( $courses_in_progress->have_posts() ) :
			$courses_in_progress->the_post();
			$tutor_course_img = get_tutor_course_thumbnail_src();
			$course_rating    = tutor_utils()->get_course_rating( get_the_ID() );
			$course_progress  = tutor_utils()->get_course_completed_percent( get_the_ID(), 0, true );
			$completed_number = 0 === (int) $course_progress['completed_count'] ? 1 : (int) $course_progress['completed_count'];
			?>
			<div class="tutor-course-progress-item tutor-card tutor-mb-20">
				<div class="tutor-row tutor-gx-0">
					<div class="tutor-col-lg-4">
						<div class="tutor-ratio tutor-ratio-3x2">
							<img class="tutor-card-image-left" src="<?php echo empty( $tutor_course_img ) ? esc_url( $placeholder_img ) : esc_url( $tutor_course_img ); ?>" alt="<?php the_title(); ?>" loading="lazy">
						</div>
					</div>

					<div class="tutor-col-lg-8 tutor-align-self-center">
						<div class="tutor-card-body">
						<?php if ( $course_rating ) : ?>
								<div class="tutor-ratings tutor-mb-4">
									<?php tutor_utils()->star_rating_generator( $course_rating->rating_avg ); ?>
									<div class="tutor-ratings-count">
										<?php echo esc_html( number_format( $course_rating->rating_avg, 2 ) ); ?>
									</div>
								</div>
							<?php endif; ?>

							<div class="tutor-course-progress-item-title tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-12">
							<?php the_title(); ?>
							</div>

							<div class="tutor-d-flex tutor-fs-7 tutor-mb-32">
								<span class="tutor-color-muted tutor-mr-4"><?php esc_html_e( 'Completed Lessons:', 'tutor' ); ?></span>
								<span class="tutor-fw-medium tutor-color-black">
									<span>
									<?php echo esc_html( $course_progress['completed_count'] ); ?>
									</span>
								<?php esc_html_e( 'of', 'tutor' ); ?>
									<span>
									<?php echo esc_html( $course_progress['total_count'] ); ?>
									</span>
								<?php echo esc_html( _n( 'lesson', 'lessons', $completed_number, 'tutor' ) ); ?>
								</span>
							</div>

							<div class="tutor-row tutor-align-center">
								<div class="tutor-col">
									<div class="tutor-progress-bar tutor-mr-16" style="--tutor-progress-value:<?php echo esc_attr( $course_progress['completed_percent'] ); ?>%"><span class="tutor-progress-value" area-hidden="true"></span></div>
								</div>

								<div class="tutor-col-auto">
									<span class="progress-percentage tutor-fs-7 tutor-color-muted">
										<span class="tutor-fw-medium tutor-color-black ">
										<?php echo esc_html( $course_progress['completed_percent'] . '%' ); ?>
										</span><?php esc_html_e( 'Complete', 'tutor' ); ?>
									</span>
								</div>
							</div>
						</div>
					</div>
					<a class="tutor-stretched-link" href="<?php the_permalink(); ?>"></a>
				</div>
			</div>
			<?php endwhile; ?>
		<?php wp_reset_postdata(); ?>
	</div>
<?php endif; ?>
