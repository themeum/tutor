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

defined( 'ABSPATH' ) || exit;

// Default values.
$icon_size               = $icon_size ?? 24;
$value                   = $value ?? 0;
$change_display          = $change ?? '';
$show_graph              = $show_graph ?? false;
$data                    = $data ?? array( 0, 0, 0 );
$change_class            = $change_class ?? 'tutor-stat-card-change ';
$change_icon             = $change_icon ?? '';
$change_display_on_hover = $change_display_on_hover ?? false;

// Required fields validation.
if ( ! isset( $card_title ) || empty( $card_title ) ) {
	return;
}
if ( ! isset( $icon ) || empty( $icon ) ) {
	return;
}

?>
<div class="tutor-card tutor-stat-card tutor-stat-card-<?php echo esc_attr( $icon ); ?>">
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
			<p class="<?php echo esc_attr( $change_class ); ?>">
				<?php echo esc_html( $change_display ); ?>
			</p>
			<?php if ( ! empty( $change_icon ) ) : ?>
				<?php tutor_utils()->render_svg_icon( $change_icon ); ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php if ( ! empty( $change_display_on_hover ) ) : ?>
			<p class="tutor-stat-card-hover-content">
				<?php echo esc_html( $change_display_on_hover ); ?>
			</p>
		<?php endif; ?>
	</div>
	<?php if ( $show_graph ) : ?>
		<div class="tutor-stat-card-chart" x-data="tutorStatCard(<?php echo wp_json_encode( $data ); ?>)">
			<canvas x-ref="canvas" hright="33" width="100%"></canvas>
		</div>
	<?php endif; ?>
</div>
