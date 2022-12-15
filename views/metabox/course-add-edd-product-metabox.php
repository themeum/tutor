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

$products   = tutor_utils()->get_edd_products();
$product_id = tutor_utils()->get_course_product_id();
$info_text  = __( 'Sell your product, process by EDD', 'tutor' );
$label_info = __( 'When selling the course', 'tutor' );

require __DIR__ . '/product-selection.php';

