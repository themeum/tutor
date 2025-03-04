<?php
/**
 * Add to cart EDD
 *
 * @package Tutor\Templates
 * @subpackage Single\Course
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.3
 */

$course_id  = get_the_ID();
$product_id = tutor_utils()->get_course_product_id();
$download   = new EDD_Download( $product_id );

if ( $download->ID ) {
	echo apply_filters( 'tutor_add_to_cart_btn', edd_get_purchase_link( array( 'download_id' => $download->ID ) ), $course_id ); //phpcs:ignore
} else {
	?>
	<p class="tutor-alert-warning">
		<?php esc_html_e( 'Please make sure that your EDD product exists and valid for this course', 'tutor' ); ?>
	</p>
	<?php
}
