<?php
/**
 * Course loop price
 *
 * @package Tutor\Templates
 * @subpackage CourseLoopPart
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.3
 */

?>
<?php
$course_id                = get_the_ID();
$is_logged_in             = is_user_logged_in();
$enable_guest_course_cart = tutor_utils()->get_option( 'enable_guest_course_cart' );
$required_loggedin_class  = '';

if ( ! $is_logged_in && ! $enable_guest_course_cart ) {
	$required_loggedin_class = apply_filters( 'tutor_enroll_required_login_class', 'tutor-open-login-modal' );
}

$enroll_btn = '<div class="tutor-course-list-btn">' . apply_filters( 'tutor_course_restrict_new_entry', '<a href="' . get_the_permalink() . '" class="tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-btn-block ' . $required_loggedin_class . '">' . __( 'Enroll Course', 'tutor' ) . '</a>' ) . '</div>';
$free_html  = $enroll_btn;

	// Show purchase button if purchaseable.
if ( tutor_utils()->is_course_purchasable() ) {
	$enroll_btn = tutor_course_loop_add_to_cart( false );

	$product_id      = tutor_utils()->get_course_product_id( $course_id );
	$product         = wc_get_product( $product_id );
	$wc_price_html   = '';
	$product_type    = '';
	$utility_classes = 'tutor-d-flex tutor-align-center tutor-justify-between';
	if ( is_a( $product, 'WC_Product' ) ) {
		$wc_price_html = apply_filters( 'tutor_loop_wc_price_html', $product->get_price_html(), $product );
		$product_type  = $product->get_type();
		if ( 'subscription' === $product_type || 'variable-subscription' === $product_type ) {
			$utility_classes = $utility_classes . ' tutor-flex-column';
		}
	}

	$total_enrolled   = (int) tutor_utils()->count_enrolled_users_by_course( $course_id );
	$maximum_students = (int) tutor_utils()->get_course_settings( $course_id, 'maximum_students' );

	if ( false === $product ) {
		echo $free_html; //phpcs:ignore --contain safe data
	} elseif ( 0 !== $maximum_students && $total_enrolled !== $maximum_students ) {
		$total_booked     = 100 / $maximum_students * $total_enrolled;
		$b_total          = ceil( $total_booked );
		$add_to_cart_text = $product->add_to_cart_text();
        // @codingStandardsIgnoreStart
		echo '<div class=" '. $utility_classes .' ">
                    <div> 
                        <span class="tutor-course-price tutor-fs-6 tutor-fw-bold tutor-color-black">' .
						$wc_price_html . ' 
                        </span>
                    </div>

                    <div class="tutor-course-booking-progress tutor-d-flex tutor-align-center">
                        <div class="tutor-mr-8">
                            <div class="tutor-progress-circle" style="--pro: ' . esc_html( $b_total ) . '%;" area-hidden="true"></div>
                        </div>
                        <div class="tutor-fs-7 tutor-fw-medium tutor-color-black">' .
						esc_html( $b_total ) . __( '% Booked', 'tutor' ) . '
                        </div>
                    </div>
                </div>
                <div class="tutor-course-booking-availability tutor-mt-16"> ' .
				apply_filters( 'tutor_course_restrict_new_entry', $enroll_btn ) . '
                </div>';
        // @codingStandardsIgnoreEnd
	}

	if ( $product && $maximum_students === $total_enrolled && 0 !== $maximum_students ) {
		$price_html = '<div class=" ' . $utility_classes . ' "><div class="list-item-price tutor-d-flex tutor-align-center"> <span class="price tutor-fs-6 tutor-fw-bold tutor-color-black">' . $wc_price_html . ' </span></div>';
		$restrict   = '<div class="list-item-booking booking-full tutor-d-flex tutor-align-center"><div class="booking-progress tutor-d-flex"><span class="tutor-mr-8 tutor-color-warning tutor-icon-circle-info"></span></div><div class="tutor-fs-7 tutor-fw-medium tutor-color-black">' . __( 'Fully Booked', 'tutor' ) . '</div></div></div>';
		echo $price_html; //phpcs:ignore --contain safe data
		echo $restrict; //phpcs:ignore --contain safe data
	}

	if ( $product && 0 === $maximum_students ) {
		$price_html = '<div class=" ' . $utility_classes . ' "><div class="list-item-price tutor-d-flex tutor-align-center"> <span class="price tutor-fs-6 tutor-fw-bold tutor-color-black">' . $wc_price_html . ' </span></div>';
		$cart_html  = '<div class="list-item-button"> ' . apply_filters( 'tutor_course_restrict_new_entry', $enroll_btn ) . ' </div></div>';
		echo $price_html; //phpcs:ignore --contain safe data
		echo $cart_html; //phpcs:ignore
	}
} else {
	echo $free_html; //phpcs:ignore --contain safe data
}

