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
$variation = isset( $variation ) ? $variation : 'enrolled';
$value     = isset( $value ) ? $value : 0;
$change    = isset( $change ) ? $change : '';
$icon_size = $icon_size ?? 24;

// Required fields validation.
if ( ! isset( $card_title ) || empty( $card_title ) ) {
	return;
}
if ( ! isset( $icon ) || empty( $icon ) ) {
	return;
}

$change_display = ! empty( $change ) ? $change : '';

?>
<div class="tutor-card tutor-stat-card tutor-stat-card-<?php echo esc_attr( $variation ); ?>">
	<div class="tutor-stat-card-header">
		<h3 class="tutor-stat-card-title">
			<?php echo esc_html( $card_title ); ?>
		</h3>
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
</div>

