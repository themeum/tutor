<?php
/**
 * A single course loop add to cart
 *
 * @package Tutor\Templates
 * @subpackage WooCommerceIntegration
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$course_id  = get_the_ID();
$product_id = tutor_utils()->get_course_product_id();
$product    = wc_get_product( $product_id );

if ( ! $product_id || ! $product ) {
	return;
}

/**
 * Add required logged-in class
 *
 * @since 1.5.5
 */
$is_logged_in             = is_user_logged_in();
$enable_guest_course_cart = tutor_utils()->get_option( 'enable_guest_course_cart' );
$required_loggedin_class  = '';
$ajax_add_to_cart_class   = '';
if ( ! $is_logged_in && ! $enable_guest_course_cart ) {
	$required_loggedin_class = apply_filters( 'tutor_enroll_required_login_class', 'tutor-open-login-modal' );
} else {
	$ajax_add_to_cart_class = $product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : '';
}

$args     = array();
$defaults = array(
	'quantity'   => 1,
	'class'      => implode(
		' ',
		array_filter(
			array(
				'tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-btn-block ',
				'product_type_' . $product->get_type(),
				$product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button ' : '',
				$ajax_add_to_cart_class,
				$required_loggedin_class,
			)
		)
	),
	'attributes' => array(
		'data-product_id'  => $product->get_id(),
		'data-product_sku' => $product->get_sku(),
		'aria-label'       => $product->add_to_cart_description(),
		'rel'              => 'nofollow',
	),
);

$args = apply_filters( 'woocommerce_loop_add_to_cart_args', wp_parse_args( $args, $defaults ), $product );

if ( isset( $args['attributes']['aria-label'] ) ) {
	$args['attributes']['aria-label'] = strip_tags( $args['attributes']['aria-label'] );
}

?>

	<?php
		//phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped --contain safe content
		echo apply_filters(
			'tutor_course_restrict_new_entry',
			apply_filters(
				'woocommerce_loop_add_to_cart_link', // WPCS: XSS ok.
				sprintf(
					'<a href="%s" data-quantity="%s" class="%s" %s><span class="tutor-icon-cart-line tutor-mr-8"></span><span class="cart-text">%s</span></a>',
					esc_url( $product->add_to_cart_url() ),
					esc_attr( isset( $args['quantity'] ) ? $args['quantity'] : 1 ),
					esc_attr( isset( $args['class'] ) ? $args['class'] : 'button' ),
					isset( $args['attributes'] ) ? wc_implode_html_attributes( $args['attributes'] ) : '',
					esc_html( $product->add_to_cart_text() )
				),
				$product,
				$args
			),
			$course_id
		);

