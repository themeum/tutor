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

use Tutor\Ecommerce\BillingController;
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

$billing_controller = new BillingController();
$billing_info       = $billing_controller->get_billing_info();

$billing_first_name = $billing_info->billing_first_name ?? '';
$billing_last_name  = $billing_info->billing_last_name ?? '';
$billing_email      = $billing_info->billing_email ?? '';
$billing_phone      = $billing_info->billing_phone ?? '';
$billing_zip_code   = $billing_info->billing_zip_code ?? '';
$billing_address    = $billing_info->billing_address ?? '';
$billing_country    = $billing_info->billing_country ?? '';
$billing_state      = $billing_info->billing_state ?? '';
$billing_city       = $billing_info->billing_city ?? '';
?>
<div class="tutor-checkout-page">
	<div>
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

							<div class="tutor-row">
								<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-12 tutor-col-lg-6">
									<div class="tutor-mb-32">
										<label class="tutor-form-label tutor-color-secondary">
											<?php esc_html_e( 'First Name', 'tutor' ); ?>
										</label>
										<input class="tutor-form-control" type="text" name="billing_first_name" value="<?php echo esc_attr( $billing_first_name ); ?>" required>
									</div>
								</div>

								<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-12 tutor-col-lg-6">
									<div class="tutor-mb-32">
										<label class="tutor-form-label tutor-color-secondary">
											<?php esc_html_e( 'Last Name', 'tutor' ); ?>
										</label>
										<input class="tutor-form-control" type="text" name="billing_last_name" value="<?php echo esc_attr( $billing_last_name ); ?>" required>
									</div>
								</div>

								<div class="tutor-col-12">
									<div class="tutor-mb-32">
										<label class="tutor-form-label tutor-color-secondary">
											<?php esc_html_e( 'Email Address', 'tutor' ); ?>
										</label>
										<input class="tutor-form-control" type="email" name="billing_email" value="<?php echo esc_attr( $billing_email ); ?>" required>
									</div>
								</div>

								<div class="tutor-col-12">
									<div class="tutor-mb-32">
										<label class="tutor-form-label tutor-color-secondary">
											<?php esc_html_e( 'Country', 'tutor' ); ?>
										</label>
										<select name="billing_country" class="tutor-form-control" required>
											<option value=""><?php esc_html_e( 'Select Country', 'tutor' ); ?></option>
											<?php foreach ( tutils()->country_options() as $key => $name ) : ?>
												<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $billing_country, $key ); ?>>
													<?php echo esc_html( $name ); ?>
												</option>
											<?php endforeach; ?>
										</select>
									</div>
								</div>

								<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-12 tutor-col-lg-6">
									<div class="tutor-mb-32">
										<label class="tutor-form-label tutor-color-secondary">
											<?php esc_html_e( 'State', 'tutor' ); ?>
										</label>
										<input class="tutor-form-control" type="text" name="billing_state" value="<?php echo esc_attr( $billing_state ); ?>" required>
									</div>
								</div>

								<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-12 tutor-col-lg-6">
									<div class="tutor-mb-32">
										<label class="tutor-form-label tutor-color-secondary">
											<?php esc_html_e( 'City', 'tutor' ); ?>
										</label>
										<input class="tutor-form-control" type="text" name="billing_city" value="<?php echo esc_attr( $billing_city ); ?>" required>
									</div>
								</div>

								<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-12 tutor-col-lg-6">
									<div class="tutor-mb-32">
										<label class="tutor-form-label tutor-color-secondary">
											<?php esc_html_e( 'Postcode / ZIP', 'tutor' ); ?>
										</label>
										<input class="tutor-form-control" type="text" name="billing_zip_code" value="<?php echo esc_attr( $billing_zip_code ); ?>" required>
									</div>
								</div>

								<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-12 tutor-col-lg-6">
									<div class="tutor-mb-32">
										<label class="tutor-form-label tutor-color-secondary">
											<?php esc_html_e( 'Phone', 'tutor' ); ?>
										</label>
										<input class="tutor-form-control" type="text" name="billing_phone" value="<?php echo esc_attr( $billing_phone ); ?>" required>
									</div>
								</div>

								<div class="tutor-col-12">
									<div class="tutor-mb-32">
										<label class="tutor-form-label tutor-color-secondary">
											<?php esc_html_e( 'Address', 'tutor' ); ?>
										</label>
										<input class="tutor-form-control" type="text" name="billing_address" value="<?php echo esc_attr( $billing_address ); ?>" required>
									</div>
								</div>
							</div>
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
	</div>
</div>
