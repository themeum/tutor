<?php
/**
 * Course Review Card Component
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

// Sample data - replace with actual data.
$reviews = array(
	array(
		'type'          => 'Bundle',
		'title'         => 'Drawing for Beginners Level -2',
		'reviewed_date' => 'September 26, 2025',
		'rating'        => 4.5,
		'review_text'   => "I've finished and I really did enjoy this course. Dan is a great teacher and I was able to learn a lot here. Before this I had tried watching random tutorial videos on youtube but in this course I feel like I could absorb better when there are structured lessons and assignments to follow. Thank you Dan, I had a good time!",
		'is_bundle'     => true,
	),
	array(
		'type'          => 'Course',
		'title'         => 'Web Design with Figma: Building Striking Compositions',
		'reviewed_date' => 'September 26, 2025',
		'rating'        => 3.5,
		'review_text'   => 'Great course for beginners, Good explanations but I would love if they provide cheat sheet after every class to revise and for quick reference.',
		'is_bundle'     => false,
	),
);

/**
 * Render star rating
 *
 * @param float $rating Rating value (0-5).
 * @return void
 */
function render_star_rating( $rating ) {
	$full_stars  = floor( $rating );
	$half_star   = ( $rating - $full_stars ) >= 0.5;
	$empty_stars = 5 - $full_stars - ( $half_star ? 1 : 0 );
	?>
	<div class="tutor-flex tutor-items-center tutor-gap-1">
		<?php
		// Full stars.
		for ( $i = 0; $i < $full_stars; $i++ ) :
			?>
			<svg class="tutor-w-5 tutor-h-5 tutor-text-warning-500" fill="currentColor" viewBox="0 0 20 20">
				<path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
			</svg>
			<?php
		endfor;

		// Half star.
		if ( $half_star ) :
			?>
			<svg class="tutor-w-5 tutor-h-5 tutor-text-warning-500" fill="currentColor" viewBox="0 0 20 20">
				<defs>
					<linearGradient id="half-star">
						<stop offset="50%" stop-color="currentColor" />
						<stop offset="50%" stop-color="#D1D5DB" stop-opacity="1" />
					</linearGradient>
				</defs>
				<path fill="url(#half-star)" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
			</svg>
			<?php
		endif;

		// Empty stars.
		for ( $i = 0; $i < $empty_stars; $i++ ) :
			?>
			<svg class="tutor-w-5 tutor-h-5 tutor-text-gray-300" fill="currentColor" viewBox="0 0 20 20">
				<path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
			</svg>
			<?php
		endfor;
		?>
	</div>
	<?php
}
?>

<section class="tutor-flex tutor-flex-column tutor-gap-4">
	<h5 class="tutor-h5">My Reviews</h5>

	<div class="tutor-flex tutor-flex-column tutor-gap-4">
		<?php foreach ( $reviews as $review ) : ?>
			<div class="tutor-surface-l1 tutor-rounded-lg tutor-border tutor-flex tutor-flex-column">
				<!-- Header Section -->
				<div class="tutor-flex tutor-flex-column tutor-gap-2 tutor-p-6">
					<!-- Type Badge with Icon -->
					<?php if ( $review['is_bundle'] ) : ?>
						<div class="tutor-badge tutor-badge-exception tutor-badge-circle">
							<?php tutor_utils()->render_svg_icon( Icon::BUNDLE, 16, 16 ); ?>
							<span class="tutor-text-sm tutor-font-medium">Bundle</span>
						</div>
					<?php else : ?>
						<div class="tutor-badge tutor-badge-primary tutor-badge-circle">
							<?php tutor_utils()->render_svg_icon( Icon::COURSES, 16, 16 ); ?>
							<span class="tutor-text-sm tutor-font-medium">Course</span>
						</div>
					<?php endif; ?>

					<!-- Course Title -->
					<h6 class="tutor-p1 tutor-font-medium tutor-mt-1">
						<?php echo esc_html( $review['title'] ); ?>
					</h6>

					<!-- Review Date -->
					<p class="tutor-p3 tutor-text-subdued">
						Reviewed on: <?php echo esc_html( $review['reviewed_date'] ); ?>
					</p>
				</div>

				<!-- Divider -->
				<hr class="tutor-section-separator" />

				<!-- Review -->
				<div class="tutor-flex tutor-flex-column tutor-p-6 tutor-gap-5">
					<!-- Rating Section -->
					<div class="tutor-p1 tutor-icon-exception4 tutor-flex tutor-items-center" style="height: 32px;">
						<?php tutor_utils()->star_rating_generator( $review['rating'] ); ?>
					</div>

					<!-- Review Text -->
					<div class="tutor-p1 tutor-text-secondary">
						<?php echo wp_kses_post( nl2br( $review['review_text'] ) ); ?>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>