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

    <?php
    $course_id = get_the_ID();
    $enroll_btn = '<div  class="list-item-button"><a href="'. get_the_permalink(). '" class="tutor-btn tutor-btn-icon- tutor-btn-disable-outline tutor-btn-md tutor-btn-full">'.__('Continue Course', 'tutor'). '</a></div>';
    $default_price = apply_filters('tutor-loop-default-price', __('Free', 'tutor'));
    $price_html = $enroll_btn;
    if (tutor_utils()->is_course_purchasable()) {
        
	    $product_id = tutor_utils()->get_course_product_id($course_id);
	    $product    = wc_get_product( $product_id );

	    if ( $product ) {
		    $price_html = $enroll_btn;;
	    }
    }
    echo $price_html;
    ?>