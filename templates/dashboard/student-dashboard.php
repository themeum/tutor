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

use TUTOR\Icon;
use Tutor\Models\CourseModel;

$user_id = get_current_user_id();

if ( tutor_utils()->get_option( 'enable_profile_completion' ) ) {
	$profile_completion = tutor_utils()->user_profile_completion( $user_id );

	$photo_data = $profile_completion['_tutor_profile_photo'] ?? array();
	$is_set     = $photo_data['is_set'] ?? null;
	$text       = $photo_data['text'] ?? '';
	if ( empty( $is_set ) ) {
		?>
		<div class="tutor-border tutor-mb-7 tutor-rounded-2xl tutor-surface-l1 tutor-p-5">
			<div class="tutor-flex tutor-items-center tutor-justify-between">
				<div class="tutor-flex tutor-items-center tutor-gap-2">
					<?php tutor_utils()->render_svg_icon( Icon::INFO, 24, 24, array( 'class' => 'tutor-icon-brand' ) ); ?>
					<span class="tutor-small">
						<?php echo esc_html( $text ); ?>
					</span>
				</div>
				<a href="<?php echo esc_attr( tutor_utils()->tutor_dashboard_url( 'settings' ) ); ?>" class="tutor-btn tutor-btn-primary-soft tutor-btn-small">
					<?php esc_html_e( 'Click Here', 'tutor' ); ?>
				</a>
			</div>
		</div>
		<?php
	}
}
?>
<?php do_action( 'tutor_before_dashboard_content' ); ?>
<div class="tutor-student-dashboard" x-data>
	<?php
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
	<div class="tutor-grid tutor-grid-cols-4 tutor-sm-grid-cols-2 tutor-gap-5 tutor-mb-7">
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
$courses_in_progress = CourseModel::get_active_courses_by_user( $user_id, 0, 2 );
?>

<?php if ( $courses_in_progress && $courses_in_progress->have_posts() ) : ?>
	<div class="tutor-student-dashboard-courses">
		<div class="tutor-flex tutor-items-center tutor-justify-between tutor-mb-4">
			<div class="tutor-small tutor-font-medium">
				<?php esc_html_e( 'Continue Learning', 'tutor' ); ?>
			</div>
			<a 
				href="<?php echo esc_url( tutor_utils()->tutor_dashboard_url( 'enrolled-courses' ) ); ?>" 
				class="tutor-btn tutor-btn-link tutor-btn-x-small tutor-text-brand tutor-p-none tutor-min-h-0"
			>
				<?php esc_html_e( 'See All', 'tutor' ); ?>
			</a>
		</div>
		<div class="tutor-flex tutor-flex-column tutor-gap-4">
		<?php
		while ( $courses_in_progress->have_posts() ) :
			$courses_in_progress->the_post();
			$tutor_course_img  = get_tutor_course_thumbnail_src();
			$course_categories = get_tutor_course_categories();
			$course_progress   = tutor_utils()->get_course_completed_percent( get_the_ID(), 0, true );
			$completed_number  = 0 === (int) $course_progress['completed_count'] ? 1 : (int) $course_progress['completed_count'];
			?>
			<div class="tutor-card tutor-progress-card">
				<div class="tutor-progress-card-thumbnail">
					<img src="<?php echo esc_url( $tutor_course_img ); ?>" alt="<?php the_title(); ?>" loading="lazy">
				</div>
				<div class="tutor-progress-card-content">
					<div class="tutor-progress-card-header">
						<?php if ( ! empty( $course_categories ) ) : ?>
							<div class="tutor-progress-card-category">
								<?php echo esc_html( implode( ', ', wp_list_pluck( $course_categories, 'name' ) ) ); ?>
							</div>
						<?php endif; ?>
						<h3 class="tutor-progress-card-title"><?php the_title(); ?></h3>
					</div>
					<div class="tutor-progress-card-progress">
						<div class="tutor-progress-card-details">
							<?php
								echo esc_html(
									sprintf(
									/* translators: 1: completed lesson count, 2: total lesson count */
										__( '%1$s of %2$s lessons', 'tutor' ),
										$course_progress['completed_count'],
										$course_progress['total_count']
									)
								);
							?>
							<span class="tutor-progress-card-separator">•</span>
							<?php
								printf(
								/* translators: %s: completed percentage */
									esc_html__( '%s%% Complete', 'tutor' ),
									esc_html( $course_progress['completed_percent'] )
								);
							?>
						</div>
						<div class="tutor-progress-card-bar">
							<div class="tutor-progress-bar" data-tutor-animated>
								<div class="tutor-progress-bar-fill" style="--tutor-progress-width: <?php echo esc_attr( $course_progress['completed_percent'] ); ?>%;"></div>
							</div>
						</div>
					</div>
				</div>
				<div class="tutor-progress-card-actions">
					<a href="<?php the_permalink(); ?>" class="tutor-btn tutor-btn-primary tutor-btn-small">
						<?php esc_html_e( 'Resume', 'tutor' ); ?>
					</a>
				</div>
				<div class="tutor-progress-card-footer">
					<a href="<?php the_permalink(); ?>" class="tutor-btn tutor-btn-primary tutor-btn-block">
						<?php esc_html_e( 'Resume', 'tutor' ); ?>
					</a>
				</div>
			</div>
			<?php endwhile; ?>
			<?php wp_reset_postdata(); ?>
		</div>
	</div>
<?php endif; ?>
