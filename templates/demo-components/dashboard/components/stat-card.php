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
$variation  = isset( $variation ) ? $variation : 'enrolled';
$card_title = isset( $card_title ) ? $card_title : '';
$icon       = isset( $icon ) ? $icon : '';
$value      = isset( $value ) ? $value : '';
$change     = isset( $change ) ? $change : '';

$change_display = ! empty( $change )
	? $change . ' ' . esc_html__( 'this month', 'tutor' )
	: '';

?>
<div class="tutor-card tutor-stat-card tutor-stat-card-<?php echo esc_attr( $variation ); ?>">
	<div class="tutor-stat-card-header">
		<?php if ( ! empty( $card_title ) ) : ?>
			<h3 class="tutor-stat-card-title">
				<?php echo esc_html( $card_title ); ?>
			</h3>
		<?php endif; ?>
		<?php if ( ! empty( $icon ) ) : ?>
			<div class="tutor-stat-card-icon">
				<?php tutor_utils()->render_svg_icon( $icon, 24, 24 ); ?>
			</div>
		<?php endif; ?>
	</div>
	<div class="tutor-stat-card-content">
		<?php if ( ! empty( $value ) ) : ?>
			<div class="tutor-stat-card-value">
				<?php echo esc_html( $value ); ?>
			</div>
		<?php endif; ?>
		<?php if ( ! empty( $change_display ) ) : ?>
			<p class="tutor-stat-card-change">
				<?php echo esc_html( $change_display ); ?>
			</p>
		<?php endif; ?>
	</div>
</div>

