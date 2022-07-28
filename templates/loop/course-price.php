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
    $enroll_btn = '<div class="tutor-course-list-btn">' . apply_filters( 'tutor_course_restrict_new_entry', '<a href="'. get_the_permalink(). '" class="tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-btn-block">' . __('Enroll Course', 'tutor') . '</a>' ) . '</div>';
    $free_html = $enroll_btn;
    if (tutor_utils()->is_course_purchasable()) {
        $enroll_btn = tutor_course_loop_add_to_cart(false);

        $product_id = tutor_utils()->get_course_product_id($course_id);
        $product    = wc_get_product( $product_id );
        
        $total_enrolled = tutor_utils()->count_enrolled_users_by_course($course_id);
        $maximum_students = tutor_utils()->get_course_settings($course_id, 'maximum_students');

        if ($maximum_students != 0 && $total_enrolled != $maximum_students) {
            $total_booked = 100 / $maximum_students * $total_enrolled;
            $b_total = $total_booked;

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
                <div class="tutor-course-booking-availability tutor-mt-16">
                    <button class="tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-btn-block">'.
                        apply_filters( 'tutor_course_restrict_new_entry', $enroll_btn ) . ' 
                    </button>
                </div>';
        }

        if ( $product && $maximum_students == $total_enrolled && $maximum_students != 0) {
            $price_html = '<div class="tutor-d-flex tutor-align-center tutor-justify-between"><div class="list-item-price tutor-d-flex tutor-align-center"> <span class="price tutor-fs-6 tutor-fw-bold tutor-color-black">'.$product->get_price_html() . ' </span></div>';
            $restrict = '<div class="list-item-booking booking-full tutor-d-flex tutor-align-center"><div class="booking-progress tutor-d-flex"><span class="tutor-mr-8 tutor-color-warning tutor-icon-circle-info"></span></div><div class="tutor-fs-7 tutor-fw-medium tutor-color-black">'. __('Fully Booked', 'tutor') .'</div></div></div>';
            echo wp_kses_post($price_html);
            echo wp_kses_post($restrict);
        }

        if ( $product && $maximum_students == 0) {
            $price_html = '<div class="tutor-d-flex tutor-align-center tutor-justify-between"><div class="list-item-price tutor-d-flex tutor-align-center"> <span class="price tutor-fs-6 tutor-fw-bold tutor-color-black">'.$product->get_price_html() . ' </span></div>';
            $cart_html = '<div class="list-item-button"> '.apply_filters( 'tutor_course_restrict_new_entry', $enroll_btn ) . ' </div></div>';
            echo wp_kses_post($price_html);
            echo wp_kses_post($cart_html);
        }
        
    } else {
        echo wp_kses_post($free_html);
    }
?>
