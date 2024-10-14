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
					if ( is_array( $course_list ) && count( $course_list ) ) :
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
										<div class="tutor-checkout-coupon-badge <?php echo esc_attr( $has_automatic_coupon ? '' : 'tutor-d-none' ); ?>">
											<i class="tutor-icon-tag" area-hidden="true"></i>
											<span><?php echo esc_html( $has_automatic_coupon ? $automatic_coupon->coupon_title : '' ); ?></span>
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
				<?php endif; ?>
			</div>
		</div>

		<div class="tutor-checkout-detail-item tutor-checkout-summary">
			<div class="tutor-checkout-summary-item">
				<div class="tutor-fw-medium"><?php esc_html_e( 'Subtotal', 'tutor' ); ?></div>
				<div class="tutor-fw-bold">
					<?php echo tutor_get_formatted_price( $subtotal ); //phpcs:ignore?>
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
<<<<<<< Updated upstream
				<div class="tutor-fw-bold tutor-discount-amount"></div>
=======
				<div class="tutor-fw-bold tutor-discount-amount">-<?php echo esc_html( tutor_get_formatted_price( $applied_coupon->deducted_price ) ); ?></div>
			</div>

			<?php
			if ( Tax::is_tax_configured() && $tax_rate > 0 && ! $is_tax_included_in_price ) :
				?>
			<div class="tutor-checkout-summary-item">
				<div><?php esc_html_e( 'Tax', 'tutor' ); ?></div>
				<div class="tutor-fw-bold"><?php echo tutor_get_formatted_price( $tax_amount ); //phpcs:ignore?></div>
>>>>>>> Stashed changes
			</div>
			<?php endif; ?>
			<!-- <div class="tutor-checkout-summary-item">
				<div><?php esc_html_e( 'Tax', 'tutor' ); ?></div>
				<div><?php echo tutor_get_formatted_price( $tax_amount ); //phpcs:ignore?></div>
			</div> -->
		</div>

		<div class="tutor-checkout-detail-item">
			<div class="tutor-checkout-summary-item">
				<div class="tutor-fw-medium"><?php esc_html_e( 'Grand Total', 'tutor' ); ?></div>
<<<<<<< Updated upstream
				<div class="tutor-fw-bold tutor-checkout-grand-total"><?php echo tutor_get_formatted_price( $subtotal + $tax_amount ); //phpcs:ignore?></div>
=======
				<div class="tutor-fw-bold tutor-checkout-grand-total">
					<?php
						$grand_total = $grand_total + ( $is_tax_included_in_price ? 0 : $tax_amount );
						echo tutor_get_formatted_price( $grand_total ); //phpcs:ignore
					?>
				</div>
			</div>
			<div class="tutor-checkout-summary-item">
				<div></div>
			<?php
			if ( Tax::is_tax_configured() && $tax_rate > 0 && $is_tax_included_in_price ) :
				?>
					<div class="tutor-fs-7 tutor-color-muted">
						<?php
						/* translators: %s: tax amount */
						echo esc_html( sprintf( __( '(Incl. Tax %s)', 'tutor' ), tutor_get_formatted_price( $tax_amount ) ) );
						?>
					</div>
					<?php endif ?>
>>>>>>> Stashed changes
			</div>

			<input type="hidden" name="object_ids" value="<?php echo esc_attr( implode( ',', $object_ids ) ); ?>">
			<input type="hidden" name="order_type" value="<?php echo esc_attr( $order_type ); ?>">
		</div>
	</div>
</div>
