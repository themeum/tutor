<?php
/**
 * Order order placement success template
 *
 * @package Tutor\templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

tutor_utils()->tutor_custom_header();

$order_status;
$order_id;
?><div class="tutor-container tutor-order-status-wrapper">
	<div class="tutor-d-flex tutor-flex-column tutor-align-center tutor-gap-2 tutor-px-20 tutor-py-80 tutor-text-center">
		<div class="tutor-order-status-icon">
			<img src="<?php echo esc_attr( tutor()->url . 'assets/images/orders/order-confirmed.svg' ); ?>" alt="<?php esc_html_e( 'order confirmed', 'tutor' ); ?>">
		</div>

		<div class="tutor-order-status-content">
		<h2 class="tutor-fs-3 tutor-fw-medium tutor-color-black">
			<?php esc_html_e( 'Order Confirmed', 'tutor' ); ?>
		</h2>
		<p class="tutor-fs-6 tutor-color-secondary">
			<?php esc_html_e( 'You will receive an order confirmation email shortly', 'tutor' ); ?>
		</p>
		</div>

		<div class="tutor-d-flex tutor-gap-2">
			<a href="<?php echo esc_url( tutor_utils()->course_archive_page_url() ); ?>" class="tutor-btn tutor-btn-primary">
				<?php esc_html_e( 'Continue Shopping', 'tutor' ); ?>
			</a>
			<a href="<?php echo esc_url( tutor_utils()->get_tutor_dashboard_page_permalink( 'purchase_history' ) ); ?>" class="tutor-btn tutor-btn-secondary">
				<?php esc_html_e( 'Check Order List', 'tutor' ); ?>
			</a>
		</div>
	</div>
</div>
<?php tutor_utils()->tutor_custom_footer(); ?>
