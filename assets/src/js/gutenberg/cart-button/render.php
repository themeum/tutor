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
	'show_icon'  => 'true',
	'show_count' => isset( $attributes['showCount'] ) ? $attributes['showCount'] : 'if_has_items',
	'class'      => isset( $attributes['customClass'] ) && ! empty( $attributes['customClass'] ) ? $attributes['customClass'] : 'cart-contents',
);

$shortcode = '[tutor_cart_button';
foreach ( $atts as $key => $value ) {
	$shortcode .= ' ' . $key . '="' . esc_attr( $value ) . '"';
}
$shortcode .= ']';

// Build inline CSS variables for custom colors set in the block editor.
// The stylesheet consumes these via var(--tutor-cart-*) with hardcoded fallbacks,
// so no <style> injection is needed — just a single style attribute on the wrapper.
$css_vars = '';
if ( ! empty( $attributes['iconColor'] ) ) {
	$css_vars .= '--tutor-cart-icon-color:' . esc_attr( $attributes['iconColor'] ) . ';';
}
if ( ! empty( $attributes['badgeBgColor'] ) ) {
	$css_vars .= '--tutor-cart-badge-bg:' . esc_attr( $attributes['badgeBgColor'] ) . ';';
}
if ( ! empty( $attributes['badgeTextColor'] ) ) {
	$css_vars .= '--tutor-cart-badge-color:' . esc_attr( $attributes['badgeTextColor'] ) . ';';
}

$wrapper_attributes = get_block_wrapper_attributes(
	array_filter( array(
		'class' => 'tutor-cart-button',
		'style' => $css_vars ?: null,
	) )
);

echo '<div ' . $wrapper_attributes . '>' . do_shortcode( $shortcode ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
