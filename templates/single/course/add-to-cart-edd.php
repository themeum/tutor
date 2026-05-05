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

defined( 'ABSPATH' ) || exit;

$course_id  = get_the_ID();
$product_id = tutor_utils()->get_course_product_id();
$download   = new EDD_Download( $product_id );

if ( $download->ID ) {

	/**
	 * Improved purchase link rendering using EDD native helper.
	 *
	 * @since 4.0.0
	 */
	add_filter(
		'edd_download_redirect_to_checkout',
		function ( $redirect ) {
			return is_user_logged_in() ? $redirect : false;
		}
	);

	$args = array(
		'download_id' => $download->ID,
		'class'       => 'tutor-btn tutor-btn-primary tutor-btn-md tutor-btn-block',
	);

	if ( ! is_user_logged_in() ) {
		$args['class'] = 'tutor-open-login-modal';
	}

	/**
	 * Added to align button styling with tutor-btn.
	 *
	 * @since 4.0.0
	 */
	add_filter(
		'edd_purchase_link_args',
		function( $args ) {
			$args['class'] = str_replace( 'edd-submit', '', $args['class'] );
			return $args;
		},
		PHP_INT_MAX
	);

	echo apply_filters( 'tutor_add_to_cart_btn', edd_get_purchase_link( $args ), $course_id ); //phpcs:ignore
} else {
	?>
	<p class="tutor-alert-warning">
		<?php esc_html_e( 'Please make sure that your EDD product exists and valid for this course', 'tutor' ); ?>
	</p>
	<?php
}
