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
            
            $total_enrolled = tutor_utils()->count_enrolled_users_by_course($course_id);
            $maximum_students = tutor_utils()->get_course_settings($course_id, 'maximum_students');

            if ($maximum_students != 0 && $total_enrolled != $maximum_students){
                $total_booked = 100 / $maximum_students * $total_enrolled;
                $b_total = $total_booked;

                $price_html = '<div class="list-item-price-with-booking tutor-bs-d-flex tutor-bs-align-items-center tutor-bs-justify-content-between"><div class="list-item-price tutor-bs-d-flex tutor-bs-align-items-center"> <span class="price tutor-text-bold-h6 tutor-color-text-primary">'.$product->get_price_html() . ' </span></div>';
                $percet_html = '<div class="list-item-booking tutor-bs-d-flex tutor-bs-align-items-center"><div class="booking-progress tutor-bs-d-flex"><div class="circle-progress progress-full" style="--pro:'.$b_total .'%;"></div></div><div class="text-medium-caption tutor-color-text-primary">'.$b_total . __('% Booked', 'tutor') . '</div></div></div>';
                $cart_html = '<div class="list-item-button tutor-mt-15 booking-available"><button class="tutor-btn tutor-btn-icon tutor-btn-disable-outline tutor-btn-md tutor-btn-full">'.apply_filters( 'tutor_course_restrict_new_entry', $enroll_btn ) . ' </button></div>';

                echo wp_kses_post($price_html);
                echo wp_kses_post($percet_html);
                echo wp_kses_post($cart_html);
            }

            if ( $product && $maximum_students == $total_enrolled && $maximum_students != 0) {
                $price_html = '<div class="tutor-bs-d-flex tutor-bs-align-items-center tutor-bs-justify-content-between"><div class="list-item-price tutor-bs-d-flex tutor-bs-align-items-center"> <span class="price tutor-text-bold-h6 tutor-color-text-primary">'.$product->get_price_html() . ' </span></div>';
                $restrict = '<div class="list-item-booking booking-full tutor-bs-d-flex tutor-bs-align-items-center"><div class="booking-progress tutor-bs-d-flex"><span class="btn-icon tutor-color-design-warning tutor-icon-circle-outline-info-filled"></span></div><div class="text-medium-caption tutor-color-text-primary">'. __('Fully Booked', 'tutor') .'</div></div></div>';
                echo wp_kses_post($price_html);
                echo wp_kses_post($restrict);
            }

            if ( $product && $maximum_students == 0) {
                $price_html = '<div class="tutor-bs-d-flex tutor-bs-align-items-center tutor-bs-justify-content-between"><div class="list-item-price tutor-bs-d-flex tutor-bs-align-items-center"> <span class="price tutor-text-bold-h6 tutor-color-text-primary">'.$product->get_price_html() . ' </span></div>';
                $cart_html = '<div class="list-item-button"> '.apply_filters( 'tutor_course_restrict_new_entry', $enroll_btn ) . ' </div></div>';
                echo wp_kses_post($price_html);
                echo wp_kses_post($cart_html);
            }


        } else{
            echo wp_kses_post($free_html);
    }
?>
