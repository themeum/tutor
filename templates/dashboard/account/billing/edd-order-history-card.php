<?php
/**
 * EDD: Order card template for billing
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Button;
use Tutor\Components\Constants\Variant;
use Tutor\Helpers\ComponentHelper;
use Tutor\Helpers\DateTimeHelper;

/**
 * EDD_Payment object.
 *
 * @var \EDD_Payment|null $order_data
 */
$order_data = $data['order_data'] ?? null;
if ( ! $order_data || ! is_object( $order_data ) ) {
	return;
}

$order_id       = $order_data->order->id;
$order_date     = $order_data->order->date_created;
$order_status   = $order_data->order->status;
$payment_method = $order_data->order->gateway;
$total_price    = $order_data->order->total;

$titles = array();
foreach ( $order_data->order->items as $item ) {
	$titles[] = $item->product_name;
}
?>
<div class="tutor-billing-card">
	<div class="tutor-billing-card-left">
		<div class="tutor-billing-card-title">
			<ul class="tutor-pl-1">
				<?php foreach ( $titles as $item_title ) : ?>
					<li><span><?php echo esc_html( $item_title ); ?></span></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<div class="tutor-billing-card-details">
			<div class="tutor-billing-card-id">
				#<?php echo esc_html( $order_id ); ?>
			</div>

			<span class="tutor-tiny">
				<?php echo esc_html( DateTimeHelper::get_gmt_to_user_timezone_date( $order_date ) ); ?>
			</span>

			<span class="tutor-section-separator-vertical tutor-sm-hidden"></span>

			<div class="tutor-billing-card-payment-method">
				<?php ComponentHelper::render_payment_method_badge( $payment_method ); ?>
			</div>
		</div>
	</div>

	<div class="tutor-billing-card-right">
		<?php ComponentHelper::render_status_badge( $order_status ); ?>

		<div class="tutor-billing-card-amount">
			<?php echo wp_kses( tutor_get_formatted_price( $total_price ), tutor_price_allowed_html() ); ?>
		</div>

		<?php
		if ( $order_data->is_recoverable() ) {
			Button::make()
				->tag( 'a' )
				->variant( Variant::LINK )
				->attr( 'href', $order_data->get_recovery_url() )
				->label( __( 'Pay', 'tutor' ) )
				->render();
		}

		/**
		 * EDD Pro has invoice functionality.
		 */
		$has_invoice = function_exists( 'edd_invoices_order_has_invoice' ) && edd_invoices_order_has_invoice( $order_id );
		if ( $has_invoice ) {
			$invoice_url = edd_invoices_get_invoice_url( $order_id );
			Button::make()
				->tag( 'a' )
				->variant( Variant::LINK )
				->attr( 'href', $invoice_url )
				->attr( 'target', '_blank' )
				->label( __( 'Invoice', 'tutor' ) )
				->render();
		}
		?>
	</div>
</div>
