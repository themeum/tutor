<?php
/**
 * Add product metabox
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @since v.1.0.0
 */

$products = tutor_utils()->get_edd_products();
$product_id = tutor_utils()->get_course_product_id();
$info_text = __('Sell your product, process by EDD', 'tutor');

require __DIR__ . '/product-selection.php';
?>
