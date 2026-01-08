<?php
/**
 * Order card template for billing
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Ecommerce\Ecommerce;
use Tutor\Helpers\ComponentHelper;
use Tutor\Helpers\DateTimeHelper;
use TUTOR\Icon;
use Tutor\Models\OrderModel;
?>
<div class="tutor-billing-card">
	<div class="tutor-billing-card-left">
		<div class="tutor-billing-card-title">
			<div class="tutor-hidden tutor-sm-block">
				<div class="tutor-ml-6"><?php echo wp_kses_post( ComponentHelper::order_status_badge( $order->order_status ) ); ?></div>
			</div>
			<ul class="tutor-pl-1">
				<?php
				$items = ( new OrderModel() )->get_order_items_by_id( $order->id );
				foreach ( $items as $item ) :
					$course_id    = $item->id; // For single order course, bundle.
					$object_title = get_the_title( $course_id );
					if ( OrderModel::TYPE_SINGLE_ORDER !== $order->order_type ) {
						$object_id = apply_filters( 'tutor_subscription_course_by_plan', $item->id, $order );
						$plan_info = apply_filters( 'tutor_get_plan_info', null, $item->id );
						if ( $plan_info && isset( $plan_info->is_membership_plan ) && $plan_info->is_membership_plan ) {
							$object_title = $plan_info->plan_name;
						} else {
							$object_title = get_the_title( $object_id );
						}
					}

					?>
					<li><span><?php echo esc_html( $object_title ); ?></span></li>
				<?php endforeach; ?>
			</ul>
			<div class="tutor-sm-hidden">
				<div class="tutor-ml-6"><?php echo wp_kses_post( ComponentHelper::order_status_badge( $order->order_status ) ); ?></div>
			</div>
		</div>
		<div class="tutor-billing-card-details">
			<div class="tutor-billing-card-id">
				#<?php echo esc_html( $order->id ); ?>
			</div>

			<span class="tutor-tiny">
				<?php echo esc_html( DateTimeHelper::get_gmt_to_user_timezone_date( $order->created_at_gmt ) ); ?>
			</span>

			<span class="tutor-section-separator-vertical tutor-sm-hidden"></span>

			<div class="tutor-billing-card-payment-method">
				<?php tutor_utils()->render_svg_icon( Icon::LESSON, 12, 12 ); ?>
				<div class="tutor-sm-hidden">
					<?php echo esc_html( Ecommerce::get_payment_method_label( $order->payment_method ?? '' ) ); ?>
				</div>
			</div>
		</div>
	</div>

	<div class="tutor-billing-card-right">
		<div class="tutor-billing-card-amount">
			<?php echo esc_html( tutor_get_formatted_price( $order->total_price ) ); ?>
		</div>

		<?php
			OrderModel::render_pay_button( $order );
			do_action( 'tutor_dashboard_invoice_button', $order );
		?>
	</div>
</div>
