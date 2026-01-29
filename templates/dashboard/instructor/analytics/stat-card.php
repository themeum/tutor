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

use TUTOR_REPORT\Analytics;

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

if ( $change_display_on_hover) {
	$template_path = tutor()->path . 'templates/dashboard/instructor/analytics/stat-card-hover.php';
	$hover_template = Analytics::get_template_output($template_path, array());
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

		<p class="<?php echo esc_attr( $change_class ); ?>">
			<?php echo esc_html( $change_display ); ?>
		</p>
		<?php if ( ! empty( $change_icon ) ) : ?>
			<?php tutor_utils()->render_svg_icon( $change_icon ); ?>
		<?php endif; ?>

		<?php if ( $change_display_on_hover ) : ?>

			<?php 
				//echo $hover_template; ?>
		<?php endif; ?>
	</div>
	<?php if ( $show_graph ) : ?>
		<div class="tutor-stat-card-chart" x-data="tutorStatCard(<?php echo wp_json_encode( $data ); ?>)">
			<canvas x-ref="canvas" hright="33" width="100%"></canvas>
		</div>
	<?php endif; ?>
</div>
