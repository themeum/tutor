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

$plan_id   = (int) Input::sanitize_request_data( 'plan' );
$plan_info = apply_filters( 'tutor_get_plan_info', null, $plan_id );

$has_trial_period = $plan_info ? $plan_info->has_trial_period : false;
$is_trial_used    = false;
if ( $plan_info ) {
	$user_subscription = apply_filters( 'tutor_get_user_plan_subscription', null, $plan_info->id, $user_id );
	$is_trial_used     = $user_subscription && $user_subscription->is_trial_used;

	if ( $has_trial_period && ! $is_trial_used ) {
		$label_interval = $plan_info->trial_value > 1 ? $plan_info->trial_interval . 's' : $plan_info->trial_interval;
		$label_interval = ucwords( $label_interval );

		/* translators: %d: trial value, %s: trial interval */
		$pay_now_btn_label = sprintf( __( 'Start %1$d-%2$s Trial', 'tutor' ), $plan_info->trial_value, $label_interval );
	}
}

// Contains Course/Bundle/Plan ids.
$object_ids = array();
$order_type = ( $plan_id && $plan_info )
			? OrderModel::TYPE_SUBSCRIPTION
			: OrderModel::TYPE_SINGLE_ORDER;

$coupon_code            = Input::sanitize_request_data( 'coupon_code', '' );
$has_manual_coupon_code = ! empty( $coupon_code );
$show_coupon_box        = Settings::is_coupon_usage_enabled();

$is_tax_included_in_price = Tax::is_tax_included_in_price();
$tax_rate                 = Tax::get_user_tax_rate( $user_id );
?>

<div class="tutor-checkout-details">

	<?php if ( $has_trial_period && $is_trial_used ) : ?>
	<div class="tutor-alert tutor-warning">
		<div class="tutor-alert-text">
			<span class="tutor-alert-icon tutor-fs-4 tutor-icon-circle-info tutor-mr-12"></span>
			<span><?php esc_html_e( "You've already claimed your trial. Purchase a plan now to continue your eLearning journey!", 'tutor' ); ?></span>
		</div>
	</div>
	<?php endif; ?>

	<?php
	if ( Settings::is_buy_now_enabled() && $course_id && tutor_utils()->is_enrolled( $course_id, get_current_user_id() ) ) {
		add_filter( 'tutor_checkout_enable_pay_now_btn', '__return_false' );
		?>
		<div class="tutor-alert tutor-warning tutor-d-flex tutor-gap-1">
			<span><?php esc_html_e( 'You\'re already enrolled in this course.', 'tutor' ); ?></span>
			<a href="<?php echo esc_url( get_the_permalink( $course_id ) ); ?>"><?php esc_html_e( 'Start learning!', 'tutor' ); ?></a>
		</div>
		<?php
	}

	if ( ! Settings::is_buy_now_enabled() && count( $course_list ) ) {
		$enrolled_courses = array();
		foreach ( $course_list as $course ) {
			if ( tutor_utils()->is_enrolled( $course->ID, get_current_user_id() ) ) {
				$enrolled_courses[] = $course;
			}
		}

		if ( count( $enrolled_courses ) ) {
			add_filter( 'tutor_checkout_enable_pay_now_btn', '__return_false' );
			?>
			<div class="tutor-alert tutor-warning">
				<div>
					<p class="tutor-mb-8">
					<?php
					if ( count( $enrolled_courses ) > 1 ) {
						esc_html_e( 'You are already enrolled in the following courses. Please remove those from your cart and continue.', 'tutor' );
					} else {
						esc_html_e( 'You are already enrolled in the following course. Please remove that from your cart and continue.', 'tutor' );
					}
					?>
					<a class="tutor-text-decoration-none tutor-color-primary" href="<?php echo esc_url( $cart_controller->get_page_url() ); ?>"><?php esc_html_e( 'View Cart', 'tutor' ); ?></a>
					</p>
					<ul>
					<?php foreach ( $enrolled_courses as $course ) : ?>
						<li><a class="tutor-text-decoration-none tutor-color-primary" href="<?php echo esc_url( get_the_permalink( $course->ID ) ); ?>"><?php echo esc_html( $course->post_title ); ?></a></li>
					<?php endforeach; ?>
					</ul>
				</div>
			</div>
			<?php
		}
	}
	?>
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

					$plan_course_id = apply_filters( 'tutor_subscription_course_by_plan', $plan_id );
					$plan_course    = get_post( $plan_course_id );

					$allow_trial_checkout_without_payment = (bool) tutor_utils()->get_option( 'allow_trial_checkout_without_payment' );
					$skip_payment_for_trial               = $allow_trial_checkout_without_payment && 0 === $checkout_data->total_price && $has_trial_period && ! $is_trial_used;

					add_filter( 'tutor_checkout_show_payment_methods', fn( $bool) => $skip_payment_for_trial ? false : $bool );


					/**
					 * Plan item details.
					 * User can purchase only one plan at a time.
					 */
					$item = $checkout_data->items[0];

					/**
					 * For new trial plan subscriber.
					 * Show price zero and cross line on regular price.
					 *
					 * @since 3.4.0
					 */
					if ( $has_trial_period && ! $is_trial_used ) {
						$item->regular_price = $plan_info->regular_price;
					}

					array_push( $object_ids, $plan_info->id );

					$plan_url = get_the_permalink( $plan_course );
					if ( $plan_info->is_membership_plan ) {
						$plan_url = $plan_info->pricing_page_url;
					}

					$thumbnail_url = get_tutor_course_thumbnail_src( 'post-thumbnail', $plan_course_id );
					$plan_title    = $plan_info->is_membership_plan ? $plan_info->plan_name : $plan_course->post_title;

					$badge_label = $item->item_name;
					?>
				<div class="tutor-checkout-course-item">
					<?php if ( ! $plan_info->is_membership_plan ) { ?>
					<div class="tutor-checkout-course-plan-badge">
						<?php echo esc_html( $badge_label ); ?>
					</div>
					<?php } ?>
					<div class="tutor-checkout-course-content">
						<div class="tutor-d-flex tutor-flex-column tutor-gap-1">
							<div class="<?php echo esc_attr( $plan_info->is_membership_plan ? '' : 'tutor-checkout-course-thumb-title' ); ?>">
								<?php if ( ! $plan_info->is_membership_plan ) { ?>
								<img src="<?php echo esc_url( $thumbnail_url ); ?>" alt="<?php echo esc_attr( $plan_title ); ?>" />
								<?php } ?>

								<?php if ( $plan_info->is_membership_plan ) : ?>
									<svg style="float:left; margin-right:4px" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M5.85714 20H17.8571M5.85714 16H17.8571L18.7143 7L15.2857 10L11.8571 5L8.42857 10L5 7L5.85714 16Z" stroke="#0049F8" stroke-width="1.28571" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
								<?php endif; ?>
								<h6 class="tutor-checkout-course-title tutor-d-flex">
									<a href="<?php echo esc_url( $plan_url ); ?>"> <?php echo esc_html( $plan_title ); ?></a>
								</h6>
							</div>
							<?php if ( $item->is_coupon_applied ) : ?>
							<div class="tutor-checkout-coupon-badge">
								<i class="tutor-icon-tag" area-hidden="true"></i>
								<span><?php echo esc_html( $checkout_data->coupon_title ); ?></span>
							</div>
							<?php endif; ?>
						</div>

						<div class="tutor-text-right tutor-d-flex tutor-align-center">
							<?php if ( $item->sale_price || $item->discount_price || ( $has_trial_period && ! $is_trial_used ) ) : ?>
							<div class="tutor-checkout-discount-price tutor-mr-4">
								<?php tutor_print_formatted_price( $item->regular_price ); ?>
							</div>
							<?php endif; ?>
							<div class="tutor-fw-bold">
								<?php tutor_print_formatted_price( $item->display_price ); ?>
							</div>
							<div class="tutor-fs-7 tutor-color-hints">
								<?php
								echo esc_html(
									$plan_info->recurring_value > 1
									? sprintf(
										/* translators: %s: value, %s: name */
										__( '/%1$s %2$s', 'tutor' ),
										$plan_info->recurring_value,
										$plan_info->recurring_interval . ( $plan_info->recurring_value > 1 ? 's' : '' )
									)
									:
									sprintf(
										/* translators: %s: recurring interval */
										__( '/%1$s', 'tutor' ),
										$plan_info->recurring_interval . ( $plan_info->recurring_value > 1 ? 's' : '' )
									)
								);
								?>
							</div>
						</div>
					</div>
					<?php if ( $enrollment_fee > 0 && ( ! $has_trial_period || $is_trial_used ) ) : ?>
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
					<?php if ( $has_trial_period && ! $is_trial_used ) : ?>
						<div class="tutor-mt-12">
							<div class="tutor-plan-trial-price">
								<div class="tutor-plan-trial-icon-wrapper">
									<svg width="16" height="17" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M7.00031 10.6673C6.87168 10.6575 6.75203 10.5976 6.66698 10.5006L4.66698 8.50064C4.62242 8.40734 4.60788 8.30251 4.62537 8.20061C4.64285 8.0987 4.6915 8.00472 4.76461 7.9316C4.83772 7.85849 4.93171 7.80984 5.03361 7.79236C5.13552 7.77487 5.24034 7.78941 5.33365 7.83397L6.98031 9.48064L12.667 3.83397C12.7603 3.78941 12.8651 3.77487 12.967 3.79236C13.0689 3.80984 13.1629 3.85849 13.236 3.9316C13.3091 4.00472 13.3578 4.0987 13.3753 4.20061C13.3927 4.30251 13.3782 4.40734 13.3336 4.50064L7.33365 10.5006C7.2486 10.5976 7.12894 10.6575 7.00031 10.6673Z" fill="#22A848"/>
										<path d="M8.00007 14.5001C6.92772 14.4984 5.87542 14.2093 4.95269 13.6629C4.02996 13.1166 3.27052 12.3329 2.75341 11.3934C2.36087 10.6932 2.11796 9.91909 2.04007 9.12012C1.91806 7.94817 2.14381 6.76624 2.68917 5.72176C3.23453 4.67728 4.07535 3.8165 5.10674 3.24678C5.80699 2.85424 6.58109 2.61134 7.38007 2.53345C8.17617 2.44856 8.98122 2.52566 9.74674 2.76012C9.81504 2.77376 9.87975 2.80153 9.93669 2.84164C9.99364 2.88175 10.0416 2.93332 10.0774 2.99304C10.1133 3.05275 10.1363 3.1193 10.1449 3.18842C10.1536 3.25754 10.1477 3.3277 10.1276 3.39441C10.1075 3.46111 10.0738 3.52289 10.0284 3.57578C9.98311 3.62867 9.92724 3.67151 9.86439 3.70155C9.80154 3.73158 9.73311 3.74816 9.66349 3.75021C9.59386 3.75226 9.52458 3.73974 9.46007 3.71345C8.81254 3.51838 8.13252 3.45491 7.46007 3.52678C6.79529 3.5943 6.15116 3.79616 5.56674 4.12012C5.00349 4.43085 4.5055 4.84735 4.10007 5.34678C3.68265 5.85555 3.37094 6.44251 3.1832 7.07326C2.99546 7.70401 2.93547 8.36589 3.00674 9.02012C3.07425 9.6849 3.27611 10.329 3.60007 10.9134C3.91081 11.4767 4.32731 11.9747 4.82674 12.3801C5.33551 12.7975 5.92246 13.1093 6.55322 13.297C7.18397 13.4847 7.84584 13.5447 8.50007 13.4734C9.16485 13.4059 9.80899 13.2041 10.3934 12.8801C10.9567 12.5694 11.4546 12.1529 11.8601 11.6534C12.2775 11.1447 12.5892 10.5577 12.7769 9.92697C12.9647 9.29622 13.0247 8.63434 12.9534 7.98012C12.9401 7.84132 12.9826 7.70294 13.0713 7.59542C13.1601 7.4879 13.2879 7.42004 13.4267 7.40678C13.5655 7.39352 13.7039 7.43594 13.8114 7.52471C13.919 7.61347 13.9868 7.74132 14.0001 7.88012C14.1215 9.05276 13.8947 10.2352 13.3481 11.2797C12.8016 12.3243 11.9594 13.1847 10.9267 13.7534C10.219 14.1621 9.43298 14.4165 8.62007 14.5001H8.00007Z" fill="#22A848"/>
									</svg>

									<?php
										$trial_label = sprintf(
											/* translators: %d: trial value, %s: trial interval, %s: free or not */
											__( '%1$d-%2$s %3$sTrial', 'tutor' ),
											$plan_info->trial_value,
											ucwords( $plan_info->trial_value > 1 ? $plan_info->trial_interval . 's' : $plan_info->trial_interval ),
											$plan_info->trial_fee > 0 ? '' : 'Free '
										);
										echo esc_html( $trial_label );
									?>
								</div>
								<?php if ( $plan_info->trial_fee > 0 ) : ?>
								<div>
									<?php tutor_print_formatted_price( $plan_info->trial_fee ); ?>
								</div>
								<?php endif; ?>
							</div>
						</div>

						<?php if ( $has_trial_period && ! $is_trial_used ) : ?>
							<ul class="tutor-fs-8 tutor-color-muted tutor-pl-20 tutor-mt-8">
								<li>
								<?php
									echo wp_kses(
										sprintf(
											/* translators: %s: tag start, %s: plan price, %s: tag close */
											__( 'After trial, regular plan price %1$s%2$s%3$s will be charged.', 'tutor' ),
											'<strong>',
											tutor_get_formatted_price( $plan_info->regular_price ),
											'</strong>'
										),
										array(
											'strong' => array(),
										)
									);
								?>
								</li>
								<?php
								if ( $enrollment_fee > 0 ) {
									?>
									<li>
									<?php
										echo wp_kses(
											sprintf(
												/* translators: %s: tag start, %s: enrollment fee, %s: tag close */
												__( 'An enrollment fee of %1$s%2$s%3$s will also be charged.', 'tutor' ),
												'<strong>',
												tutor_get_formatted_price( $enrollment_fee ),
												'</strong>'
											),
											array(
												'strong' => array(),
											)
										);
									?>
									</li>
									<?php
								}
								?>
							</ul>
						<?php endif; ?>
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

					<?php if ( ! $has_trial_period && $show_coupon_box && ! $checkout_data->is_coupon_applied ) : ?>
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
			<div class="tutor-checkout-summary-item" data-tax-amount>
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

		<?php
		$is_zero_price    = empty( $checkout_data->total_price ) && OrderModel::TYPE_SINGLE_ORDER === $checkout_data->order_type;
		$pay_now_btn_text = $is_zero_price ? __( 'Enroll Now', 'tutor' ) : __( 'Pay Now', 'tutor' );
		$pay_now_btn_text = apply_filters( 'tutor_checkout_pay_now_btn_text', $pay_now_btn_text, $checkout_data );
		?>
		<input type="hidden" id="pay_now_btn_text" value="<?php echo esc_attr( $pay_now_btn_text ); ?>">
	</div>
</div>
