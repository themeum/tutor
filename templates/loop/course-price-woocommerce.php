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
    $isLoggedIn = is_user_logged_in();
    $enable_guest_course_cart = tutor_utils()->get_option('enable_guest_course_cart');
    $required_loggedin_class = '';
    if ( ! $isLoggedIn && ! $enable_guest_course_cart){
        $required_loggedin_class = apply_filters('tutor_enroll_required_login_class', 'tutor-open-login-modal');
    }
    $enroll_btn = '<div class="tutor-course-list-btn">' . apply_filters( 'tutor_course_restrict_new_entry', '<a href="'. get_the_permalink(). '" class="tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-btn-block '.$required_loggedin_class.'">' . __('Enroll Course', 'tutor') . '</a>' ) . '</div>';
    $free_html = $enroll_btn;

    // Show purchase button if purchaseable
    if ( tutor_utils()->is_course_purchasable() ) {
        $enroll_btn = tutor_course_loop_add_to_cart(false);

        $product_id = tutor_utils()->get_course_product_id($course_id);
        $product    = wc_get_product( $product_id );

        $total_enrolled = tutor_utils()->count_enrolled_users_by_course($course_id);
        $maximum_students = tutor_utils()->get_course_settings($course_id, 'maximum_students');

        if ( false === $product ) {
            echo tutor_kses_html( $free_html );
        } elseif ( $maximum_students != 0 && $total_enrolled != $maximum_students ) {
            $total_booked = 100 / $maximum_students * $total_enrolled;
            $b_total = number_format($total_booked);
            $add_to_cart_text = $product->add_to_cart_text();

            echo '<div class="tutor-d-flex tutor-align-center tutor-justify-between">
                    <div> 
                        <span class="tutor-course-price tutor-fs-6 tutor-fw-bold tutor-color-black">'.
                            $product->get_price_html() . ' 
                        </span>
                    </div>

                    <div class="tutor-course-booking-progress tutor-d-flex tutor-align-center">
                        <div class="tutor-mr-8">
                            <div class="tutor-progress-circle" style="--pro: ' . $b_total . '%;" area-hidden="true"></div>
                        </div>
                        <div class="tutor-fs-7 tutor-fw-medium tutor-color-black">'.
                            $b_total . __('% Booked', 'tutor') . '
                        </div>
                    </div>
                </div>
                <div class="tutor-course-booking-availability tutor-mt-16"> '.
                    apply_filters( 'tutor_course_restrict_new_entry', $enroll_btn ) . '
                </div>';
        }

        if ( $product && $maximum_students == $total_enrolled && $maximum_students != 0) {
            $price_html = '<div class="tutor-d-flex tutor-align-center tutor-justify-between"><div class="list-item-price tutor-d-flex tutor-align-center"> <span class="price tutor-fs-6 tutor-fw-bold tutor-color-black">'.$product->get_price_html() . ' </span></div>';
            $restrict = '<div class="list-item-booking booking-full tutor-d-flex tutor-align-center"><div class="booking-progress tutor-d-flex"><span class="tutor-mr-8 tutor-color-warning tutor-icon-circle-info"></span></div><div class="tutor-fs-7 tutor-fw-medium tutor-color-black">'. __('Fully Booked', 'tutor') .'</div></div></div>';
            echo tutor_kses_html($price_html);
            echo tutor_kses_html($restrict);
        }

        if ( $product && $maximum_students == 0) {
            $price_html = '<div class="tutor-d-flex tutor-align-center tutor-justify-between"><div class="list-item-price tutor-d-flex tutor-align-center"> <span class="price tutor-fs-6 tutor-fw-bold tutor-color-black">'.$product->get_price_html() . ' </span></div>';
            $cart_html = '<div class="list-item-button"> '.apply_filters( 'tutor_course_restrict_new_entry', $enroll_btn ) . ' </div></div>';
            echo tutor_kses_html($price_html);
            echo tutor_kses_html($cart_html);
        }

    } else {
        echo tutor_kses_html($free_html);
    }
?>
