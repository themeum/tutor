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
use Tutor\Components\Button;
use Tutor\Helpers\DateTimeHelper;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;

$default_review = array(
	'title'          => '',
	'review_date'    => '',
	'rating'         => 0,
	'is_bundle'      => false,
	'review_content' => '',
);

$review = wp_parse_args( $review, $default_review );
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
			<div class="tutor-badge tutor-badge-primary-soft tutor-badge-circle">
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
			<?php
				printf(
					// translators: %s - Review date.
					esc_html__( 'Reviewed on: %s', 'tutor' ),
					esc_html( DateTimeHelper::get_gmt_to_user_timezone_date( $review['review_date'], get_option( 'date_format' ) ) ?? '' )
				);
				?>
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
				<?php
				tutor_load_template(
					'demo-components.dashboard.components.star-rating',
					array(
						'rating'        => $review['rating'] ?? 0,
						'wrapper_class' => 'tutor-ratings-stars tutor-flex tutor-items-center tutor-gap-2',
						'icon_class'    => 'tutor-icon-exception4',
					)
				);
				?>
			</div>

			<!-- Actions -->
			<div class="tutor-review-actions">
				<?php
					Button::make()
						->label( __( 'Edit Review', 'tutor' ) )
						->variant( Variant::SECONDARY )
						->size( Size::X_SMALL )
						->icon( tutor_utils()->get_svg_icon( Icon::EDIT_2 ) )
						->icon_only()
						->render();
				?>
				<?php
					Button::make()
						->label( __( 'Delete Review', 'tutor' ) )
						->variant( Variant::SECONDARY )
						->size( Size::X_SMALL )
						->icon( tutor_utils()->get_svg_icon( Icon::DELETE_2 ) )
						->icon_only()
						->render();
				?>
			</div>
		</div>

		<!-- Review Text -->
		<div class="tutor-review-text">
			<?php echo esc_textarea( htmlspecialchars( stripslashes( $review['review_content'] ?? '' ) ) ); ?>
		</div>
	</div>
</div>
