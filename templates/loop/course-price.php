<?php

/**
 * Course loop price
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */
?>

<div class="tutor-course-loop-price">
    <?php
    $course_id = get_the_ID();
    $enroll_btn = '<div  class="tutor-loop-cart-btn-wrap"><a href="'. get_the_permalink(). '">'.__('Get Enrolled', 'tutor'). '</a></div>';
    $price_html = '<div class="price"> '.__('Free', 'tutor').$enroll_btn. '</div>';
    if (tutor_utils()->is_course_purchasable()) {
	    $enroll_btn = tutor_course_loop_add_to_cart(false);

	    $product_id = tutor_utils()->get_course_product_id($course_id);
	    $product    = wc_get_product( $product_id );

	    if ( $product ) {
		    $price_html = '<div class="price"> '.$product->get_price_html().$enroll_btn.' </div>';
	    }
    }
    echo $price_html;
    ?>
</div>