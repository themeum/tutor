<?php
/**
 * Checkout info template
 *
 * @package Tutor\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

use Tutor\Ecommerce\OptionKeys;
use Tutor\Ecommerce\Supports\Shop;
use TUTOR\Input;
use Tutor\Models\CouponModel;
use Tutor\Models\OrderModel;

$plan_id      = Input::get( 'plan', 0, Input::TYPE_INT );
$plan_info    = new stdClass();
$coupon_model = new CouponModel();

$plan_info = apply_filters( 'tutor_checkout_plan_info', $plan_info, $plan_id );

/**
 * Course/Bundle ids to apply coupon
 */
$object_ids = array();
$order_type = OrderModel::TYPE_SINGLE_ORDER;

$is_coupon_applicable = tutor_utils()->get_option( OptionKeys::IS_COUPON_APPLICABLE );
?>

<div class="tutor-checkout-details">
	<h4 class="tutor-fs-3 tutor-fw-bold tutor-color-black tutor-mb-24">
		<?php echo esc_html_e( 'Checkout', 'tutor' ); ?>
	</h4>
	<div class="tutor-checkout-details-inner">
		<div class="tutor-checkout-detail-item">
			<h5 class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-24">
				<?php esc_html_e( 'Order Details', 'tutor' ); ?>
			</h5>

			<div class="tutor-checkout-courses">
				<?php
				if ( isset( $plan_info->plan_name, $plan_info->regular_price ) ) :
					$order_type       = OrderModel::TYPE_SUBSCRIPTION;
					$automatic_coupon = $coupon_model->apply_automatic_coupon_discount( $plan_info->id, $order_type );
					$regular_price    = $plan_info->regular_price;
					$sale_price       = $plan_info->in_sale_price ? $plan_info->sale_price : 0;
					$enrollment_fee   = floatval( $plan_info->enrollment_fee );

					if ( $automatic_coupon->is_applied ) {
						foreach ( $automatic_coupon->items as $item ) {
							if ( $item->item_id === $plan_info->course_id ) {
								$regular_price = $item->regular_price;
								$sale_price    = $item->discount_price;
								break;
							}
						}
					}

					$subtotal  = $sale_price ? $sale_price : $regular_price;
					$subtotal += $enrollment_fee;

					array_push( $object_ids, $plan_info->id );
					?>
				<div class="tutor-checkout-course-item">
					<div class="tutor-checkout-course-content">
						<div>
							<h6 class="tutor-checkout-course-title">
								<?php echo esc_html( $plan_info->plan_name ); ?>
							</h6>
							<?php if ( $automatic_coupon->is_applied ) : ?>
							<div class="tutor-checkout-coupon-badge">
								<i class="tutor-icon-tag" area-hidden="true"></i>
								<span><?php echo esc_html( $automatic_coupon->coupon_title ); ?></span>
							</div>
							<?php endif; ?>
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
					<?php if ( $enrollment_fee > 0 ) : ?>
						<div class="tutor-checkout-enrollment-fee">
							<div>
								<?php echo esc_html_e( 'Enrollment Fee', 'tutor' ); ?>
							</div>
							<div class="tutor-text-right">
								<div class="tutor-fw-bold">
									<?php echo tutor_get_formatted_price( $enrollment_fee ); //phpcs:ignore ?>
								</div>
							</div>
						</div>
					<?php endif; ?>
				</div>
				<?php else : ?>
					<?php
					$course_list = $checkout_manager->get_items();

					if ( ! empty( $course_list ) ) :
						$course_ids       = array_column( $course_list, 'ID' );
						$automatic_coupon = ( new CouponModel() )->apply_automatic_coupon_discount( $course_ids );
						?>
						<?php
						foreach ( $course_list as $key => $course ) :
							$has_automatic_coupon = false;
							$course_price         = tutor_utils()->get_raw_course_price( $course->ID );
							$regular_price        = $course_price->regular_price;
							$sale_price           = $course_price->sale_price;

							if ( $automatic_coupon->is_applied ) {
								foreach ( $automatic_coupon->items as $item ) {
									if ( $item->item_id === $course->ID && $item->is_applied ) {
										$has_automatic_coupon = $item->is_applied;
										$regular_price        = $item->regular_price;
										$sale_price           = $item->discount_price;
										break;
									}
								}
							}

							$subtotal += $sale_price ? $sale_price : $regular_price;

							array_push( $object_ids, $course->ID );
							?>
							<div class="tutor-checkout-course-item" data-course-id="<?php echo esc_attr( $course->ID ); ?>">
								<?php if ( tutor()->has_pro && 'course-bundle' === $course->post_type ) : ?>
								<div class="tutor-checkout-course-bundle-badge">
									<?php
										$bundle_model      = new \TutorPro\CourseBundle\Models\BundleModel();
										$bundle_course_ids = $bundle_model::get_bundle_course_ids( $course->ID );
										// translators: %d: Number of courses in the cart.
										echo esc_html( sprintf( __( '%d Course bundle', 'tutor' ), count( $bundle_course_ids ) ) );
									?>
								</div>
								<?php endif; ?>
								<div class="tutor-checkout-course-content">
									<div>
										<h6 class="tutor-checkout-course-title">
											<a href="<?php echo esc_url( get_the_permalink( $course ) ); ?>">
												<?php echo esc_html( $course->post_title ); ?>
											</a>
										</h6>
										<div class="tutor-checkout-coupon-badge <?php echo esc_attr( $checkout_manager->is_coupon_applied() ? '' : 'tutor-d-none' ); ?>">
											<i class="tutor-icon-tag" area-hidden="true"></i>
											<span><?php echo esc_html( $checkout_manager->get_coupon_code() . ' (' . Shop::as_negative( $course->final_price->discount_value_with_currency ) . ')' ); ?></span>
										</div>
									</div>
									<div class="tutor-text-right">
										<div class="tutor-fw-bold">
											<?php echo $checkout_manager->is_coupon_applied() ? $course->final_price->discounted_price_with_currency : $course->final_price->item_price_with_currency; ?>
										</div>
										<?php if ( $checkout_manager->is_coupon_applied() ) : ?>
											<div class="tutor-checkout-discount-price">
												<?php echo $course->final_price->item_price_with_currency; //phpcs:ignore?>
											</div>
										<?php endif; ?>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					<?php else : ?>
						<?php tutor_utils()->tutor_empty_state( tutor_utils()->not_found_text() ); ?>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>

		<div class="tutor-checkout-detail-item tutor-checkout-summary">
			<div class="tutor-checkout-summary-item">
				<div class="tutor-fw-medium"><?php esc_html_e( 'Subtotal', 'tutor' ); ?></div>
				<div class="tutor-fw-bold">
					<?php // echo tutor_get_formatted_price( $subtotal ); //phpcs:ignore ?>
					<?php echo $checkout_manager->is_coupon_applied() ? $summary->discounted_subtotal_with_currency : $summary->subtotal_with_currency; ?>
				</div>
			</div>
			<?php if ( $is_coupon_applicable && ( ! isset( $automatic_coupon ) || ! $automatic_coupon->is_applied ) ) : ?>
			<div class="tutor-checkout-summary-item tutor-have-a-coupon">
				<div><?php esc_html_e( 'Have a coupon?', 'tutor' ); ?></div>
				<button type="button" id="tutor-toggle-coupon-form" class="tutor-btn tutor-btn-link">
					<?php esc_html_e( 'Click here', 'tutor' ); ?>
				</button>
			</div>
			<div class="tutor-apply-coupon-form tutor-d-none">
				<input type="text" name="coupon_code" placeholder="<?php esc_html_e( 'Add coupon code', 'tutor' ); ?>">
				<button type="button" class="tutor-btn tutor-btn-secondary" data-object-ids="<?php echo esc_attr( implode( ',', $object_ids ) ); ?>"><?php esc_html_e( 'Apply', 'tutor' ); ?></button>
			</div>
			<div class="tutor-checkout-summary-item tutor-checkout-coupon-wrapper tutor-d-none">
				<div class="tutor-checkout-coupon-badge tutor-has-delete-button">
					<i class="tutor-icon-tag" area-hidden="true"></i>
					<span></span>
					<button type="button" id="tutor-checkout-remove-coupon">
						<i class="tutor-icon-times" area-hidden="true"></i>
					</button>
				</div>
				<div class="tutor-fw-bold tutor-discount-amount"></div>
			</div>
			<?php endif; ?>
			<div class="tutor-checkout-summary-item">
				<button type="button" class="tutor-show-tax-rates">
					<span><?php esc_html_e( 'Tax', 'tutor' ); ?></span>
				</button>
				<div><?php echo $summary->taxable_amount_with_currency; //phpcs:ignore?></div>
			</div>

			<div class="tutor-tax-breakdown-modal tutor-d-none">
				<div class="tutor-tax-breakdown-modal__backdrop"></div>
				<div class="tutor-tax-breakdown-modal__content">
				<div class="tutor-tax-breakdown-modal__header">
					<h6><?php esc_html_e( 'Tax rates', 'tutor' ); ?></h6>
					<button type="button"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.686 7.6a.65.65 0 0 1 0-.911.629.629 0 0 1 .898 0L12 11.165l4.416-4.476a.629.629 0 0 1 .898 0 .65.65 0 0 1 0 .91l-4.416 4.477 4.266 4.325a.65.65 0 0 1 0 .91.63.63 0 0 1-.898 0L12 12.986l-4.266 4.325a.629.629 0 0 1-.898 0 .65.65 0 0 1 0-.91l4.266-4.325-4.416-4.477Z" fill="currentColor"/></svg></button>
				</div>

					<?php foreach ( $course_list as $course ) : ?>
						<div class="tutor-tax-breakdown-modal__item">
							<span><?php echo esc_html( $course->post_title ); ?></span>
							<span><?php echo $course->tax_rate . '%'; ?></span>
							<span><?php echo $course->taxable_amount_with_currency; ?></span>
						</div>
						<?php endforeach; ?>

						<div class="tutor-tax-breakdown-modal__item">
							<span><strong><?php echo esc_html_e( 'Total tax', 'tutor' ); ?></strong></span>
							<span></span>
							<span><?php echo $summary->taxable_amount_with_currency; ?></span>
						</div>
					</div>
				</div>
		</div>

		<div class="tutor-checkout-detail-item">
			<div class="tutor-checkout-summary-item">
				<div class="tutor-fw-medium"><?php esc_html_e( 'Grand Total', 'tutor' ); ?></div>
				<div class="tutor-fw-bold tutor-checkout-grand-total"><?php echo $summary->total_with_currency; //phpcs:ignore?></div>
			</div>

			<input type="hidden" name="object_ids" value="<?php echo esc_attr( implode( ',', $object_ids ) ); ?>">
			<input type="hidden" name="order_type" value="<?php echo esc_attr( $order_type ); ?>">
		</div>
	</div>
</div>

<script>
	window.addEventListener('DOMContentLoaded', () => {
		const modal = document.querySelector('.tutor-tax-breakdown-modal');
		const closeButton = modal.querySelector('.tutor-tax-breakdown-modal__header > button');
		const showModalButton = document.querySelector('.tutor-show-tax-rates');

		showModalButton.addEventListener('click', () => {
			modal.classList.remove('tutor-d-none');
		});

		closeButton.addEventListener('click', () => {
			modal.classList.add('tutor-d-none');
		})
	})
</script>
