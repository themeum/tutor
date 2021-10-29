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
<?php
    $course_id = get_the_ID();
    $is_public = get_post_meta( $course_id, '_tutor_is_public_course', true )=='yes';
    $enroll_btn = '<div class="list-item-button">' . apply_filters( 'tutor_course_restrict_new_entry', '<a href="'. get_the_permalink(). '" class="tutor-btn tutor-btn-icon- tutor-btn-disable-outline tutor-btn-md tutor-btn-full">' . __('Enroll Course', 'tutor') . '</a>' ) . '</div>';
    $free_html = $enroll_btn;
    if (tutor_utils()->is_course_purchasable()) {
	    $enroll_btn = tutor_course_loop_add_to_cart(false);

	    $product_id = tutor_utils()->get_course_product_id($course_id);
	    $product    = wc_get_product( $product_id );
        

	    if ( $product ) {
		    $price_html = '<div class="tutor-bs-d-flex tutor-bs-align-items-center tutor-bs-justify-content-between"><div class="list-item-price tutor-bs-d-flex tutor-bs-align-items-center"> <span class="price text-bold-h6 color-text-primary">'.$product->get_price_html() . ' </span></div>';
		    $cart_html = '<div class="list-item-button"> '.apply_filters( 'tutor_course_restrict_new_entry', $enroll_btn ) . ' </div></div>';
	    }
            echo $price_html;
            echo $cart_html;
        } else{
            echo $free_html;
    }
?>
