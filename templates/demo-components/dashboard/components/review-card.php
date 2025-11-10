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
 * @return string
 */
function render_star_rating( $rating = 0.00 ) {

	$output = '<div class="tutor-ratings-stars tutor-flex tutor-items-center tutor-gap-2" data-rating-value="' . $rating . '">';

	for ( $i = 1; $i <= 5; $i++ ) {
		if ( (int) $rating >= $i ) {
			$output .= '<i class="tutor-icon-star-bold tutor-icon-exception4" data-rating-value="' . $i . '"></i>';
		} elseif ( ( $rating - $i ) >= -0.5 ) {
			$output .= '<i class="tutor-icon-star-half-bold tutor-icon-exception4" data-rating-value="' . $i . '"></i>';
		} else {
			$output .= '<i class="tutor-icon-star-line tutor-icon-exception4" data-rating-value="' . $i . '"></i>';
		}
	}

	$output .= '</div>';

	return $output;
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
					<div class="tutor-icon-exception4 tutor-p1 tutor-flex tutor-items-center" style="height: 32px;">
						<?php echo render_star_rating( $review['rating'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
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