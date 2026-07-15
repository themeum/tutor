<?php
/**
 * Recent Student Review Item Component
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Avatar;
use Tutor\Components\StarRating;
use Tutor\Components\Constants\Size;

?>
<div class="tutor-dashboard-home-review">
	<div class="tutor-flex tutor-gap-4 tutor-items-start">
		<!-- Avatar -->
		<div class="tutor-dashboard-home-review-avatar">
			<?php Avatar::make()->user( $review['user']['id'] )->size( Size::SIZE_48 )->render(); ?>
		</div>

		<!-- Review Content -->
		<div class="tutor-flex-1 tutor-flex tutor-flex-column tutor-gap-4">
			<!-- Header: Name and Rating -->
			<div class="tutor-flex tutor-justify-between tutor-items-start">
				<div class="tutor-flex tutor-flex-column tutor-gap-1 tutor-flex-1">
					<div class="tutor-small tutor-font-semibold">
						<?php echo esc_html( $review['user']['name'] ); ?>
					</div>
					<div class="tutor-flex tutor-gap-5 tutor-items-center tutor-sm-flex-column tutor-sm-items-normal tutor-sm-gap-4">
						<div class="tutor-badge"><?php echo esc_html( $review['course_name'] ); ?></div>
						<div class="tutor-flex tutor-items-center tutor-justify-between">
							<div class="tutor-hidden tutor-sm-block">
								<?php StarRating::make()->rating( $review['rating'] ?? 0 )->render(); ?>
							</div>
							<div class="tutor-text-subdued tutor-tiny">
								<?php
								echo esc_html(
									sprintf(
										/* translators: %s human-readable time difference. */
										_x( '%s ago', 'human-readable time difference', 'tutor' ),
										human_time_diff( strtotime( $review['date'] ) )
									)
								);
								?>
							</div>
						</div>
					</div>
				</div>

				<!-- Star Rating -->
				<div class="tutor-sm-hidden">
					<?php StarRating::make()->rating( $review['rating'] ?? 0 )->render(); ?>
				</div>
			</div>

			<!-- Review Text -->
			<div class="tutor-tiny tutor-text-secondary">
				<?php echo esc_html( $review['review_text'] ); ?>
			</div>
		</div>
	</div>
</div>
