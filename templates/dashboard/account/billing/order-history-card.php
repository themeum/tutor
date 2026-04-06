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

use Tutor\Helpers\ComponentHelper;
use Tutor\Helpers\DateTimeHelper;
use Tutor\Models\OrderModel;

$titles   = OrderModel::get_order_history_titles( $order );
$pay_link = OrderModel::get_order_history_pay_link( $order );

?>
<div class="tutor-billing-card">
	<div class="tutor-billing-card-left">
		<div class="tutor-billing-card-title">
			<div class="tutor-hidden tutor-sm-block">
				<?php ComponentHelper::render_status_badge( $order->order_status ); ?>
			</div>
			<ul class="tutor-pl-1">
				<?php foreach ( $titles as $item_title ) : ?>
					<li><span><?php echo esc_html( $item_title ); ?></span></li>
				<?php endforeach; ?>
			</ul>
			<div class="tutor-sm-hidden">
				<div class="tutor-ml-6"><?php ComponentHelper::render_status_badge( $order->order_status ); ?></div>
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
				<?php ComponentHelper::render_payment_method_badge( $order->payment_method ); ?>
			</div>
		</div>
	</div>

	<div class="tutor-billing-card-right">
		<div class="tutor-billing-card-amount">
			<?php echo wp_kses( tutor_get_formatted_price( $order->total_price ), tutor_price_allowed_html() ); ?>
		</div>

		<?php
		echo wp_kses(
			$pay_link,
			array(
				'a' => array(
					'href'  => true,
					'class' => true,
				),
			)
		);

		$order->titles = $titles;
		OrderModel::render_billing_receipt_action( $order );
		?>
	</div>
</div>
