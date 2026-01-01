<?php
/**
 * Top Performing Course Item Component
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

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
				<?php tutor_utils()->render_svg_icon( Icon::DOLLAR, 12, 12, array( 'class' => 'tutor-icon-secondary' ) ); ?>
				<div class="tutor-tiny tutor-text-subdued">
					<?php esc_html_e( 'Revenue', 'tutor' ); ?>
				</div>
			</div>

			<div class="tutor-tiny tutor-font-semibold">
				<?php echo esc_html( $item['revenue'] ); ?>
			</div>
		</div>

		<!-- Students -->
		<div class="tutor-flex tutor-flex-column tutor-items-center">
			<div class="tutor-flex tutor-items-center tutor-gap-2">
				<!-- @TODO: Add students icon -->
				<?php tutor_utils()->render_svg_icon( Icon::STUDENT, 12, 12, array( 'class' => 'tutor-icon-secondary' ) ); ?>
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
