<?php
/**
 * Checkout Template.
 *
 * @package Tutor\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Tutor\Ecommerce\CartController;

$tutor_toc_page_link = tutor_utils()->get_toc_page_link();

$cart_controller = new CartController();
$get_cart        = $cart_controller->get_cart_items();
$courses         = $get_cart['courses'];
$total_count     = $courses['total_count'];
$course_list     = $courses['results'];
$subtotal        = 0;
$tax_amount      = 0; // @TODO: Need to implement later.
$course_ids      = implode( ', ', array_values( array_column( $course_list, 'ID' ) ) );

?>
<div class="tutor-checkout-page">
	<form method="post">
		<input type="hidden" name="tutor_action" value="tutor_pay_now">
		<div class="tutor-row tutor-g-0">
			<div class="tutor-col-md-6">
				<div class="tutor-checkout-billing">
					<div class="tutor-checkout-billing-inner">
						<h4 class="tutor-fs-3 tutor-fw-bold tutor-color-black tutor-mb-48">
							<?php echo esc_html_e( 'Checkout', 'tutor' ); ?>
						</h4>
						<h5 class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-24">
							<?php echo esc_html_e( 'Billing Address', 'tutor' ); ?>
						</h5>

						<form id="user_billing_form" style="max-width: 600px;">
							<?php tutor_nonce_field(); ?>
							<input type="hidden" value="tutor_save_billing_info" name="action" />

							<?php require tutor()->path . 'templates/dashboard/settings/billing-form-fields.php'; ?>
						</form>

						<h5 class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-24 tutor-mt-20">
							<?php esc_html_e( 'Payment Method', 'tutor' ); ?>
						</h5>
						<div class="tutor-checkout-payment-options">
							<input type="hidden" name="payment_method">
							<button type="button" data-payment-method="paypal">
								<img src="<?php echo esc_url( tutor()->url . 'assets/images/paypal.svg' ); ?>" alt="paypal" />
								Paypal
							</button>
							<button type="button" data-payment-method="stripe">
								<img src="<?php echo esc_url( tutor()->url . 'assets/images/stripe.svg' ); ?>" alt="stripe" />
								Stripe
							</button>
						</div>
					</div>
				</div>
			</div>
			<div class="tutor-col-md-6">
				<?php
				$file = __DIR__ . '/checkout-details.php';
				if ( file_exists( $file ) ) {
					include $file;
				}
				?>
			</div>
		</div>
	</form>
</div>
