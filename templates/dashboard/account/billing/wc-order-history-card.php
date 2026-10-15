<?php
/**
 * WooCommerce: Order card template for billing
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

$order_data = $data['order_data'] ?? null;
if ( ! $order_data ) {
	return;
}

$wc_order = wc_get_order( $order_data->ID );

if ( ! $wc_order ) {
	return;
}

$order_id       = $wc_order->get_id();
$total_price    = (float) $wc_order->get_total();
$order_status   = $wc_order->get_status();
$payment_status = $wc_order->get_status();
$payment_method = $wc_order->get_payment_method_title();
$order_date_obj = $wc_order->get_date_created();
$order_date     = $order_date_obj ? $order_date_obj->date( get_option( 'date_format' ) . ', ' . get_option( 'time_format' ) ) : '';

$titles  = array();
$courses = tutor_utils()->get_course_enrolled_ids_by_order_id( $order_id );
if ( tutor_utils()->count( $courses ) ) {
	foreach ( $courses as $course ) {
		if ( empty( $course['course_id'] ) ) {
			continue;
		}

		$titles[] = get_the_title( $course['course_id'] );
	}
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
				<?php echo esc_html( $order_date ); ?>
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
		if ( 'pending' === $order_status ) {
			Button::make()
				->variant( Variant::LINK )
				->tag( 'a' )
				->attr( 'href', $wc_order->get_checkout_payment_url() )
				->label( __( 'Pay', 'tutor' ) )
				->render();
		}

		if ( 'completed' === $order_status ) {
			Button::make()
				->tag( 'button' )
				->label( __( 'Receipt', 'tutor' ) )
				->variant( Variant::LINK )
				->attr( 'type', 'button' )
				->attr( 'class', 'tutor-export-purchase-history' )
				->attr( 'data-order', $order_id )
				->attr( 'data-course-name', implode( ', ', $titles ) )
				->attr( 'data-price', $total_price )
				->attr( 'data-date', $order_date )
				->attr( 'data-status', $order_status )
				->render();
		}
		?>
	</div>
</div>
