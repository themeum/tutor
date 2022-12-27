<?php
/**
 * Add product metabox
 *
 * @package Tutor\Views
 * @subpackage Tutor\MetaBox
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

$products   = tutor_utils()->get_wc_products_db( get_the_ID() );
$product_id = tutor_utils()->get_course_product_id();

$info_text = __( 'Sell your product, process by WooCommerce', 'tutor' );
if ( tutor()->has_pro ) {
	$info_text = __( 'You can select an existing WooCommerce product, alternatively, a new WooCommerce product will be created for you.', 'tutor' );
}

$label_info = 'When selling the course';
if ( tutor()->has_pro ) {
	$label_info = __( 'on WooCommerce', 'tutor' );
}

require __DIR__ . '/product-selection.php';

