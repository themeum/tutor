<?php
/**
 * Course Card Component
 * Reusable course card component for dashboard
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Default values - all data must be passed from parent.
$image_url       = isset( $image_url ) ? $image_url : '';
$course_title    = isset( $title ) ? $title : '';
$rating_avg      = isset( $rating_avg ) ? $rating_avg : 0;
$rating_count    = isset( $rating_count ) ? $rating_count : 0;
$learners        = isset( $learners ) ? $learners : 0;
$instructor      = isset( $instructor ) ? $instructor : '';
$instructor_url  = isset( $instructor_url ) ? $instructor_url : '#';
$provider        = isset( $provider ) ? $provider : '';
$show_bestseller = isset( $show_bestseller ) ? $show_bestseller : false;
$price           = isset( $price ) ? $price : '';
$original_price  = isset( $original_price ) ? $original_price : '';
$permalink       = isset( $permalink ) ? $permalink : '#';

?>
<div class="tutor-card tutor-card--rounded-2xl tutor-card--padding-small tutor-course-card">
	<a href="<?php echo esc_url( $permalink ); ?>" class="tutor-course-card-thumbnail">
		<div class="tutor-ratio tutor-ratio-16x9">
			<img 
				src="<?php echo esc_url( $image_url ); ?>" 
				alt="<?php echo esc_attr( $course_title ); ?>" 
				loading="lazy"
			/>
		</div>
		<?php if ( $show_bestseller ) : ?>
			<span class="tutor-badge tutor-badge-primary tutor-course-card-badge">
				<?php esc_html_e( 'Bestseller', 'tutor' ); ?>
			</span>
		<?php endif; ?>
	</a>

	<div class="tutor-card-body">
		<?php if ( $rating_avg > 0 ) : ?>
			<div class="tutor-course-card-rating">
				<div class="tutor-ratings">
					<?php
					tutor_load_template(
						'demo-components.dashboard.components.star-rating',
						array(
							'rating'              => $rating_avg,
							'wrapper_class'       => 'tutor-course-card-ratings-stars',
							'icon_class'          => '',
							'show_rating_average' => true,
						)
					);
					?>
					<?php if ( $rating_count > 0 ) : ?>
						<div class="tutor-ratings-count">
							(<?php echo esc_html( number_format_i18n( $rating_count ) ); ?>)
						</div>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>

		<h3 class="tutor-course-card-title">
			<a href="<?php echo esc_url( $permalink ); ?>">
				<?php echo esc_html( $course_title ); ?>
			</a>
		</h3>

		<div class="tutor-course-card-meta">
			<?php if ( $learners > 0 ) : ?>
				<span>
					<?php
					/* translators: %d: number of learners */
					echo esc_html( sprintf( _n( '%d Learner', '%d Learners', $learners, 'tutor' ), $learners ) );
					?>
				</span>
			<?php endif; ?>
			<?php if ( ! empty( $instructor ) ) : ?>
				<?php if ( $learners > 0 ) : ?>
					<span class="tutor-course-card-separator"></span>
				<?php endif; ?>
				<span class="tutor-course-card-instructor">
					<?php echo esc_html( $instructor ); ?>
				</span>
			<?php endif; ?>
			<?php if ( ! empty( $provider ) ) : ?>
				<?php if ( $learners > 0 || ! empty( $instructor ) ) : ?>
					<span class="tutor-course-card-separator"></span>
				<?php endif; ?>
				<span>
					<?php
					/* translators: %s: provider name */
					echo esc_html( sprintf( __( 'by %s', 'tutor' ), $provider ) );
					?>
				</span>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( ! empty( $price ) ) : ?>
		<div class="tutor-course-card-footer">
			<span class="tutor-course-card-price">
				<?php echo esc_html( $price ); ?>
			</span>
			<?php if ( ! empty( $original_price ) ) : ?>
				<del class="tutor-course-card-price-original">
					<?php echo esc_html( $original_price ); ?>
				</del>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>

