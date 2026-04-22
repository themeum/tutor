<?php
/**
 * Edd price template for the course list page
 *
 * @package Tutor\Templates
 * @subpackage EDDIntegration
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.3
 */

$product_id = tutor_utils()->get_course_product_id();
$download   = new EDD_Download( $product_id );

if ( $download->ID ) {

	$args = array( 'download_id' => $download->ID );

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

	if ( ! is_user_logged_in() ) {
		$args['class'] = 'tutor-open-login-modal';
	}

	echo edd_get_purchase_link( $args ); //phpcs:ignore
}
