<?php
/**
 * Stat Card Component
 * Reusable stat card component for dashboard
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

// Default values.
$icon_size  = $icon_size ?? 24;
$variation  = $variation ?? 'enrolled';
$value      = $value ?? 0;
$change     = $change ?? '';
$show_graph = $show_graph ?? false;
$data       = $data ?? array( 0, 0, 0 );

// Required fields validation.
if ( empty( $card_title ) || empty( $icon ) ) {
	return;
}
$change_display = $change ?? '';

?>
<div class="tutor-card tutor-stat-card tutor-stat-card-<?php echo esc_attr( $variation ); ?>">
	<div class="tutor-stat-card-header">
		<div class="tutor-stat-card-title">
			<?php echo esc_html( $card_title ); ?>
		</div>
		<div class="tutor-stat-card-icon">
			<?php tutor_utils()->render_svg_icon( $icon, $icon_size, $icon_size ); ?>
		</div>
	</div>
	<div class="tutor-stat-card-content">
		<div class="tutor-stat-card-value">
			<?php echo esc_html( $value ); ?>
		</div>
		<?php if ( ! empty( $change_display ) ) : ?>
			<p class="tutor-stat-card-change">
				<?php echo esc_html( $change_display ); ?>
			</p>
		<?php endif; ?>
	</div>
	<?php if ( $show_graph ) : ?>
		<div class="tutor-stat-card-chart" x-data="tutorStatCard(<?php echo wp_json_encode( $data ); ?>)">
			<canvas x-ref="canvas" hright="33" width="100%"></canvas>
		</div>
	<?php endif; ?>
</div>

