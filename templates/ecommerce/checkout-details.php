<?php
/**
 * Checkout info template
 *
 * @package Tutor\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

use TUTOR\Input;
use Tutor\Ecommerce\Tax;
use Tutor\Models\OrderModel;
use Tutor\Ecommerce\Settings;
use Tutor\Models\CouponModel;
use Tutor\Ecommerce\CartController;
use Tutor\Ecommerce\CheckoutController;

/**
 * User ID is required.
 * Renders this view only (excluding checkout.php) when the country or state changes via AJAX.
 */
$user_id = apply_filters( 'tutor_checkout_user_id', get_current_user_id() );

$coupon_model        = new CouponModel();
$cart_controller     = new CartController( false );
$checkout_controller = new CheckoutController( false );
$get_cart            = $cart_controller->get_cart_items();
$courses             = $get_cart['courses'];
$total_count         = $courses['total_count'];
$course_id           = (int) Input::sanitize_request_data( 'course_id', 0 );
$course_list         = Settings::is_buy_now_enabled() && $course_id ? array( get_post( $course_id ) ) : $courses['results'];

$plan_id       = (int) Input::sanitize_request_data( 'plan' );
$plan_info     = apply_filters( 'tutor_get_plan_info', null, $plan_id );
$has_plan_info = $plan_id && $plan_info;

// Contains Course/Bundle/Plan ids.
$object_ids = array();
$item_ids   = $has_plan_info ? array( $plan_info->id ) : array_column( $course_list, 'ID' );
$order_type = $has_plan_info ? OrderModel::TYPE_SUBSCRIPTION : OrderModel::TYPE_SINGLE_ORDER;

$coupon_code            = apply_filters( 'tutor_checkout_coupon_code', Input::sanitize_request_data( 'coupon_code', '' ), $order_type, $item_ids );
$has_manual_coupon_code = ! empty( $coupon_code );

$should_calculate_tax     = Tax::should_calculate_tax();
$is_tax_included_in_price = Tax::is_tax_included_in_price();
$tax_rate                 = Tax::get_user_tax_rate( $user_id );

$checkout_data   = $checkout_controller->prepare_checkout_items( $item_ids, $order_type, $coupon_code );
$show_coupon_box = Settings::is_coupon_usage_enabled() && ! $checkout_data->is_coupon_applied;
?>

<div class="tutor-checkout-details">

	<?php do_action( 'tutor_before_checkout_order_details', $course_list ); ?>

	<div class="tutor-checkout-details-inner">
		<h5 class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-border-bottom tutor-pb-8">
			<?php esc_html_e( 'Order Details', 'tutor' ); ?>
		</h5>
		<div class="tutor-checkout-detail-item">
			<div class="tutor-checkout-courses">
				<?php
				// Subscription plan checkout item.
				if ( $plan_info ) {
					$plan_item_template = apply_filters( 'tutor_checkout_plan_item_template', null, $plan_info );
					if ( file_exists( $plan_item_template ) ) {
						require $plan_item_template;
					}
				} else {
					/**
					 * Course and bundle checkout items.
					 */
					if ( is_array( $course_list ) && count( $course_list ) ) :
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
										<?php if ( $checkout_data->total_items > 1 && $item->tax_amount > 0 && $item->tax_collection ) : ?>
										<div class="tutor-fs-8 tutor-color-muted tutor-checkout-incl-tax-label">
											<?php echo esc_html( $item->tax_amount_readable ); ?>
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
				<div>
				<?php
					$sale_discount_label = apply_filters( 'tutor_checkout_sale_discount_label', __( 'Sale discount', 'tutor' ) );
					echo esc_html( $sale_discount_label );
				?>
					</div>
				<div class="tutor-fw-bold">
					- <?php tutor_print_formatted_price( $checkout_data->sale_discount ); ?>
				</div>
			</div>
			<?php endif ?>

			<?php if ( $show_coupon_box ) : ?>
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

					<?php if ( $checkout_data->is_coupon_applied ) : ?>
					<button type="button" id="tutor-checkout-remove-coupon" class="tutor-btn">
						<i class="tutor-icon-times" area-hidden="true"></i>
					</button>
					<?php endif; ?>
				</div>
				<div class="tutor-fw-bold tutor-discount-amount">-<?php tutor_print_formatted_price( $checkout_data->coupon_discount ); ?></div>
			</div>

			<?php if ( $checkout_data->tax_amount > 0 && ! $is_tax_included_in_price ) : ?>
			<div class="tutor-checkout-summary-item tutor-checkout-tax-amount">
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
			<div class="tutor-checkout-summary-item tutor-checkout-incl-tax-label">
				<div></div>
					<?php if ( $checkout_data->tax_amount > 0 && $is_tax_included_in_price ) : ?>
					<div class="tutor-fs-7 tutor-color-muted">
						<?php
						/* translators: %s: tax amount */
						echo esc_html( sprintf( __( '(Incl. Tax %s)', 'tutor' ), tutor_get_formatted_price( $checkout_data->tax_amount ) ) );
						?>
					</div>
					<?php endif ?>
			</div>

			<input type="hidden" name="coupon_code" value="<?php echo esc_attr( $coupon_code ); ?>">
			<input type="hidden" name="object_ids" value="<?php echo esc_attr( implode( ',', $object_ids ) ); ?>">
			<input type="hidden" name="order_type" value="<?php echo esc_attr( $order_type ); ?>">
		</div>

		<?php
		$is_zero_price        = empty( $checkout_data->total_price ) && OrderModel::TYPE_SINGLE_ORDER === $checkout_data->order_type;
		$pay_now_btn_text     = $is_zero_price ? __( 'Enroll Now', 'tutor' ) : __( 'Pay Now', 'tutor' );
		$pay_now_btn_text     = apply_filters( 'tutor_checkout_pay_now_btn_text', $pay_now_btn_text, $checkout_data );
		$show_payment_methods = apply_filters( 'tutor_checkout_show_payment_methods', ! $is_zero_price, $checkout_data );

		$checkout_data->pay_now_btn_text        = $pay_now_btn_text;
		$checkout_data->payment_method_required = $show_payment_methods;
		?>
		<input type="hidden" id="checkout_data" value="<?php echo esc_attr( wp_json_encode( $checkout_data ) ); ?>">
	</div>
</div>
