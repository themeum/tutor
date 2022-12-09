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
	$course_id    = get_the_ID();
	$monetization = tutor_utils()->get_option( 'monetize_by' );
	/**
	 * If Monetization is PMPRO then ignore ajax enrolment
	 * to avoid Paid course enrollment without payment.
	 *
	 * Note: There is no mapping between Tutor Course and PMPRO
	 * That is way there is no way to determine whether course if free
	 * or paid
	 *
	 * @since v2.1.2
	 */
	$button_class = 'pmpro' === $monetization ? ' ' : ' tutor-course-list-enroll';
if ( ! is_user_logged_in() ) {
	$button_class = ' tutor-open-login-modal';
}
	$enroll_btn = '<div class="tutor-course-list-btn">' . apply_filters( 'tutor_course_restrict_new_entry', '<a href="' . get_the_permalink() . '" class="tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-btn-block ' . $button_class . ' " data-course-id="' . $course_id . '">' . __( 'Enroll Course', 'tutor' ) . '</a>' ) . '</div>';
	$free_html  = $enroll_btn;
if ( tutor_utils()->is_course_purchasable() ) {
	$enroll_btn = tutor_course_loop_add_to_cart( false );

	$product_id = tutor_utils()->get_course_product_id( $course_id );
	$product    = wc_get_product( $product_id );

	$total_enrolled   = tutor_utils()->count_enrolled_users_by_course( $course_id );
	$maximum_students = tutor_utils()->get_course_settings( $course_id, 'maximum_students' );

	if ( 0 != $maximum_students && $total_enrolled != $maximum_students ) {
		$total_booked = 100 / $maximum_students * $total_enrolled;
		$b_total      = $total_booked;
		//phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div class="tutor-d-flex tutor-align-center tutor-justify-between">
                    <div> 
                        <span class="tutor-course-price tutor-fs-6 tutor-fw-bold tutor-color-black">' .
						$product->get_price_html() . ' 
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
                <div class="tutor-course-booking-availability tutor-mt-16">
                    <button class="tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-btn-block">' .
					apply_filters( 'tutor_course_restrict_new_entry', $enroll_btn ) . ' 
                    </button>
                </div>';
	}

	if ( $product && $maximum_students == $total_enrolled && 0 != $maximum_students ) {
		$price_html = '<div class="tutor-d-flex tutor-align-center tutor-justify-between"><div class="list-item-price tutor-d-flex tutor-align-center"> <span class="price tutor-fs-6 tutor-fw-bold tutor-color-black">' . $product->get_price_html() . ' </span></div>';
		$restrict   = '<div class="list-item-booking booking-full tutor-d-flex tutor-align-center"><div class="booking-progress tutor-d-flex"><span class="tutor-mr-8 tutor-color-warning tutor-icon-circle-info"></span></div><div class="tutor-fs-7 tutor-fw-medium tutor-color-black">' . __( 'Fully Booked', 'tutor' ) . '</div></div></div>';
		echo wp_kses_post( $price_html );
		echo wp_kses_post( $restrict );
	}

	if ( $product && 0 == $maximum_students ) {
		$price_html = '<div class="tutor-d-flex tutor-align-center tutor-justify-between"><div class="list-item-price tutor-d-flex tutor-align-center"> <span class="price tutor-fs-6 tutor-fw-bold tutor-color-black">' . $product->get_price_html() . ' </span></div>';
		$cart_html  = '<div class="list-item-button"> ' . apply_filters( 'tutor_course_restrict_new_entry', $enroll_btn ) . ' </div></div>';
		echo wp_kses_post( $price_html );
		echo wp_kses_post( $cart_html );
	}
} else {
	echo wp_kses_post( $free_html );
}

