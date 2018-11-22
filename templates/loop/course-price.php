<?php

/**
 * Course loop price
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 */
?>

<div class="dozent-course-loop-price">
    <?php
    $course_id = get_the_ID();
    $enroll_btn = '<div  class="dozent-loop-cart-btn-wrap"><a href="'. get_the_permalink(). '">'.__('Get Enrolled', 'dozent'). '</a></div>';
    $price_html = '<p class="price"> '.__('Free', 'dozent').$enroll_btn. '</p>';
    if (dozent_utils()->is_course_purchasable()) {
	    $enroll_btn = dozent_course_loop_add_to_cart(false);

	    $product_id = dozent_utils()->get_course_product_id($course_id);
	    $product    = wc_get_product( $product_id );

	    if ( $product ) {
		    $price_html = '<p class="price"> '.$product->get_price_html().$enroll_btn.' </p>';
	    }
    }

    echo $price_html;
    ?>
</div>