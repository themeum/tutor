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
$course_ids      = implode( ', ', array_values( array_column( $course_list, 'ID') ) );
?>
<div class="tutor-checkout-page">
	<form id="tutor-checkout-form">
		<?php tutor_nonce_field(); ?>
		<input type="hidden" name="action" value="tutor_pay_now">
		<input type="hidden" name="course_id" value="<?php echo esc_attr( $course_ids ); ?>">
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

						<div class="tutor-row">
							<div class="tutor-col-12">
								<div class="tutor-form-group">
									<label><?php echo esc_html_e( 'Country', 'tutor' ); ?></label>
									<select name="country" class="tutor-form-control" require>
										<option value="">Select Country</option>
										<?php foreach ( tutils()->country_options() as $key => $name ) : ?>
											<option value="<?php echo esc_attr( $key ); ?>">
												<?php echo esc_html( $name ); ?>
											</option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>

							<div class="tutor-col-6">
								<div class="tutor-form-group">
									<label><?php echo esc_html_e( 'First Name', 'tutor' ); ?></label>
									<input type="text" name="first_name" class="tutor-form-control" required>
								</div>
							</div>

							<div class="tutor-col-6">
								<div class="tutor-form-group">
									<label><?php echo esc_html_e( 'Last Name', 'tutor' ); ?></label>
									<input type="text" name="last_name" class="tutor-form-control" required>
								</div>
							</div>

							<div class="tutor-col-12">
								<div class="tutor-form-group">
									<label><?php echo esc_html_e( 'Email Address', 'tutor' ); ?></label>
									<input type="email" name="email" class="tutor-form-control" required>
								</div>
							</div>

							<div class="tutor-col-12">
								<div class="tutor-form-group">
									<label><?php echo esc_html_e( 'Mobile', 'tutor' ); ?></label>
									<input type="text" name="mobile" class="tutor-form-control" required>
								</div>
							</div>
						</div>

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

						<!-- <div class="tutor-checkout-separator">
							<span><?php esc_html_e( 'Or', 'tutor' ); ?></span>
						</div>

						<div class="tutor-row">
							<div class="tutor-col-12">
								<div class="tutor-form-group">
									<label><?php echo esc_html_e( 'Card Number', 'tutor' ); ?></label>
									<input type="text" name="card_number" class="tutor-form-control">
								</div>
							</div>

							<div class="tutor-col-6">
								<div class="tutor-form-group">
									<label><?php echo esc_html_e( 'Expiration', 'tutor' ); ?></label>
									<input type="text" name="expiration" class="tutor-form-control">
								</div>
							</div>

							<div class="tutor-col-6">
								<div class="tutor-form-group">
									<label><?php echo esc_html_e( 'CVC', 'tutor' ); ?></label>
									<input type="text" name="cvc" class="tutor-form-control">
								</div>
							</div>
						</div> -->
					</div>
				</div>
			</div>
			<div class="tutor-col-md-6">
				<div class="tutor-checkout-details">
					<div class="tutor-p-32 tutor-border-bottom">
						<h5 class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-24">
							<?php esc_html_e( 'Order Details', 'tutor' ); ?>
						</h5>

						<div class="tutor-checkout-courses">
							<?php if ( is_array( $course_list ) && count( $course_list ) ) : ?>
								<?php
								foreach ( $course_list as $key => $course ) :
									$course_price  = tutor_utils()->get_raw_course_price( $course->ID );
									$regular_price = $course_price->regular_price;
									$sale_price    = $course_price->sale_price;

									$subtotal += $sale_price ? $sale_price : $regular_price;
									?>
									<div class="tutor-checkout-course-item">
										<!-- @TODO: Need to add bundle product support -->
										<!-- <div class="tutor-checkout-course-bundle-badge">5 Course bundle</div> -->
										<div class="tutor-checkout-course-content">
											<div>
												<h6 class="tutor-checkout-course-title">
													<a href="<?php echo esc_url( get_the_permalink( $course ) ); ?>">
														<?php echo esc_html( $course->post_title ); ?>
													</a>
												</h6>
												<div class="tutor-checkout-coupon-badge tutor-d-none">
													<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
														<path d="M4.48736 3.73748C4.63569 3.73748 4.7807 3.78147 4.90403 3.86388C5.02737 3.94629 5.1235 4.06342 5.18027 4.20047C5.23703 4.33751 5.25188 4.48831 5.22294 4.6338C5.19401 4.77929 5.12258 4.91292 5.01769 5.01781C4.9128 5.1227 4.77916 5.19413 4.63367 5.22307C4.48819 5.25201 4.33739 5.23716 4.20034 5.18039C4.0633 5.12363 3.94616 5.0275 3.86375 4.90416C3.78134 4.78082 3.73736 4.63582 3.73736 4.48748C3.73736 4.28857 3.81637 4.0978 3.95703 3.95715C4.09768 3.8165 4.28844 3.73748 4.48736 3.73748ZM6.38169 2.17392L8.26801 4.06024L8.43583 4.22806L9.82102 5.61325C10.0091 5.80177 10.1151 6.05695 10.116 6.32325C10.1166 6.45393 10.0911 6.58341 10.0408 6.70403C9.99052 6.82466 9.91657 6.93397 9.82332 7.02553L9.82354 7.02574L7.02563 9.82366L7.02541 9.82344C6.93386 9.91669 6.82454 9.99064 6.70392 10.0409C6.58329 10.0912 6.45381 10.1168 6.32314 10.1161C6.19232 10.1166 6.06271 10.0912 5.94172 10.0414C5.82074 9.99166 5.71078 9.9185 5.61814 9.82614L4.22793 8.43596L4.06011 8.26814L2.17879 6.38682C2.08511 6.29386 2.0108 6.18325 1.96017 6.06137C1.90953 5.9395 1.88357 5.80879 1.88379 5.67682V4.68183V2.88392C1.88455 2.61894 1.99016 2.36502 2.17753 2.17765C2.3649 1.99028 2.61881 1.88468 2.88379 1.88392H4.6817H5.67668C5.80749 1.88347 5.93711 1.90886 6.05809 1.95863C6.17908 2.0084 6.28904 2.08156 6.38169 2.17392ZM7.73084 4.93304L7.56302 4.76522L5.6817 2.88392H2.88379V5.68183L4.93291 7.73097L6.32261 9.12067L9.11651 6.31874L9.11601 6.31824L7.73084 4.93304Z" fill="currentColor"/>
													</svg>
													<span>WINTERISHERE</span>
												</div>
											</div>
											<div class="tutor-text-right">
												<div class="tutor-fw-bold">
													<?php echo tutor_get_formatted_price( $sale_price ? $sale_price : $regular_price ); //phpcs:ignore?>
												</div>
												<?php if ( $regular_price && $sale_price && $sale_price !== $regular_price ) : ?>
												<div class="tutor-checkout-discount-price">
													<?php echo tutor_get_formatted_price( $regular_price ); //phpcs:ignore?>
												</div>
												<?php endif; ?>
											</div>
										</div>
									</div>
								<?php endforeach; ?>
							<?php else : ?>
								<?php tutor_utils()->tutor_empty_state( tutor_utils()->not_found_text() ); ?>
							<?php endif; ?>
						</div>
					</div>

					<div class="tutor-checkout-summary tutor-p-32 tutor-border-bottom">
						<div class="tutor-checkout-summary-item">
							<div class="tutor-fw-medium"><?php esc_html_e( 'Subtotal', 'tutor' ); ?></div>
							<div class="tutor-fw-bold">
								<?php echo tutor_get_formatted_price( $subtotal ); //phpcs:ignore?>
							</div>
						</div>
						<div class="tutor-checkout-summary-item">
							<div><?php esc_html_e( 'Have a coupon?', 'tutor' ); ?></div>
							<button type="button" id="tutor-toggle-coupon-form" class="tutor-btn tutor-btn-link">
								<?php esc_html_e( 'Click here', 'tutor' ); ?>
							</button>
						</div>
						<div class="tutor-checkout-coupon-form tutor-d-none">
							<input type="text" placeholder="<?php esc_html_e( 'Add coupon code', 'tutor' ); ?>">
							<button type="button" class="tutor-btn tutor-btn-secondary"><?php esc_html_e( 'Apply', 'tutor' ); ?></button>
						</div>
						<div class="tutor-checkout-summary-item tutor-checkout-coupon-wrapper tutor-d-none">
							<div class="tutor-checkout-coupon-badge tutor-has-delete-button">
								<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M7.4799 6.22906C7.72713 6.22906 7.9688 6.30237 8.17437 6.43972C8.37993 6.57707 8.54014 6.77229 8.63475 7.0007C8.72936 7.22911 8.75412 7.48044 8.70588 7.72292C8.65765 7.96539 8.5386 8.18812 8.36379 8.36294C8.18897 8.53775 7.96624 8.65681 7.72376 8.70504C7.48129 8.75327 7.22996 8.72851 7.00155 8.6339C6.77314 8.53929 6.57792 8.37908 6.44057 8.17352C6.30321 7.96796 6.2299 7.72628 6.2299 7.47905C6.2299 7.14753 6.3616 6.82959 6.59602 6.59517C6.83044 6.36075 7.14838 6.22906 7.4799 6.22906ZM10.6371 3.62311L13.781 6.76699L14.0607 7.04669L16.3693 9.35534C16.6828 9.66953 16.8595 10.0948 16.861 10.5387C16.8621 10.7565 16.8194 10.9723 16.7356 11.1733C16.6518 11.3743 16.5286 11.5565 16.3732 11.7091L16.3735 11.7095L11.7104 16.3727L11.71 16.3723C11.5574 16.5277 11.3752 16.651 11.1742 16.7348C10.9731 16.8186 10.7573 16.8612 10.5395 16.8601C10.3215 16.8609 10.1055 16.8186 9.90385 16.7356C9.70222 16.6527 9.51894 16.5307 9.36454 16.3768L7.04753 14.0599L6.76783 13.7801L3.63229 10.6446C3.47616 10.4897 3.35231 10.3053 3.26792 10.1022C3.18352 9.89909 3.14026 9.68124 3.14063 9.46129V7.80296V4.80645C3.1419 4.36481 3.3179 3.94163 3.63019 3.62934C3.94247 3.31706 4.36566 3.14105 4.80729 3.13978H7.80381H9.4621C9.68013 3.13904 9.89616 3.18136 10.0978 3.2643C10.2994 3.34724 10.4827 3.46918 10.6371 3.62311ZM12.8857 8.22165L12.606 7.94195L9.47048 4.80645H4.80729V9.46964L8.22249 12.8849L10.5387 15.201L15.1952 10.5312L15.1943 10.5303L12.8857 8.22165Z" fill="#4B505C"/>
								</svg>
								<span>WINTERISHERE</span>
								<button type="button" id="tutor-checkout-remove-coupon">
									<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path fill-rule="evenodd" clip-rule="evenodd" d="M5.54243 5.36494C5.78651 5.12087 6.18224 5.12087 6.42632 5.36494L10.1775 9.11612L13.9287 5.36494C14.1727 5.12087 14.5685 5.12087 14.8125 5.36494C15.0566 5.60902 15.0566 6.00475 14.8125 6.24883L11.0614 10L14.8125 13.7512C15.0566 13.9952 15.0566 14.391 14.8125 14.6351C14.5685 14.8791 14.1727 14.8791 13.9287 14.6351L10.1775 10.8839L6.42632 14.6351C6.18224 14.8791 5.78651 14.8791 5.54243 14.6351C5.29836 14.391 5.29836 13.9952 5.54243 13.7512L9.29361 10L5.54243 6.24883C5.29836 6.00475 5.29836 5.60902 5.54243 5.36494Z" fill="#9197A8"/>
									</svg>
								</button>
							</div>
							<div class="tutor-fw-bold">-$10.00</div>
						</div>
						<div class="tutor-checkout-summary-item">
							<div><?php esc_html_e( 'Tax', 'tutor' ); ?></div>
							<div><?php echo tutor_get_formatted_price( $tax_amount ); //phpcs:ignore?></div>
						</div>
					</div>

					<div class="tutor-p-32">
						<div class="tutor-checkout-summary-item tutor-mb-40">
							<div class="tutor-fw-medium"><?php esc_html_e( 'Grand Total', 'tutor' ); ?></div>
							<div class="tutor-fw-bold"><?php echo tutor_get_formatted_price( $subtotal + $tax_amount ); //phpcs:ignore?></div>
						</div>

						<?php if ( null !== $tutor_toc_page_link ) : ?>
							<div class="tutor-mb-16">
								<div class="tutor-form-check">
									<input type="checkbox" id="tutor_checkout_agree_to_terms" name="agree_to_terms" class="tutor-form-check-input">
									<label for="tutor_checkout_agree_to_terms">
										<?php esc_html_e( 'I agree with the website\'s', 'tutor' ); ?> <a target="_blank" href="<?php echo esc_url( $tutor_toc_page_link ); ?>" class="tutor-color-primary"><?php esc_html_e( 'Terms and Conditions', 'tutor' ); ?></a>
									</label>
								</div>
							</div>
						<?php endif; ?>

						<button type="submit" id="tutor-checkout-pay-now-button" class="tutor-btn tutor-btn-primary tutor-btn-lg tutor-w-100 tutor-justify-center">
							<?php esc_html_e( 'Pay Now', 'tutor' ); ?>
						</button>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
