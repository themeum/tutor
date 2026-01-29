<?php
/**
 * Stat Card Hover Content
 *
 * @package Tutor\Templates
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

$hover_period_current = $hover_period_current ?? 'Jan-Dec 2025';
$hover_period_prev    = $hover_period_prev ?? 'Jan-Dec 2024';
$hover_amount         = $hover_amount ?? '$740.00';
$hover_percent        = $hover_percent ?? '48%';
$hover_direction      = $hover_direction ?? 'up';

$arrow_icon = 'up' === $hover_direction ? TUTOR\Icon::ARROW_UP : TUTOR\Icon::ARROW_DOWN;
$arrow_class = 'up' === $hover_direction ? 'tutor-text-success' : 'tutor-text-danger';
?>

<div class="tutor-stat-card-hover-contents tutor-flex tutor-flex-column tutor-gap-2">
	<div class="tutor-flex tutor-items-center tutor-gap-2 tutor-text-tiny tutor-text-secondary">
		<span><?php echo esc_html( $hover_period_current ); ?></span>
		<span><?php esc_html_e( 'vs', 'tutor' ); ?></span>
		<span><?php echo esc_html( $hover_period_prev ); ?></span>
	</div>
	<div class="tutor-flex tutor-items-center tutor-justify-between">
		<span class="tutor-text-base tutor-font-bold tutor-text-primary">
			<?php echo esc_html( $hover_amount ); ?>
		</span>
		<span class="tutor-flex tutor-items-center tutor-gap-1 tutor-text-base tutor-font-bold <?php echo esc_attr( $arrow_class ); ?>">
			<?php echo esc_html( $hover_percent ); ?>
			<?php tutor_utils()->render_svg_icon( $arrow_icon, 16, 16 ); ?>
		</span>
	</div>
</div>
