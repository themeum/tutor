<?php
/**
 * Dashboard Invoice
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

$back_url = add_query_arg(
	array(
		'subpage' => 'billing',
		'tab'     => 'orders',
	),
	remove_query_arg( 'invoice' )
);

?>

<div class="tutor-billing-invoice">
	<div class="tutor-billing-invoice-header">
		<a href="<?php echo esc_url( $back_url ); ?>" class="tutor-btn tutor-btn-secondary tutor-btn-x-small tutor-btn-icon">
			<?php tutor_utils()->render_svg_icon( Icon::LEFT ); ?>
		</a>

		<button class="tutor-btn tutor-btn-primary tutor-btn-x-small tutor-gap-2">
			<?php tutor_utils()->render_svg_icon( Icon::DOWNLOAD_2 ); ?>
			<?php esc_html_e( 'Download Invoice', 'tutor' ); ?>
		</button>
	</div>

	<!-- Invoice Content -->
	<div class="tutor-billing-invoice-content">
		<img src="https://placehold.co/1000x1000/png" alt="Invoice Image">
	</div>
</div>