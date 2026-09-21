<?php
/**
 * Server-side rendering for the Tutor Cart Button block
 *
 * @package Tutor
 * @since 4.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attributes = $attributes ?? array();

$atts = array(
	'show_count' => isset( $attributes['showCount'] ) ? $attributes['showCount'] : 'if_has_items',
	'class'      => isset( $attributes['customClass'] ) && ! empty( $attributes['customClass'] ) ? $attributes['customClass'] : 'tutor-cart-button',
	'cart_icon'  => isset( $attributes['cartIcon'] ) ? $attributes['cartIcon'] : 'cart',
);

$shortcode = '[tutor_cart_button';
foreach ( $atts as $key => $value ) {
	$shortcode .= sprintf( ' %s="%s"', $key, esc_attr( $value ) );
}
$shortcode .= ']';

// Build inline CSS variables for custom colors and size set in the block editor.
// The stylesheet consumes these via var(--tutor-cart-*) with hardcoded fallbacks,
// so no <style> injection is needed — just a single style attribute on the wrapper.
$css_vars = '';
if ( ! empty( $attributes['iconColor'] ) ) {
	$css_vars .= '--tutor-cart-icon-color:' . esc_attr( $attributes['iconColor'] ) . ';';
}
if ( ! empty( $attributes['iconSize'] ) ) {
	$css_vars .= '--tutor-cart-icon-size:' . intval( $attributes['iconSize'] ) . 'px;';
}
if ( ! empty( $attributes['badgeBgColor'] ) ) {
	$css_vars .= '--tutor-cart-badge-bg:' . esc_attr( $attributes['badgeBgColor'] ) . ';';
}
if ( ! empty( $attributes['badgeTextColor'] ) ) {
	$css_vars .= '--tutor-cart-badge-color:' . esc_attr( $attributes['badgeTextColor'] ) . ';';
}

$wrapper_attributes = get_block_wrapper_attributes(
	array_filter(
		array(
			'class' => 'tutor-cart-button',
			'style' => $css_vars ? $css_vars : null,
		)
	)
);

echo '<div ' . $wrapper_attributes . '>' . do_shortcode( $shortcode ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
