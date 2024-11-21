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

	$price_html       = tutor_utils()->get_course_price();
	$utility_classes  = 'tutor-d-flex tutor-align-center tutor-justify-between';
	$total_enrolled   = (int) tutor_utils()->count_enrolled_users_by_course( $course_id );
	$maximum_students = (int) tutor_utils()->get_course_settings( $course_id, 'maximum_students' );

	if ( ! $price_html ) {
		echo $free_html; //phpcs:ignore --contain safe data
	} else {
		if ( 0 === $maximum_students ) {
			?>
				<div class="<?php echo esc_attr( $utility_classes ); ?>">
					<div class="list-item-price tutor-d-flex tutor-align-center">
						<span class="price tutor-fs-6 tutor-fw-bold tutor-color-black">
							<?php echo wp_kses_post( $price_html ); ?>
						</span>
					</div>
					<div class="list-item-button"> 
						<?php echo wp_kses_post( apply_filters( 'tutor_course_restrict_new_entry', $enroll_btn ) ); ?>
					</div>
				</div>
			<?php
		} elseif ( $maximum_students > 0 && $total_enrolled < $maximum_students ) {
			$total_booked = 100 / $maximum_students * $total_enrolled;
			$b_total      = ceil( $total_booked );
			?>
			<div class="<?php echo esc_attr( $utility_classes ); ?>">
				<div> 
					<span class="tutor-course-price tutor-fs-6 tutor-fw-bold tutor-color-black">
						<?php echo wp_kses_post( $price_html ); ?>
					</span>
				</div>

				<div class="tutor-course-booking-progress tutor-d-flex tutor-align-center">
					<div class="tutor-mr-8">
						<div class="tutor-progress-circle" style="--pro: <?php echo esc_html( $b_total ) . '%'; ?>" area-hidden="true"></div>
					</div>
					<div class="tutor-fs-7 tutor-fw-medium tutor-color-black">
					<?php echo esc_html( $b_total ) . __( '% Booked', 'tutor' ); ?>
					</div>
				</div>
			</div>
			<div class="tutor-course-booking-availability tutor-mt-16"> 
				<?php echo wp_kses_post( apply_filters( 'tutor_course_restrict_new_entry', $enroll_btn ) ); ?>
			</div>
			<?php
		} elseif ( $maximum_students > 0 && $maximum_students === $total_enrolled ) {
			?>
			<div class="<?php echo esc_attr( $utility_classes ); ?>">
				<div class="list-item-price tutor-d-flex tutor-align-center"> 
					<span class="price tutor-fs-6 tutor-fw-bold tutor-color-black"><?php echo wp_kses_post( $price_html ); ?></span>
				</div>
				<div class="list-item-booking booking-full tutor-d-flex tutor-align-center">
					<div class="booking-progress tutor-d-flex">
						<span class="tutor-mr-8 tutor-color-warning tutor-icon-circle-info"></span>
					</div>
					<div class="tutor-fs-7 tutor-fw-medium tutor-color-black">
						<?php __( 'Fully Booked', 'tutor' ); ?>
					</div>
				</div>
			</div>
			<?php
		} else {
			echo $free_html; //phpcs:ignore --contain safe data
		}
	}
} else {
	echo $free_html; //phpcs:ignore --contain safe data
}
