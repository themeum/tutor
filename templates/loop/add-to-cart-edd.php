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

defined( 'ABSPATH' ) || exit;

use Tutor\Components\SvgIcon;

$product_id = tutor_utils()->get_course_product_id();
$download   = new EDD_Download( $product_id );

if ( $download->ID ) {

	$args = array(
		'download_id' => $download->ID,
		'class'       => 'tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-btn-block',
	);

	/**
	 * Improved purchase link rendering using EDD native helper.
	 *
	 * @since 4.0.0
	 */
	add_filter( 'edd_download_redirect_to_checkout', fn( $redirect ) => is_user_logged_in() ? $redirect : false );

	if ( ! is_user_logged_in() ) {
		$args['class'] = $args['class'] . ' tutor-open-login-modal';
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

	echo edd_get_purchase_link( $args ); //phpcs:ignore
} else {
	?>
	<div class="tutor-d-flex tutor-items-center tutor-gap-1 tutor-fs-7 tutor-color-muted">
		<?php SvgIcon::make()->name( 'info' )->size( 20 )->render(); ?>
		<p class="tutor-m-0"><?php esc_html_e( 'No EDD product for this course', 'tutor' ); ?></p>
	</div>
	<?php
}
