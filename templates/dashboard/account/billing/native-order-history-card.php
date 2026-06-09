<?php
/**
 * Native: Order card template for billing
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Helpers\ComponentHelper;
use Tutor\Helpers\DateTimeHelper;
use Tutor\Models\OrderModel;

$order_data = $data['order_data'] ?? null;
if ( ! $order_data || ! is_object( $order_data ) ) {
	return;
}

$titles = OrderModel::get_order_history_titles( $order_data );

?>
<div class="tutor-billing-card">
	<div class="tutor-billing-card-left">
		<div class="tutor-billing-card-title">
			<ul class="tutor-pl-1 tutor-m-none">
				<?php foreach ( $titles as $item_title ) : ?>
					<li><span><?php echo esc_html( $item_title ); ?></span></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<div class="tutor-billing-card-details">
			<div class="tutor-billing-card-id">
				#<?php echo esc_html( $order_data->id ); ?>
			</div>

			<span class="tutor-tiny">
				<?php echo esc_html( DateTimeHelper::get_gmt_to_user_timezone_date( $order_data->created_at_gmt ) ); ?>
			</span>

			<?php if ( ! empty( $order_data->payment_method ) ) : ?>
			<span class="tutor-section-separator-vertical tutor-sm-hidden"></span>

			<div class="tutor-billing-card-payment-method">
				<?php ComponentHelper::render_payment_method_badge( $order_data->payment_method ); ?>
			</div>
			<?php endif; ?>
		</div>
	</div>

	<div class="tutor-billing-card-right">
		<?php ComponentHelper::render_status_badge( $order_data->order_status ); ?>

		<div class="tutor-billing-card-amount">
			<?php echo wp_kses( tutor_get_formatted_price( $order_data->total_price ), tutor_price_allowed_html() ); ?>
		</div>

		<?php
		OrderModel::render_pay_button( $order_data );
		do_action( 'tutor_dashboard_invoice_button', $order_data );
		?>
	</div>
</div>
