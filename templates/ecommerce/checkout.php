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
use TUTOR\Input;

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
	<form method="post" id="tutor-checkout-form">
		<?php tutor_nonce_field(); ?>
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

						<?php require tutor()->path . 'templates/dashboard/settings/billing-form-fields.php'; ?>

						<h5 class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-24 tutor-mt-20">
							<?php esc_html_e( 'Payment Method', 'tutor' ); ?>
						</h5>
						<div class="tutor-checkout-payment-options">
							<input type="hidden" name="payment_method">
							<?php
							$payment_gateways = tutor_get_all_active_payment_gateways();
							if ( empty( $payment_gateways['automate'] ) && empty( $payment_gateways['manual'] ) ) {
								?>
								<div class="tutor-alert tutor-warning">
									<?php esc_html_e( 'No payment method has been configured. Please contact the site administrator.', 'tutor' ); ?>
								</div>
								<?php
							} else {
								foreach ( $payment_gateways['automate'] as $key => $gateway ) {
									list( $label, $icon ) = array_values( $gateway );
									?>
										<button type="button" data-payment-method="<?php echo esc_attr( $key ); ?>" data-payment-type="automate">
											<img src = "<?php echo esc_url( $icon ); ?>" alt="<?php echo esc_attr( $key ); ?>"/>
											<?php echo esc_html( $label ); ?>
										</button>
									<?php
								}

								// Show manual payment for only regular order.
								$plan_id = Input::get( 'plan', 0, Input::TYPE_INT );
								if ( ! $plan_id ) {
									foreach ( $payment_gateways['manual'] as $gateway ) {
										list( $label, $additional_details, $payment_instructions ) = array_values( $gateway );
										?>
											<button type="button" data-payment-method="<?php echo esc_attr( $label ); ?>" data-payment-type="manual" data-payment-details="<?php echo esc_attr( $gateway['additional_details'] ); ?>" data-payment-instruction="<?php echo esc_attr( $gateway['payment_instructions'] ); ?>">
												<?php echo esc_html( $label ); ?>
											</button>
										<?php
									}
								} elseif ( empty( $payment_gateways['automate'] ) ) {
									?>
									<div class="tutor-alert tutor-warning">
										<?php esc_html_e( 'No payment method supporting subscriptions has been configured. Please contact the site administrator.', 'tutor' ); ?>
									</div>
									<?php
								}
							}
							?>
						</div>
						<div class="tutor-payment-instructions tutor-d-none"></div>
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
