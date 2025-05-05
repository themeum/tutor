<?php
/**
 * Order order placement failed template
 *
 * @package Tutor\templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

use Tutor\Ecommerce\CheckoutController;
use TUTOR\Input;

tutor_utils()->tutor_custom_header();

$order_status;
$order_id;
$error_msg = Input::get( 'error_message' );
$back_url  = wp_get_referer() ? wp_get_referer() : CheckoutController::get_page_url();?>

<div class="tutor-container tutor-order-status-wrapper">
	<div class="tutor-d-flex tutor-flex-column tutor-align-center tutor-gap-2 tutor-px-20 tutor-py-80 tutor-text-center">
		<div class="tutor-order-status-icon">
			<img src="<?php echo esc_attr( tutor()->url . 'assets/images/orders/payment-failed.svg' ); ?>" alt="<?php esc_html_e( 'payment failed', 'tutor' ); ?>">
		</div>

		<div class="tutor-order-status-content">
			<h2 class="tutor-fs-3 tutor-fw-medium tutor-color-black">
				<?php esc_html_e( 'Payment failed', 'tutor' ); ?>
			</h2>
			<p class="tutor-fs-6 tutor-color-secondary">
				<?php echo $error_msg ? esc_html( $error_msg ) : esc_html__( 'An error occurred. Please try to place the order again', 'tutor' ); ?>
			</p>
		</div>

		<div class="tutor-order-status-actions">
			<a href="<?php echo esc_url( $back_url ); ?>" class="tutor-btn tutor-btn-primary">
				<?php esc_html_e( 'Back to Checkout', 'tutor' ); ?>
			</a>
		</div>
	</div>
</div>
<?php tutor_utils()->tutor_custom_footer(); ?>
