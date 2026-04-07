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
use TUTOR\Dashboard;
use TUTOR\Icon;
use Tutor\Components\SvgIcon;
use Tutor\Components\Constants\Color;
use Tutor\Helpers\UrlHelper;
use Tutor\Models\CourseModel;

$user_id   = get_current_user_id();
$user_data = get_userdata( $user_id );

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
					<?php SvgIcon::make()->name( Icon::INFO )->size( 24 )->color( Color::BRAND )->render(); ?>
					<span class="tutor-small">
						<?php echo esc_html( $text ); ?>
					</span>
				</div>
				<a href="<?php echo esc_attr( Dashboard::get_account_page_url( 'settings' ) ); ?>" class="tutor-btn tutor-btn-primary-soft tutor-btn-small">
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

	$enrolled_course_link  = tutor_utils()->tutor_dashboard_url( 'courses' );
	$completed_course_link = tutor_utils()->tutor_dashboard_url( 'courses/completed-courses' );
	$active_course_link    = tutor_utils()->tutor_dashboard_url( 'courses/active-courses' );

	$time_spent = Course::get_total_course_duration( $completed_courses );
	$grid_col   = $time_spent['hours'] > 0 ? 'tutor-grid-cols-4' : 'tutor-grid-cols-3';
	?>
	<div class="tutor-grid tutor-sm-grid-cols-2 tutor-gap-5 tutor-mb-7 <?php echo esc_attr( $grid_col ); ?>">
		<a href="<?php echo esc_url( $enrolled_course_link ); ?>" class="tutor-stat-card tutor-stat-card-enrolled">
			<div class="tutor-stat-card-header">
				<h3 class="tutor-stat-card-title">
					<?php echo esc_html__( 'Enrolled Courses', 'tutor' ); ?>
				</h3>
				<div class="tutor-stat-card-icon tutor-flex">
					<?php SvgIcon::make()->name( Icon::COURSES )->size( 20 )->render(); ?>
				</div>
			</div>
			<div class="tutor-stat-card-content">
				<div class="tutor-stat-card-value">
					<?php echo esc_html( $enrolled_course_count ); ?>
				</div>
			</div>
		</a>

		<a href="<?php echo esc_url( $active_course_link ); ?>" class="tutor-stat-card tutor-stat-card-active">
			<div class="tutor-stat-card-header">
				<h3 class="tutor-stat-card-title">
					<?php echo esc_html__( 'Active', 'tutor' ); ?>
				</h3>
				<div class="tutor-stat-card-icon tutor-flex">
					<?php SvgIcon::make()->name( Icon::PLAY_LINE )->size( 20 )->render(); ?>
				</div>
			</div>
			<div class="tutor-stat-card-content">
				<div class="tutor-stat-card-value">
					<?php echo esc_html( $active_course_count ); ?>
				</div>
			</div>
		</a>

		<a href="<?php echo esc_url( $completed_course_link ); ?>" class="tutor-stat-card tutor-stat-card-completed">
			<div class="tutor-stat-card-header">
				<h3 class="tutor-stat-card-title">
					<?php echo esc_html__( 'Completed', 'tutor' ); ?>
				</h3>
				<div class="tutor-stat-card-icon tutor-flex">
					<?php SvgIcon::make()->name( Icon::COMPLETED_CIRCLE )->size( 20 )->render(); ?>
				</div>
			</div>
			<div class="tutor-stat-card-content">
				<div class="tutor-stat-card-value">
					<?php echo esc_html( $completed_course_count ); ?>
				</div>
			</div>
		</a>
		<?php if ( $time_spent['hours'] > 0 ) : ?>
		<div 
			class="tutor-stat-card tutor-stat-card-time-spent"
			@click="TutorCore.modal.showModal('tutor-time-spent-modal')"
		>
			<div class="tutor-stat-card-header">
				<h3 class="tutor-stat-card-title">
					<?php echo esc_html__( 'Time Spent', 'tutor' ); ?>
				</h3>
				<div class="tutor-stat-card-icon tutor-flex">
					<?php SvgIcon::make()->name( Icon::TIME )->size( 20 )->render(); ?>
				</div>
			</div>
			<div class="tutor-stat-card-content">
				<div class="tutor-stat-card-value">
					<?php
					echo esc_html(
						sprintf(
						/* translators: 1: total hour spent */
							__( '%1$d h+', 'tutor' ),
							$time_spent['hours']
						)
					);
					?>
				</div>
			</div>
		</div>
		<?php endif; ?>
	</div>

	<div x-data="tutorModal({ id: 'tutor-time-spent-modal' })" x-cloak>
		<template x-teleport="body">
			<div x-bind="getModalBindings()">
				<div x-bind="getBackdropBindings()"></div>
				<div x-bind="getModalContentBindings()" style="width: 354px;">
					<div class="tutor-modal-body tutor-px-9 tutor-pt-9 tutor-pb-8 tutor-text-center">
						<div class="tutor-flex tutor-justify-center">
							<img src="<?php echo esc_attr( UrlHelper::asset( 'images/illustrations/confetti.svg' ) ); ?>" alt="<?php esc_html_e( 'Confetti', 'tutor' ); ?>" />
						</div>

						<h3 class="tutor-h3 tutor-mb-2 tutor-mt-6">
							<span class="tutor-font-regular"><?php esc_html_e( 'Fantastic,', 'tutor' ); ?></span> 
							<?php echo esc_attr( $user_data->display_name ); ?>!
						</h3>

						<div class="tutor-tiny tutor-text-secondary tutor-mb-6">
							<?php echo esc_html__( "You've dedicated over", 'tutor' ); ?>
						</div>

						<h2 class="tutor-h2 tutor-text-exception4 tutor-py-6 tutor-surface-warning tutor-rounded-lg tutor-mb-6">
							<?php
							echo esc_html(
								sprintf(
								/* translators: 1: total hour spent */
									__( '%1$d+ hours', 'tutor' ),
									$time_spent['hours']
								)
							);
							?>
						</h2>
						<p class="tutor-p2 tutor-mb-7">
							<?php if ( $time_spent['minutes'] > 0 ) : ?>
								<?php echo esc_html__( "That's", 'tutor' ); ?> 
							<span class="tutor-font-medium">
								<?php
								echo esc_html(
									sprintf(
									/* translators: 1: total minutes spent */
										__( '%1$d+ minutes', 'tutor' ),
										$time_spent['minutes']
									)
								);
								?>
							</span> 
							<?php endif; ?>
							
							<?php if ( $time_spent['seconds'] > 0 ) : ?>
							<span class="tutor-font-medium">
								<?php
								echo esc_html(
									sprintf(
									/* translators: 1: total seconds spent */
										__( ', and %1$d+ seconds!', 'tutor' ),
										$time_spent['seconds']
									)
								);
								?>
							</span>
							<?php endif; ?>
							<br>
							<?php echo esc_html__( 'Incredible!', 'tutor' ); ?>
						</p>

						<button 
							class="tutor-btn tutor-btn-primary tutor-btn-large tutor-rounded-full tutor-btn-block tutor-gap-2" 
							@click="TutorCore.modal.closeModal('tutor-time-spent-modal')"
						>
							<?php SvgIcon::make()->name( Icon::HAPPY )->size( 20 )->render(); ?>
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
				href="<?php echo esc_url( tutor_utils()->tutor_dashboard_url( 'courses' ) ); ?>" 
				class="tutor-btn tutor-btn-link tutor-btn-x-small tutor-text-brand tutor-p-none tutor-min-h-0"
			>
				<?php esc_html_e( 'See All', 'tutor' ); ?>
			</a>
		</div>
		<div class="tutor-flex tutor-flex-column tutor-gap-4">
		<?php
		while ( $courses_in_progress->have_posts() ) :
			$courses_in_progress->the_post();
			$tutor_course_img    = get_tutor_course_thumbnail_src();
			$course_categories   = get_tutor_course_categories();
			$course_progress     = tutor_utils()->get_course_completed_percent( get_the_ID(), 0, true );
			$completed_number    = 0 === (int) $course_progress['completed_count'] ? 1 : (int) $course_progress['completed_count'];
			$course_learning_url = tutor_utils()->get_course_first_lesson();
			if ( get_post_type() !== tutor()->course_post_type ) {
				$course_learning_url = get_permalink();
			}
			?>
			<div class="tutor-progress-card">
				<div class="tutor-progress-card-inner" onclick="window.location.href = '<?php echo esc_url( $course_learning_url ); ?>';">
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
				</div>
				<div class="tutor-progress-card-actions">
					<?php do_action( 'tutor_course_action_btn', get_the_ID() ); ?>
				</div>
			</div>
			<?php endwhile; ?>
			<?php wp_reset_postdata(); ?>
		</div>
	</div>
	<?php
endif;
	do_action( 'tutor_after_continue_learning_section' );
?>
