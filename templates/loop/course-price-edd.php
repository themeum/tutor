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

$product_id = tutor_utils()->get_course_product_id();
$download   = new EDD_Download( $product_id );

add_filter(
	'edd_purchase_link_defaults',
	function( $defaults ) {
		if ( isset( $defaults['class'] ) ) {
			$defaults['class'] = 'edd-add-to-cart button white edd-submit edd-has-js tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-btn-block ';
		}
		return $defaults;
	}
);

?>

<?php
	$course_id  = get_the_ID();
	$enroll_btn = '<div class="list-item-button">' . apply_filters( 'tutor_course_restrict_new_entry', '<a href="' . get_the_permalink() . '" class="tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-btn-block">' . __( 'Enroll Course', 'tutor' ) . '</a>' ) . '</div>';
	$free_html  = $enroll_btn;
if ( tutor_utils()->is_course_purchasable() ) {
		$enroll_btn = tutor_course_loop_add_to_cart( false );
		$product_id = tutor_utils()->get_course_product_id( $course_id );
		$edd_price  = function_exists( 'edd_price' ) ? edd_price( $product_id, false ) : '';

		$total_enrolled   = tutor_utils()->count_enrolled_users_by_course( $course_id );
		$maximum_students = tutor_utils()->get_course_settings( $course_id, 'maximum_students' );

	if ( $maximum_students != 0 && $total_enrolled != $maximum_students ) {
		$total_booked = 100 / $maximum_students * $total_enrolled;
		$b_total      = ceil( $total_booked );
		// @codingStandardsIgnoreStart
        /**
         * Remove price since is also coming from add tutor_course_loop_add_to_cart
         * 
         * @see $enroll_btn if purchase able
         *
         * @since v2.0.9
         */
		echo '<div class="list-item-price-with-booking tutor-d-flex tutor-align-center tutor-justify-between">
                        <div class="list-item-booking tutor-d-flex tutor-align-center">
                            <div class="booking-progress tutor-d-flex">
                                <div class="circle-progress progress-full" style="--pro:'.$b_total .'%;">
                                </div>
                            </div>
                            <div class="tutor-fs-7 tutor-fw-medium tutor-color-black">'.
						$b_total . __('% Booked', 'tutor') . '
                            </div>
                        </div>
                    </div>
                    <div class="list-item-button tutor-mt-16 booking-available">
                        ' . apply_filters( 'tutor_course_restrict_new_entry', $enroll_btn ) . '
                    </div>';
		// @codingStandardsIgnoreEnd
	}

	if ( $maximum_students == $total_enrolled && $maximum_students != 0 ) {
		$price_html = '<div class="tutor-d-flex tutor-align-center tutor-justify-between">
                                    <div class="list-item-price tutor-d-flex tutor-align-center"> 
                                        <span class="price tutor-fs-6 tutor-fw-bold tutor-color-black">' .
									$edd_price . ' 
                                        </span>
                                    </div>';

		$restrict = '<div class="list-item-booking booking-full tutor-d-flex tutor-align-center">
                                <div class="booking-progress tutor-d-flex">
                                    <span class="tutor-mr-8 tutor-color-warning tutor-icon-circle-info"></span>
                                </div>
                                <div class="tutor-fs-7 tutor-fw-medium tutor-color-black">' .
							__( 'Fully Booked', 'tutor' ) . '
                                </div>
                            </div></div>';
		echo $price_html;//phpcs:ignore --trusted markups
		echo $restrict;//phpcs:ignore --trusted markups
	}

	if ( 0 == $maximum_students ) {
		?>
				<div class="list-item-button"> 
			<?php
				// PHPCS - the variable $enroll_btn holds safe data.
				echo apply_filters( 'tutor_course_restrict_new_entry', $enroll_btn );// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --safe data
			?>
				</div>
			<?php
	}
} else {
	echo $free_html;//phpcs:ignore --contain safe data
}
?>
