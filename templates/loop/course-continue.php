<?php

/**
 * Course loop continue when enrolled
 *
 * @since v.1.7.4
 * @author themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.7.4
 */
?>

<div class="tutor-course-loop-price">
    <?php
    $course_id = get_the_ID();
    $enroll_btn = '<div  class="tutor-loop-cart-btn-wrap"><a href="'. get_the_permalink(). '">'.__('Continue Course', 'tutor'). '</a></div>';
    $price_html = '<div class="price"> '.__('Free', 'tutor').$enroll_btn. '</div>';
    if (tutor_utils()->is_course_purchasable()) {
        
	    $product_id = tutor_utils()->get_course_product_id($course_id);
	    $product    = wc_get_product( $product_id );

	    if ( $product ) {
		    $price_html = '<div class="price"> '.$product->get_price_html().$enroll_btn.' </div>';
	    }
    }
    echo $price_html;
    ?>
</div>