<?php
/**
 * Recent Student Review Item Component
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;
use Tutor\Components\Avatar;
use Tutor\Components\Constants\Size;

?>

<div class="tutor-dashboard-home-review">
	<div class="tutor-flex tutor-gap-4 tutor-items-start">
		<!-- Avatar -->
		<div class="tutor-dashboard-home-review-avatar">
			<?php Avatar::make()->src( $review['user']['avatar'] )->initials( $review['user']['name'] )->size( Size::SIZE_48 )->render(); ?>
		</div>

		<!-- Review Content -->
		<div class="tutor-flex-1 tutor-flex tutor-flex-column tutor-gap-3">
			<!-- Header: Name and Rating -->
			<div class="tutor-flex tutor-justify-between tutor-items-start">
				<div class="tutor-flex tutor-flex-column tutor-gap-1">
					<div class="tutor-small tutor-font-semibold">
						<?php echo esc_html( $review['user']['name'] ); ?>
					</div>
					<div class="tutor-flex tutor-gap-5 tutor-items-center">
						<span class="tutor-badge"><?php echo esc_html( $review['course_name'] ); ?></span>
						<span class="tutor-text-subdued tutor-tiny"><?php echo esc_html( human_time_diff( strtotime( $review['date'] ) ) . ' ago' ); ?></span>
					</div>
				</div>

				<!-- Star Rating -->
				<div>
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
			</div>

			<!-- Review Text -->
			<div class="tutor-tiny tutor-text-secondary">
				<?php echo esc_html( $review['review_text'] ); ?>
			</div>

			<!-- Actions -->
			<div class="tutor-flex tutor-items-center tutor-gap-6">
				<button class="tutor-dashboard-home-review-action">
					<?php tutor_utils()->render_svg_icon( Icon::THUMB, 16, 16, array( 'class' => 'tutor-icon-secondary' ) ); ?>
					<span>
						<?php
						printf(
							// translators: %d: Number of helpful votes.
							esc_html__( 'Helpful (%d)', 'tutor' ),
							esc_html( $review['helpful_count'] ?? 0 )
						);
						?>
					</span>
				</button>
				<button class="tutor-dashboard-home-review-action">
					<?php tutor_utils()->render_svg_icon( Icon::COMMENTS, 16, 16, array( 'class' => 'tutor-icon-secondary' ) ); ?>
					<span><?php esc_html_e( 'Reply', 'tutor' ); ?></span>
				</button>
			</div>
		</div>
	</div>
</div>
