<?php
/**
 * Checkout info template
 *
 * @package Tutor\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

use Tutor\Ecommerce\CartController;
use Tutor\Ecommerce\CheckoutController;
use Tutor\Ecommerce\Settings;
use Tutor\Ecommerce\Tax;
use TUTOR\Input;
use Tutor\Models\CouponModel;
use Tutor\Models\OrderModel;


$coupon_model        = new CouponModel();
$cart_controller     = new CartController( false );
$checkout_controller = new CheckoutController( false );
$get_cart            = $cart_controller->get_cart_items();
$courses             = $get_cart['courses'];
$total_count         = $courses['total_count'];
$course_list         = $courses['results'];

$plan_id   = (int) Input::sanitize_request_data( 'plan' );
$plan_info = apply_filters( 'tutor_checkout_plan_info', new stdClass(), $plan_id );

// Contains Course/Bundle/Plan ids.
$object_ids = array();
$order_type = ( $plan_id && $plan_info )
			? OrderModel::TYPE_SUBSCRIPTION
			: OrderModel::TYPE_SINGLE_ORDER;

$coupon_code            = Input::sanitize_request_data( 'coupon_code', '' );
$has_manual_coupon_code = ! empty( $coupon_code );
$show_coupon_box        = Settings::is_coupon_usage_enabled();

$is_tax_included_in_price = Tax::is_tax_included_in_price();
$tax_rate                 = Tax::get_user_tax_rate( get_current_user_id() );
?>

<div class="tutor-checkout-details">
	<div class="tutor-checkout-details-inner">
		<h5 class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-border-bottom tutor-pb-8">
			<?php esc_html_e( 'Order Details', 'tutor' ); ?>
		</h5>
		<div class="tutor-checkout-detail-item">
			<div class="tutor-checkout-courses">
				<?php
				// Subscription plan checkout item.
				if ( isset( $plan_info->plan_name, $plan_info->regular_price ) ) {
					$checkout_data   = $checkout_controller->prepare_checkout_items( $plan_info->id, $order_type, $coupon_code );
					$enrollment_fee  = floatval( $plan_info->enrollment_fee );
					$show_coupon_box = $plan_info->in_sale_price ? false : true;

					$plan_course_id        = apply_filters( 'tutor_subscription_course_by_plan', $plan_id );
					$plan_course           = get_post( $plan_course_id );
					$plan_course_thumbnail = get_tutor_course_thumbnail_src( 'post-thumbnail', $plan_course_id );

					/**
					 * Plan item details.
					 * User can purchase only one plan at a time.
					 */
					$item = $checkout_data->items[0];

					array_push( $object_ids, $plan_info->id );
					?>
				<div class="tutor-checkout-course-item">
					<div class="tutor-checkout-course-plan-badge">
						<?php echo esc_html( $item->item_name ); ?>
					</div>
					<div class="tutor-checkout-course-content">
						<div class="tutor-d-flex tutor-flex-column tutor-gap-1">
							<div class="tutor-checkout-course-thumb-title">
								<img src="<?php echo esc_url( $plan_course_thumbnail ); ?>" alt="<?php echo esc_attr( $plan_course->post_title ); ?>" />
								<h6 class="tutor-checkout-course-title">
									<a href="<?php echo esc_url( get_the_permalink( $plan_course ) ); ?>">
										<?php echo esc_html( $plan_course->post_title ); ?>
									</a>
								</h6>
							</div>
							<?php if ( $item->is_coupon_applied ) : ?>
							<div class="tutor-checkout-coupon-badge">
								<i class="tutor-icon-tag" area-hidden="true"></i>
								<span><?php echo esc_html( $checkout_data->coupon_title ); ?></span>
							</div>
							<?php endif; ?>
						</div>

						<div class="tutor-text-right">
							<div class="tutor-fw-bold">
								<?php tutor_print_formatted_price( $item->display_price ); ?>
							</div>
							<?php if ( $item->sale_price || $item->discount_price ) : ?>
							<div class="tutor-checkout-discount-price">
								<?php tutor_print_formatted_price( $item->regular_price ); ?>
							</div>
							<?php endif; ?>
							<div class="tutor-fs-7 tutor-color-hints">
								<?php
								echo esc_html(
									$plan_info->recurring_value > 1
									? sprintf(
										/* translators: %s: value, %s: name */
										__( '/%1$s %2$s', 'tutor-pro' ),
										$plan_info->recurring_value,
										$plan_info->recurring_interval . ( $plan_info->recurring_value > 1 ? 's' : '' )
									)
									:
									sprintf(
										/* translators: %s: recurring interval */
										__( '/%1$s', 'tutor-pro' ),
										$plan_info->recurring_interval . ( $plan_info->recurring_value > 1 ? 's' : '' )
									)
								);
								?>
							</div>
						</div>
					</div>
					<?php if ( $enrollment_fee > 0 ) : ?>
						<div class="tutor-checkout-enrollment-fee">
							<div class="tutor-fs-6 tutor-color-black">
								<?php echo esc_html_e( 'Enrollment Fee', 'tutor' ); ?>
							</div>
							<div class="tutor-text-right">
								<div class="tutor-fw-bold">
									<?php tutor_print_formatted_price( $enrollment_fee ); ?>
								</div>
							</div>
						</div>
					<?php endif; ?>
				</div>
				<!-- end subscription plan checkout item -->
					<?php
				} else {
					/**
					 * Course and bundle checkout items.
					 */
					if ( is_array( $course_list ) && count( $course_list ) ) :
						$course_ids    = array_column( $course_list, 'ID' );
						$checkout_data = $checkout_controller->prepare_checkout_items( $course_ids, $order_type, $coupon_code );
						?>
						<?php
						foreach ( $checkout_data->items as $item ) :
							$course           = get_post( $item->item_id );
							$course_thumbnail = get_tutor_course_thumbnail_src( 'post-thumbnail', $course->ID );
							array_push( $object_ids, $item->item_id );
							?>
							<div class="tutor-checkout-course-item" data-course-id="<?php echo esc_attr( $item->item_id ); ?>">
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
									<div class="tutor-d-flex tutor-flex-column tutor-gap-1">
										<div class="tutor-checkout-course-thumb-title">
											<img src="<?php echo esc_url( $course_thumbnail ); ?>" alt="<?php echo esc_attr( $course->post_title ); ?>" />
											<h6 class="tutor-checkout-course-title">
												<a href="<?php echo esc_url( get_the_permalink( $course ) ); ?>">
													<?php echo esc_html( $course->post_title ); ?>
												</a>
											</h6>
										</div>
										<div class="tutor-checkout-coupon-badge <?php echo esc_attr( $item->is_coupon_applied ? '' : 'tutor-d-none' ); ?>">
											<i class="tutor-icon-tag" area-hidden="true"></i>
											<span><?php echo esc_html( $item->is_coupon_applied ? $checkout_data->coupon_title : '' ); ?></span>
										</div>
									</div>
									<div class="tutor-text-right">
										<div class="tutor-fw-bold">
											<?php tutor_print_formatted_price( $item->display_price ); ?>
										</div>
										<?php if ( $item->sale_price || $item->discount_price ) : ?>
										<div class="tutor-checkout-discount-price">
											<?php tutor_print_formatted_price( $item->regular_price ); ?>
										</div>
										<?php endif; ?>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					<?php else : ?>
						<?php tutor_utils()->tutor_empty_state( tutor_utils()->not_found_text() ); ?>
					<?php endif; ?>
				<?php } ?>
			</div>
		</div>

		<div class="tutor-checkout-detail-item tutor-checkout-summary">
			<div class="tutor-checkout-summary-item">
				<div class="tutor-fw-medium"><?php esc_html_e( 'Subtotal', 'tutor' ); ?></div>
				<div class="tutor-fw-bold">
					<?php tutor_print_formatted_price( $checkout_data->subtotal_price ); ?>
				</div>
			</div>

					<?php if ( $checkout_data->sale_discount > 0 ) : ?>
			<div class="tutor-checkout-summary-item">
				<div><?php esc_html_e( 'Sale discount', 'tutor' ); ?></div>
				<div class="tutor-fw-bold">
					- <?php tutor_print_formatted_price( $checkout_data->sale_discount ); ?>
				</div>
			</div>
			<?php endif ?>

					<?php if ( $show_coupon_box && ! $checkout_data->is_coupon_applied ) : ?>
			<div class="tutor-checkout-summary-item tutor-have-a-coupon">
				<div><?php esc_html_e( 'Have a coupon?', 'tutor' ); ?></div>
				<button type="button" id="tutor-toggle-coupon-button" class="tutor-btn tutor-btn-link">
						<?php esc_html_e( 'Click here', 'tutor' ); ?>
				</button>
			</div>
			<div class="tutor-apply-coupon-form tutor-d-none">
				<input type="text" name="coupon_code"
						<?php if ( 'manual' === $checkout_data->coupon_type && $checkout_data->is_coupon_applied ) : ?>
						value="<?php echo esc_attr( $coupon_code ); ?>"
				<?php endif; ?>
				placeholder="<?php esc_html_e( 'Add coupon code', 'tutor' ); ?>">
				<button type="button" id="tutor-apply-coupon-button" class="tutor-btn tutor-btn-secondary" data-object-ids="<?php echo esc_attr( implode( ',', $object_ids ) ); ?>"><?php esc_html_e( 'Apply', 'tutor' ); ?></button>
			</div>
			<?php endif; ?>

			<div class="tutor-checkout-summary-item tutor-checkout-coupon-wrapper <?php echo esc_attr( $checkout_data->is_coupon_applied ? '' : 'tutor-d-none' ); ?>">
				<div class="tutor-checkout-coupon-badge tutor-has-delete-button">
					<i class="tutor-icon-tag" area-hidden="true"></i>
					<span><?php echo esc_html( $checkout_data->coupon_title ); ?></span>

					<?php if ( 'manual' === $checkout_data->coupon_type && $checkout_data->is_coupon_applied ) : ?>
					<button type="button" id="tutor-checkout-remove-coupon" class="tutor-btn">
						<i class="tutor-icon-times" area-hidden="true"></i>
					</button>
					<?php endif; ?>
				</div>
				<div class="tutor-fw-bold tutor-discount-amount">-<?php tutor_print_formatted_price( $checkout_data->coupon_discount ); ?></div>
			</div>

					<?php
					if ( Tax::is_tax_configured() && $tax_rate > 0 && ! $is_tax_included_in_price ) :
						?>
			<div class="tutor-checkout-summary-item">
				<div><?php esc_html_e( 'Tax', 'tutor' ); ?></div>
				<div class="tutor-fw-bold"><?php tutor_print_formatted_price( $checkout_data->tax_amount ); ?></div>
			</div>
					<?php endif; ?>
		</div>

		<div class="tutor-pt-12 tutor-pb-20">
			<div class="tutor-checkout-summary-item">
				<div class="tutor-fw-medium"><?php esc_html_e( 'Grand Total', 'tutor' ); ?></div>
				<div class="tutor-fs-5 tutor-fw-bold tutor-checkout-grand-total">
					<?php tutor_print_formatted_price( $checkout_data->total_price ); ?>
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
						echo esc_html( sprintf( __( '(Incl. Tax %s)', 'tutor' ), tutor_get_formatted_price( $checkout_data->tax_amount ) ) );
						?>
					</div>
							<?php endif ?>
			</div>

					<?php if ( 'manual' === $checkout_data->coupon_type && $checkout_data->is_coupon_applied ) : ?>
				<input type="hidden" name="coupon_code" value="<?php echo esc_attr( $coupon_code ); ?>">
			<?php endif; ?>
			<input type="hidden" name="object_ids" value="<?php echo esc_attr( implode( ',', $object_ids ) ); ?>">
			<input type="hidden" name="order_type" value="<?php echo esc_attr( $order_type ); ?>">
		</div>
	</div>
</div>
