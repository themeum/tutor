<?php
/**
 * Progress Card Component
 * Reusable progress card component for dashboard
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

// Default values.
$image_url         = isset( $image_url ) ? $image_url : '';
$category          = isset( $category ) ? $category : '';
$course_title      = isset( $course_title ) ? $course_title : '';
$lessons_completed = isset( $lessons_completed ) ? $lessons_completed : 0;
$lessons_total     = isset( $lessons_total ) ? $lessons_total : 0;
$progress_percent  = isset( $progress_percent ) ? $progress_percent : 0;

?>
<div class="tutor-card tutor-progress-card">
	<?php if ( ! empty( $image_url ) ) : ?>
		<div class="tutor-progress-card-thumbnail">
			<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $course_title ); ?>" />
		</div>
	<?php endif; ?>
	<div class="tutor-progress-card-content">
		<?php if ( ! empty( $category ) || ! empty( $course_title ) ) : ?>
			<div class="tutor-progress-card-header">
				<?php if ( ! empty( $category ) ) : ?>
					<div class="tutor-progress-card-category">
						<?php echo esc_html( $category ); ?>
					</div>
				<?php endif; ?>
				<?php if ( ! empty( $course_title ) ) : ?>
					<h3 class="tutor-progress-card-title">
						<?php echo esc_html( $course_title ); ?>
					</h3>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<?php if ( $lessons_total > 0 || $progress_percent > 0 ) : ?>
			<div class="tutor-progress-card-progress">
				<?php if ( $lessons_total > 0 ) : ?>
					<div class="tutor-progress-card-details">
						<?php
						echo esc_html( $lessons_completed ) . ' ' . esc_html__( 'of', 'tutor' ) . ' ' . esc_html( $lessons_total ) . ' ' . esc_html__( 'lessons', 'tutor' );
						?>
						<span class="tutor-progress-card-separator">•</span>
						<?php echo esc_html( $progress_percent ); ?>% <?php echo esc_html__( 'Complete', 'tutor' ); ?>
					</div>
				<?php endif; ?>
				<?php if ( $progress_percent > 0 ) : ?>
					<div class="tutor-progress-card-bar">
						<div class="tutor-progress-bar" data-tutor-animated>
							<div class="tutor-progress-bar-fill" style="--tutor-progress-width: <?php echo esc_attr( $progress_percent ); ?>%;"></div>
						</div>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</div>

