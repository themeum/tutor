<?php
/**
 * Top Performing Course Item Component
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;
use Tutor\Components\Constants\Color;
use Tutor\Components\SvgIcon;
?>

<div class="tutor-dashboard-home-course">
	<div class="tutor-flex tutor-items-center tutor-gap-4">
		<div class="tutor-dashboard-home-course-index">
			#<?php echo esc_html( $item_key + 1 ); ?>
		</div>
		<div class="tutor-p3">
			<?php echo esc_html( $item['name'] ); ?>
		</div>
	</div>

	<div class="tutor-flex tutor-items-center tutor-gap-7">
		<!-- Revenue -->
		<div class="tutor-flex tutor-flex-column tutor-items-center">
			<div class="tutor-flex tutor-items-center tutor-gap-2">
				<?php SvgIcon::make()->name( Icon::DOLLAR )->size( 12 )->color( Color::SECONDARY )->render(); ?>
				<div class="tutor-tiny tutor-text-subdued">
					<?php esc_html_e( 'Revenue', 'tutor' ); ?>
				</div>
			</div>

			<div class="tutor-tiny tutor-font-semibold">
				<?php echo wp_kses( $item['revenue'], tutor_price_allowed_html() ); ?>
			</div>
		</div>

		<!-- Students -->
		<div class="tutor-flex tutor-flex-column tutor-items-center">
			<div class="tutor-flex tutor-items-center tutor-gap-2">
				<!-- @TODO: Add students icon -->
				<?php SvgIcon::make()->name( Icon::STUDENT )->size( 12 )->color( Color::SECONDARY )->render(); ?>
				<div class="tutor-tiny tutor-text-subdued">
					<?php esc_html_e( 'Students', 'tutor' ); ?>
				</div>
			</div>
			
			<div class="tutor-tiny tutor-font-semibold">
				<?php echo esc_html( $item['students'] ); ?>
			</div>
		</div>
	</div>
</div>
