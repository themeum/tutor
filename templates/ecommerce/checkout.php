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

use Tutor\Ecommerce\CheckoutController;
use Tutor\Ecommerce\CartController;
use TUTOR\Input;

$user_id = apply_filters( 'tutor_checkout_user_id', get_current_user_id() );

$tutor_toc_page_link     = tutor_utils()->get_toc_page_link();
$tutor_privacy_page_link = tutor_utils()->get_privacy_page_link();

$cart_controller = new CartController();
$get_cart        = $cart_controller->get_cart_items();
$courses         = $get_cart['courses'];
$total_count     = $courses['total_count'];
$course_list     = $courses['results'];
$subtotal        = 0;
$course_ids      = implode( ', ', array_values( array_column( $course_list, 'ID' ) ) );
$plan_id         = Input::get( 'plan', 0, Input::TYPE_INT );

$is_checkout_page = true;

?>
<div class="tutor-checkout-page">
<div class="tutor-container">
<div class="tutor-checkout-container">
	<?php
	$echo_before_return    = true;
	$user_has_subscription = apply_filters( 'tutor_checkout_user_has_subscription', false, $plan_id, $echo_before_return );
	if ( $user_has_subscription ) {
		return;
	}
	?>

	<!-- Alert if nonce failed -->
	<?php
	$nonce_alert = get_transient( CheckoutController::PAY_NOW_ALERT_MSG_TRANSIENT_KEY . 'pay_now_nonce_alert' );
	if ( $nonce_alert ) {
		?>
		<div class="tutor-alert tutor-danger">
			<div class="tutor-color-danger"><?php echo esc_html( $nonce_alert[0] ); ?></div>
		</div>
		<?php
		delete_transient( CheckoutController::PAY_NOW_ALERT_MSG_TRANSIENT_KEY . 'pay_now_nonce_alert' );
	}
	?>

	<form method="post" id="tutor-checkout-form">
		<?php tutor_nonce_field(); ?>
		<input type="hidden" name="tutor_action" value="tutor_pay_now">
		<div class="tutor-row tutor-g-5">
			<div class="tutor-col-md-6" tutor-checkout-details>
				<?php
				$file = __DIR__ . '/checkout-details.php';
				if ( file_exists( $file ) ) {
					include $file;
				}
				?>
			</div>
			<div class="tutor-col-md-6">
				<div class="tutor-checkout-billing">
					<div class="tutor-checkout-billing-inner">
					<?php
					if ( ! is_user_logged_in() ) {
						$login_url = tutor_utils()->get_option( 'enable_tutor_native_login', null ) ? '' : wp_login_url( tutor()->current_url );
						?>
							<div class="tutor-mb-32 tutor-d-flex tutor-align-center tutor-justify-between tutor-border tutor-radius-6 tutor-p-12">
								<p class="tutor-m-0"><?php esc_html_e( 'Already have an account?', 'tutor' ); ?></p>
								<button type="button" class="tutor-btn tutor-btn-secondary tutor-btn-sm tutor-open-login-modal" data-login_url="<?php echo esc_url( $login_url ); ?>">
								<?php esc_html_e( 'Login', 'tutor' ); ?>
								</button>
							</div>
					<?php } ?>

						<h5 class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-12 tutor-mt-0">
							<?php
							$address_title = __( 'Billing Address', 'tutor' );
							if ( ! is_user_logged_in() ) {
								$address_title = __( 'Continue as Guest', 'tutor' );
							}

							echo esc_html( $address_title );
							?>
						</h5>

						<div class="tutor-billing-fields">
							<?php require tutor()->path . 'templates/ecommerce/billing-form-fields.php'; ?>
						</div>
						<div class="tutor-payment-method-wrapper tutor-mt-20 <?php echo esc_attr( $show_payment_methods ? '' : 'tutor-d-none' ); ?>">
							<h5 class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-12">
								<?php esc_html_e( 'Payment Method', 'tutor' ); ?>
							</h5>
							<div class="tutor-checkout-payment-options tutor-mb-24">
								<input type="hidden" name="payment_type">

								<?php if ( ! $show_payment_methods ) : ?>
									<input type="hidden" name="payment_method" value="free" id="tutor-temp-payment-method">
								<?php endif; ?>

								<?php
								$supported_gateways = $plan_id ? tutor_get_subscription_supported_payment_gateways() : tutor_get_all_active_payment_gateways();
								if ( empty( $supported_gateways ) ) {
									?>

										<div class="tutor-alert tutor-warning">
										<?php esc_html_e( 'No payment method found. Please contact the site administrator.', 'tutor' ); ?>
										</div>
										<?php
								} else {
									foreach ( $supported_gateways as $gateway ) {
										list( 'name' => $name, 'label' => $label, 'icon' => $icon ) = $gateway;
										$is_manual = $gateway['is_manual'] ?? false;
										if ( $is_manual ) {
											?>
											<label class="tutor-checkout-payment-item" data-payment-method="<?php echo esc_attr( $name ); ?>" data-payment-type="manual">
												<input type="radio" value="<?php echo esc_attr( $name ); ?>" name="payment_method" class="tutor-form-check-input">
												<div class="tutor-payment-item-content">
												<?php if ( ! empty( $icon ) ) : ?>
													<img src ="<?php echo esc_url( $icon ); ?>" alt="<?php echo esc_attr( $name ); ?>"/>
													<?php endif; ?>
												<?php echo esc_html( $label ); ?>
												</div>
												<div class="tutor-d-none tutor-payment-item-instructions"><?php echo wp_kses_post( $gateway['payment_instructions'] ?? '' ); ?></div>
											</label>
												<?php
										} else {
											?>
											<label class="tutor-checkout-payment-item" data-payment-type="automate">
												<input type="radio" name="payment_method" value="<?php echo esc_attr( $name ); ?>" class="tutor-form-check-input">
												<div class="tutor-payment-item-content">
												<?php if ( ! empty( $icon ) ) : ?>
													<img src = "<?php echo esc_url( $icon ); ?>" alt="<?php echo esc_attr( $name ); ?>"/>
													<?php endif; ?>
												<?php echo esc_html( $label ); ?>
												</div>
											</label>
												<?php
										}
									}
								}
								?>
							</div>

							<div class="tutor-payment-instructions tutor-mb-20 tutor-d-none"></div>
						</div>
						<?php if ( null !== $tutor_toc_page_link ) : ?>
							<div class="tutor-mt-20">
								<div class="tutor-form-check tutor-d-flex">
									<input type="checkbox" id="tutor_checkout_agree_to_terms" name="agree_to_terms" class="tutor-form-check-input" required>
									<label for="tutor_checkout_agree_to_terms">
										<span class="tutor-color-subdued tutor-fw-normal">
											<?php esc_html_e( 'I agree with the website\'s', 'tutor' ); ?> 
											<a target="_blank" href="<?php echo esc_url( $tutor_toc_page_link ); ?>" class="tutor-color-primary"><?php esc_html_e( 'Terms of Use', 'tutor' ); ?></a> 
											<?php if ( null !== $tutor_privacy_page_link ) : ?>
												<?php esc_html_e( 'and', 'tutor' ); ?> 
												<a target="_blank" href="<?php echo esc_url( $tutor_privacy_page_link ); ?>" class="tutor-color-primary"><?php esc_html_e( 'Privacy Policy', 'tutor' ); ?></a>
											<?php endif; ?>
										</span>
									</label>
								</div>
							</div>
						<?php endif; ?>
						<!-- handle errors -->
						<?php
						$pay_now_errors    = get_transient( CheckoutController::PAY_NOW_ERROR_TRANSIENT_KEY . $user_id );
						$pay_now_alert_msg = get_transient( CheckoutController::PAY_NOW_ALERT_MSG_TRANSIENT_KEY . $user_id );

						delete_transient( CheckoutController::PAY_NOW_ALERT_MSG_TRANSIENT_KEY . $user_id );
						delete_transient( CheckoutController::PAY_NOW_ERROR_TRANSIENT_KEY . $user_id );
						if ( $pay_now_errors || $pay_now_alert_msg ) :
							?>
						<div class="tutor-break-word tutor-mt-16">
							<?php
							if ( ! empty( $pay_now_alert_msg ) ) :
								list( $alert, $message ) = array_values( $pay_now_alert_msg );
								?>
								<div class="tutor-alert tutor-<?php echo esc_attr( $alert ); ?>">
									<div class="tutor-color-<?php echo esc_attr( $alert ); ?>"><?php echo esc_html( $message ); ?></div>
								</div>
							<?php endif; ?>

							<?php if ( is_array( $pay_now_errors ) && count( $pay_now_errors ) ) : ?>
							<div class="tutor-alert tutor-danger">
								<ul class="tutor-mb-0">
									<?php foreach ( $pay_now_errors as $pay_now_err ) : ?>
										<li class="tutor-color-danger"><?php echo esc_html( ucfirst( str_replace( '_', ' ', $pay_now_err ) ) ); ?></li>
									<?php endforeach; ?>
								</ul>
							</div>
							<?php endif; ?>
						</div>
						<?php endif; ?>
						<!-- handle errors end -->
						<?php $enable_pay_now_btn = apply_filters( 'tutor_checkout_enable_pay_now_btn', true, $checkout_data ); ?>
						<button type="submit" <?php echo $enable_pay_now_btn ? '' : 'disabled'; ?>  id="tutor-checkout-pay-now-button" class="tutor-btn tutor-btn-primary tutor-btn-lg tutor-w-100 tutor-justify-center tutor-mt-16">
							<?php echo esc_html( $pay_now_btn_text ); ?>
						</button>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
</div>
</div>
<?php
if ( ! is_user_logged_in() ) {
	tutor_load_template_from_custom_path( tutor()->path . '/views/modal/login.php' );
}
?>
