<?php
/**
 * Course Review Card single item component.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

// @TODO: Need to move this to utils file.
/**
 * Render star rating.
 *
 * @param float $rating Rating value (0–5).
 * @return string HTML output for stars.
 */
function render_star_rating( $rating = 0.00 ) {
	$output = '<div class="tutor-ratings-stars tutor-flex tutor-items-center tutor-gap-2" data-rating-value="' . esc_attr( $rating ) . '">';

	for ( $i = 1; $i <= 5; $i++ ) {
		if ( (int) $rating >= $i ) {
			$output .= '<i class="tutor-icon-star-bold tutor-icon-exception4" data-rating-value="' . esc_attr( $i ) . '"></i>';
		} elseif ( ( $rating - $i ) >= -0.5 ) {
			$output .= '<i class="tutor-icon-star-half-bold tutor-icon-exception4" data-rating-value="' . esc_attr( $i ) . '"></i>';
		} else {
			$output .= '<i class="tutor-icon-star-line tutor-icon-exception4" data-rating-value="' . esc_attr( $i ) . '"></i>';
		}
	}

	$output .= '</div>';

	return $output;
}

// @TODO: Replace with real data.
$review = array(
	'title'         => 'Course Title',
	'reviewed_date' => '2022-01-01',
	'rating'        => 4.5,
	'is_bundle'     => false,
	'review_text'   => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed euismod, nisl eget ultrices ultricies, orci ipsum tincidunt nisi, sit amet ultricies nisi nisl eu nisl. Donec euismod, nisl eget ultrices ultricies, orci ipsum tincidunt nisi, sit amet ultricies nisi nisl eu nisl.',
);
?>

<div class="tutor-review-card">
	<!-- Header Section -->
	<div class="tutor-review-header">
		<!-- Type Badge with Icon -->
		<?php if ( ! empty( $review['is_bundle'] ) ) : ?>
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
		<div class="tutor-review-title">
			<?php echo esc_html( $review['title'] ?? '' ); ?>
		</div>

		<!-- Review Date -->
		<div class="tutor-review-date">
			Reviewed on: <?php echo esc_html( $review['reviewed_date'] ?? '' ); ?>
		</div>
	</div>

	<!-- Divider -->
	<hr class="tutor-section-separator" />

	<!-- Review Content -->
	<div class="tutor-review-content">
		<!-- Actions and Rating -->
		<div class="tutor-review-rating-wrapper">
			<!-- Rating -->
			<div class="tutor-review-rating">
				<?php echo render_star_rating( $review['rating'] ?? 0 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>

			<!-- Actions -->
			<div class="tutor-review-actions">
				<a href="#" class="tutor-review-actions-button">
					<?php tutor_utils()->render_svg_icon( Icon::EDIT, 16, 16 ); ?>
				</a>
				<a href="#" class="tutor-review-actions-button">
					<?php tutor_utils()->render_svg_icon( Icon::DELETE, 16, 16 ); ?>
				</a>
			</div>
		</div>

		<!-- Review Text -->
		<div class="tutor-review-text">
			<?php echo wp_kses_post( nl2br( $review['review_text'] ?? '' ) ); ?>
		</div>
	</div>
</div>
